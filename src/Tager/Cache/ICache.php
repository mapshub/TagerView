<?php

namespace Tager\Cache;

interface ICache
{

    const ITEMS_COUNT_QUERY = "ITEMS_COUNT";
    const ITEMS_LIST_QUERY = "ITEMS_LIST";
    const ITEMS_DISTINCT_VALUES_QUERY = "DISTINCT_VALUES";
    const ITEMS_MIN_MAX_QUERY = "MINMAX_VALUES";

    public function isConnected();

    public function setEnabled($flag);

    public function invalidate();


    public function cacheSetItemsCount($configHash, $queryHash, $count);

    public function cacheGetItemsCount($configHash, $queryHash);


    public function cacheSetItems($configHash, $queryHash, $arrItems);

    public function cacheGetItems($configHash, $queryHash);


    public function cacheSetDistinctValues($configHash, $queryHash, $arrValues);

    public function cacheGetDistinctValues($configHash, $queryHash);


    public function cacheSetMinMaxValues($configHash, $queryHash, $minMaxData);

    public function cacheGetMinMaxValues($configHash, $queryHash);


}