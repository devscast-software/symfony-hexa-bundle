<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Controller;

/**
 * Class CrudAction.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
enum CrudAction
{
    case CREATE;
    case READ;
    case UPDATE;
    case DELETE;
}
