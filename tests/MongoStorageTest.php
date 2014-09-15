<?php

namespace TCacheTest;

use Tager\View;
use Tager\Helpers\Corrector;

class MongoStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Tager\Drivers\Exception\UndefinedMongodbCollectionException
     */
    public function testUndefinedMongoDbNameException()
    {
        $tcache = new View();
        $storage = $tcache->driver();
        $this->assertNull($storage->getDb()); // $storage->getDb() throws UndefinedMongoDbNameException
    }


    public function testDefaultCollectionName()
    {
        $tcache = new View();
        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testDefaultCollectionName"));

        $this->assertEquals("ItemsTest_testDefaultCollectionName", $storage->getItemsCollectionName());

        $storage->getDb()->drop();
    }

    public function testSaveItem()
    {
        $tcache = new View();

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("MongoStorageTest_testSaveItem"));

        $tcache->scheme()->getCriterias()->add("sex");
        $tcache->scheme()->getCriterias()->add("name");
        $tcache->scheme()->getCriterias()->add("birthday");
        $tcache->scheme()->getCriterias()->add("nicknames", Corrector::VTYPE_STRING, true);

        $data = [
            'sex' => 'M',
            'name' => 'Vetal',
            'birthday' => '2014-06-05',
            'nicknames' => 'bubuzzz'
        ];
        $item = $tcache->items()->createWithData($data);
        $saveditem = $tcache->items()->save($item);
        $this->assertEquals($item, $saveditem);
        $saveditem = $tcache->items()->save($item);
        $this->assertEquals($item, $saveditem);
        $storage->getDb()->drop();
    }

    public function testRemoveItem()
    {
        $tcache = new View();
        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("MongoStorageTest_testRemoveItem"));

        $tcache->scheme()->getCriterias()->add("sex");
        $tcache->scheme()->getCriterias()->add("name");
        $tcache->scheme()->getCriterias()->add("birthday");
        $tcache->scheme()->getCriterias()->add("nicknames", Corrector::VTYPE_STRING, true);

        $data = [
            'sex' => 'M',
            'name' => 'Vetal',
            'birthday' => '2014-06-05',
            'nicknames' => 'bubuzzz'
        ];
        $item = $tcache->items()->createWithData($data);
        $saveditem = $tcache->items()->save($item);

        $tcache->items()->removeItem($saveditem);

        $storage->getDb()->drop();
    }
}
 