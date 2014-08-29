<?php

namespace Tager\Queries;

use Tager\Criterias\Criteria;
use Tager\Queries\Field;
use Tager\Helpers\Corrector;
use Tager\View;

class Query
{
    /** @var View */
    protected $cache = null;

    protected $modeOR = false;
    protected $systemUser = false;
    protected $skip = 0;
    protected $limit = -1;
    protected $order = null;

    /** @var Field[] */
    protected $conditions = [];

    function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $modeOR
     * @return $this
     */
    public function setModeOR($modeOR)
    {
        $this->modeOR = $modeOR;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getModeOR()
    {
        return $this->modeOR;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $limit = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($limit, Corrector::VTYPE_INT);
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $skip
     */
    public function setSkip($skip)
    {
        $skip = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($skip, Corrector::VTYPE_INT);
        $this->skip = $skip;
    }

    /**
     * @return int
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * @param $field
     * @param $dir
     * @return $this
     */
    public function addSort($field, $dir)
    {
        $field = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($field, Corrector::VTYPE_STRING);
        $dir = $this->cache->sm()->getCorrectorHelper()->getCorrectValue($dir, Corrector::VTYPE_INT);
        if ($dir != 0) {
            if (is_null($this->order)) {
                $this->order = [];
            }
            $this->order[$field] = $dir < 0 ? -1 : 1;
        }
        return $this;
    }

    /**
     * @return Array|null
     */
    public function getSort()
    {
        return $this->order;
    }

    public function resetSort()
    {
        $this->order = null;
        return $this;
    }

    /**
     * @param boolean $systemUser
     */
    public function setSystemUserQuery($systemUser)
    {
        $this->systemUser = $systemUser;
    }

    /**
     * @return boolean
     */
    public function getSystemUser()
    {
        return $this->systemUser;
    }

    /**
     * @param $name
     * @return Field
     */
    public function add($name)
    {
        /** @var Field $cond */
        $cond = $this->cache->sm()->getEmptyQueryField()->setName($name);
        $this->conditions[] = $cond;
        return $cond;
    }

    /**
     * @param Query $query
     * @return Query
     */
    public function subquery($query = null)
    {
        if (!($query instanceof Query)) {
            $query = $this->cache->sm()->getEmptyQuery();
        }
        $this->conditions[] = $query;
        return $query;
    }


    public function extract()
    {
        $mode = $this->getModeOR() === true ? '$or' : '$and';
        $userQuery = [];
        foreach ($this->conditions as $next) {
            $userQuery[] = $next->extract();
        }

        $query = null;
        if ($this->systemUser === false) {
            $query = [
                '$and' => [
                    ['TcConfigHash' => $this->cache->sm()->getHashesHelper()->getConfigHash()],
                ]
            ];
            if (count($userQuery)) {
                $query['$and'][] = [$mode => $userQuery];
            }
        } else {
            if (count($userQuery)) {
                $query = [$mode => $userQuery];
            }
        }

        return !is_null($query) ? $query : [];
    }

    public function getHash()
    {

        $hash = md5($this->modeOR . $this->systemUser . $this->skip . $this->limit . $this->cache->sm()->getHashesHelper()->getArrayHash($this->order));

        $arrSubHashes = [];
        foreach ($this->conditions as $condidion) {
            /** @var $condition Field|Query */
            $arrSubHashes[] = $condidion->getHash();
        }

        if (count($arrSubHashes)) {
            sort($arrSubHashes, SORT_STRING);

            foreach ($arrSubHashes as $nextHash) {
                $hash = md5($hash . $nextHash);
            }
        }

        return $hash;
    }
}