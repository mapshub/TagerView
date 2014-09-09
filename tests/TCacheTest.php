<?php

namespace TCacheTest;

use Tager\View;

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
        $tcache = new View();
        $this->assertInstanceOf('Tager\Drivers\MongoStorage', $tcache->driver());
        $this->assertInstanceOf('Tager\Criterias', $tcache->scheme()->getCriterias());
        $this->assertInstanceOf('Tager\Items', $tcache->items());
        $this->assertInstanceOf('Tager\Criterias\Criteria', $tcache->sm()->getCriteria());
        $this->assertInstanceOf('Tager\Items\Item', $tcache->sm()->getEmptyItem());
        $this->assertInstanceOf('Tager\Helpers\Hashes', $tcache->sm()->getHashesHelper());
        $this->assertInstanceOf('Tager\Helpers\Corrector', $tcache->sm()->getCorrectorHelper());
        $this->assertInstanceOf('Tager\Helpers\Validator', $tcache->sm()->getValidatorHelper());
        $this->assertInstanceOf('Tager\Queries\Query', $tcache->sm()->getEmptyQuery());
        $this->assertInstanceOf('Tager\Queries\Field', $tcache->sm()->getEmptyQueryField());
        $this->assertInstanceOf('Tager\Scheme', $tcache->scheme());
        $this->assertInstanceOf('Tager\Cache', $tcache->cache());
        $this->assertInstanceOf('Tager\Cache\Memcache\Memcache', $tcache->cache()->memcache());
        $this->assertInstanceOf('Tager\Cache\TCache\TagerCacheView', $tcache->cache()->master());
        $this->assertInstanceOf('Tager\Queries', $tcache->queries());
    }

    public function testCriteriaHash()
    {
        $tcache = new View("TCacheTest", "ver2_testLoad"); //TODO: переделать
        $tcache->scheme()->getCriterias()->add("name");
        $tcache->scheme()->getCriterias()->add("age");
        $tcache->scheme()->getCriterias()->add("sex");
        $hash_1 = $tcache->sm()->getHashesHelper()->getConfigHash();

        $tcache = new View("TCacheTest", "ver2_testLoad"); //TODO: переделать
        $tcache->scheme()->getCriterias()->add("age");
        $tcache->scheme()->getCriterias()->add("sex");
        $tcache->scheme()->getCriterias()->add("name");
        $hash_2 = $tcache->sm()->getHashesHelper()->getConfigHash();

        $this->assertEquals($hash_1, $hash_2);
    }

    public function testFillItems_1()
    {
        $tcache = new View();
        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("TCacheTest_testFillItems_1"));

        foreach ($this->getData() as $arrNextData) {
            $tcache->items()->saveItem(
                $tcache->items()->createItem($arrNextData)
            );
        }
        $tcache->driver()->getDb()->drop();
    }

    public function testFillItems_2()
    {
        $tcache = new View();
        $tcache->scheme()->getCriterias()->add("Brands")->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("Models")->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("Salons")->setTagsMode(true);

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("TCacheTest_testFillItems_2"));

        $dataList = $this->getData();

        $mess = "";

        foreach ($dataList as $arrNextData) {
            $tcache->items()->saveItem(
                $tcache->items()->createItem($arrNextData)
            );
        }

        $selectAll = $tcache->queries()->create();

        $this->assertEquals(count($dataList), $tcache->items()->getCount($selectAll));


        //изменили конфиг поэтому в БД данные опять добавятся - ненужные должен удалить Демон
        $tcache->scheme()->getCriterias()->add("Bodies");

        foreach ($dataList as $arrNextData) {
            $tcache->items()->saveItem(
                $tcache->items()->createItem($arrNextData)
            );
        }

        $this->assertEquals(count($dataList), $tcache->items()->getCount($selectAll));

        //In sysmode count = N*2
        $query = $tcache->queries()->create();
        $query->setSystemUserQuery(true);
        $this->assertEquals(count($dataList) * 2, $tcache->items()->getCount($query));

        $tcache->driver()->getDb()->drop();
    }


    public function testFillItems_3()
    {
        $tcache = new View();
        $tcache->scheme()->getCriterias()->add("Brands")->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("Models")->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("Salons")->setTagsMode(true);

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("TCacheTest_testFillItems_3"));

        $dataList = $this->getData();

        foreach ($dataList as $arrNextData) {
            $tcache->items()->saveItem(
                $tcache->items()->createItem($arrNextData)
            );
        }

        $tcache->driver()->getDb()->drop();
    }
}