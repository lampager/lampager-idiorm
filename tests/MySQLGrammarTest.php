<?php

namespace Lampager\Idiorm\Tests;

use NilPortugues\Sql\QueryFormatter\Formatter;
use ORM;
use PHPUnit\Framework\TestCase;

class MySQLGrammarTest extends TestCase
{
    /**
     * @param $expected
     * @param $actual
     */
    protected function assertSqlEquals($expected, $actual)
    {
        $formatter = new Formatter();
        $this->assertEquals($formatter->format($expected), $formatter->format($actual));
    }

    /**
     * @param  ORM    $builder
     * @return string
     */
    protected function toSql(ORM $builder)
    {
        $reflector = new \ReflectionProperty($builder, '_raw_query');
        $reflector->setAccessible(true);
        return $reflector->getValue($builder);
    }

    /**
     * @test
     */
    public function testAscendingForwardStart()
    {
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('created_at')
            ->order_by_asc('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            SELECT * FROM `posts`
            WHERE `user_id` = ? 
            ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
            LIMIT 4
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testAscendingForwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('created_at')
            ->order_by_asc('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testAscendingForwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->forward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('created_at')
            ->order_by_asc('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testAscendingBackwardStart()
    {
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->backward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('created_at')
            ->order_by_asc('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            SELECT * FROM `posts`
            WHERE `user_id` = ?
            ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
            LIMIT 4
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testAscendingBackwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->backward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('created_at')
            ->order_by_asc('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testAscendingBackwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->backward()->limit(3)
            ->order_by_asc('updated_at')
            ->order_by_asc('created_at')
            ->order_by_asc('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testDescendingForwardStart()
    {
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->forward()->limit(3)
            ->order_by_desc('updated_at')
            ->order_by_desc('created_at')
            ->order_by_desc('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            SELECT * FROM `posts`
            WHERE `user_id` = ?
            ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
            LIMIT 4
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testDescendingForwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->forward()->limit(3)
            ->order_by_desc('updated_at')
            ->order_by_desc('created_at')
            ->order_by_desc('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testDescendingForwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->forward()->limit(3)
            ->order_by_desc('updated_at')
            ->order_by_desc('created_at')
            ->order_by_desc('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testDescendingBackwardStart()
    {
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->backward()->limit(3)
            ->order_by_desc('updated_at')
            ->order_by_desc('created_at')
            ->order_by_desc('id')
            ->seekable()
            ->build();
        $this->assertSqlEquals('
            SELECT * FROM `posts`
            WHERE `user_id` = ?
            ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
            LIMIT 4
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testDescendingBackwardInclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->backward()->limit(3)
            ->order_by_desc('updated_at')
            ->order_by_desc('created_at')
            ->order_by_desc('id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` < ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` >= ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testDescendingBackwardExclusive()
    {
        $cursor = ['updated_at' => '', 'created_at' => '', 'id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('user_id', 2))
            ->backward()->limit(3)
            ->order_by_desc('updated_at')
            ->order_by_desc('created_at')
            ->order_by_desc('id')
            ->seekable()
            ->exclusive()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` <= ? OR 
                  `updated_at` = ? AND `created_at` < ? OR 
                  `updated_at` < ?
                )
                ORDER BY `updated_at` DESC, `created_at` DESC, `id` DESC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `user_id` = ? AND (
                  `updated_at` = ? AND `created_at` = ? AND `id` > ? OR 
                  `updated_at` = ? AND `created_at` > ? OR 
                  `updated_at` > ?
                )
                ORDER BY `updated_at` ASC, `created_at` ASC, `id` ASC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }

    /**
     * @test
     */
    public function testQualifiedColumnOrder()
    {
        $cursor = ['posts.updated_at' => '', 'posts.created_at' => '', 'posts.id' => ''];
        $builder = lampager(ORM::forTable('posts')->where_equal('posts.user_id', 2))
            ->forward()->limit(3)
            ->order_by_asc('posts.updated_at')
            ->order_by_asc('posts.created_at')
            ->order_by_asc('posts.id')
            ->seekable()
            ->build($cursor);
        $this->assertSqlEquals('
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `posts`.`user_id` = ? AND (
                  `posts`.`updated_at` = ? AND `posts`.`created_at` = ? AND `posts`.`id` < ? OR
                  `posts`.`updated_at` = ? AND `posts`.`created_at` < ? OR
                  `posts`.`updated_at` < ?
                )
                ORDER BY `posts`.`updated_at` DESC, `posts`.`created_at` DESC, `posts`.`id` DESC
                LIMIT 1
            ) `temporary_table`
            UNION ALL
            SELECT * FROM (
                SELECT * FROM `posts`
                WHERE `posts`.`user_id` = ? AND (
                  `posts`.`updated_at` = ? AND `posts`.`created_at` = ? AND `posts`.`id` >= ? OR
                  `posts`.`updated_at` = ? AND `posts`.`created_at` > ? OR
                  `posts`.`updated_at` > ?
                )
                ORDER BY `posts`.`updated_at` ASC, `posts`.`created_at` ASC, `posts`.`id` ASC
                LIMIT 4
            ) `temporary_table`
        ', $this->toSql($builder));
    }
}
