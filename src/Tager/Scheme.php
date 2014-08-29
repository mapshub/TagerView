<?php

namespace Tager;


class Scheme
{
    /** @var View */
    private $cache = null;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Criterias
     */
    public function getCriterias()
    {
        return $this->cache->sm()->getCriterias();
    }

    public function validate()
    {
        // TODO: scheme validate()
    }
} 