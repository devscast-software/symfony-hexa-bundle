<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * trait CommandBusAwareDispatchTrait.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
trait CommandBusAwareDispatchTrait
{
    /**
     * @throws \Throwable
     */
    protected function dispatchSync(object $command): ?Envelope
    {
        try {
            if (property_exists($this, 'commandBus')) {
                return $this->commandBus->dispatch($command);
            }

            return $this->getCommandBus()->dispatch($command);
        } catch (HandlerFailedException $e) {
            while ($e instanceof HandlerFailedException) {
                $e = $e->getPrevious();
            }
            if ($e !== null) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * @throws \Throwable
     */
    protected function getHandledResultSync(object $command): mixed
    {
        /** @var Envelope|null $envelope */
        $envelope = $this->dispatchSync($command);

        /** @var HandledStamp|null $stamp */
        $stamp = $envelope?->last(HandledStamp::class);

        return $stamp?->getResult();
    }

    protected function dispatchAsync(object $command): Envelope
    {
        if (property_exists($this, 'commandBus')) {
            return $this->commandBus->dispatch($command);
        }

        return $this->getCommandBus()->dispatch($command);
    }
}
