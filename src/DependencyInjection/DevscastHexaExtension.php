<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class DevscastHexaExtension.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class DevscastHexaExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');
        $loader->load('maker.xml');
        $loader->load('twig.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('devscast_hexa.configuration', $config);
    }

    public function getAlias(): string
    {
        return 'devscast_hexa';
    }
}
