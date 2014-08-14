<?php

namespace TCache;

use TCache\Storage\MongoStorage;
use TCache\Utils\Corrector;
use TCache\Utils\Hashes;

class TCache
{
    /** @var ServiceManager $serviceManager */
    private $serviceManager = null;

    function __construct()
    {
        $this->serviceManager = new ServiceManager($this);
        $this->serviceManager->setCriteriasClass('\TCache\Criterias');
        $this->serviceManager->setCriteriaClass('\TCache\Criterias\Criteria');
        $this->serviceManager->setStorageClass('\TCache\Storage\MongoStorage');
        $this->serviceManager->setItemsClass('\TCache\Items');
        $this->serviceManager->setItemClass('\TCache\Items\Item');
        $this->serviceManager->setHashesClass('\TCache\Utils\Hashes');
        $this->serviceManager->setCorrectorClass('\TCache\Utils\Corrector');
        $this->serviceManager->setValidatorClass('\TCache\Utils\Validator');
        $this->serviceManager->setQueryClass('\TCache\Query');
        $this->serviceManager->setQueryFieldClass('\TCache\Query\Field');
    }

    /**
     * @return \TCache\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return \TCache\Criterias
     */
    public function getCriterias()
    {
        return $this->serviceManager->getCriterias();
    }

    /**
     * @return MongoStorage
     */
    public function getStorage()
    {
        return $this->serviceManager->getStorage();
    }

    /**
     * @return \TCache\Items
     */
    public function getItems()
    {
        return $this->serviceManager->getItems();
    }

    /**
     * @return Hashes
     */
    public function getHashes()
    {
        return $this->serviceManager->getHashesUtil();
    }

    /**
     * @return Corrector
     */
    public function getCorrector()
    {
        return $this->serviceManager->getCorrectorUtil();
    }

    /**
     * @return Utils\Validator
     */
    public function getValidator()
    {
        return $this->getServiceManager()->getValidatorUtil();
    }

    /**
     * @return Query
     */
    public function createQuery()
    {
        return $this->getServiceManager()->getEmptyQuery();
    }

    public function getDaemon()
    {
        //TODO: getDaemon()
    }


}