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
use Oro\Bundle\ShippingBundle\Context\LineItem\Builder\Factory\ShippingLineItemBuilderFactoryInterface;
use Oro\Bundle\ShippingBundle\Context\LineItem\Collection\Factory\ShippingLineItemCollectionFactoryInterface;
use Oro\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingLineItemCollectionInterface;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;

class ShoppingListShippingLineItemConverter implements ShoppingListShippingLineItemConverterInterface
{

    private ShippingLineItemCollectionFactoryInterface $shippingLineItemCollectionFactory;
    private ShippingLineItemBuilderFactoryInterface $shippingLineItemBuilderFactory;

    /**
     * @param ShippingLineItemCollectionFactoryInterface $shippingLineItemCollectionFactory
     * @param ShippingLineItemBuilderFactoryInterface $shippingLineItemBuilderFactory
     */
    public function __construct(
        ShippingLineItemCollectionFactoryInterface $shippingLineItemCollectionFactory,
        ShippingLineItemBuilderFactoryInterface $shippingLineItemBuilderFactory
    ) {
        $this->shippingLineItemCollectionFactory = $shippingLineItemCollectionFactory;
        $this->shippingLineItemBuilderFactory = $shippingLineItemBuilderFactory;
    }

    /**
     * @inheritDoc
     */
    public function convertLineItems(Collection|array $shoppingListLineItems): ShippingLineItemCollectionInterface
    {
        $shippingLineItems = [];

        /** @var LineItem $shoppingListLineItem */
        foreach ($shoppingListLineItems as $shoppingListLineItem) {
            if ($shoppingListLineItem->getProductUnit() === null) {
                $shippingLineItems = [];

                break;
            }

            $builder = $this->shippingLineItemBuilderFactory->createBuilder(
                $shoppingListLineItem->getProductUnit(),
                $shoppingListLineItem->getProductUnit()->getCode(),
                $shoppingListLineItem->getQuantity(),
                $shoppingListLineItem
            );

            if (null !== $shoppingListLineItem->getProduct()) {
                $builder->setProduct($shoppingListLineItem->getProduct());
            }

            $shippingLineItems[] = $builder->getResult();
        }

        return $this->shippingLineItemCollectionFactory->createShippingLineItemCollection($shippingLineItems);
    }
}
