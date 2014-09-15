<?php

namespace Tager\Helpers;


use Tager\Items\Item;
use Tager\View;

class Validator
{
    /** @var View */
    private $cache = null;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function validateItem($item)
    {
        //TODO: протестировать validateItem($item)
        return true;
    }
} 