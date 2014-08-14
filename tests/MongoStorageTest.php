<?php

namespace TCacheTest;

use TCache\TCache;
use TCache\Utils\Corrector;

class MongoStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \TCache\Storage\Exception\UndefinedMongoDbNameException
     */
    public function testUndefinedMongoDbNameException()
    {
        $tcache = new TCache();
        $storage = $tcache->getStorage();
        $this->assertNull($storage->getDb()); // $storage->getDb() throws UndefinedMongoDbNameException
        $storage->dropDb();
    }


    public function testDefaultCollectionName()
    {
        $tcache = new TCache();
        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest");
        $this->assertEquals("TCacheTest", $storage->getItemsCollectionName());
        $defaultCollection = $storage->getItemsCollection();
        $this->assertNotNull($defaultCollection);
        $userCollection = $storage->setItemsCollectionName("MongoStorageTest_testDefaultCollectionName")->getItemsCollection();
        $this->assertNotNull($userCollection);
        $this->assertNotEquals($defaultCollection->getName(), $userCollection->getName());

        $storage->dropDb();
    }

    public function testGetItemsCllection()
    {
        $tcache = new TCache();
        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("MongoStorageTest_testGetItemsCllection");

        $storage->dropDb();
    }

    public function testSaveItem()
    {
        $tcache = new TCache();
        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("MongoStorageTest_testSaveItem");

        $tcache->getCriterias()->add("sex");
        $tcache->getCriterias()->add("name");
        $tcache->getCriterias()->add("birthday");
        $tcache->getCriterias()->add("nicknames", Corrector::VTYPE_STRING, true);

        $data = [
            'sex' => 'M',
            'name' => 'Vetal',
            'birthday' => '2014-06-05',
            'nicknames' => 'bubuzzz'
        ];
        $item = $tcache->getItems()->createItem($data);
        $saveditem = $tcache->getItems()->saveItem($item);
        $this->assertEquals($item, $saveditem);
        $saveditem = $tcache->getItems()->saveItem($item);
        $this->assertEquals($item, $saveditem);
        $storage->dropDb();
    }

    public function testRemoveItem()
    {
        $tcache = new TCache();
        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("MongoStorageTest_testRemoveItem");

        $tcache->getCriterias()->add("sex");
        $tcache->getCriterias()->add("name");
        $tcache->getCriterias()->add("birthday");
        $tcache->getCriterias()->add("nicknames", Corrector::VTYPE_STRING, true);

        $data = [
            'sex' => 'M',
            'name' => 'Vetal',
            'birthday' => '2014-06-05',
            'nicknames' => 'bubuzzz'
        ];
        $item = $tcache->getItems()->createItem($data);
        $saveditem = $tcache->getItems()->saveItem($item);

        $tcache->getItems()->removeItem($saveditem);


        $storage->dropDb();
    }
}
 