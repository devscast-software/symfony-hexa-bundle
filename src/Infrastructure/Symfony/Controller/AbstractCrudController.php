<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * class AbstractCrudController.
 *
 * @author bernard-ng <bernard@devscast.tech>
 **/
abstract class AbstractCrudController extends AbstractController
{
    use DeleteCsrfTrait;

    protected const PREFIX = 'admin';

    protected const DOMAIN = 'shared';

    protected const ENTITY = 'default';

    public function handleCommand(object $command, CrudParams $params = new CrudParams()): Response
    {
        return match ($params->action) {
            CrudAction::DELETE => $this->handleDelete($command, $params),
            CrudAction::CREATE, CrudAction::UPDATE => $this->handleWithForm($command, $params),
            default => $this->handleDefault($command, $params)
        };
    }

    public function getViewPath(string $name, bool $override = false): string
    {
        return match (true) {
            $override => $name,
            default => vsprintf('@%s/domain/%s/%s/%s.html.twig', [
                static::PREFIX, static::DOMAIN, static::ENTITY, $name,
            ])
        };
    }

    private function handleDefault(object $command, CrudParams $params = new CrudParams()): Response
    {
        try {
            $this->dispatchSync($command);
            $this->addSuccessFlash('Action done successfully !');
        } catch (\Throwable $e) {
            $this->addSafeMessageExceptionFlash($e);
        }

        return new RedirectResponse((string) $params->redirectUrl, Response::HTTP_SEE_OTHER);
    }

    private function handleWithForm(object $command, CrudParams $params = new CrudParams()): Response
    {
        $request = $this->getCurrentRequest();
        $form = $this->createForm((string) $params->formClass, $command, [
            'action' => $this->generateUrl(
                route: $request->attributes->getString('_route'),
                parameters: (array) $request->attributes->get('_route_params', []),
            ),
        ])->handleRequest($request);

        // used when the form is rendered via modal in a turbe frame
        $turbo = $request->headers->get('Turbo-Frame');

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->dispatchSync($command);
                $this->addSuccessFlash('Action done successfully !');

                return new RedirectResponse((string) $params->redirectUrl, Response::HTTP_SEE_OTHER);
            } catch (\Throwable $e) {
                $turbo !== null ? $form->addError($this->addSafeMessageExceptionError($e)) : $this->addSafeMessageExceptionFlash($e);
                $status = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        }

        return $this->render(
            view: (string) $params->view,
            parameters: [
                'form' => $form,
                'data' => $params->item,
                '_turbo_frame_target' => $turbo,
            ],
            response: new Response(status: $status ?? Response::HTTP_OK)
        );
    }

    private function handleDelete(object $command, CrudParams $params = new CrudParams()): Response
    {
        $isXmlHttpRequest = $this->getCurrentRequest()->isXmlHttpRequest();

        if ($params->item && $this->isDeleteCsrfTokenValid($params->item)) {
            try {
                $this->dispatchSync($command);

                if ($isXmlHttpRequest) {
                    return new JsonResponse(null, Response::HTTP_ACCEPTED);
                }

                $this->addSuccessFlash('Item deleted successfully !');
            } catch (\Throwable $e) {
                if ($isXmlHttpRequest) {
                    return new JsonResponse([
                        'message' => $this->getSafeMessageException($e),
                    ], Response::HTTP_BAD_REQUEST);
                }

                $this->addSafeMessageExceptionFlash($e);
            }
        }

        return new RedirectResponse((string) $params->redirectUrl, Response::HTTP_SEE_OTHER);
    }
}
