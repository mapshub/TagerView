<?php

namespace TCache\Storage;

use TCache\Items\Item;
use TCache\Query;
use TCache\Storage\Exception\UndefinedQueryLimitException;
use TCache\TCache;

class MongoStorage
{
    use TMongoStorage {
        TMongoStorage::setItemsCollectionName as setItemsCollectionNameParent;
    }

    /** @var  TCache */
    private $cache;

    private $defaultWhere = null;

    /**
     * @param $cache TCache
     */
    function __construct($cache)
    {
        $this->cache = $cache;
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

    /**
     * @param $itemsCollectionName
     * @return $this
     */
    public function setItemsCollectionName($itemsCollectionName)
    {
        $res = $this->setItemsCollectionNameParent($itemsCollectionName);
        $this->ensureBasicIndexes();
        return $res;
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
        foreach ($this->cache->getCriterias()->getAll() as $criteriaNext) {
            $basicIndexes['ix_' . $criteriaNext->getName()] = [
                'key' => [$criteriaNext->getName() => 1],
                'props' => []
            ];
        }
        return $basicIndexes;
    }

    /**
     * @return \TCache\Query
     */
    public function createQuery()
    {
        return $this->cache->getServiceManager()->getEmptyQuery();
    }

    /**
     * @param Query $query
     * @return int
     */
    public function getItemsCount($query)
    {
        $where = null;
        if ($query instanceof Query) {
            $where = $query->extract();
        } else {
            $where = $this->getDefaultQuery()->extract();
        }
        return $this->getItemsCollection()->count($where);
    }

    /**
     * @param Query $query
     * @return array
     * @throws Exception\UndefinedQueryLimitException
     */
    public function getItems($query)
    {
        $where = null;
        if ($query instanceof Query) {
            $where = $query->extract();
        } else {
            $where = $this->getDefaultQuery()->extract();
        }
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
        return isset($res['ok']) && $res['ok'] == 1 ? $res['result'] : $res;
    }

    /**
     * @param string $field
     * @param Query $query
     * @param bool $unwind
     * @return null | array
     */
    public function getDistinctValues($field, $query, $unwind = false)
    {
        $values = null;

        $where = null;
        if ($query instanceof Query) {
            $where = $query->extract();
        } else {
            $where = $this->getDefaultQuery()->extract();
        }

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
            $values = $tempResult['result'];
        } else {
            $values = $tempResult;
        }

        return $values;
    }

    public function getMinMaxValues($field, $query, $unwind = false)
    {
        $values = null;

        $where = null;
        if ($query instanceof Query) {
            $where = $query->extract();
        } else {
            $where = $this->getDefaultQuery()->extract();
        }

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
                $values = $tempResult['result'][0];
            }
        } else {
            $values = $tempResult;
        }

        return $values;
    }

    /**
     * @return Query
     */
    protected function getDefaultQuery()
    {
        if (is_null($this->defaultWhere)) {
            $this->defaultWhere = $this->cache->getServiceManager()->getEmptyQuery();
        }
        return $this->defaultWhere;
    }
}