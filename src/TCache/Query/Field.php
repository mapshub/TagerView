<?php

namespace TCache\Query;


use TCache\TCache;
use TCache\Utils\Corrector;

class Field
{
    /** @var TCache */
    protected $cache = null;
    protected $name = null;
    protected $valid = false;
    protected $valuesType = null;
    protected $value = null;

    private $conditions = [
        'in' => '$in',
        'notin' => '$nin',
        'ne' => '$ne',
        'lt' => '$lt',
        'lte' => '$lte',
        'gt' => '$gt',
        'gte' => '$gte',
        'exists' => '$exists',
        'all' => '$all',
        'size' => '$size',
    ];

    private $condition = "";

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    protected function getCorrectArgument($mix)
    {
        //TODO: протестировать getCorrectArgument($mix)

        $correctValue = $mix;
        $corrector = $this->cache->getCorrector();
        if ($this->condition === "size") {
            $correctValue = $corrector->getCorrectValue($mix, Corrector::VTYPE_INT);
        } else {
            if (!is_null($this->valuesType)) {
                if (is_array($mix)) {
                    $correctValue = [];
                    foreach ($mix as $k => $v) {
                        $correctValue[$k] = $corrector->getCorrectValue($v, $this->valuesType);
                    }
                } else {
                    $correctValue = $corrector->getCorrectValue($mix, $this->valuesType);
                }
            }
        }
        return $correctValue;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        $criteria = $this->cache->getCriterias()->get($name);
        if (!is_null($criteria)) {
            $this->setValuesType($criteria->getValuesType());
        }
        return $this;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $valueType
     * @return $this
     */
    public function setValuesType($valueType)
    {
        $this->valuesType = $this->cache->getCorrector()->getValidatedVType($valueType);
        return $this;
    }

    /**
     * @return null
     */
    public function getValuesType()
    {
        return $this->valuesType;
    }

    /**
     * @return null
     */
    public function extract()
    {
        $value = null;

        if (!is_null($this->value)) {
            $arg = $this->getCorrectArgument($this->value);

            if (isset($this->conditions[$this->condition])) {

                if ($this->condition == "in" || $this->condition == "notint" || $this->condition == "all") {
                    if (!is_array($arg)) {
                        $arg = [$arg];
                    }
                }

                $value = [$this->name => [$this->conditions[$this->condition] => $arg]];
            } else {
                $value = [$this->name => $arg];
            }
        }
        return $value;
    }

    /**
     * @param $value
     * @param string $condition
     * @return $this
     */
    public function setValue($value, $condition = "eq")
    {
        $condition = mb_strtolower(trim($condition));
        if (isset($this->conditions[$condition])) {
            $this->condition = $condition;
        } else {
            $this->condition = "eq";
        }
        $this->value = $value;

        return $this;
    }

    public function in($arr)
    {
        return $this->setValue($arr, "in");
    }

    public function notin($arr)
    {
        return $this->setValue($arr, "notin");
    }

    public function eq($value)
    {
        return $this->setValue($value, "eq");
    }

    public function ne($value)
    {
        return $this->setValue($value, "ne");
    }

    public function lt($value)
    {
        return $this->setValue($value, "lt");
    }

    public function lte($value)
    {
        return $this->setValue($value, "lte");
    }

    public function gt($value)
    {
        return $this->setValue($value, "gt");
    }

    public function gte($value)
    {
        return $this->setValue($value, "gte");
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function exists($flag)
    {
        return $this->setValue(!!$flag, "exists");
    }

    public function all($arr)
    {
        return $this->setValue($arr, "all");
    }

    public function size($size)
    {
        return $this->setValue($size, "size");
    }
}