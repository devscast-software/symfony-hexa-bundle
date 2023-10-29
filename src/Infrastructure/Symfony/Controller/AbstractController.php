<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Controller;

use Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Messenger\CommandBusAwareDispatchTrait;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractController.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
abstract class AbstractController extends SymfonyAbstractController
{
    use FlashMessageTrait;
    use CommandBusAwareDispatchTrait;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'translator' => '?' . TranslatorInterface::class,
            'knp_paginator' => '?' . PaginatorInterface::class,
            'logger' => '?' . LoggerInterface::class,
            'event_dispatcher' => '?' . EventDispatcherInterface::class,
            'messenger.default_bus' => '?' . MessageBusInterface::class,
        ]);
    }

    public function getLogger(): LoggerInterface
    {
        /** @var LoggerInterface $logger */
        $logger = $this->container->get('logger');

        return $logger;
    }

    public function getCommandBus(): MessageBusInterface
    {
        /** @var MessageBusInterface $bus */
        $bus = $this->container->get('messenger.default_bus');

        return $bus;
    }

    protected function redirectSeeOther(string $route, array $params = []): RedirectResponse
    {
        return $this->redirectToRoute($route, $params, Response::HTTP_SEE_OTHER);
    }

    protected function createUnprocessableEntityResponse(): Response
    {
        return new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function dispatchEvent(object $event): object
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');

        return $dispatcher->dispatch($event);
    }

    protected function getCurrentRequest(): Request
    {
        /** @var RequestStack $stack */
        $stack = $this->container->get('request_stack');
        $request = $stack->getCurrentRequest();

        if ($request === null) {
            throw new \RuntimeException('unable to get the current request');
        }

        return $request;
    }

    protected function getTranslator(): TranslatorInterface
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');

        return $translator;
    }

    protected function getPaginator(): PaginatorInterface
    {
        /** @var PaginatorInterface $paginator */
        $paginator = $this->container->get('knp_paginator');

        return $paginator;
    }
}
