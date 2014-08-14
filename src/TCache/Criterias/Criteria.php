<?php

namespace TCache\Criterias;

use TCache\Criterias;
use TCache\Query;
use TCache\TCache;

class Criteria
{
    /** @var TCache */
    private $cache = null;

    private $name = null;
    private $valuesAttribute = null;
    private $valuesType = null;
    private $tagsMode = false;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param bool $tagsMode
     * @return $this
     */
    public function setTagsMode($tagsMode)
    {
        $this->tagsMode = !!$tagsMode;
        return $this;
    }

    /**
     * @param $valuesAttribute
     * @return $this
     */
    public function setValuesAttribute($valuesAttribute)
    {
        $this->valuesAttribute = $valuesAttribute;
        return $this;
    }

    /**
     * @param $valuesType
     * @return $this
     */
    public function setValuesType($valuesType)
    {
        $this->valuesType = $valuesType;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getTagsMode()
    {
        return $this->tagsMode;
    }

    /**
     * @return string
     */
    public function getValuesAttribute()
    {
        return $this->valuesAttribute;
    }

    /**
     * @return string
     */
    public function getValuesType()
    {
        return $this->valuesType;
    }

    public function getCorrectValue($sourceValue)
    {
        return $this->cache->getCorrector()->getCorrectValue($sourceValue, $this->getValuesType());
    }

    /**
     * @param $query
     * @return array|null
     */
    public function getDistinctValues($query)
    {
        return $this->cache->getStorage()->getDistinctValues($this->getName(), $query, $this->getTagsMode());
    }

    /**
     * @param $query
     * @return array|null
     */
    public function getMinMaxValues($query)
    {
        return $this->cache->getStorage()->getMinMaxValues($this->getName(), $query, $this->getTagsMode());
    }
}