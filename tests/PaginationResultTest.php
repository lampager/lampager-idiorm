<?php

namespace Lampager\Idiorm\Tests;

use ORM;

class PaginationResultTest extends TestCase
{
    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    protected function assertResultSame($expected, $actual)
    {
        $this->assertSame(
            json_decode(json_encode($expected), true),
            json_decode(json_encode($actual), true)
        );
    }

    /**
     * @test
     */
    public function testCollectionCall()
    {
        $result = lampager(ORM::for_table('posts'))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('id')
            ->seekable()
            ->paginate(['id' => 3, 'updated_at' => '2017-01-01 10:00:00']);

        $this->assertResultSame(
            3,
            $result->count()
        );
    }

    /**
     * @test
     */
    public function testJsonEncodeWithOption()
    {
        $actual = lampager(ORM::for_table('posts'))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('id')
            ->seekable()
            ->paginate(['id' => 3, 'updated_at' => '2017-01-01 10:00:00'])
            ->to_json(JSON_PRETTY_PRINT);

        $string = <<<'EOD'
{
    "records": [
        {
            "id": "3",
            "updated_at": "2017-01-01 10:00:00"
        },
        {
            "id": "5",
            "updated_at": "2017-01-01 10:00:00"
        },
        {
            "id": "2",
            "updated_at": "2017-01-01 11:00:00"
        }
    ],
    "has_previous": true,
    "previous_cursor": {
        "updated_at": "2017-01-01 10:00:00",
        "id": "1"
    },
    "has_next": true,
    "next_cursor": {
        "updated_at": "2017-01-01 11:00:00",
        "id": "4"
    }
}
EOD;
        $number = <<<'EOD'
{
    "records": [
        {
            "id": 3,
            "updated_at": "2017-01-01 10:00:00"
        },
        {
            "id": 5,
            "updated_at": "2017-01-01 10:00:00"
        },
        {
            "id": 2,
            "updated_at": "2017-01-01 11:00:00"
        }
    ],
    "has_previous": true,
    "previous_cursor": {
        "updated_at": "2017-01-01 10:00:00",
        "id": 1
    },
    "has_next": true,
    "next_cursor": {
        "updated_at": "2017-01-01 11:00:00",
        "id": 4
    }
}
EOD;
        $expected = version_compare(PHP_VERSION, '8.1', '>=') ? $number : $string;
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testUndefinedMethod()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Lampager\Idiorm\PaginationResult::invalid()');

        lampager(ORM::for_table('posts'))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('id')
            ->seekable()
            ->paginate(['id' => 3, 'updated_at' => '2017-01-01 10:00:00'])
            ->invalid();
    }
}
