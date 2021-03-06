<?php

namespace TCacheTest;

use Tager\View;
use Tager\Helpers\Corrector;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testQueryBuilder_0()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_TIMESTAMP);

        $items = $tcache->items();

        $query = $tcache->queries()->create();

        $arg = time();
        $argCor = $query->add('f1')->eq($arg)->extract();
        $this->assertTrue(isset($argCor['f1']));
        $this->assertInstanceOf('MongoDate', $argCor['f1']);

        $arg = (0.1+0.7)*10;
        $condition = $query->add('none')->eq($arg);
        $argCor = $condition->extract();
        $this->assertTrue(isset($argCor['none']));
        $this->assertInternalType("float", $argCor['none']);

        $condition->setValuesType(Corrector::VTYPE_BOOL);
        $argCor = $condition->extract();
        $this->assertTrue(isset($argCor['none']));
        $this->assertEquals(true, $argCor['none']);
        $this->assertInternalType("bool", $argCor['none']);

        $condition->setValuesType("nonexistingtype");
        $argCor = $condition->extract();
        $this->assertTrue(isset($argCor['none']));
        $this->assertInternalType("string", $argCor['none']);
    }

    public function testQueryBuilder_size()
    {
        $tcache = new View();

        $tcache->scheme()->getCriterias()->add("f1")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $items = $tcache->items();

        $query = $tcache->queries()->create();
        $condition = $query->add('f1')->size(3);
        $correctedArg = $condition->extract();
        $this->assertTrue(isset($correctedArg['f1']));
        $this->assertTrue(isset($correctedArg['f1']['$size']));
        $this->assertInternalType("float", $correctedArg['f1']['$size']);
    }
}
 