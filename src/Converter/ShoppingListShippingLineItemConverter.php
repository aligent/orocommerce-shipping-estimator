<?php
/**
 * @category  Aligent
 * @package
 * @author    Brendan Hart <brendan.hart@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\Converter;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ShippingBundle\Context\LineItem\Factory\ShippingLineItemFromProductLineItemFactoryInterface;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;

class ShoppingListShippingLineItemConverter implements ShoppingListShippingLineItemConverterInterface
{
    public function __construct(
        protected ShippingLineItemFromProductLineItemFactoryInterface $shippingLineItemFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function convertLineItems(Collection|array $shoppingListLineItems): Collection
    {
        return $this->shippingLineItemFactory->createCollection($shoppingListLineItems);
    }
}
