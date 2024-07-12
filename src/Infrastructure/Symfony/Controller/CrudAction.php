<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Controller;

/**
 * Class CrudAction.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
enum CrudAction: string
{
    case CREATE = 'write';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
}
