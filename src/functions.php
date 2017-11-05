<?php

use Lampager\Idiorm\Paginator;

if (!function_exists('lampager')) {
    /**
     * @param  ORM       $builder
     * @return Paginator
     */
    function lampager(ORM $builder)
    {
        return Paginator::create($builder);
    }
}
