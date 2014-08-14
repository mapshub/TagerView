<?php

namespace TCache\Storage;


use TCache\Storage\Exception\UndefinedMongoDbNameException;

trait TMongoStorage
{
    private $host = "localhost";
    private $port = "27017";
    private $dbName = null;
    private $login = "";
    private $password = "";
    private $itemsCollectionName = null;

    private $connected = false;

    /** @var \MongoClient */
    private $connection = null;

    /** @var \MongoDB */
    private $db = null;

    private $createIndexMethod = null;

    private function connect()
    {
        if ($this->connected === false || is_null($this->connection)) {
            $this->connection = new \MongoClient("mongodb://{$this->host}:{$this->port}");
        }

        if (($this->connected === false || is_null($this->db))) {
            if (!is_null($this->dbName)) {
                $this->db = $this->connection->selectDB($this->dbName);
                if (!empty($this->login)) {
                    $this->db->authenticate($this->login, $this->password);
                }
            } else {
                throw new UndefinedMongoDbNameException();
            }
        }

        $this->connected = true;
    }

    public function getConnection()
    {
        $this->connect();
        return $this->connection;
    }

    public function setDbName($database)
    {
        if ($this->dbName !== $database) {
            $this->connected = false;
            $this->dbName = $database;
        }
        return $this;
    }

    public function setHost($host)
    {
        $this->connected = false;
        $this->host = $host;
        return $this;
    }

    public function setLogin($login)
    {
        $this->connected = false;
        $this->login = $login;
        return $this;
    }

    public function setPassword($password)
    {
        $this->connected = false;
        $this->password = $password;
        return $this;
    }

    public function setPort($port)
    {
        $this->connected = false;
        $this->port = $port;
        return $this;
    }

    /**
     * @param $itemsCollectionName
     * @return $this
     */
    public function setItemsCollectionName($itemsCollectionName)
    {
        $this->itemsCollectionName = $itemsCollectionName;
        return $this;
    }

    /**
     * @return null
     */
    public function getItemsCollectionName()
    {
        if (is_null($this->itemsCollectionName)) {
            $this->itemsCollectionName = $this->dbName;
        }
        return $this->itemsCollectionName;
    }

    /**
     * @return \MongoDB
     */
    public function getDb()
    {
        $this->connect();
        return $this->db;
    }

    /**
     * @return \MongoCollection
     */
    public function getItemsCollection()
    {
        return $this->getDb()->selectCollection($this->getItemsCollectionName());
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

    /**
     * @return array
     */
    public function dropItemsCollection()
    {
        $res = $this->getDb()->dropCollection($this->getItemsCollectionName());
        $this->itemsCollectionName = null;
        return $res;
    }

    /**
     * @return array
     */
    public function dropDb()
    {
        $res = $this->getDb()->drop();
        $this->db = null;
        $this->connected = false;
        return $res;
    }
} 