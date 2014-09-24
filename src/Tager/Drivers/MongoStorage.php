<?php

namespace Tager\Drivers;

use Tager\Cache\ICache;
use Tager\Items\Item;
use Tager\Queries\Query;
use Tager\Drivers\Exception\UndefinedQueryLimitException;
use Tager\View;

class MongoStorage
{
    use TMongoStorage;

    /** @var  View */
    private $cache;

    private $defaultWhere = null;


    /** @var ICache */
    private $mastercache = null;
    /** @var ICache */
    private $memcache = null;

    /**
     * @param $cache View
     */
    function __construct($cache)
    {
        $this->cache = $cache;
        $this->mastercache = $this->cache->cache()->master();
        $this->memcache = $this->cache->cache()->memcache();
    }

    public function connectTo(\MongoCollection $collection, $ensureIndexes = true)
    {
        $this->connect($collection);
        if (false !== $ensureIndexes) {
            $this->ensureBasicIndexes();
        }
        return $this;
    }

    /**
     * @param Item $item
     * @return null|Item
     */
    public function saveItem($item)
    {
        $mixres = null;
        if ($item instanceof Item) {
            $extracted = $item->extract();
            if (is_null($extracted)) {
                $this->removeItem($item);
            } else {
                $result = $this->getItemsCollection()->update(
                    [
                        'TcObjectHash' => $extracted['TcObjectHash'],
                        'TcConfigHash' => $extracted['TcConfigHash']
                    ],
                    $extracted,
                    ['upsert' => true]
                );

                if (isset($result['ok']) && $result['ok'] == 1) {
                    if (false !== $item->load($extracted)) {
                        $mixres = $item;
                    }
                }
            }
        }
        return $mixres;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function removeItem($item)
    {
        $mixres = false;
        if ($item instanceof Item) {
            if ($item->getObjectIsLoaded() === true) {

                $document = $item->getLoadedData();

                $result = $this->getItemsCollection()->remove(
                    [
                        'TcObjectHash' => $document['TcObjectHash'],
                        'TcConfigHash' => $document['TcConfigHash']
                    ]
                );

                $mixres = true;
            }
        }
        return $mixres;
    }

    public function ensureBasicIndexes()
    {
        $basicIndexes = $this->getBasicIndexesConfig();
        $this->ensureIndexes($this->getItemsCollection(), $basicIndexes);
    }

    /**
     * @return array
     */
    public function getBasicIndexesConfig()
    {
        $basicIndexes = [
            'TcObjectHash' => [
                'key' => ['TcObjectHash' => 1, 'TcConfigHash' => 1],
                'props' => ['unique' => true, 'dropDups' => true]
            ],
            'TcConfigHash' => [
                'key' => ['TcConfigHash' => 1],
                'props' => []
            ],
            'TcConfigHashCreated' => [
                'key' => ['TcConfigHash' => 1, 'TcDatetimeCreated' => -1],
                'props' => []
            ],
            'TcConfigHashUpdated' => [
                'key' => ['TcConfigHash' => 1, 'TcDatetimeUpdated' => -1],
                'props' => []
            ],
            'TcLastUpdated' => [
                'key' => ['TcDatetimeUpdated' => 1, 'TcConfigHash' => 1],
                'props' => []
            ],
        ];
        foreach ($this->cache->scheme()->getCriterias()->getAll() as $criteriaNext) {
            $basicIndexes['ix_' . $criteriaNext->getName()] = [
                'key' => [$criteriaNext->getName() => 1],
                'props' => []
            ];
        }
        return $basicIndexes;
    }

    /**
     * @return Query
     */
    public function createQuery()
    {
        return $this->cache->sm()->getEmptyQuery();
    }

    /**
     * @param Query $query
     * @return int
     */
    public function getItemsCount($query)
    {
        if (!($query instanceof Query)) {
            $query = $this->getDefaultQuery();
        }

        $queryHash = $query->getHash();
        $configHash = $this->cache->sm()->getHashesHelper()->getConfigHash();
        $result = $this->cache->cache()->cacheGetItemsCount($configHash, $queryHash);
        if (false === $result) {
            $where = $query->extract();
            $result = $this->getItemsCollection()->count($where);
            $this->cache->cache()->cacheSetItemsCount($configHash, $queryHash, $result);
        }
        return $result;
    }

    /**
     * @param Query $query
     * @return array
     * @throws Exception\UndefinedQueryLimitException
     */
    public function getItems($query)
    {
        if (!($query instanceof Query)) {
            $query = $this->getDefaultQuery();
        }

        $queryHash = $query->getHash();
        $configHash = $this->cache->sm()->getHashesHelper()->getConfigHash();
        $result = $this->cache->cache()->cacheGetItems($configHash, $queryHash);

        if (false === $result) {

            $where = $query->extract();

            $ops = [
                ['$match' => $where]
            ];

            if (($skip = $query->getSkip()) > 0) {
                array_push($ops, ['$skip' => $skip]);
            }

            if (($limit = $query->getLimit()) > 0) {
                array_push($ops, ['$limit' => $limit]);
            } else {
                throw new UndefinedQueryLimitException();
            }

            $sort = $query->getSort();
            if (!empty($sort)) {
                array_push($ops, ['$sort' => $sort]);
            }

            $res = $this->getItemsCollection()->aggregate($ops);
            $result = isset($res['ok']) && $res['ok'] == 1 ? $res['result'] : $res;

            $this->cache->cache()->cacheSetItems($configHash, $queryHash, $result);
        }

        return $result;
    }

    /**
     * @param string $field
     * @param Query $query
     * @param bool $unwind
     * @return null | array
     */
    public function getDistinctValues($field, $query, $unwind = false)
    {
        if (!($query instanceof Query)) {
            $query = $this->getDefaultQuery();
        }

        $queryHash = $this->cache->sm()->getHashesHelper()->getAggregationQueryHash($field, $query, $unwind);
        $configHash = $this->cache->sm()->getHashesHelper()->getConfigHash();
        $result = $this->cache->cache()->cacheGetDistinctValues($configHash, $queryHash);

        if (false === $result) {

            $where = $query->extract();

            $ops = [];

            if (!empty($where)) {
                array_push($ops, ['$match' => $where]);
            }

            if ($unwind === true) {
                array_push($ops, ['$unwind' => '$' . $field]);
            }

            array_push($ops, ['$project' => [$field => 1, 'cnt' => ['$add' => [1]]]]);
            array_push($ops, ['$group' => ['_id' => '$' . $field, 'count' => ['$sum' => '$cnt']]]);
            array_push($ops, ['$project' => ['value' => '$_id', 'count' => 1, '_id' => 0]]);

            array_push($ops, ['$sort' => ['value' => 1]]);

            if (($skip = $query->getSkip()) > 0) {
                array_push($ops, ['$skip' => $skip]);
            }

            if (($limit = $query->getLimit()) > 0) {
                array_push($ops, ['$limit' => $limit]);
            }

            $tempResult = $this->getItemsCollection()->aggregate($ops);
            if (isset($tempResult['ok']) && $tempResult['ok'] == 1) {
                $result = $tempResult['result'];
            } else {
                $result = $tempResult;
            }

            $this->cache->cache()->cacheSetDistinctValues($configHash, $queryHash, $result);
        }
        return $result;
    }

    public function getMinMaxValues($field, $query, $unwind = false)
    {
        if (!($query instanceof Query)) {
            $query = $this->getDefaultQuery();
        }

        $queryHash = $this->cache->sm()->getHashesHelper()->getAggregationQueryHash($field, $query, $unwind);
        $configHash = $this->cache->sm()->getHashesHelper()->getConfigHash();
        $result = $this->cache->cache()->cacheGetMinMaxValues($configHash, $queryHash);

        if (false === $result) {

            $where = $query->extract();

            $ops = [];

            if (!empty($where)) {
                array_push($ops, ['$match' => $where]);
            }

            if ($unwind === true) {
                array_push($ops, ['$unwind' => '$' . $field]);
            }

            array_push($ops, ['$group' => ['_id' => 0, 'min' => ['$min' => '$' . $field], 'max' => ['$max' => '$' . $field]]]);
            array_push($ops, ['$project' => ['_id' => 0, 'min' => 1, 'max' => 1]]);

            $tempResult = $this->getItemsCollection()->aggregate($ops);
            if (isset($tempResult['ok']) && $tempResult['ok'] == 1) {
                if (isset($tempResult['result'][0])) {
                    $result = $tempResult['result'][0];
                } else {
                    $result = [];
                }
            } else {
                $result = $tempResult;
            }

            $this->cache->cache()->cacheSetMinMaxValues($configHash, $queryHash, $result);
        }
        return $result;
    }

    /**
     * @return Query
     */
    protected function getDefaultQuery()
    {
        if (is_null($this->defaultWhere)) {
            $this->defaultWhere = $this->cache->sm()->getEmptyQuery();
        }
        return $this->defaultWhere;
    }
}