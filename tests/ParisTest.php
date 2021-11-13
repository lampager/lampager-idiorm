<?php

namespace Lampager\Idiorm\Tests;

use Model;

class Post extends Model
{
    public static $_table = 'posts';
}

class ParisTest extends TestCase
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
    public function testAscendingForwardInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(1)],
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 11:00:00', 'id' => $this->number(4)],
            ],
            lampager(Model::factory(Post::class))
                ->forward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }
}
