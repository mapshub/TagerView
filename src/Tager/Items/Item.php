<?php

namespace Tager\Items;


use Tager\View;
use Tager\Helpers\Corrector;

class Item
{
    /** @var View */
    protected $cache = null;

    protected $isValid = false;

    protected $loadedData = null;

    protected $objectIsLoaded = false;

    protected $data = [
        'TcData' => [],
        'TcDataHash' => "",
        'TcObjectHash' => "",
        'TcDatetimeCreated' => null,
        'TcDatetimeUpdated' => null,
        'TcConfigHash' => "",
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
        if (false !== $this->getObjectIsLoaded() && $this->loadedData['TcDatetimeCreated'] instanceof \MongoDate) {
            $this->data['TcDatetimeCreated'] = $this->loadedData['TcDatetimeCreated'];
        } else {
            $this->data['TcDatetimeCreated'] = $time;
        }

        if (is_array($userData) && !empty($userData)) {
            $dataHash = $this->cache->sm()->getHashesHelper()->getArrayHash($userData);


            if (!is_null($dataHash)) {
                $this->data['TcData'] = $userData;
                $this->data['TcDataHash'] = $dataHash;
                $this->data['TcObjectHash'] = ""; //TcObjectHash устанавливается в extract
                $this->data['TcConfigHash'] = ""; //TcConfigHash устанавливается в extract
                $this->data['TcDatetimeUpdated'] = $time;
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
        if (isset($source['TcData'], $source['TcDataHash'], $source['TcObjectHash'], $source['TcDatetimeCreated'], $source['TcDatetimeUpdated'], $source['TcConfigHash'])) {

            if ($source['TcDatetimeCreated'] instanceof \MongoDate && $source['TcDatetimeUpdated'] instanceof \MongoDate) {
                if (is_array($source['TcData'])) {
                    if ($this->cache->sm()->getHashesHelper()->getArrayHash($source['TcData']) === $source['TcDataHash']) {

                        $this->data['TcData'] = $source['TcData'];
                        $this->data['TcDataHash'] = $source['TcDataHash'];
                        $this->data['TcObjectHash'] = $source['TcObjectHash'];
                        $this->data['TcDatetimeCreated'] = $source['TcDatetimeCreated'];
                        $this->data['TcDatetimeUpdated'] = $source['TcDatetimeUpdated'];
                        $this->data['TcConfigHash'] = $source['TcConfigHash'];

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
        return $this->data['TcData'];
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
                $dbObject['TcObjectHash'] = $this->cache->sm()->getHashesHelper()->getObjectHash($indexes, $dbObject['TcDataHash']);
                $dbObject['TcConfigHash'] = $this->cache->sm()->getHashesHelper()->getConfigHash();
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
            $c = $this->data['TcDatetimeCreated'];
            return $c->sec;
        }
        return null;
    }

    public function setDatetimeCreatedTs($timestamp)
    {
        if ($this->isValid) {
            $newMongoDate = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($timestamp, Corrector::VTYPE_TIMESTAMP);
            $this->data['TcDatetimeCreated'] = $newMongoDate;
        }
    }

    public function getDatetimeUpdatedTs()
    {
        if ($this->isValid) {
            /** @var \MongoDate $u */
            $u = $this->data['TcDatetimeUpdated'];
            return $u->sec;
        }
        return null;
    }

    public function setDatetimeUpdatedTs($timestamp)
    {
        if ($this->isValid) {
            $newMongoDate = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($timestamp, Corrector::VTYPE_TIMESTAMP);
            $this->data['TcDatetimeUpdated'] = $newMongoDate;
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