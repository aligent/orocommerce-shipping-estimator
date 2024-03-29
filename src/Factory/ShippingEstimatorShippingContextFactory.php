<?php
/**
 * @category  Aligent
 * @package
 * @author    Brendan Hart <brendan.hart@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\Factory;

use Aligent\ShippingEstimatorBundle\Converter\ShoppingListShippingLineItemConverterInterface;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CurrencyBundle\DependencyInjection\Configuration as CurrencyConfig;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\ShippingBundle\Context\Builder\Factory\ShippingContextBuilderFactoryInterface;
use Oro\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingListTotal;

class ShippingEstimatorShippingContextFactory implements ShippingEstimatorShippingContextFactoryInterface
{
    protected ConfigManager $oroGlobalConfigManager;
    protected ShoppingListShippingLineItemConverterInterface $shippingLineItemConverter;
    protected ?ShippingContextBuilderFactoryInterface $shippingContextBuilderFactory;

    public function __construct(
        ConfigManager $oroGlobalConfigManager,
        ShoppingListShippingLineItemConverterInterface $shippingLineItemConverter,
        ShippingContextBuilderFactoryInterface $shippingContextBuilderFactory = null
    ) {
        $this->shippingLineItemConverter = $shippingLineItemConverter;
        $this->shippingContextBuilderFactory = $shippingContextBuilderFactory;
        $this->oroGlobalConfigManager = $oroGlobalConfigManager;
    }

    public function create(
        ShoppingList $shoppingList,
        string $postcode,
        ?Region $region = null,
        ?Country $country = null
    ): ?ShippingContextInterface {
        if (null === $this->shippingContextBuilderFactory) {
            return null;
        }

        // note: this shopping list shipping context builder was modelled on the OrderShippingContext builder
        // which uses the Order object and ID as the source entity params. The source entity info does not
        // appear to get used in the process but presumably is present for potential logging/tracing purposes
        $shippingContextBuilder = $this->shippingContextBuilderFactory->createShippingContextBuilder(
            $shoppingList,
            (string)$shoppingList->getId()
        );

        $currency = $this->getDefaultCurrency();

        /** @var string $shoppingListSubtotal */
        $shoppingListSubtotal = $this->getShoppingListSubtotal($shoppingList, $currency);
        $subtotal = Price::create(
            $shoppingListSubtotal,
            $currency
        );

        $shippingContextBuilder
            ->setSubTotal($subtotal)
            ->setCurrency($currency);
            // ->setPaymentMethod(null); // no payment method info at shipping estimate stage

        // create a "fake" address for the shipping destination: country, state and postcode will be enough for all
        // current shipping rules
        $shippingAddress = new Address();
        $shippingAddress->setPostalCode($postcode);

        if ($country) {
            $shippingAddress->setCountry($country);
        } else {
            // Fallback to AU as a base
            $shippingAddress->setCountry(new Country('AU'));
        }

        if ($region) {
            $shippingAddress->setRegion($region);
        }
        $shippingContextBuilder->setShippingAddress($shippingAddress);

        $shippingContextBuilder->setWebsite($shoppingList->getWebsite());

        if (null !== $shoppingList->getCustomer()) {
            $shippingContextBuilder->setCustomer($shoppingList->getCustomer());
        }

        if (null !== $shoppingList->getCustomerUser()) {
            $shippingContextBuilder->setCustomerUser($shoppingList->getCustomerUser());
        }

        $convertedLineItems = $this->shippingLineItemConverter->convertLineItems($shoppingList->getLineItems());

        if (!$convertedLineItems->isEmpty()) {
            $shippingContextBuilder->setLineItems($convertedLineItems);
        }

        return $shippingContextBuilder->getResult();
    }

    protected function getShoppingListSubtotal(ShoppingList $shoppingList, string $currency): float
    {
        $shoppingListTotals = $shoppingList->getTotals();

        /** @var ShoppingListTotal $shoppingListTotal_defaultCurrency */
        $shoppingListTotal_defaultCurrency = array_filter(
            $shoppingListTotals->toArray(),
            function ($shoppingListTotal) use ($currency) {

                /** @var ShoppingListTotal $shoppingListTotal */
                return $shoppingListTotal->getCurrency() === $currency;
            }
        );

        /** @var ShoppingListTotal $shoppingListTotal */
        $shoppingListTotal = reset($shoppingListTotal_defaultCurrency);

        return $shoppingListTotal->getSubtotal()->getAmount();
    }

    protected function getDefaultCurrency(): string
    {
        $currencyConfigKey = CurrencyConfig::getConfigKeyByName(CurrencyConfig::KEY_DEFAULT_CURRENCY);

        return $this->oroGlobalConfigManager->get($currencyConfigKey) ?: CurrencyConfig::DEFAULT_CURRENCY;
    }
}
