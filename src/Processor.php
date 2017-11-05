<?php

namespace Lampager\Idiorm;

use IdiormResultSet;
use Lampager\ArrayProcessor;
use Lampager\Contracts\Formatter;
use Lampager\Idiorm\Concerns\HasSnakeAliases;
use Lampager\Query;

/**
 * Class Processor
 *
 * @method static set_default_formatter(callable|Formatter|string $formatter) Override static default formatter.
 * @method static restore_default_formatter() Restore static default formatter.
 * @method $this use_formatter(callable|Formatter|string $formatter) Use custom formatter.
 * @method $this restore_formatter() Restore default formatter.
 *
 * @see AbstractProcessor, ArrayProcessor
 */
class Processor extends ArrayProcessor
{
    use HasSnakeAliases;

    /**
     * Slice rows, like PHP function array_slice().
     *
     * @param  array[]|IdiormResultSet $rows
     * @param  int                     $offset
     * @param  null|int                $length
     * @return mixed
     */
    protected function slice($rows, $offset, $length = null)
    {
        $r = $rows instanceof IdiormResultSet ? $rows->get_results() : $rows;
        $r = array_slice($r, $offset, $length);
        return $rows instanceof IdiormResultSet ? new IdiormResultSet($r) : $r;
    }

    /**
     * Reverse rows, like PHP function array_reverse().
     *
     * @param  array[]|IdiormResultSet $rows
     * @return mixed
     */
    protected function reverse($rows)
    {
        $r = $rows instanceof IdiormResultSet ? $rows->get_results() : $rows;
        $r = array_reverse($r);
        return $rows instanceof IdiormResultSet ? new IdiormResultSet($r) : $r;
    }

    /**
     * Format result.
     *
     * @param  array[]|IdiormResultSet $rows
     * @param  array                   $meta
     * @param  Query                   $query
     * @return PaginationResult
     */
    protected function defaultFormat($rows, array $meta, Query $query)
    {
        return new PaginationResult($rows, $meta);
    }
}
