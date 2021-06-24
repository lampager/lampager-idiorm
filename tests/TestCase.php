<?php

namespace Lampager\Idiorm\Tests;

use ORM;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    private static $initialized = false;

    /**
     * @beforeClass
     */
    public static function initialize()
    {
        if (!self::$initialized) {
            self::$initialized = true;

            ORM::configure('sqlite::memory:');
            ORM::rawExecute('CREATE TABLE posts(id INTEGER PRIMARY KEY AUTOINCREMENT, updated_at TEXT)');

            $table = ORM::forTable('posts');

            $table->create(['id' => 1, 'updated_at' => '2017-01-01 10:00:00'])->save();
            $table->create(['id' => 3, 'updated_at' => '2017-01-01 10:00:00'])->save();
            $table->create(['id' => 5, 'updated_at' => '2017-01-01 10:00:00'])->save();
            $table->create(['id' => 2, 'updated_at' => '2017-01-01 11:00:00'])->save();
            $table->create(['id' => 4, 'updated_at' => '2017-01-01 11:00:00'])->save();
        }
    }
}
