<?php

class CacheTest extends PHPUnit_Framework_TestCase
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

    public function testCache_1()
    {

        $memcacheServer = new Memcache();
        $memcacheServer->connect("localhost", 11211);

        $mongoClient = new \MongoClient();
        $mongoDb = $mongoClient->selectDB("TCacheTest");
        $itemsCollection = $mongoDb->selectCollection("CacheTest_testCache_1");
        $cacheCollection = $mongoDb->selectCollection("CacheTest_testCache_1_cache");

        $view = new Tager\View();

        $view->driver()->connectTo($itemsCollection);

        $slowCache = $view->cache()->master();
        $fastCache = $view->cache()->memcache();

        $this->assertFalse($slowCache->isConnected());
        $this->assertFalse($fastCache->isConnected());

        $slowCache->driver()->connectTo($cacheCollection);
        $fastCache->connectTo($memcacheServer);

        $this->assertTrue($slowCache->isConnected());
        $this->assertTrue($fastCache->isConnected());

        $slowCache->setEnabled(false);
        $fastCache->setEnabled(false);

        $this->assertFalse($slowCache->isConnected());
        $this->assertFalse($fastCache->isConnected());

        $mongoDb->drop();
    }

    public function testItemsCount()
    {
        $memcacheServer = new Memcache();
        $memcacheServer->connect("localhost", 11211);

        $mongoClient = new \MongoClient();
        $mongoDb = $mongoClient->selectDB("TCacheTest");
        $itemsCollection = $mongoDb->selectCollection("CacheTest_testItemsCount");
        $cacheCollection = $mongoDb->selectCollection("CacheTest_testItemsCount_cache");

        $view = new Tager\View();
        $view->scheme()->getCriterias()->add('f1', \Tager\Helpers\Corrector::VTYPE_INT);

        $view->driver()->connectTo($itemsCollection);
        $view->cache()->master()->driver()->connectTo($cacheCollection);
        $view->cache()->memcache()->connectTo($memcacheServer);

        $query = $view->queries()->create();
        $query->add('f1')->in([1, 0, 2]);

        $count = $view->queries()->findCount($query);
        $this->assertEquals(0, $count);

        $masterCount = $view->cache()->master()->cacheGetItemsCount($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash());
        $this->assertEquals($count, $masterCount);

        $memcacheCount = $view->cache()->memcache()->cacheGetItemsCount($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash());
        $this->assertEquals($count, $memcacheCount);

        foreach ($this->data as $itemData) {
            $item = $view->items()->createWithData($itemData);
            $view->items()->save($item);
        }

        $view->cache()->invalidate();

        $count = $view->queries()->findCount($query);
        $this->assertEquals(5, $count);

        $masterCount = $view->cache()->master()->cacheGetItemsCount($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash());
        $this->assertEquals($count, $masterCount);

        $memcacheCount = $view->cache()->memcache()->cacheGetItemsCount($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash());
        $this->assertEquals($count, $memcacheCount);

        $view->cache()->invalidate();
        $mongoDb->drop();
    }

    public function testItemsSelect()
    {
        $memcacheServer = new Memcache();
        $memcacheServer->connect("localhost", 11211);

        $mongoClient = new \MongoClient();
        $mongoDb = $mongoClient->selectDB("TCacheTest");
        $itemsCollection = $mongoDb->selectCollection("CacheTest_testItemsSelect");
        $cacheCollection = $mongoDb->selectCollection("CacheTest_testItemsSelect_cache");

        $view = new Tager\View();
        $view->scheme()->getCriterias()->add('f1', \Tager\Helpers\Corrector::VTYPE_INT);

        $view->driver()->connectTo($itemsCollection);
        $view->cache()->master()->driver()->connectTo($cacheCollection);
        $view->cache()->memcache()->connectTo($memcacheServer);

        //
        $expected = [
            ['f1' => 0, 'f2' => 0, 'f3' => 2, 'f4' => 8, 'f5' => 1, 'f6' => true, 'f7' => ['A', 'B', 'C'], 'f8' => 'A'],
            ['f1' => 0, 'f2' => 2, 'f3' => 4, 'f4' => 8, 'f5' => 7, 'f7' => ['B'], 'f8' => 'H'],
            ['f1' => 1, 'f2' => 5, 'f3' => 3, 'f4' => 6, 'f5' => 8, 'f7' => ['B', 'C'], 'f8' => 'R'],
            ['f1' => 2, 'f2' => 3, 'f3' => 5, 'f4' => 8, 'f5' => 4, 'f6' => false, 'f7' => ['A'], 'f8' => 'D'],
            ['f1' => 2, 'f2' => 2, 'f3' => 3, 'f4' => 1, 'f5' => 6, 'f6' => true, 'f7' => ['A', 'C'], 'f8' => 'Y'],
        ];

        $resultBuilder = function ($result) {
            $resultActual = [];
            foreach ($result as $next) {
                $resultActual[] = $next['TcData'];
            }
            return $resultActual;
        };

        //
        $query = $view->queries()->create();
        $query->add('f1')->in([1, 0, 2]);
        $query->setLimit(100);

        $actual = $resultBuilder($view->queries()->findDocuments($query));
        $this->assertEquals([], $actual);

        $masterActual = $view->cache()->master()->cacheGetItems($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash());
        $this->assertEquals([], $masterActual);

        $memcacheActulal = $view->cache()->memcache()->cacheGetItems($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash());
        $this->assertEquals([], $memcacheActulal);

        foreach ($this->data as $itemData) {
            $item = $view->items()->createWithData($itemData);
            $view->items()->save($item);
        }

        $view->cache()->invalidate();

        $actual = $resultBuilder($view->queries()->findDocuments($query));
        $this->assertEquals($expected, $actual);

        $masterActual = $resultBuilder($view->cache()->master()->cacheGetItems($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash()));
        $this->assertEquals($expected, $masterActual);

        $memcacheActulal = $resultBuilder($view->cache()->memcache()->cacheGetItems($view->sm()->getHashesHelper()->getConfigHash(), $query->getHash()));
        $this->assertEquals($expected, $memcacheActulal);

        $view->cache()->invalidate();
        $mongoDb->drop();
    }

    public function testDistinctValues()
    {
        $memcacheServer = new Memcache();
        $memcacheServer->connect("localhost", 11211);

        $mongoClient = new \MongoClient();
        $mongoDb = $mongoClient->selectDB("TCacheTest");
        $itemsCollection = $mongoDb->selectCollection("CacheTest_testDistinctValues");
        $cacheCollection = $mongoDb->selectCollection("CacheTest_testDistinctValues_cache");

        $view = new Tager\View();
        $view->scheme()->getCriterias()->add('f1', \Tager\Helpers\Corrector::VTYPE_INT);
        $view->scheme()->getCriterias()->add('f7')->setValuesType(\Tager\Helpers\Corrector::VTYPE_STRING)->setTagsMode(true);

        $view->driver()->connectTo($itemsCollection);
        $view->cache()->master()->driver()->connectTo($cacheCollection);
        $view->cache()->memcache()->connectTo($memcacheServer);

        //
        $expected = [
            ['count' => 2, 'value' => 0],
            ['count' => 1, 'value' => 1],
            ['count' => 2, 'value' => 2],
        ];
        //
        $query = $view->queries()->create();
        $query->add('f1')->in([1, 0, 2]);
        $query->setLimit(100);

        $queryHash = $view->sm()->getHashesHelper()->getAggregationQueryHash('f1', $query, false);

        $actual = $view->queries()->findDistinctValues('f1', $query);
        $this->assertEquals([], $actual);

        $masterActual = $view->cache()->master()->cacheGetDistinctValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals([], $masterActual);

        $memcacheActulal = $view->cache()->memcache()->cacheGetDistinctValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals([], $memcacheActulal);


        foreach ($this->data as $itemData) {
            $item = $view->items()->createWithData($itemData);
            $view->items()->save($item);
        }

        $view->cache()->invalidate();

        $actual = $view->queries()->findDistinctValues('f1', $query);
        $this->assertEquals($expected, $actual);

        $masterActual = $view->cache()->master()->cacheGetDistinctValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals($expected, $masterActual);

        $memcacheActulal = $view->cache()->memcache()->cacheGetDistinctValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals($expected, $memcacheActulal);

        $view->cache()->invalidate();
        $mongoDb->drop();
    }

    public function testMinMaxValues()
    {

        $memcacheServer = new Memcache();
        $memcacheServer->connect("localhost", 11211);

        $mongoClient = new \MongoClient();
        $mongoDb = $mongoClient->selectDB("TCacheTest");
        $itemsCollection = $mongoDb->selectCollection("CacheTest_testMinMaxValues");
        $cacheCollection = $mongoDb->selectCollection("CacheTest_testMinMaxValues_cache");

        $view = new Tager\View();
        $view->scheme()->getCriterias()->add('f1', \Tager\Helpers\Corrector::VTYPE_INT);
        $view->scheme()->getCriterias()->add('f7')->setValuesType(\Tager\Helpers\Corrector::VTYPE_STRING)->setTagsMode(true);

        $view->driver()->connectTo($itemsCollection);
        $view->cache()->master()->driver()->connectTo($cacheCollection);
        $view->cache()->memcache()->connectTo($memcacheServer);

        //
        $expected = ['min' => 0, 'max' => 2];
        //
        $query = $view->queries()->create();
        $query->add('f1')->in([1, 0, 2]);
        $query->setLimit(100);

        $queryHash = $view->sm()->getHashesHelper()->getAggregationQueryHash('f1', $query, false);

        $actual = $view->queries()->findMinMaxValues('f1', $query);
        $this->assertEquals([], $actual);

        $masterActual = $view->cache()->master()->cacheGetMinMaxValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals([], $masterActual);

        $memcacheActulal = $view->cache()->memcache()->cacheGetMinMaxValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals([], $memcacheActulal);




        foreach ($this->data as $itemData) {
            $item = $view->items()->createWithData($itemData);
            $view->items()->save($item);
        }

        $view->cache()->invalidate();

        $actual = $view->queries()->findMinMaxValues('f1', $query);
        $this->assertEquals($expected, $actual);

        $masterActual = $view->cache()->master()->cacheGetMinMaxValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals($expected, $masterActual);

        $memcacheActulal = $view->cache()->memcache()->cacheGetMinMaxValues($view->sm()->getHashesHelper()->getConfigHash(), $queryHash);
        $this->assertEquals($expected, $memcacheActulal);

        $view->cache()->invalidate();
        $mongoDb->drop();
    }
}