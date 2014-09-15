<?php

namespace Tager;

use Tager\Cache\TCache\TagerCacheView;
use Tager\Criterias\Criteria;
use Tager\Items\Item;
use Tager\Drivers\MongoStorage;
use Tager\Helpers\Corrector;
use Tager\Helpers\Hashes;
use Tager\Helpers\Validator;
use Tager\Queries\Query;

class ServiceManager
{
    /** @var View */
    private $cache = null;

    private $storageClass = null;
    private $storage = null;

    private $criteriasClass = null;
    private $criterias = null;

    private $criteriaClass = null;

    private $queryClass = null;
    private $queryEmptyObject = null;

    private $itemsClass = null;
    private $items = null;

    private $itemClass = null;
    private $itemEmptyObject = null;

    private $queryFieldClass = null;
    private $queryFieldEmptyObject = null;

    private $hashesClass = null;
    private $hashes = null;

    private $correctorClass = null;
    private $corrector = null;

    private $validatorClass = null;
    private $validator = null;

    private $numberFormatter = null;

    private $schemeClass = null;
    private $scheme = null;

    private $cachesClass = null;
    private $caches = null;

    private $inStorageCacheClass = null;
    private $inStorageCache = null;

    private $inMemoryCacheClass = null;
    private $inMemoryCache = null;

    private $queriesClass = null;
    private $queries = null;

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param null $criteriasClass
     */
    public function setCriteriasClass($criteriasClass)
    {
        $this->criteriasClass = $criteriasClass;
    }

    /**
     * @param null $itemsClass
     */
    public function setItemsClass($itemsClass)
    {
        $this->itemsClass = $itemsClass;
    }

    /**
     * @param null $storageClass
     */
    public function setDriverClass($storageClass)
    {
        $this->storageClass = $storageClass;
    }

    /**
     * @param null $criteriaClass
     */
    public function setCriteriaClass($criteriaClass)
    {
        $this->criteriaClass = $criteriaClass;
    }

    /**
     * @param null $queryClass
     */
    public function setQueryClass($queryClass)
    {
        $this->queryClass = $queryClass;
    }

    /**
     * @param null $itemClass
     */
    public function setItemClass($itemClass)
    {
        $this->itemClass = $itemClass;
    }

    /**
     * @param null $hashesClass
     */
    public function setHashesClass($hashesClass)
    {
        $this->hashesClass = $hashesClass;
    }

    /**
     * @param null $correctorClass
     */
    public function setCorrectorClass($correctorClass)
    {
        $this->correctorClass = $correctorClass;
    }

    /**
     * @param null $validatorClass
     */
    public function setValidatorClass($validatorClass)
    {
        $this->validatorClass = $validatorClass;
    }

    /**
     * @param null $queryFieldClass
     */
    public function setQueryFieldClass($queryFieldClass)
    {
        $this->queryFieldClass = $queryFieldClass;
    }

    /**
     * @param null $schemeClass
     */
    public function setSchemeClass($schemeClass)
    {
        $this->schemeClass = $schemeClass;
    }

    /**
     * @param null $inMemoryCacheClass
     */
    public function setInMemoryCacheClass($inMemoryCacheClass)
    {
        $this->inMemoryCacheClass = $inMemoryCacheClass;
    }

    /**
     * @param null $inStorageCacheClass
     */
    public function setInStorageCacheClass($inStorageCacheClass)
    {
        $this->inStorageCacheClass = $inStorageCacheClass;
    }

    /**
     * @param null $cachesClass
     */
    public function setCachesClass($cachesClass)
    {
        $this->cachesClass = $cachesClass;
    }

    /**
     * @param null $queriesClass
     */
    public function setQueriesClass($queriesClass)
    {
        $this->queriesClass = $queriesClass;
    }

    /**
     * @return Criterias
     */
    public function getCriterias()
    {
        if (is_null($this->criterias) && !is_null($this->criteriasClass)) {
            $this->criterias = new $this->criteriasClass($this->cache);
        }
        return $this->criterias;
    }

    /**
     * @return Items
     */
    public function getItems()
    {
        if (is_null($this->items) && !is_null($this->itemsClass)) {
            $this->items = new $this->itemsClass($this->cache);
        }
        return $this->items;
    }

    /**
     * @return MongoStorage
     */
    public function getStorage()
    {
        if (is_null($this->storage) && !is_null($this->storageClass)) {
            $this->storage = new $this->storageClass($this->cache);
        }
        return $this->storage;
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        $criteria = null;
        if (!is_null($this->criteriaClass)) {
            $criteria = new $this->criteriaClass($this->cache);
        }
        return $criteria;
    }

    /**
     * @return Item
     */
    public function createBlankItem()
    {
        $item = null;
        if (!is_null($this->itemClass)) {
            if (is_null($this->itemEmptyObject)) {
                $this->itemEmptyObject = new $this->itemClass($this->cache);
            }
            $item = clone $this->itemEmptyObject;
        }
        return $item;
    }

    /**
     * @return Corrector
     */
    public function getCorrectorHelper()
    {
        if (is_null($this->corrector) && !is_null($this->correctorClass)) {
            $this->corrector = new $this->correctorClass($this->cache);
        }
        return $this->corrector;
    }

    /**
     * @return Hashes
     */
    public function getHashesHelper()
    {
        if (is_null($this->hashes) && !is_null($this->hashesClass)) {
            $this->hashes = new $this->hashesClass($this->cache);
        }
        return $this->hashes;
    }

    /**
     * @return Validator
     */
    public function getValidatorHelper()
    {
        if (is_null($this->validator) && !is_null($this->validatorClass)) {
            $this->validator = new $this->validatorClass($this->cache);
        }
        return $this->validator;
    }

    /**
     * @return Query
     */
    public function getEmptyQuery()
    {
        $query = null;
        if (!is_null($this->queryClass)) {
            if (is_null($this->queryEmptyObject)) {
                $this->queryEmptyObject = new $this->queryClass($this->cache);
            }
            $query = clone $this->queryEmptyObject;
        }
        return $query;
    }

    /**
     * @param int $min_fraction_digits
     * @param int $max_fraction_digits
     * @return null|\NumberFormatter
     */
    public function getNumberFormatter($min_fraction_digits = 0, $max_fraction_digits = 4)
    {
        $numberFormatter = null;
        if (is_null($this->numberFormatter)) {
            $this->numberFormatter = \NumberFormatter::create("ru_RU", \NumberFormatter::DECIMAL);
            $this->numberFormatter->setSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, '.');
            $this->numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
        }
        $this->numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $min_fraction_digits);
        $this->numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $max_fraction_digits);
        return $this->numberFormatter;
    }

    /**
     * @return \Tager\Queries\Field
     */
    public function getEmptyQueryField()
    {
        $field = null;
        if (!is_null($this->queryFieldClass)) {
            if (is_null($this->queryFieldEmptyObject)) {
                $this->queryFieldEmptyObject = new $this->queryFieldClass($this->cache);
            }
            $field = clone $this->queryFieldEmptyObject;
        }
        return $field;
    }

    /**
     * @return Scheme
     */
    public function getScheme()
    {
        if (is_null($this->scheme) && !is_null($this->schemeClass)) {
            $this->scheme = new $this->schemeClass($this->cache);
        }
        return $this->scheme;
    }

    /**
     * @return Cache
     */
    public function getCaches()
    {
        if (is_null($this->caches) && !is_null($this->cachesClass)) {
            $this->caches = new $this->cachesClass($this->cache);
        }
        return $this->caches;
    }

    /**
     * @return TagerCacheView
     */
    public function getInStorageCache()
    {
        if (is_null($this->inStorageCache) && !is_null($this->inStorageCacheClass)) {
            $this->inStorageCache = new $this->inStorageCacheClass($this->cache);
        }
        return $this->inStorageCache;
    }

    /**
     * @return \Tager\Cache\Memcache\Memcache
     */
    public function getInMemoryCache()
    {
        if (is_null($this->inMemoryCache) && !is_null($this->inMemoryCacheClass)) {
            $this->inMemoryCache = new $this->inMemoryCacheClass($this->cache);
        }
        return $this->inMemoryCache;
    }

    /**
     * @return Queries
     */
    public function getQueries()
    {
        if (is_null($this->queries) && !is_null($this->queriesClass)) {
            $this->queries = new $this->queriesClass($this->cache);
        }
        return $this->queries;
    }
}