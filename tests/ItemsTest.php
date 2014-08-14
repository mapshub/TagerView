<?php

namespace TCacheTest;

use TCache\TCache;
use TCache\Utils\Corrector;

class ItemsTest extends \PHPUnit_Framework_TestCase
{

    private $data = [
        ['f1' => 0, 'f2' => 0, 'f3' => 2, 'f4' => 8, 'f5' => 1, 'f6' => true, 'f7' => ['A', 'B', 'C'], 'f8' => 'A'],
        ['f1' => 1, 'f2' => 5, 'f3' => 3, 'f4' => 6, 'f5' => 8, 'f7' => ['B', 'C'], 'f8' => 'R'],
        ['f1' => 2, 'f2' => 3, 'f3' => 5, 'f4' => 8, 'f5' => 4, 'f6' => false, 'f7' => ['A'], 'f8' => 'D'],
        ['f1' => 5, 'f2' => 0, 'f3' => 4, 'f4' => 0, 'f5' => 3, 'f6' => true, 'f7' => ['A', 'B'], 'f8' => 'Z'],
        ['f1' => 2, 'f2' => 2, 'f3' => 3, 'f4' => 1, 'f5' => 6, 'f6' => true, 'f7' => ['A', 'C'], 'f8' => 'Y'],
        ['f1' => 0, 'f2' => 2, 'f3' => 4, 'f4' => 8, 'f5' => 7, 'f7' => ['B'], 'f8' => 'H'],
        ['f1' => 7, 'f2' => 1, 'f3' => 3, 'f4' => 6, 'f5' => 2, 'f7' => ['C'], 'f8' => 'N'],
    ];

    public function testFindItems()
    {
        $tcache = new TCache();

        $tcache->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("ItemsTest_testFindItems");

        $items = $tcache->getItems();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $query = $items->createQuery();
        $query->add("f2")->lte(10);
        $subquery = $query->subquery()->setModeOR(true);
        $subquery->add('f7')->all(['C']);
        $subquery->add('f7')->all(['A', 'B']);

        $query->setLimit(1000);
        $this->assertEquals(5, count($items->getQueryResults($query)));

        $query->setLimit(1);
        $this->assertEquals(1, count($items->getQueryResults($query)));

        $subquery->setModeOR(false);

        $c = count($items->getQueryResults($query));
        $this->assertEquals(1, $c);

        $tcache->getStorage()->dropDb();
    }

    public function testFindWithSort()
    {

        $tcache = new TCache();

        $tcache->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("ItemsTest_testFindWithSort");

        $items = $tcache->getItems();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $expected = [
            ['f1' => 0, 'f2' => 0, 'f3' => 2, 'f4' => 8, 'f5' => 1, 'f6' => true, 'f7' => ['A', 'B', 'C'], 'f8' => 'A'],
            ['f1' => 7, 'f2' => 1, 'f3' => 3, 'f4' => 6, 'f5' => 2, 'f7' => ['C'], 'f8' => 'N'],
            ['f1' => 5, 'f2' => 0, 'f3' => 4, 'f4' => 0, 'f5' => 3, 'f6' => true, 'f7' => ['A', 'B'], 'f8' => 'Z'],
            ['f1' => 2, 'f2' => 3, 'f3' => 5, 'f4' => 8, 'f5' => 4, 'f6' => false, 'f7' => ['A'], 'f8' => 'D'],
            ['f1' => 2, 'f2' => 2, 'f3' => 3, 'f4' => 1, 'f5' => 6, 'f6' => true, 'f7' => ['A', 'C'], 'f8' => 'Y'],
            ['f1' => 0, 'f2' => 2, 'f3' => 4, 'f4' => 8, 'f5' => 7, 'f7' => ['B'], 'f8' => 'H'],
            ['f1' => 1, 'f2' => 5, 'f3' => 3, 'f4' => 6, 'f5' => 8, 'f7' => ['B', 'C'], 'f8' => 'R'],
        ];


        $query = $items->createQuery();
        $query->addSort("f5", 1);
        $query->setLimit(1000);

        $actual = [];
        foreach ($items->getQueryResults($query) as $next) {
            $actual[] = $items->createItem()->load($next)->getData();
        }

        $this->assertEquals($expected, $actual);

        $tcache->getStorage()->dropDb();
    }

    public function testDistinctWithQueryLimit()
    {
        $tcache = new TCache();

        $tcache->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("ItemsTest_testDistinctWithQueryLimit");

        $items = $tcache->getItems();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $expected = ['H', 'N', 'Y'];

        $query = $items->createQuery();
        $query->add('f5')->gt(1);
        $query->add('f5')->lt(8);
        $query->setSkip(1);
        $query->setLimit(3);


        $actual = [];
        foreach ($items->getDistinctValues('f8', $query) as $next) {
            $actual[] = $next['value'];
        }

        $this->assertEquals($expected, $actual);

        $tcache->getStorage()->dropDb();
    }

    public function testMinMaxWithQuery()
    {
        $tcache = new TCache();

        $tcache->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("ItemsTest_testMinMaxWithQuery");

        $items = $tcache->getItems();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $query = $items->createQuery();
        $actual = $items->getMinMaxValues('f1', $query);
        $expected = ['min' => 0, 'max' => 7];
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expected, $tcache->getCriterias()->get("f1")->getMinMaxValues($query));

        $query->add('f6')->eq(true);
        $actual = $items->getMinMaxValues('f1', $query);
        $expected = ['min' => 0, 'max' => 5];
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expected, $tcache->getCriterias()->get("f1")->getMinMaxValues($query));

        $query = $items->createQuery();
        $actual = $items->getMinMaxValues('f6', $query);
        $expected = ['min' => false, 'max' => true];
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expected, $tcache->getCriterias()->get("f6")->getMinMaxValues($query));

        $query = $items->createQuery();
        $actual = $items->getMinMaxValues('f7', $query, true);
        $expected = ['min' => "A", 'max' => "C"];
        $this->assertEquals($expected, $tcache->getCriterias()->get("f7")->getMinMaxValues($query));

        $query = $items->createQuery();
        $query->add("f1")->eq(2);
        $actual = $items->getMinMaxValues('f8', $query);
        $expected = ['min' => "D", 'max' => "Y"];
        $this->assertEquals($expected, $tcache->getCriterias()->get("f8")->getMinMaxValues($query));

        $tcache->getStorage()->dropDb();
    }

    /**
     * @expectedException \TCache\Storage\Exception\UndefinedQueryLimitException
     */
    public function testQueryWithoutLimit_shouldThrowException()
    {
        $tcache = new TCache();

        $tcache->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("ItemsTest_testMinMaxWithQuery");

        $items = $tcache->getItems();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $query = $items->createQuery();
        $items->getQueryResults($query);
    }


    public function testSerializeItemObject()
    {
        $tcache = new TCache();

        $tcache->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $storage = $tcache->getStorage();
        $storage->setDbName("TCacheTest")->setItemsCollectionName("ItemsTest_testSerializeItemObject");

        $items = $tcache->getItems();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $query = $items->createQuery();
        $query->setLimit(1);
        $res = $items->getQueryResults($query);

        $serres = serialize($res);

        $this->assertEquals($res, unserialize($serres));
    }
}
 