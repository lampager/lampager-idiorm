<?php

namespace Lampager\Idiorm\Tests;

use ORM;

class PaginationResultTest extends TestCase
{
    /**
     * @param $expected
     * @param $actual
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
            ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00']);

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
            ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
            ->to_json(JSON_PRETTY_PRINT);

        $expected = <<<'EOD'
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
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Call to undefined method Lampager\Idiorm\PaginationResult::invalid()
     */
    public function testUndefinedMethod()
    {
        lampager(ORM::for_table('posts'))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('id')
            ->seekable()
            ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
            ->invalid();
    }
}
