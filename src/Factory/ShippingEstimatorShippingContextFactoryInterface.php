<?php
namespace Aligent\ShippingEstimatorBundle\Factory;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ShippingBundle\Context\ShippingContextInterface;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;

interface ShippingEstimatorShippingContextFactoryInterface
{
    public function create(
        ShoppingList $shoppingList,
        string $postcode,
        ?Region $region = null,
        ?Country $country = null
    ): ?ShippingContextInterface;
}
