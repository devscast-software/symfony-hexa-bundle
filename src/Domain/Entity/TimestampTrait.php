<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Domain\Entity;

/**
 * Trait TimestampTrait.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
trait TimestampTrait
{
    protected ?\DateTimeImmutable $created_at = null;

    protected ?\DateTimeImmutable $updated_at = null;

    public function setCreatedAtWithCurrentTime(): void
    {
        if ($this->created_at !== null) {
            $this->created_at = new \DateTimeImmutable();
        }
    }

    public function setUpdatedAtWithCurrentTime(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface|string|false|null $created_at): self
    {
        $this->created_at = $this->createDateTime($created_at);

        return $this;
    }

    public function createDateTime(\DateTimeInterface|string|false|null $date): ?\DateTimeImmutable
    {
        if (is_string($date)) {
            $datetime = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date);

            return $datetime === false ? null : $datetime;
        } elseif ($date instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($date);
        }

        return null;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface|string|false|null $updated_at): self
    {
        $this->updated_at = $this->createDateTime($updated_at);

        return $this;
    }
}
