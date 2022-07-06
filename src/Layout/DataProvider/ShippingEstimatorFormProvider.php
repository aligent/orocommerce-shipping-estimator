<?php
/**
 * @category  Aligent
 * @package
 * @author    Chris Rossi <chris.rossi@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\Layout\DataProvider;

use Aligent\ShippingEstimatorBundle\Form\Type\ShippingEstimatorType;
use JetBrains\PhpStorm\ArrayShape;
use Oro\Bundle\LayoutBundle\Layout\DataProvider\AbstractFormProvider;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ShippingEstimatorFormProvider extends AbstractFormProvider
{
    /**
     * @return FormInterface
     */
    public function getEstimatorForm(): FormInterface
    {
        return $this->getForm(
            ShippingEstimatorType::class,
            [],
            $this->getOptions()
        );
    }

    /**
     * @param ShoppingList $shoppingList
     * @return FormView
     */
    public function getEstimatorFormView(ShoppingList $shoppingList): FormView
    {
        return $this->getFormView(
            ShippingEstimatorType::class,
            $this->getFormViewData($shoppingList),
            $this->getOptions()
        );
    }

    /**
     * @return array
     */
    #[ArrayShape(['action' => "string"])]
    private function getOptions(): array
    {
        return ['action' => $this->generateUrl('aligent_shipping_estimate')];
    }

    /**
     * @param ShoppingList $shoppingList
     * @return array
     */
    #[ArrayShape(['shoppingListId' => "int"])]
    private function getFormViewData(ShoppingList $shoppingList): array
    {
        return ['shoppingListId' => $shoppingList->getId()];
    }
}
