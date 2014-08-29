<?php

namespace Tager\Helpers;


use Tager\Criterias;
use Tager\Queries\Query;
use Tager\View;

class Hashes
{
    /** @var View */
    protected $cache = null;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $arrData
     * @return null|string
     */
    public function getArrayHash($arrData)
    {
        $hash = "";
        if (is_array($arrData)) {
            ksort($arrData, SORT_STRING);
            foreach ($arrData as $k => $v) {
                if (is_array($v)) {
                    $v = $this->getArrayHash($v);
                }
                $hash = md5($hash . $k . $v);
            }
        }
        unset($arrData);
        return empty($hash) ? null : $hash;
    }

    public function getConfigHash()
    {
        $criteriasList = $this->cache->scheme()->getCriterias()->getAll();
        $md5 = md5("");
        $keys = array_keys($criteriasList);
        sort($keys, SORT_STRING);
        foreach ($keys as $nextKey) {
            $md5 = md5($md5 . $this->getCriteriaHash($criteriasList[$nextKey]));
        }
        return $md5;
    }

    /**
     * @param Criterias\Criteria $criteria
     * @return string
     */
    public function getCriteriaHash($criteria)
    {
        return md5($criteria->getName() . $criteria->getValuesAttribute() . $criteria->getValuesType() . $criteria->getTagsMode());
    }

    /**
     * @param $arrIndexes
     * @param $TcDataHash
     * @return string
     */
    public function getObjectHash($arrIndexes, $TcDataHash)
    {
        return md5($this->getArrayHash($arrIndexes) . $TcDataHash);
    }

    /**
     * @param $field
     * @param $query Query
     * @param $unwind
     * @return string
     */
    public function getAggregationQueryHash($field, $query, $unwind)
    {
        return md5($field . $query->getHash() . ($unwind === false ? '0' : '1'));
    }
}