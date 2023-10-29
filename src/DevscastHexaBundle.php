<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle;

use Devscast\Bundle\HexaBundle\DependencyInjection\DevscastHexaExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * class DevscastHexaBundle.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
class DevscastHexaBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DevscastHexaExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
