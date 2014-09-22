<?php

namespace Tager\Items;


use Tager\View;
use Tager\Helpers\Corrector;

class Item
{
    const TcData = "TcData";
    const TcDataHash = "TcDataHash";
    const TcObjectHash = "TcObjectHash";
    const TcDatetimeCreated = "TcDatetimeCreated";
    const TcDatetimeUpdated = "TcDatetimeUpdated";
    const TcConfigHash = "TcConfigHash";

    /** @var View */
    protected $cache = null;

    protected $isValid = false;

    protected $loadedData = null;

    protected $objectIsLoaded = false;

    protected $data = [
        self::TcData => [],
        self::TcDataHash => "",
        self::TcObjectHash => "",
        self::TcDatetimeCreated => null,
        self::TcDatetimeUpdated => null,
        self::TcConfigHash => "",
    ];

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param Array $userData
     * @return bool
     */
    public function setData($userData)
    {
        $this->isValid = false;
        $this->data = [];
        $time = new \MongoDate();
        if (false !== $this->getObjectIsLoaded() && $this->loadedData[self::TcDatetimeCreated] instanceof \MongoDate) {
            $this->data[self::TcDatetimeCreated] = $this->loadedData[self::TcDatetimeCreated];
        } else {
            $this->data[self::TcDatetimeCreated] = $time;
        }

        if (is_array($userData) && !empty($userData)) {
            $dataHash = $this->cache->sm()->getHashesHelper()->getArrayHash($userData);


            if (!is_null($dataHash)) {
                $this->data[self::TcData] = $userData;
                $this->data[self::TcDataHash] = $dataHash;
                $this->data[self::TcObjectHash] = ""; //TcObjectHash устанавливается в extract
                $this->data[self::TcConfigHash] = ""; //TcConfigHash устанавливается в extract
                $this->data[self::TcDatetimeUpdated] = $time;
                $this->isValid = true;
            }
        }
        return $this->isValid === true ? $this : false;
    }

    /**
     * @param $source
     * @return bool|Item
     */
    public function load($source)
    {
        $this->isValid = false;
        $this->objectIsLoaded = false;
        $this->loadedData = null;
        $this->data = [];
        if (isset($source[self::TcData], $source[self::TcDataHash], $source[self::TcObjectHash], $source[self::TcDatetimeCreated], $source[self::TcDatetimeUpdated], $source[self::TcConfigHash])) {

            if ($source[self::TcDatetimeCreated] instanceof \MongoDate && $source[self::TcDatetimeUpdated] instanceof \MongoDate) {
                if (is_array($source[self::TcData])) {
                    if ($this->cache->sm()->getHashesHelper()->getArrayHash($source[self::TcData]) === $source[self::TcDataHash]) {

                        $this->data[self::TcData] = $source[self::TcData];
                        $this->data[self::TcDataHash] = $source[self::TcDataHash];
                        $this->data[self::TcObjectHash] = $source[self::TcObjectHash];
                        $this->data[self::TcDatetimeCreated] = $source[self::TcDatetimeCreated];
                        $this->data[self::TcDatetimeUpdated] = $source[self::TcDatetimeUpdated];
                        $this->data[self::TcConfigHash] = $source[self::TcConfigHash];

                        $this->isValid = true;
                        $this->objectIsLoaded = true;
                        $this->loadedData = $source;
                    }
                }
            }
        }
        return $this->isValid === true ? $this : false;
    }

    /**
     * @return null|Array
     */
    public function getData()
    {
        return $this->data[self::TcData];
    }

    /**
     * @return null|Array
     */
    public function getLoadedData()
    {
        return $this->loadedData;
    }

    /**
     * @return array|null
     */
    public function extract()
    {
        $dbObject = $this->data;
        if ($this->isValid) {
            if (false !== $this->cache->sm()->getValidatorHelper()->validateItem($this)) {
                $indexes = $this->cache->sm()->getCorrectorHelper()->getIndexes($this->getData());
                $dbObject[self::TcObjectHash] = $this->cache->sm()->getHashesHelper()->getObjectHash($indexes, $dbObject[self::TcDataHash]);
                $dbObject[self::TcConfigHash] = $this->cache->sm()->getHashesHelper()->getConfigHash();
                foreach ($indexes as $key => $value) {
                    $dbObject[$key] = $value;
                }
                return $dbObject;
            }
        }
        return null;
    }

    public function getDatetimeCreatedTs()
    {
        if ($this->isValid) {
            /** @var \MongoDate $c */
            $c = $this->data[self::TcDatetimeCreated];
            return $c->sec;
        }
        return null;
    }

    public function setDatetimeCreatedTs($timestamp)
    {
        if ($this->isValid) {
            $newMongoDate = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($timestamp, Corrector::VTYPE_TIMESTAMP);
            $this->data[self::TcDatetimeCreated] = $newMongoDate;
        }
    }

    public function getDatetimeUpdatedTs()
    {
        if ($this->isValid) {
            /** @var \MongoDate $u */
            $u = $this->data[self::TcDatetimeUpdated];
            return $u->sec;
        }
        return null;
    }

    public function setDatetimeUpdatedTs($timestamp)
    {
        if ($this->isValid) {
            $newMongoDate = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($timestamp, Corrector::VTYPE_TIMESTAMP);
            $this->data[self::TcDatetimeUpdated] = $newMongoDate;
        }
    }

    /**
     * @return boolean
     */
    public function getObjectIsLoaded()
    {
        return $this->objectIsLoaded;
    }
} 