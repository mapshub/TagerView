<?php

namespace Tager\Cache\TCache;

use Tager\Cache\ICache;
use Tager\View;
use Tager\Helpers\Corrector;

class TagerCacheView extends View implements ICache
{
    /** @var View */
    private $cache = null;

    private $enabled = true;

    public function __construct($cache)
    {
        $this->cache = $cache;

        parent::__construct();
        $criterias = $this->scheme()->getCriterias();
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
            'userQueryName' => $queryName,
            'userQueryHash' => $queryHash,
            'userData' => $arrData,
        ];

        $this->items()->save(
            $this->items()->createWithData($arrData)
        );
    }

    private function getData($configHash, $queryHash, $queryName)
    {
        $result = null;

        $query = $this->sm()->getEmptyQuery();
        $query->add("userConfigHash")->eq($configHash);
        $query->add("userQueryHash")->eq($queryHash);
        $query->add("userQueryName")->eq($queryName);
        $query->setLimit(1);

        $arrayItems = $this->queries()->findDocuments($query);
        if (isset($arrayItems[0], $arrayItems[0]['TcData'])) {
            $result = $arrayItems[0]['TcData']['userData'];
        }
        return $result;
    }

    public function isConnected()
    {
        return $this->enabled && $this->driver()->isConnected();
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $this->sm()->getCorrectorHelper()->getCorrectValue($enabled, Corrector::VTYPE_BOOL);
    }

    public function invalidate()
    {
        $this->driver()->getItemsCollection()->remove([]);
    }

    public function cacheSetItemsCount($configHash, $queryHash, $count)
    {
        $count = $this->sm()->getCorrectorHelper()->getCorrectValue($count, Corrector::VTYPE_INT);
        $arrData = ['count' => $count];
        $this->setData($configHash, $queryHash, self::ITEMS_COUNT_QUERY, $arrData);
    }

    public function cacheGetItemsCount($configHash, $queryHash)
    {
        $value = false;
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
        $value = false;
        $data = $this->getData($configHash, $queryHash, self::ITEMS_LIST_QUERY);
        if (is_array($data) && isset($data['items_ser'])) {
            $value = unserialize($data['items_ser']);
            unset($data);
        }
        return $value;
    }

    public function cacheSetDistinctValues($configHash, $queryHash, $arrValues)
    {
        $arrData['items_ser'] = serialize($arrValues);
        $this->setData($configHash, $queryHash, self::ITEMS_DISTINCT_VALUES_QUERY, $arrData);
    }

    public function cacheGetDistinctValues($configHash, $queryHash)
    {
        $value = false;
        $data = $this->getData($configHash, $queryHash, self::ITEMS_DISTINCT_VALUES_QUERY);
        if (is_array($data) && isset($data['items_ser'])) {
            $value = unserialize($data['items_ser']);
            unset($data);
        }
        return $value;
    }

    public function cacheSetMinMaxValues($configHash, $queryHash, $minMaxData)
    {
        $arrData['items_ser'] = serialize($minMaxData);
        $this->setData($configHash, $queryHash, self::ITEMS_MIN_MAX_QUERY, $arrData);
    }

    public function cacheGetMinMaxValues($configHash, $queryHash)
    {
        $value = false;
        $data = $this->getData($configHash, $queryHash, self::ITEMS_MIN_MAX_QUERY);
        if (is_array($data) && isset($data['items_ser'])) {
            $value = unserialize($data['items_ser']);
            unset($data);
        }
        return $value;
    }

}