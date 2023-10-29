<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Symfony\Controller;

use Devscast\Bundle\HexaBundle\Domain\Entity\HasIdentityInterface;

/**
 * Class CrudParams.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class CrudParams
{
    public function __construct(
        public CrudAction $action = CrudAction::READ,
        public ?HasIdentityInterface $item = null,
        public ?string $formClass = null,
        public ?string $view = null,
        public ?string $redirectUrl = null,
        public bool $hasIndex = true,
        public bool $hasShow = false,
        public bool $overrideView = false
    ) {
    }
}
