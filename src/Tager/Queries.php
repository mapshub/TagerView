<?php

namespace Tager;


use Tager\Queries\Query;

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

    /**
     * @param Query $query
     * @return int
     */
    public function findCount($query)
    {
        return $this->cache->driver()->getItemsCount($query);
    }

    /**
     * @param Query $query
     * @return array
     */
    public function findDocuments($query)
    {
        return $this->cache->driver()->getItems($query);
    }

    /**
     * @param $field
     * @param $query
     * @param bool $unwind <p>if target field is array - set true</p>
     * @return array|null
     */
    public function findDistinctValues($field, $query, $unwind = false)
    {
        return $this->cache->driver()->getDistinctValues($field, $query, $unwind);
    }

    public function findMinMaxValues($field, $query, $unwind = false)
    {
        return $this->cache->driver()->getMinMaxValues($field, $query, $unwind);
    }
} 