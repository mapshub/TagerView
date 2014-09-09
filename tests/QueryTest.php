<?php

namespace TCacheTest;


use Tager\View;
use Tager\Helpers\Corrector;

class QueryTest extends \PHPUnit_Framework_TestCase
{

    private $data = [
        ['f1' => 0, 'f2' => 0, 'f3' => 2, 'f4' => 8, 'f5' => 2, 'f6' => true, 'f7' => ['A', 'B', 'C']],
        ['f1' => 1, 'f2' => 5, 'f3' => 3, 'f4' => 6, 'f5' => 8, 'f7' => ['B', 'C']],
        ['f1' => 2, 'f2' => 3, 'f3' => 5, 'f4' => 8, 'f5' => 9, 'f6' => false, 'f7' => ['A']],
        ['f1' => 5, 'f2' => 0, 'f3' => 4, 'f4' => 0, 'f5' => 0, 'f6' => true, 'f7' => ['A', 'B']],
        ['f1' => 2, 'f2' => 2, 'f3' => 3, 'f4' => 1, 'f5' => 1, 'f6' => true, 'f7' => ['A', 'C']],
        ['f1' => 0, 'f2' => 2, 'f3' => 4, 'f4' => 8, 'f5' => 8, 'f7' => ['B']],
        ['f1' => 7, 'f2' => 1, 'f3' => 3, 'f4' => 6, 'f5' => 1, 'f7' => ['C']],
    ];

    public function testGuestAndSystemModeQuery()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("QueryTest_testGuestAndSystemModeQuery"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $query = $tcache->queries()->create();
        $query->add('f7')->in(['B', 'C']);
        $this->assertEquals(6, $items->getCount($query));


        //меняем конфигурацию немного после добавления - значит валидных данных нестало
        $tcache->scheme()->getCriterias()->get('f4')->setValuesType(Corrector::VTYPE_BOOL);
        $this->assertEquals(0, $items->getCount($query));

        //меняем query на systemUser
        $query->setSystemUserQuery(true);
        $this->assertEquals(6, $items->getCount($query));
        $query->setSystemUserQuery(false);
        $this->assertEquals(0, $items->getCount($query));

        //возвращаем конфигурацию
        $tcache->scheme()->getCriterias()->get('f4')->setValuesType(Corrector::VTYPE_INT);
        $this->assertEquals(6, $items->getCount($query));

        $tcache->driver()->getDb()->drop();
    }

    public function testQueryDistinctValues()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f7")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("QueryTest_testQueryDistinctValues"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $values = $tcache->scheme()->getCriterias()->get('f1')->getDistinctValues($tcache->queries()->create());

        $actualValues = [];
        foreach ($values as $next) {
            $actualValues[$next['value']] = $next['count'];
        }
        $valuesExpected = [0 => 2, 1 => 1, 2 => 2, 5 => 1, 7 => 1];

        $this->assertEquals($valuesExpected, $actualValues);

        //tags - true

        $values = $tcache->scheme()->getCriterias()->get('f7')->getDistinctValues($tcache->queries()->create());

        $actualValues = [];
        foreach ($values as $next) {
            $actualValues[$next['value']] = $next['count'];
        }
        $valuesExpected = ['A' => 4, 'B' => 4, 'C' => 4];

        $this->assertEquals($valuesExpected, $actualValues);

        $tcache->driver()->getDb()->drop();
    }

    public function testQueryBuilder_0()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("QueryTest_testQueryBuilder_0"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        $query = $tcache->queries()->create();
        $query->add('f2')->eq(0);
        //guest mode
        $query->setModeOR(false);
        $this->assertArrayHasKey('TcConfigHash', $query->extract()['$and'][0]);
        $this->assertArrayHasKey('$and', $query->extract()['$and'][1]);
        $query->setModeOR(true);
        $this->assertArrayHasKey('TcConfigHash', $query->extract()['$and'][0]);
        $this->assertArrayHasKey('$or', $query->extract()['$and'][1]);
        //system mode
        $query->setSystemUserQuery(true);
        $query->setModeOR(false);
        $this->assertArrayHasKey('$and', $query->extract());
        $query->setModeOR(true);
        $this->assertArrayHasKey('$or', $query->extract());

        $tcache->driver()->getDb()->drop();
    }

    public function testQueryBuilder_1()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f2")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f3")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f4")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f5")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f6")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("f7")->setTagsMode(true);

        $storage = $tcache->driver();

        $mongoClient = new \MongoClient();
        $tcache->driver()->connectTo($mongoClient->selectDB("TCacheTest")->selectCollection("QueryTest_testQueryBuilder_1"));

        $items = $tcache->items();
        foreach ($this->data as $data) {
            $items->saveItem($items->createItem($data));
        }

        //LIKE: (f2=0 AND (f1=0 OR f5=0))
        $query = $tcache->queries()->create();
        $query->add('f2')->eq(0);
        $subquery = $query->subquery();
        $subquery->add('f1')->eq(0);
        $subquery->add('f5')->eq(0);
        $subquery->setModeOR(true);
        $this->assertEquals(2, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f2')->ne(0);
        $this->assertEquals(5, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f3')->in(['4', '5', '6']);
        $this->assertEquals(3, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f3')->notin(['4', '5', '6']);
        $this->assertEquals(4, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f4')->gt(' 6 ');
        $this->assertEquals(3, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f4')->gte(' 6 ');
        $this->assertEquals(5, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f1')->lt(0);
        $this->assertEquals(0, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f1')->lte(0);
        $this->assertEquals(2, $items->getCount($query));


        $query = $tcache->queries()->create();
        $query->add('f6')->eq(true);
        $this->assertEquals(3, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f6')->ne(true);
        $this->assertEquals(4, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f6')->ne(true);
        $query->add('f6')->exists(true);
        $this->assertEquals(1, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f7')->all(['A', 'B']);
        $this->assertEquals(2, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f7')->all(['C', 'B', 'A']);
        $this->assertEquals(1, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f7')->in(['A', 'B']);
        $this->assertEquals(6, $items->getCount($query));

        $query = $tcache->queries()->create();
        $query->add('f7')->size(3);
        $this->assertEquals(1, $items->getCount($query));

        $tcache->driver()->getDb()->drop();
    }
}
 