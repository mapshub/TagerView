<?php

class MemcacheTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Tager\Cache\Memcache\Exception\MemcacheConnectFailException
     */
    public function testSetData_shouldThrowException_whenNotActualHostAndPort()
    {
        $tcache = new \Tager\View();

        $memcache = $tcache->cache()->memcache();
        $memcache->connectTo("example.com", "1233");
    }

    /**
     * @expectedException \Tager\Cache\Memcache\Exception\MemcacheNotConnectedException
     */
    public function testSetData_shouldThrowException_whenNotConnected()
    {
        $tcache = new \Tager\View();
        $memcache = $tcache->cache()->memcache();
        $memcache->setData("guertsy", "test");
    }

    public function testConnect()
    {
        $tcache = new \Tager\View();
        $tcache->cache()->memcache()->connectTo();
        $this->assertTrue($tcache->cache()->memcache()->isConnected());
    }

    public function testSetData()
    {
        $tcache = new \Tager\View();
        $memcache = $tcache->cache()->memcache()->connectTo();

        $memcacheActual = new Memcache();
        $memcacheActual->connect("localhost", 11211);

        $expectedValue = new stdClass();
        $expectedValue->arr = [0, 1, 2, 3];
        $expectedValue->boo = false;


        $memcache->setData("tager-test", $expectedValue);

        $source = $memcacheActual->get("tager-test");
        $actualvalue = $source !== false ? unserialize($source) : null;

        $this->assertEquals($expectedValue, $actualvalue);

        $memcacheActual->flush();
    }

    public function testGetData()
    {
        $tcache = new \Tager\View();
        $memcache = $tcache->cache()->memcache()->connectTo();

        $expectedValue = new stdClass();
        $expectedValue->arr = [0, 1, 2, 3];
        $expectedValue->boo = false;

        $memcache->setData("tager-test-set-get", $expectedValue);

        $tcache2 = new \Tager\View();
        $actualValue = $tcache2->cache()->memcache()->connectTo()->getData("tager-test-set-get");

        $this->assertEquals($expectedValue, $actualValue);
    }
}
 