<?php

namespace Tager\Cache\Memcache;

use Tager\Cache\ICache;
use Tager\Helpers\Corrector;
use Tager\View;
use Tager\Cache\Memcache\Exception\MemcacheConnectFailException;
use Tager\Cache\Memcache\Exception\MemcacheNotConnectedException;

class Memcache implements ICache
{
    /** @var View */
    private $cache = null;

    private $memcache = null;

    //default memcache server account
    private $host = "localhost";
    private $port = 11211;
    private $connected = false;
    private $enabled = true;
    //expire after 45 seconds
    private $livingTime = 45;

    public function __construct($cache)
    {
        $this->cache = $cache;
        $this->memcache = new \Memcache();
    }

    public function connectTo($host = "localhost", $port = 11211)
    {
        $this->setHost($host);
        $this->setPort($port);
        if (false === $this->connected) {
            $this->connected = @$this->memcache->connect($host, $port);
            if (false === $this->connected) {
                throw new MemcacheConnectFailException();
            }
        }
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $newHost = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($host, Corrector::VTYPE_STRING);
        if ($newHost !== $this->host) {
            $this->host = $newHost;
            $this->connected = false;
        }
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $newPort = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($port, Corrector::VTYPE_INT);
        if ($newPort !== $this->port) {
            $this->port = $newPort;
            $this->connected = false;
        }
        return $this;
    }

    public function getLivingTime()
    {
        return $this->livingTime;
    }

    public function setLivingTime($livetime)
    {
        $this->livingTime = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($livetime, Corrector::VTYPE_INT);
    }

    public function isConnected()
    {
        return $this->enabled && $this->connected;
    }

    public function setData($key, $value)
    {
        if (false !== $this->isConnected()) {
            $this->memcache->set($key, serialize($value), 0, $this->getLivingTime());
        } else {
            throw new MemcacheNotConnectedException();
        }
    }

    public function getData($key)
    {
        $value = false;

        if (false !== $this->isConnected()) {
            $value = $this->memcache->get($key);
        } else {
            throw new MemcacheNotConnectedException();
        }

        return ($value !== false ? unserialize($value) : false);
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($enabled, Corrector::VTYPE_BOOL);
    }

    public function invalidate()
    {
        $this->memcache->flush();
    }

    public function cacheSetItemsCount($configHash, $queryHash, $count)
    {
        $this->setData($configHash . ":" . $queryHash . ":" . self::ITEMS_COUNT_QUERY, $count);
    }

    public function cacheGetItemsCount($configHash, $queryHash)
    {
        return $this->getData($configHash . ":" . $queryHash . ":" . self::ITEMS_COUNT_QUERY);
    }

    public function cacheSetItems($configHash, $queryHash, $arrItems)
    {
        $this->setData($configHash . ":" . $queryHash . ":" . self::ITEMS_LIST_QUERY, $arrItems);
    }

    public function cacheGetItems($configHash, $queryHash)
    {
        return $this->getData($configHash . ":" . $queryHash . ":" . self::ITEMS_LIST_QUERY);
    }

    public function cacheSetDistinctValues($configHash, $queryHash, $arrValues)
    {
        $this->setData($configHash . ":" . $queryHash . ":" . self::ITEMS_DISTINCT_VALUES_QUERY, $arrValues);
    }

    public function cacheGetDistinctValues($configHash, $queryHash)
    {
        return $this->getData($configHash . ":" . $queryHash . ":" . self::ITEMS_DISTINCT_VALUES_QUERY);
    }

    public function cacheSetMinMaxValues($configHash, $queryHash, $minMaxData)
    {
        $this->setData($configHash . ":" . $queryHash . ":" . self::ITEMS_MIN_MAX_QUERY, $minMaxData);
    }

    public function cacheGetMinMaxValues($configHash, $queryHash)
    {
        return $this->getData($configHash . ":" . $queryHash . ":" . self::ITEMS_MIN_MAX_QUERY);
    }
}