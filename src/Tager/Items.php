<?php

namespace Tager;

use Tager\Items\Item;
use Tager\Queries\Query;

class Items
{

    /** @var View */
    private $cache = null;

    function __construct(View $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param null $data
     * @return Item
     */
    public function createItem($data = null)
    {
        $item = $this->cache->sm()->getEmptyItem();
        if (is_array($data) && !empty($data)) {
            $item->setData($data);
        }
        return $item;
    }

    /**
     * @param $item
     * @return null|Item
     */
    public function saveItem($item)
    {
        return $this->cache->driver()->saveItem($item);
    }

    /**
     * @param $item
     * @return bool
     */
    public function removeItem($item)
    {
        return $this->cache->driver()->removeItem($item);
    }

    /**
     * @param Query $query
     * @return int
     */
    public function getCount($query)
    {
        return $this->cache->driver()->getItemsCount($query);
    }

    /**
     * @param Query $query
     * @return array
     */
    public function getItems($query)
    {
        return $this->cache->driver()->getItems($query);
    }

    /**
     * @param $field
     * @param $query
     * @param bool $unwind <p>if target field is array - set true</p>
     * @return array|null
     */
    public function getDistinctValues($field, $query, $unwind = false)
    {
        return $this->cache->driver()->getDistinctValues($field, $query, $unwind);
    }

    public function getMinMaxValues($field, $query, $unwind = false)
    {
        return $this->cache->driver()->getMinMaxValues($field, $query, $unwind);
    }
}