<?php

namespace TCache;

use TCache\Criterias\Criteria;
use TCache\Items\Item;
use TCache\Query\Field;
use TCache\Storage\MongoStorage;
use TCache\Utils\Corrector;
use TCache\Utils\Hashes;
use TCache\Utils\Validator;

class ServiceManager
{
    /** @var TCache */
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
    public function setStorageClass($storageClass)
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
    public function getEmptyItem()
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
    public function getCorrectorUtil()
    {
        if (is_null($this->corrector) && !is_null($this->correctorClass)) {
            $this->corrector = new $this->correctorClass($this->cache);
        }
        return $this->corrector;
    }

    /**
     * @return Hashes
     */
    public function getHashesUtil()
    {
        if (is_null($this->hashes) && !is_null($this->hashesClass)) {
            $this->hashes = new $this->hashesClass($this->cache);
        }
        return $this->hashes;
    }

    /**
     * @return Validator
     */
    public function getValidatorUtil()
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
     * @return Field
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
}