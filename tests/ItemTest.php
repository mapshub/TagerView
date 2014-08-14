<?php

namespace TCacheTest;


use TCache\TCache;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    public function testSetData()
    {
        $tcache = new TCache();

        $this->assertEquals(false, $tcache->getServiceManager()->getEmptyItem()->setData(true));
        $this->assertEquals(false, $tcache->getServiceManager()->getEmptyItem()->setData([]));

        $this->assertEquals($item = $tcache->getServiceManager()->getEmptyItem(), $item = $tcache->getServiceManager()->getEmptyItem());

        $data = ['data' => 'test', 'mode' => [0, 1]];
        $item = $tcache->getServiceManager()->getEmptyItem();
        $this->assertInstanceOf('TCache\Items\Item', $item->setData($data));

        $this->assertEquals($data, $item->getData());

        $extracetd = $item->extract();
        $dataHash = $tcache->getServiceManager()->getHashesUtil()->getArrayHash($item->getData());
        $this->assertEquals($dataHash, $extracetd['TcDataHash']);
        $this->assertInternalType("string", $extracetd['TcObjectHash']);
        $this->assertInternalType("string", $extracetd['TcConfigHash']);

        $this->assertInstanceOf("MongoDate", $extracetd['TcDatetimeCreated']);
        $this->assertInstanceOf("MongoDate", $extracetd['TcDatetimeUpdated']);
        $this->assertEquals(date("Y-m-d H:i", $extracetd['TcDatetimeCreated']->sec), date("Y-m-d H:i", time()));
        $this->assertEquals(date("Y-m-d H:i", $extracetd['TcDatetimeUpdated']->sec), date("Y-m-d H:i", time()));
    }

    public function testLoadData()
    {
        $tcache = new TCache();
        $item = $tcache->getServiceManager()->getEmptyItem();

        $mongoDate = new \MongoDate();
        $objectHash = md5("");
        $data = ['guertsy' => 'test'];


        $object = [
            '_id' => "",
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => $objectHash,
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => $tcache->getHashes()->getConfigHash()
        ];

        //нормальный объект
        $this->assertInstanceOf('TCache\Items\Item', $item->load($object));

        //ошибка !array
        $object['TcData'] = "";
        $this->assertFalse($item->load($object));

        //ошибка hash(data) invalid
        $object['TcData'] = [];
        $object['TcDataHash'] = '123123';
        $this->assertFalse($item->load($object));

        //опять нормальный объект
        $object['TcData'] = $data;
        $object['TcDataHash'] = $tcache->getHashes()->getArrayHash($data);
        $this->assertInstanceOf('TCache\Items\Item', $item->load($object));

        //!MOngoDate
        $object['TcDatetimeCreated'] = "";
        $this->assertFalse($item->load($object));

        //!MOngoDate
        $object['TcDatetimeCreated'] = $mongoDate;
        $object['TcDatetimeUpdated'] = "";
        $this->assertFalse($item->load($object));

        //опять нормальный объект
        $object['TcDatetimeCreated'] = $mongoDate;
        $object['TcDatetimeUpdated'] = $mongoDate;
        $this->assertInstanceOf('TCache\Items\Item', $item->load($object));
    }

    public function testGetdata()
    {
        $tcache = new TCache();

        $mongoDate = new \MongoDate();
        $data = ['guertsy' => 'test'];

        $object = [
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => "",
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => ""
        ];

        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->setData($data);
        $this->assertEquals($data, $item->getData());

        $objectToLoad = $object;
        $objectToLoad['_id'] = "";
        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($objectToLoad);
        $this->assertEquals($data, $item->getData());
        $this->assertEquals($data, $item->extract()['TcData']);
    }

    public function testGetLoadedData()
    {
        $tcache = new TCache();
        $mongoDate = new \MongoDate();
        $data = ['guertsy' => 'test'];
        $object = [
            '_id' => 'wefweff',
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => "",
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => ""
        ];
        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($object);
        $this->assertEquals($object, $item->getLoadedData());
        $this->assertArrayNotHasKey("_id", $item->extract());

        $item = $tcache->getServiceManager()->getEmptyItem();
        $res = $item->load($data); //not valid db object load
        $this->assertFalse($res);
        $this->assertEquals(null, $item->getLoadedData());
    }

    public function testExtract()
    {
        $tcache = new TCache();
        $mongoDate = new \MongoDate();
        $data = ['sex' => 'male'];
        $object = [
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => "",
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => ""
        ];

        $objectSource = $object;
        $objectSource['_id'] = 'wefweff';
        $objectSource['fakeField1'] = true;
        $objectSource['fakeField3'] = "test";

        //нет критериев - нет полей индекса в объекте

        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->setData($data);
        $arrIndexes = $tcache->getCorrector()->getIndexes($item->getData());
        $TcDataHash = $tcache->getHashes()->getArrayHash($item->getData());
        $object['TcObjectHash'] = $tcache->getHashes()->getObjectHash($arrIndexes, $TcDataHash);
        $object['TcConfigHash'] = $tcache->getHashes()->getConfigHash();
        $this->assertEquals($object, $item->extract());


        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($objectSource);
        $arrIndexes = $tcache->getCorrector()->getIndexes($item->getData());
        $TcDataHash = $tcache->getHashes()->getArrayHash($item->getData());
        $object['TcObjectHash'] = $tcache->getHashes()->getObjectHash($arrIndexes, $TcDataHash);
        $object['TcConfigHash'] = $tcache->getHashes()->getConfigHash();
        $this->assertEquals($object, $item->extract());

        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($data); //not valid object
        $this->assertNull($item->extract());

        $tcache->getCriterias()->add("sex"); //добавили критерий
        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->setData($data);
        $arrIndexes = $tcache->getCorrector()->getIndexes($item->getData());
        $TcDataHash = $tcache->getHashes()->getArrayHash($item->getData());
        $object['TcObjectHash'] = $tcache->getHashes()->getObjectHash($arrIndexes, $TcDataHash);
        $object['TcConfigHash'] = $tcache->getHashes()->getConfigHash();
        $object['sex'] = "male"; //прверка на наличие поля индекса
        $this->assertEquals($object, $item->extract());


    }

    public function testGetDatetimeCreated()
    {
        $tcache = new TCache();
        $mongoDate = new \MongoDate();
        $data = ['guertsy' => 'test'];
        $object = [
            '_id' => 'mongoid',
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => "",
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => ""
        ];
        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->setData($data);
        $this->assertEquals(date("Y-m-d H:i"), date("Y-m-d H:i", $item->getDatetimeCreatedTs()));

        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($object);
        $this->assertEquals($mongoDate->sec, $item->getDatetimeCreatedTs());
    }

    public function testGetDatetimeUpdated()
    {
        $tcache = new TCache();
        $mongoDate = new \MongoDate();
        $data = ['guertsy' => 'test'];
        $object = [
            '_id' => 'mongoid',
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => "",
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => ""
        ];
        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->setData($data);
        $this->assertEquals(date("Y-m-d H:i"), date("Y-m-d H:i", $item->getDatetimeUpdatedTs()));

        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($object);
        $this->assertEquals($mongoDate->sec, $item->getDatetimeUpdatedTs());
    }

    public function testSetDatetimeUpdatedTs()
    {
        $tcache = new TCache();
        $mongoDate = new \MongoDate();
        $data = ['guertsy' => 'test'];
        $object = [
            '_id' => 'mongoid',
            'TcData' => $data,
            'TcDataHash' => $tcache->getHashes()->getArrayHash($data),
            "TcObjectHash" => "",
            "TcDatetimeCreated" => $mongoDate,
            "TcDatetimeUpdated" => $mongoDate,
            "TcConfigHash" => ""
        ];
        $item = $tcache->getServiceManager()->getEmptyItem();
        $item->load($object);
        $newTimestamp = mktime(22, 55, 03, 5, 11, 1982);
        $item->setDatetimeUpdatedTs($newTimestamp);
        $this->assertEquals("1982-05-11 22:55:03", date("Y-m-d H:i:s", $item->getDatetimeUpdatedTs()));
    }
}
 