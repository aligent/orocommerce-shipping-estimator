<?php
namespace Aligent\ShippingEstimatorBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ShippingBundle\Context\ShippingLineItem;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;

interface ShoppingListShippingLineItemConverterInterface
{
    /**
     * @param Collection<int,LineItem>|array<LineItem> $shoppingListLineItems
     * @return Collection<int, ShippingLineItem>
     */
    public function convertLineItems(Collection|array $shoppingListLineItems): Collection;
}
