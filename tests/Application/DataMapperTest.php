<?php

declare(strict_types=1);

namespace Devscast\Bundle\HexaBundle\Tests\Application;

use PHPUnit\Framework\TestCase;
use Devscast\Bundle\HexaBundle\Application\DataMapper;

/**
 * Class DataMapperTest.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class DataMapperTest extends TestCase
{
    public function testMap(): void
    {
        $source = new class () {
            public string $name = 'John';
            public int $age = 25;
        };
        $destination = new class () {
            public string $name = 'Doe';
            public int $age = 30;
        };

        DataMapper::map($source, $destination);
        $this->assertEquals('John', $destination->name);
    }

    public function testMapWithIgnore(): void
    {
        $source = new class () {
            public string $name = 'John';
            public int $age = 25;
        };
        $destination = new class () {
            public string $name = 'Doe';
            public int $age = 30;
        };

        DataMapper::map($source, $destination, ['name']);

        $this->assertEquals('Doe', $destination->name);
    }

    public function testMapToArray(): void
    {
        $source =  $source = new class () {
            public string $name = 'John';
            public int $age = 25;
        };

        $expectedData = [
            'name' => 'John',
            'age' => 25,
        ];

        $this->assertEquals($expectedData, DataMapper::mapToArray($source));
    }

    public function testMapToArrayWithIgnore(): void
    {
        $source =  $source = new class () {
            public string $name = 'John';
            public int $age = 25;
        };

        $expectedData = [
            'age' => 25,
        ];

        $this->assertEquals($expectedData, DataMapper::mapToArray($source, ['name']));
    }
}
