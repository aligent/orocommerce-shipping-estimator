<?php
namespace Aligent\ShippingEstimatorBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;

interface ShoppingListShippingLineItemConverterInterface
{
    /**
     * @param Collection|LineItem[] $shoppingListLineItems
     *
     * @return ShippingLineItemCollectionInterface
     */
    public function convertLineItems(Collection|array $shoppingListLineItems): ShippingLineItemCollectionInterface;
}
