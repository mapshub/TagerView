<?php

namespace TCacheTest;

use Tager\View;
use Tager\Helpers\Corrector;

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
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);


        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testFindItems"));


        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->save($items->createWithData($data));
        }

        $query = $tcache->queries()->create();
        $query->add("f2")->lte(10);
        $subquery = $query->subquery()->setModeOR(true);
        $subquery->add('f7')->all(['C']);
        $subquery->add('f7')->all(['A', 'B']);



        $query->setLimit(1000);

        $result = $tcache->queries()->findDocuments($query);

        $this->assertEquals(5, count($result));

        $query->setLimit(1);
        $this->assertEquals(1, count($tcache->queries()->findDocuments($query)));

        $subquery->setModeOR(false);

        $c = count($tcache->queries()->findDocuments($query));
        $this->assertEquals(1, $c);

        $tcache->driver()->getDb()->drop();
    }

    public function testFindWithSort()
    {

        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testFindWithSort"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->save($items->createWithData($data));
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


        $query = $tcache->queries()->create();
        $query->addSort("f5", 1);
        $query->setLimit(1000);

        $actual = [];
        foreach ($tcache->queries()->findDocuments($query) as $next) {
            $actual[] = $items->createWithData()->load($next)->getData();
        }

        $this->assertEquals($expected, $actual);

        $tcache->driver()->getDb()->drop();
    }

    public function testDistinctWithQueryLimit()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testDistinctWithQueryLimit"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->save($items->createWithData($data));
        }

        $expected = ['H', 'N', 'Y'];

        $query = $tcache->queries()->create();
        $query->add('f5')->gt(1);
        $query->add('f5')->lt(8);
        $query->setSkip(1);
        $query->setLimit(3);


        $actual = [];
        foreach ($tcache->queries()->findDistinctValues('f8', $query) as $next) {
            $actual[] = $next['value'];
        }

        $this->assertEquals($expected, $actual);

        $tcache->driver()->getDb()->drop();
    }

    public function testMinMaxWithQuery()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);


        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testMinMaxWithQuery"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->save($items->createWithData($data));
        }

        $query = $tcache->queries()->create();
        $actual = $tcache->queries()->findMinMaxValues('f1', $query);
        $expected = ['min' => 0, 'max' => 7];
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expected, $tcache->scheme()->getCriterias()->get("f1")->getMinMaxValues($query));

        $query->add('f6')->eq(true);
        $actual = $tcache->queries()->findMinMaxValues('f1', $query);
        $expected = ['min' => 0, 'max' => 5];
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expected, $tcache->scheme()->getCriterias()->get("f1")->getMinMaxValues($query));

        $query = $tcache->queries()->create();
        $actual = $tcache->queries()->findMinMaxValues('f6', $query);
        $expected = ['min' => false, 'max' => true];
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expected, $tcache->scheme()->getCriterias()->get("f6")->getMinMaxValues($query));

        $query = $tcache->queries()->create();
        $actual = $tcache->queries()->findMinMaxValues('f7', $query, true);
        $expected = ['min' => "A", 'max' => "C"];
        $this->assertEquals($expected, $tcache->scheme()->getCriterias()->get("f7")->getMinMaxValues($query));

        $query = $tcache->queries()->create();
        $query->add("f1")->eq(2);
        $actual = $tcache->queries()->findMinMaxValues('f8', $query);
        $expected = ['min' => "D", 'max' => "Y"];
        $this->assertEquals($expected, $tcache->scheme()->getCriterias()->get("f8")->getMinMaxValues($query));

        $tcache->driver()->getDb()->drop();
    }

    /**
     * @expectedException \Tager\Drivers\Exception\UndefinedQueryLimitException
     */
    public function testQueryWithoutLimit_shouldThrowException()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testMinMaxWithQuery"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->save($items->createWithData($data));
        }

        $query = $tcache->queries()->create();
        $tcache->queries()->findDocuments($query);

        $tcache->driver()->getDb()->drop();
    }


    public function testSerializeItemObject()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);
        $tcache->scheme()->getCriterias()->add("f8")->setValuesType(Corrector::VTYPE_STRING);

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("ItemsTest_testSerializeItemObject"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->save($items->createWithData($data));
        }

        $query = $tcache->queries()->create();
        $query->setLimit(1);
        $res = $tcache->queries()->findDocuments($query);

        $serres = serialize($res);

        $this->assertEquals($res, unserialize($serres));

        $tcache->driver()->getDb()->drop();
    }
}
 