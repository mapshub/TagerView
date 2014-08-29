<?php

namespace Tager;

use SebastianBergmann\Exporter\Exception;
use Tager\Cache\ICache;

class Cache implements ICache
{
    /** @var View */
    private $cache = null;

    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Cache\TCache\TagerCacheView
     */
    public function master()
    {
        return $this->cache->sm()->getInStorageCache();
    }

    /**
     * @return Cache\Memcache\Memcache
     */
    public function memcache()
    {
        return $this->cache->sm()->getInMemoryCache();
    }

    public function isConnected()
    {
        throw new Exception("Metod " . __CLASS__ . ":" . __METHOD__ . " is deprecated");
    }

    public function setEnabled($flag)
    {
        $this->master()->setEnabled($flag);
        $this->memcache()->setEnabled($flag);
    }

    public function invalidate()
    {
        if ($this->master()->isConnected()) $this->master()->invalidate();
        if ($this->memcache()->isConnected()) $this->memcache()->invalidate();
    }

    public function cacheSetItemsCount($configHash, $queryHash, $count)
    {
        if ($this->master()->isConnected()) $this->master()->cacheSetItemsCount($configHash, $queryHash, $count);
        if ($this->memcache()->isConnected()) $this->memcache()->cacheSetItemsCount($configHash, $queryHash, $count);
    }

    public function cacheSetItems($configHash, $queryHash, $arrItems)
    {
        if ($this->master()->isConnected()) $this->master()->cacheSetItems($configHash, $queryHash, $arrItems);
        if ($this->memcache()->isConnected()) $this->memcache()->cacheSetItems($configHash, $queryHash, $arrItems);
    }

    public function cacheSetDistinctValues($configHash, $queryHash, $arrValues)
    {
        if ($this->master()->isConnected()) $this->master()->cacheSetDistinctValues($configHash, $queryHash, $arrValues);
        if ($this->memcache()->isConnected()) $this->memcache()->cacheSetDistinctValues($configHash, $queryHash, $arrValues);
    }

    public function cacheSetMinMaxValues($configHash, $queryHash, $minMaxData)
    {
        if ($this->master()->isConnected()) $this->master()->cacheSetMinMaxValues($configHash, $queryHash, $minMaxData);
        if ($this->memcache()->isConnected()) $this->memcache()->cacheSetMinMaxValues($configHash, $queryHash, $minMaxData);
    }

    public function cacheGetItemsCount($configHash, $queryHash)
    {
        $result = false;
        if ($this->memcache()->isConnected()) {
            $result = $this->memcache()->cacheGetItemsCount($configHash, $queryHash);
        }
        if ($result === false && $this->master()->isConnected()) {
            $result = $this->master()->cacheGetItemsCount($configHash, $queryHash);
        }
        return $result;
    }

    public function cacheGetItems($configHash, $queryHash)
    {
        $result = false;
        if ($this->memcache()->isConnected()) {
            $result = $this->memcache()->cacheGetItems($configHash, $queryHash);
        }
        if ($result === false && $this->master()->isConnected()) {
            $result = $this->master()->cacheGetItems($configHash, $queryHash);
        }
        return $result;
    }

    public function cacheGetDistinctValues($configHash, $queryHash)
    {
        $result = false;
        if ($this->memcache()->isConnected()) {
            $result = $this->memcache()->cacheGetDistinctValues($configHash, $queryHash);
        }
        if ($result === false && $this->master()->isConnected()) {
            $result = $this->master()->cacheGetDistinctValues($configHash, $queryHash);
        }
        return $result;
    }

    public function cacheGetMinMaxValues($configHash, $queryHash)
    {
        $result = false;
        if ($this->memcache()->isConnected()) {
            $result = $this->memcache()->cacheGetMinMaxValues($configHash, $queryHash);
        }
        if ($result === false && $this->master()->isConnected()) {
            $result = $this->master()->cacheGetMinMaxValues($configHash, $queryHash);
        }
        return $result;
    }
} 