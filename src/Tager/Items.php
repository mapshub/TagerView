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
     * @return Item
     */
    public function createBlank()
    {
        return $this->cache->sm()->createBlankItem();
    }

    /**
     * @param null $data
     * @return Item
     */
    public function createWithData($data = null)
    {
        $item = $this->cache->sm()->createBlankItem();
        if (is_array($data) && !empty($data)) {
            $item->setData($data);
        }
        return $item;
    }

    /**
     * @param $doc
     * @return Item
     */
    public function createWithDocument($doc)
    {
        $item = $this->cache->sm()->createBlankItem();
        $item->load($doc);
        return $item;
    }

    /**
     * @param $item
     * @return null|Item
     */
    public function save($item)
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
}