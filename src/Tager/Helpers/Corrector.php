<?php

namespace Tager\Helpers;


use Tager\Items\Item;
use Tager\View;

class Corrector
{

    const VTYPE_INT = "int";
    const VTYPE_BOOL = "bool";
    const VTYPE_FLOAT = "float";
    const VTYPE_STRING = "string";
    const VTYPE_TIMESTAMP = "timestamp";

    /** @var View */
    protected $cache = null;
    private $formatter = null;
    private $locale = null;

    function __construct($cache)
    {
        $this->cache = $cache;
        $this->locale = new \Locale();
    }

    /**
     * @param $value
     * @param $vtype
     * @return mixed
     */
    public function getCorrectValue($value, $vtype)
    {
        $vtype = $this->getValidatedVType($vtype);
        $realType = gettype($value);
        switch ($vtype) {
            case self::VTYPE_INT:
                if ($realType !== "double") {
                    settype($value, "float");
                }
                $value = round($value);
                break;
            case self::VTYPE_BOOL:
                if ($realType !== "boolean") {
                    settype($value, "boolean");
                }
                break;
            case self::VTYPE_FLOAT:
                if ($realType !== "double") {
                    settype($value, "float");
                }
                break;
            case self::VTYPE_STRING:
                if ($realType !== "string") {
                    settype($value, "string");
                }
                break;
            case self::VTYPE_TIMESTAMP:
                if ($realType !== "integer") {
                    settype($value, "integer");
                }
                $value = new \MongoDate($value);
                break;
            default:
                break;
        }
        return $value;
    }

    public function getDefaultVType()
    {
        return self::VTYPE_STRING;
    }

    public function getValidatedVType($vtype)
    {
        switch ($vtype) {
            case self::VTYPE_INT:
                break;
            case self::VTYPE_BOOL:
                break;
            case self::VTYPE_FLOAT:
                break;
            case self::VTYPE_STRING:
                break;
            case self::VTYPE_TIMESTAMP:
                break;
            default:
                $vtype = $this->getDefaultVType();
                break;
        }
        return $vtype;
    }

    public function formatFloat($value, $min_fraction_digits = 0, $max_fraction_digits = 4)
    {
        $fmt = $this->cache->sm()->getNumberFormatter($min_fraction_digits, $max_fraction_digits);
        $valueFormatted = $fmt->format($value);
        return $valueFormatted;
    }


    /**
     * @param $data
     * @return array
     */
    public function getIndexes($data)
    {
        $criterias = $this->cache->scheme()->getCriterias();
        $indexes = [];

        foreach ($criterias->getAll() as $criteria) {
            $name = $criteria->getName();
            $isTags = $criteria->getTagsMode();
            $dataAttr = $criteria->getValuesAttribute();

            if (!isset($data[$dataAttr]) && $dataAttr !== $name) {
                $dataAttr = $name;
            }

            if (isset($data[$dataAttr])) {
                $mixValue = $data[$dataAttr];
                if ($isTags === true) {
                    $arrData = [];
                    if (is_array($mixValue)) {
                        foreach ($mixValue as $nextTag) {
                            $arrData[] = $criteria->getCorrectValue($nextTag);
                        }
                        $indexes[$name] = $arrData;
                    } else {
                        $indexes[$name] = [$criteria->getCorrectValue($mixValue)];
                    }
                } else {
                    $indexes[$name] = $criteria->getCorrectValue($mixValue);
                }
            }
        }
        return $indexes;
    }
}