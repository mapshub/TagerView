<?php

namespace Tager\Drivers;

use Tager\Drivers\Exception\UndefinedMongodbCollectionException;

trait TMongoStorage
{
    /** @var \MongoCollection */
    private $collection = null;

    private $createIndexMethod = null;

    protected function connect(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    public function isConnected()
    {
        return $this->collection instanceof \MongoCollection;
    }

    /**
     * @return null
     */
    public function getItemsCollectionName()
    {
        return $this->getItemsCollection()->getName();
    }

    /**
     * @return \MongoDB
     */
    public function getDb()
    {
        return $this->getItemsCollection()->db;
    }

    /**
     * @return \MongoCollection
     */
    public function getItemsCollection()
    {
        if (is_null($this->collection)) {
            throw new UndefinedMongodbCollectionException();
        }
        return $this->collection;
    }

    /**
     * @param \MongoCollection $collection
     * @param $arrExpectedIndexes
     */
    public function ensureIndexes($collection, $arrExpectedIndexes)
    {
        if (is_null($this->createIndexMethod)) {
            $this->createIndexMethod = method_exists($this->getItemsCollection(), 'createIndex') ? 'createIndex' : 'ensureIndex';
        }
        $method_name = $this->createIndexMethod;

        $arrRealIndexes = $collection->getIndexInfo();
        foreach ($arrRealIndexes as $realIndexNext) {
            if (isset($arrExpectedIndexes[$realIndexNext['name']])) {
                $indexIsInvalid = false;
                if (count($arrExpectedIndexes[$realIndexNext['name']]['key']) !== count($realIndexNext['key'])) {
                    $indexIsInvalid = true;
                }
                foreach ($arrExpectedIndexes[$realIndexNext['name']]['key'] as $propNext => $dirNext) {
                    //TODO: протестировать синх идексов по полям и направлениям сортировки
                    if (!isset($realIndexNext['key'][$propNext]) || $realIndexNext['key'][$propNext] !== $dirNext) {
                        $indexIsInvalid = true;
                    }
                }
                foreach ($arrExpectedIndexes[$realIndexNext['name']]['props'] as $propNext => $valNext) {
                    //TODO: протестировать синх идексов по свойствам индекса
                    if (!isset($realIndexNext[$propNext]) || $realIndexNext[$propNext] !== $valNext) {
                        $indexIsInvalid = true;
                    }
                }
                if ($indexIsInvalid === false) {
                    unset($arrExpectedIndexes[$realIndexNext['name']]);
                } else {
                    $arrExpectedIndexes[$realIndexNext['name']]['invalid'] = true;
                }
            } else {
                //TODO: протестировать удаление несуществующих индексов

                $result = $this->getDb()->command(array(
                    "deleteIndexes" => $collection->getName(),
                    "index" => $realIndexNext['name'],
                ));
                //var_dump($result);
            }
        }

        foreach ($arrExpectedIndexes as $indexName => $params) {
            if (isset($params['invalid']) && $params['invalid'] === true) {
                $this->getDb()->command(array(
                    "deleteIndexes" => $collection->getName(),
                    "index" => $indexName,
                ));
            }
            $params['props']['name'] = $indexName;
            $collection->$method_name($params['key'], $params['props']);
        }
    }
} 