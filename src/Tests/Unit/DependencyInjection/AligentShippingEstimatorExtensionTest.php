<?php
/**
 * @category  Aligent
 * @author    Bruno Pasqualini <bruno.pasqualini@aligent.com.au>
 * @copyright 2022 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */

namespace Aligent\ShippingEstimatorBundle\Tests\Unit\DependencyInjection;

use Aligent\ShippingEstimatorBundle\DependencyInjection\AligentShippingEstimatorExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class AligentShippingEstimatorExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new AligentShippingEstimatorExtension());

        // Services
        $expectedDefinitions = [
            'Aligent\ShippingEstimatorBundle\Controller\Frontend\Api\AjaxShippingEstimatorController',
            'Aligent\ShippingEstimatorBundle\Factory\ShippingEstimatorShippingContextFactory',
            'Aligent\ShippingEstimatorBundle\Layout\DataProvider\ShippingEstimatorFormProvider',
            'Aligent\ShippingEstimatorBundle\Form\Type\ShippingEstimatorType',
            'Aligent\ShippingEstimatorBundle\Converter\ShoppingListShippingLineItemConverter',
            'Aligent\ShippingEstimatorBundle\Form\EventListener\CountryAndRegionSubscriber'
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}