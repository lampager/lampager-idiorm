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
                    ['id' => '3', 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => '5', 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => '2', 'updated_at' => '2017-01-01 11:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => '1'],
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 11:00:00', 'id' => '4'],
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
