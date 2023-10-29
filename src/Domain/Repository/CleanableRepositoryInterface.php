<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Domain\Repository;

/**
 * interface CleanableRepositoryInterface.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
interface CleanableRepositoryInterface
{
    public function clean(): int;
}
