<?php
/**
 * Class Configuration
 *
 * @category  Aligent
 * @package   Aligent\ShippingEstimatorBundle\DependencyInjection
 * @author    Bruno Pasqualini <bruno.pasqualini@aligent.com.au>
 * @copyright 2022 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'aligent_shipping_estimator';
    const SHIPPING_ESTIMATOR_IS_ENABLED = 'is_enabled';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                self::SHIPPING_ESTIMATOR_IS_ENABLED => ['type' => 'boolean', 'value' => true],
            ]
        );

        return $treeBuilder;
    }

    public static function getConfigKeyByName(string $key): string
    {
        return implode(ConfigManager::SECTION_MODEL_SEPARATOR, [AligentShippingEstimatorExtension::ALIAS, $key]);
    }
}
