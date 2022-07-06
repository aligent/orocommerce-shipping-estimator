<?php
namespace Aligent\ShippingEstimatorBundle\Context;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;

interface ShippingEstimatorShippingContextFactoryInterface
{
    /**
     * @param ShoppingList $shoppingList
     * @param string $postcode
     * @param Region|null $region
     * @param Country|null $country
     * @return ShippingContextInterface|null
     */
    public function create(
        ShoppingList $shoppingList, string $postcode, ?Region $region = null, ?Country $country = null
    ): ?ShippingContextInterface;
}
