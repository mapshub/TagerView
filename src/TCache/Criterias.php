<?php

namespace TCache;

use TCache\Criterias\Criteria;

class Criterias
{
    /** @var TCache */
    private $cache = null;

    private $list = [];

    function __construct($cache)
    {
        $this->cache = $cache;
    }


    /**
     * @param $name
     * @param string $valuesType
     * @param bool $tagsMode
     * @param null $valuesAttribute
     * @return Criteria
     */
    public function add($name, $valuesType = null, $tagsMode = false, $valuesAttribute = null)
    {
        if (is_null($valuesAttribute)) {
            $valuesAttribute = $name;
        }

        $valuesType = $this->cache->getCorrector()->getValidatedVType($valuesType);

        if ($tagsMode !== true) {
            $tagsMode = false;
        }

        /** @var Criteria $Criteria */
        $Criteria = $this->cache->getServiceManager()->getCriteria();
        $Criteria->setName($name);
        $Criteria->setTagsMode($tagsMode);
        $Criteria->setValuesType($valuesType);
        $Criteria->setValuesAttribute($valuesAttribute);
        $this->list[$name] = $Criteria;

        return $this->list[$name];
    }

    /**
     * @return Criteria[]
     */
    public function getAll()
    {
        return $this->list;
    }

    /**
     * @param $name
     * @return Criteria
     */
    public function get($name)
    {
        return isset($this->list[$name]) ? $this->list[$name] : null;
    }

    /**
     * @param $name
     * @return $this
     */
    public function remove($name)
    {
        unset($this->list[$name]);
        return $this;
    }
}