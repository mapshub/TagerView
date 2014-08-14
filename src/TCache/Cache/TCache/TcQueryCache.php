<?php

namespace TCache\Cache\TCache;

use TCache\Cache\IQueryCache;
use TCache\TCache;
use TCache\Utils\Corrector;

class TcQueryCache extends TCache implements IQueryCache
{

    const ITEMS_COUNT_QUERY = "ITEMS_COUNT";
    const ITEMS_LIST_QUERY = "ITEMS_LIST";
    const ITEMS_DISTINCT_VALUES_QUERY = "ITEMS_DISTINCT";
    const ITEMS_MIN_MAX_QUERY = "ITEMS_MINMAX";

    public function __construct()
    {
        parent::__construct();
        $criterias = $this->getCriterias();
        $criterias->add("userConfigHash")->setValuesType(Corrector::VTYPE_STRING);
        $criterias->add("userQueryName")->setValuesType(Corrector::VTYPE_STRING);
        $criterias->add("userQueryHash")->setValuesType(Corrector::VTYPE_STRING);
    }

    private function setData($configHash, $queryHash, $queryName, $arrData)
    {
        if (!is_array($arrData)) {
            $arrData = [$arrData];
        }

        $arrData = [
            'userConfigHash' => $configHash,
            'userQueryName' => $queryHash,
            'userQueryHash' => $queryName,
            'userData' => $arrData,
        ];

        $this->getItems()->saveItem(
            $this->getItems()->createItem($arrData)
        );
    }

    private function getData($configHash, $queryHash, $queryName)
    {
        $result = null;

        $query = $this->getServiceManager()->getEmptyQuery();
        $query->add("userConfigHash")->eq($configHash);
        $query->add("userQueryHash")->eq($queryHash);
        $query->add("userQueryName")->eq($queryName);

        $arrayItems = $this->getItems()->getQueryResults($query);
        if (isset($arrayItems[0], $arrayItems[0]['TcData'])) {
            $result = $arrayItems[0]['TcData'];
        }
        return $result;
    }

    public function invalidate()
    {
        $this->getStorage()->getItemsCollection()->remove([]);
    }

    public function cacheSetItemsCount($configHash, $queryHash, $count)
    {
        $count = $this->getCorrector()->getCorrectValue($count, Corrector::VTYPE_INT);
        $arrData = ['count' => $count];
        $this->setData($configHash, $queryHash, self::ITEMS_COUNT_QUERY, $arrData);
    }

    public function cacheGetItemsCount($configHash, $queryHash)
    {
        $value = null;
        $data = $this->getData($configHash, $queryHash, self::ITEMS_COUNT_QUERY);
        if (is_array($data) && isset($data['count'])) {
            $value = $data['count'];
            unset($data);
        }
        return $value;
    }

    public function cacheSetItems($configHash, $queryHash, $arrItems)
    {
        if (is_array($arrItems)) {
            $arrData['items_ser'] = serialize($arrItems);
            $this->setData($configHash, $queryHash, self::ITEMS_LIST_QUERY, $arrData);
        }
    }

    public function cacheGetItems($configHash, $queryHash)
    {
        $value = null;
        $data = $this->getData($configHash, $queryHash, self::ITEMS_LIST_QUERY);
        if (is_array($data) && isset($data['items_ser'])) {
            $value = unserialize($data['items_ser']);
            unset($data);
        }
        return $value;
    }

    public function cacheSetDistinctValues($configHash, $queryHash, $arrValues)
    {
        // TODO: Implement cacheSetDistinctValues() method.
    }

    public function cacheGetDistinctValues($configHash, $queryHash)
    {
        // TODO: Implement cacheGetDistinctValues() method.
    }

    public function cacheSetMinMaxValues($configHash, $queryHash, $minMaxData)
    {
        // TODO: Implement cacheSetMinMaxValues() method.
    }

    public function cacheGetMinMaxValues($configHash, $queryHash)
    {
        // TODO: Implement cacheGetMinMaxValues() method.
    }

}