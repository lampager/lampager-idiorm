<?php

namespace Lampager\Idiorm\Tests;

use ORM;

class ProcessorTest extends TestCase
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
    public function testAscendingForwardStartInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => null,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 11:00:00', 'id' => $this->number(2)],
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testAscendingForwardStartExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => null,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->exclusive()
                ->paginate()
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
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testAscendingForwardExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(4), 'updated_at' => '2017-01-01 11:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
                'has_next' => false,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->exclusive()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testAscendingBackwardStartInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(4), 'updated_at' => '2017-01-01 11:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(3)],
                'has_next' => null,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testAscendingBackwardStartExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(4), 'updated_at' => '2017-01-01 11:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
                'has_next' => null,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->exclusive()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testAscendingBackwardInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => false,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testAscendingBackwardExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => false,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(1)],
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_asc('updated_at')
                ->order_by_asc('id')
                ->seekable()
                ->exclusive()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testDescendingForwardStartInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(4), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => null,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(3)],
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testDescendingForwardStartExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(4), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => null,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->exclusive()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testDescendingForwardInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
                'has_next' => false,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testDescendingForwardExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(1)],
                'has_next' => false,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->exclusive()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testDescendingBackwardStartInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 11:00:00', 'id' => $this->number(2)],
                'has_next' => null,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testDescendingBackwardStartExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(1), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
                'has_next' => null,
                'next_cursor' => null,
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->exclusive()
                ->paginate()
        );
    }

    /**
     * @test
     */
    public function testDescendingBackwardInclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['updated_at' => '2017-01-01 11:00:00', 'id' => $this->number(4)],
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(1)],
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testDescendingBackwardExclusive()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(4), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                ],
                'has_previous' => false,
                'previous_cursor' => null,
                'has_next' => true,
                'next_cursor' => ['updated_at' => '2017-01-01 10:00:00', 'id' => $this->number(5)],
            ],
            lampager(ORM::for_table('posts'))
                ->backward()->limit(3)
                ->order_by_desc('updated_at')
                ->order_by_desc('id')
                ->seekable()
                ->exclusive()
                ->paginate(['id' => '3', 'updated_at' => '2017-01-01 10:00:00'])
        );
    }

    /**
     * @test
     */
    public function testQualifiedColumnOrder()
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => $this->number(3), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(5), 'updated_at' => '2017-01-01 10:00:00'],
                    ['id' => $this->number(2), 'updated_at' => '2017-01-01 11:00:00'],
                ],
                'has_previous' => true,
                'previous_cursor' => ['posts.updated_at' => '2017-01-01 10:00:00', 'posts.id' => $this->number(1)],
                'has_next' => true,
                'next_cursor' => ['posts.updated_at' => '2017-01-01 11:00:00', 'posts.id' => $this->number(4)],
            ],
            lampager(ORM::for_table('posts'))
                ->forward()->limit(3)
                ->order_by_asc('posts.updated_at')
                ->order_by_asc('posts.id')
                ->seekable()
                ->paginate(['posts.id' => '3', 'posts.updated_at' => '2017-01-01 10:00:00'])
        );
    }
}
