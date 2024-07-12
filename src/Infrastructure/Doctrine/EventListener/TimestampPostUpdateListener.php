<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Doctrine\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Class TimestampPostUpdateListener.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class TimestampPostUpdateListener
{
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if (method_exists($object, 'setUpdatedAt') && method_exists($object, 'getUpdatedAt')) {
            if ($object->getUpdatedAt() === null) {
                $object->setUpdatedAt(new \DateTimeImmutable());
            }
        }
    }
}
