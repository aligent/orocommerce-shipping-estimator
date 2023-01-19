<?php
/**
 * Class AligentShippingEstimatorExtension
 *
 * @category  Aligent
 * @package   Aligent\ShippingEstimatorBundle\DependencyInjection
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
namespace Aligent\ShippingEstimatorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AligentShippingEstimatorExtension extends Extension
{
    const ALIAS = 'aligent_shipping_estimator';

    /**
     * @param array<int,mixed> $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->prependExtensionConfig($this->getAlias(), $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('controllers.yml');
    }

    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
