<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Infrastructure\Doctrine\Repository;

use Doctrine\DBAL\Result;

/**
 * Trait NativeQueryTrait.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
trait NativeQueryTrait
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function execute(string $sql, array $data = []): Result
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = $connection->prepare($sql);

        foreach ($data as $key => $value) {
            $statement->bindValue($key, $value);
        }

        return $statement->executeQuery();
    }
}
