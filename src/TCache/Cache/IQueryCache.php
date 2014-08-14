<?php

namespace TCache\Cache;

interface IQueryCache
{

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