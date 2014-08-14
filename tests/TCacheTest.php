<?php

namespace TCacheTest;

use TCache\TCache;

class TCacheTest extends \PHPUnit_Framework_TestCase
{
    private static $data = [];

    private function getData()
    {
        if (empty(self::$data)) {
            $dom = new \DOMDocument('1.0', 'utf-8');
            $dom->load(__DIR__ . "/exportsaloncars.xml");
            $xpath = new \DOMXPath($dom);
            $offers = $xpath->query("//offer");
            for ($i = 0; $i < $offers->length; $i++) {
                $offer = $offers->item($i);

                $item = [];

                $params = $xpath->query("*", $offer);
                for ($j = 0; $j < $params->length; $j++) {
                    $param = $params->item($j);

                    $item[$param->nodeName] = $param->nodeValue;
                }

                if (!empty($item)) {
                    self::$data[] = $item;
                }
            }

            //self::$data = array_slice(self::$data, 0, 10);
        }
        return self::$data;
    }

    public function testInstances()
    {
        $tcache = new TCache();
        $this->assertInstanceOf('TCache\Storage\MongoStorage', $tcache->getStorage());
        $this->assertInstanceOf('TCache\Criterias', $tcache->getCriterias());
        $this->assertInstanceOf('TCache\Items', $tcache->getItems());
        $this->assertInstanceOf('TCache\Criterias\Criteria', $tcache->getServiceManager()->getCriteria());
        $this->assertInstanceOf('TCache\Items\Item', $tcache->getServiceManager()->getEmptyItem());
        $this->assertInstanceOf('TCache\Utils\Hashes', $tcache->getServiceManager()->getHashesUtil());
        $this->assertInstanceOf('TCache\Utils\Corrector', $tcache->getServiceManager()->getCorrectorUtil());
        $this->assertInstanceOf('TCache\Utils\Validator', $tcache->getServiceManager()->getValidatorUtil());
        $this->assertInstanceOf('TCache\Query', $tcache->getServiceManager()->getEmptyQuery());
        $this->assertInstanceOf('TCache\Query\Field', $tcache->getServiceManager()->getEmptyQueryField());
    }

    public function testCriteriaHash()
    {
        $tcache = new TCache("TCacheTest", "ver2_testLoad"); //TODO: переделать
        $tcache->getCriterias()->add("name");
        $tcache->getCriterias()->add("age");
        $tcache->getCriterias()->add("sex");
        $hash_1 = $tcache->getHashes()->getConfigHash();

        $tcache = new TCache("TCacheTest", "ver2_testLoad"); //TODO: переделать
        $tcache->getCriterias()->add("age");
        $tcache->getCriterias()->add("sex");
        $tcache->getCriterias()->add("name");
        $hash_2 = $tcache->getHashes()->getConfigHash();

        $this->assertEquals($hash_1, $hash_2);
    }

    public function testFillItems_1()
    {
        $tcache = new TCache();
        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("TCacheTest_testFillItems_1");

        foreach ($this->getData() as $arrNextData) {
            $tcache->getItems()->saveItem(
                $tcache->getItems()->createItem($arrNextData)
            );
        }
        $tcache->getStorage()->dropDb();
    }

    public function testFillItems_2()
    {
        $tcache = new TCache();
        $tcache->getCriterias()->add("Brands")->setTagsMode(true);
        $tcache->getCriterias()->add("Models")->setTagsMode(true);
        $tcache->getCriterias()->add("Salons")->setTagsMode(true);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("TCacheTest_testFillItems_2");

        $dataList = $this->getData();

        $mess = "";

        foreach ($dataList as $arrNextData) {
            $tcache->getItems()->saveItem(
                $tcache->getItems()->createItem($arrNextData)
            );
        }

        $selectAll = $tcache->getItems()->createQuery();

        $this->assertEquals(count($dataList), $tcache->getItems()->getCount($selectAll));


        //изменили конфиг поэтому в БД данные опять добавятся - ненужные должен удалить Демон
        $tcache->getCriterias()->add("Bodies");

        foreach ($dataList as $arrNextData) {
            $tcache->getItems()->saveItem(
                $tcache->getItems()->createItem($arrNextData)
            );
        }

        $this->assertEquals(count($dataList), $tcache->getItems()->getCount($selectAll));

        //In sysmode count = N*2
        $query = $tcache->getItems()->createQuery();
        $query->setSystemUserQuery(true);
        $this->assertEquals(count($dataList) * 2, $tcache->getItems()->getCount($query));

        $tcache->getStorage()->dropDb();
    }


    public function testFillItems_3()
    {
        $tcache = new TCache();
        $tcache->getCriterias()->add("Brands")->setTagsMode(true);
        $tcache->getCriterias()->add("Models")->setTagsMode(true);
        $tcache->getCriterias()->add("Salons")->setTagsMode(true);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("TCacheTest_testFillItems_3");

        $dataList = $this->getData();

        foreach ($dataList as $arrNextData) {
            $tcache->getItems()->saveItem(
                $tcache->getItems()->createItem($arrNextData)
            );
        }

        $tcache->getStorage()->dropDb();
    }
}