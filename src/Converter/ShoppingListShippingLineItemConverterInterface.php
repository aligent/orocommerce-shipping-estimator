<?php
namespace Aligent\ShippingEstimatorBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Oro\Bundle\ShippingBundle\Context\ShippingLineItemInterface;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;

interface ShoppingListShippingLineItemConverterInterface
{
    /**
     * @param Collection<int,LineItem>|LineItem[] $shoppingListLineItems
     * @return ShippingLineItemCollectionInterface<int,ShippingLineItemInterface>
     */
    public function convertLineItems(Collection|array $shoppingListLineItems): ShippingLineItemCollectionInterface;
}
