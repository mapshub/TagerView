<?php

namespace Tager;


class Queries
{
    /** @var View */
    private $cache = null;

    function __construct(View $cache)
    {
        $this->cache = $cache;
    }

    public function create()
    {
        return $this->cache->sm()->getEmptyQuery();
    }
} 