<?php
/**
 * @category  Aligent
 * @package
 * @author    Brendan Hart <brendan.hart@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\Controller\Frontend\Api;

use Aligent\ShippingEstimatorBundle\Factory\ShippingEstimatorShippingContextFactory;
use Aligent\ShippingEstimatorBundle\Layout\DataProvider\ShippingEstimatorFormProvider;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\ShippingBundle\Provider\Price\ShippingPriceProviderInterface;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Manager\CurrentShoppingListManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Controller for getting the possible shipping methods and costs for a shopping list
 */
class AjaxShippingEstimatorController extends AbstractController
{
    // Form Fields
    const FORM_FIELD_STATE = 'region';
    const FORM_FIELD_COUNTRY = 'country';
    const FORM_FIELD_POSTCODE = 'postcode';
    const FORM_FIELD_SHOPPING_LIST_ID = 'shoppingListId';

    private FeatureChecker $featureChecker;

    protected CurrentShoppingListManager $shoppingListManager;
    protected ShippingPriceProviderInterface $shippingPriceProvider;
    protected ShippingEstimatorFormProvider $shippingEstimatorFormProvider;
    protected ShippingEstimatorShippingContextFactory $shippingEstimatorShippingContextFactory;
    protected TranslatorInterface $translator;

    public function __construct(
        CurrentShoppingListManager $shoppingListManager,
        ShippingPriceProviderInterface $shippingPriceProvider,
        ShippingEstimatorFormProvider $shippingEstimatorFormProvider,
        ShippingEstimatorShippingContextFactory $shippingEstimatorShippingContextFactory,
        TranslatorInterface $translator,
        FeatureChecker $featureChecker,
    ) {
        $this->shoppingListManager = $shoppingListManager;
        $this->shippingPriceProvider = $shippingPriceProvider;
        $this->shippingEstimatorFormProvider = $shippingEstimatorFormProvider;
        $this->shippingEstimatorShippingContextFactory = $shippingEstimatorShippingContextFactory;
        $this->translator = $translator;
        $this->featureChecker = $featureChecker;
    }

    /**
     * Return true if the Shipping Estimator functionality is enabled
     * @return bool
     */
    public function isShippingEstimatorEnabled(): bool
    {
        return $this->featureChecker->isFeatureEnabled('aligent_shipping_estimator');
    }

    /**
     * @Route("/shipping-estimate/", name="aligent_shipping_estimate", options={"expose"= true})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function shippingEstimateAction(Request $request): JsonResponse
    {
        if (!$this->isShippingEstimatorEnabled()) {
            return $this->makeErrorJsonResponse(
                'processing_error',
                $this->translator->trans('aligent.shipping.estimator.action.error.not_enable'),
                []
            );
        }
        // use symfony forms to validate request fields
        $form = $this->shippingEstimatorFormProvider->getEstimatorForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this->getShippingEstimate($form->getData());
            } catch (\RuntimeException $e) {
                return $this->makeErrorJsonResponse(
                    'processing_error',
                    $this->translator->trans('aligent.shipping.estimator.action.error.processing_request'),
                    [$e->getMessage()]
                );
            }
        }

        // return response with details of validation error
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $this->makeErrorJsonResponse(
            'validation_error',
            $this->translator->trans('aligent.shipping.estimator.action.error.validation'),
            $errors
        );
    }

    /**
     * Helper to make a consistent JSON object for error messages
     *
     * @param string $type
     * @param string $title
     * @param array<string|integer, string> $errors
     * @param int $httpCode
     * @return JsonResponse
     */
    public function makeErrorJsonResponse(string $type, string $title, array $errors, int $httpCode = 400): JsonResponse
    {
        $responseData = [
            'error' => [
                'type'          => $type,
                'title'         => $title,
                'error_details' => $errors,
            ],
        ];

        return new JsonResponse($responseData, $httpCode);
    }

    /**
     * @param array<mixed> $requestData
     * @return JsonResponse
     * @throws \Exception
     */
    public function getShippingEstimate(array $requestData): JsonResponse
    {
        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->shoppingListManager->getForCurrentUser($requestData[self::FORM_FIELD_SHOPPING_LIST_ID]);

        // handle case where there is no shopping list or list had no items
        if (!($shoppingList instanceof ShoppingList) || count($shoppingList->getLineItems()) < 1) {
            return $this->makeErrorJsonResponse(
                'cart_empty_error',
                $this->translator->trans(
                    'aligent.shipping.estimator.action.error.shipping_cannot_be_estimated_due_empty_cart'
                ),
                []
            );
        }

        // use the postcode and state to make a fake "shipping" address
        $shippingContext = $this->shippingEstimatorShippingContextFactory->create(
            $shoppingList,
            $requestData[self::FORM_FIELD_POSTCODE],
            $requestData[self::FORM_FIELD_STATE],
            $requestData[self::FORM_FIELD_COUNTRY]
        );

        $shippingMethodViews = $this->shippingPriceProvider
            ->getApplicableMethodsViews($shippingContext)
            ->toArray();

        // transform $shippingMethodViews array into a simplified structure
        $shippingEstimates = $this->simplifyShippingMethodViews($shippingMethodViews);

        $responseData = [
            "shippingEstimates" => $shippingEstimates
        ];

        return new JsonResponse($responseData);
    }

    /**
     * ShippingMethodViews data contains extra fields/structure not needed for the Shipping Estimates display,
     * this will simplify the data
     *
     * @param array<string, array<string, array<string, mixed>>> $shippingMethodViews
     * @return array<integer, array<string, mixed>>
     */
    public function simplifyShippingMethodViews(array $shippingMethodViews): array
    {
        $shippingEstimates = [];

        foreach ($shippingMethodViews as /** @var array $shippingMethod  */ $shippingMethod) {
            if (array_key_exists('types', $shippingMethod) && count($shippingMethod['types'])) {
                foreach ($shippingMethod['types'] as /** @var array $type */ $type) {
                    if (array_key_exists('price', $type) && $type['price'] instanceof Price) {
                        $shippingEstimates[] = [
                            'label' => $type['label'],
                            'price_value' => $type['price']->getValue(),
                            'price_currency' => $type['price']->getCurrency(),
                        ];
                    }
                }
            }
        }

        return $shippingEstimates;
    }
}
