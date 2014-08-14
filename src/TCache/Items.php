<?php

namespace TCache;

use TCache\Items\Item;

class Items
{

    /** @var TCache */
    private $cache = null;

    function __construct(TCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param null $data
     * @return Item
     */
    public function createItem($data = null)
    {
        $item = $this->cache->getServiceManager()->getEmptyItem();
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
        return $this->cache->getStorage()->saveItem($item);
    }

    /**
     * @param $item
     * @return bool
     */
    public function removeItem($item)
    {
        return $this->cache->getStorage()->removeItem($item);
    }

    /**
     * @param Query $query
     * @return int
     */
    public function getCount($query)
    {
        return $this->cache->getStorage()->getItemsCount($query);
    }

    /**
     * @param Query $query
     * @return array
     */
    public function getQueryResults($query)
    {
        return $this->cache->getStorage()->getItems($query);
    }

    /**
     * @param $field
     * @param $query
     * @param bool $unwind <p>if target field is array - set true</p>
     * @return array|null
     */
    public function getDistinctValues($field, $query, $unwind = false)
    {
        return $this->cache->getStorage()->getDistinctValues($field, $query, $unwind);
    }

    public function getMinMaxValues($field, $query, $unwind = false)
    {
        return $this->cache->getStorage()->getMinMaxValues($field, $query, $unwind);
    }

    /**
     * @return Query
     */
    public function createQuery()
    {
        return $this->cache->getStorage()->createQuery();
    }
}