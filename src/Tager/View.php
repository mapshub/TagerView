<?php

namespace Tager;

use Tager\Drivers\MongoStorage;

class View
{
    /** @var ServiceManager $serviceManager */
    private $serviceManager = null;

    function __construct()
    {
        $this->serviceManager = new ServiceManager($this);
        $this->serviceManager->setCriteriasClass('\Tager\Criterias');
        $this->serviceManager->setCriteriaClass('\Tager\Criterias\Criteria');
        $this->serviceManager->setDriverClass('\Tager\Drivers\MongoStorage');
        $this->serviceManager->setItemsClass('\Tager\Items');
        $this->serviceManager->setItemClass('\Tager\Items\Item');
        $this->serviceManager->setHashesClass('\Tager\Helpers\Hashes');
        $this->serviceManager->setCorrectorClass('\Tager\Helpers\Corrector');
        $this->serviceManager->setValidatorClass('\Tager\Helpers\Validator');
        $this->serviceManager->setQueryClass('\Tager\Queries\Query');
        $this->serviceManager->setQueryFieldClass('\Tager\Queries\Field');
        $this->serviceManager->setSchemeClass('\Tager\Scheme');
        $this->serviceManager->setCachesClass('\Tager\Cache');
        $this->serviceManager->setInMemoryCacheClass('\Tager\Cache\Memcache\Memcache');
        $this->serviceManager->setInStorageCacheClass('\Tager\Cache\TCache\TagerCacheView');
        $this->serviceManager->setQueriesClass('\Tager\Queries');
    }

    /**
     * @return \Tager\ServiceManager
     */
    public function sm()
    {
        return $this->serviceManager;
    }

    /**
     * @return Scheme
     */
    public function scheme()
    {
        return $this->serviceManager->getScheme();
    }

    /**
     * @return MongoStorage
     */
    public function driver()
    {
        return $this->serviceManager->getStorage();
    }

    /**
     * @return Cache
     */
    public function cache()
    {
        return $this->serviceManager->getCaches();
    }

    /**
     * @return \Tager\Items
     */
    public function items()
    {
        return $this->serviceManager->getItems();
    }

    /**
     * @return Queries
     */
    public function queries()
    {
        return $this->serviceManager->getQueries();
    }
}