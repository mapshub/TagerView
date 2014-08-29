<?php

namespace TCacheTest;


use Tager\View;
use Tager\Helpers\Corrector;

class CorrectorTest extends \PHPUnit_Framework_TestCase
{
    public function testINTCorrectValuesMaker()
    {
        $tcache = new View();

        $int = $tcache->scheme()->getCriterias()->add("field" . Corrector::VTYPE_INT, Corrector::VTYPE_INT);

        //TEST INT
        $this->assertInternalType("float", $int->getCorrectValue(9999999999999999));
        $this->assertEquals(9999999999999999, $int->getCorrectValue(9999999999999999));

        $this->assertInternalType("float", $int->getCorrectValue('10.5'));
        $this->assertEquals(11, $int->getCorrectValue('10.9'));

        $this->assertInternalType("float", $int->getCorrectValue(3.91823648272837774858573));
        $this->assertEquals(4, $int->getCorrectValue(3.91823648272837774858573));

        $this->assertInternalType("float", $int->getCorrectValue(6.5));
        $this->assertEquals(7, $int->getCorrectValue(6.5));

        $this->assertInternalType("float", $int->getCorrectValue(6.49));
        $this->assertEquals(6, $int->getCorrectValue(6.49));

        $this->assertInternalType("float", $int->getCorrectValue((0.1 + 0.7) * 10));
        $this->assertEquals(8, $int->getCorrectValue((0.1 + 0.7) * 10)); //!!!Nice. Not 7 as with (int)$value;

        $this->assertInternalType("float", $int->getCorrectValue(true));
        $this->assertEquals(1, $int->getCorrectValue(true));
        $this->assertEquals(0, $int->getCorrectValue(false));

        $this->assertInternalType("float", $int->getCorrectValue(null));
        $this->assertEquals(0, $int->getCorrectValue(null));

    }

    public function testBOOLCorrectValuesMaker()
    {
        $tcache = new View();

        $bool = $tcache->scheme()->getCriterias()->add("field" . Corrector::VTYPE_BOOL, Corrector::VTYPE_BOOL);

        //TEST BOOL
        $this->assertInternalType("bool", $bool->getCorrectValue(true));
        $this->assertEquals(true, $bool->getCorrectValue(true));

        $this->assertInternalType("bool", $bool->getCorrectValue(false));
        $this->assertEquals(false, $bool->getCorrectValue(false));

        $this->assertInternalType("bool", $bool->getCorrectValue("false"));
        $this->assertEquals(true, $bool->getCorrectValue("false"));

        $this->assertInternalType("bool", $bool->getCorrectValue(""));
        $this->assertEquals(false, $bool->getCorrectValue(""));

        $this->assertInternalType("bool", $bool->getCorrectValue(0));
        $this->assertEquals(false, $bool->getCorrectValue(0));

        $this->assertInternalType("bool", $bool->getCorrectValue(1));
        $this->assertEquals(true, $bool->getCorrectValue(1));

        $this->assertInternalType("bool", $bool->getCorrectValue(-1));
        $this->assertEquals(true, $bool->getCorrectValue(-1));
    }

    public function testFloatCorrectValuesMaker()
    {
        $tcache = new View();

        $float = $tcache->scheme()->getCriterias()->add("field" . Corrector::VTYPE_FLOAT, Corrector::VTYPE_FLOAT);

        $this->assertInternalType("float", $float->getCorrectValue(true));
        $this->assertEquals(1.0, $float->getCorrectValue(true));

        $this->assertInternalType("float", $float->getCorrectValue(false));
        $this->assertEquals(0, $float->getCorrectValue(false));

        $this->assertInternalType("float", $float->getCorrectValue(' 123.567test j'));
        $this->assertEquals(123.567, $float->getCorrectValue(' 123.567test j'));

        $this->assertInternalType("float", $float->getCorrectValue('g 123.567test j'));
        $this->assertEquals(0, $float->getCorrectValue('g 123.567test j'));
    }

    public function testStringCorrectValuesMaker()
    {
        $tcache = new View();

        $string = $tcache->scheme()->getCriterias()->add("field" . Corrector::VTYPE_STRING, Corrector::VTYPE_STRING);

        $this->assertInternalType("string", $string->getCorrectValue(true));
        $this->assertEquals('1', $string->getCorrectValue(true));

        $this->assertInternalType("string", $string->getCorrectValue(false));
        $this->assertEquals('', $string->getCorrectValue(false));

        $this->assertInternalType("string", $string->getCorrectValue(23543.0987654321));
        $this->assertEquals('23543.098765432', $tcache->sm()->getNumberFormatter(0, 100)->format(
            $string->getCorrectValue(23543.0987654321)
        ));
        $s = $tcache->sm()->getNumberFormatter(0, 100)->format(
            $string->getCorrectValue(9999999999999996.88888888888888888)
        );
        $this->assertEquals('9999999999999999.88888888888888888', $s); //TODO: при $s = 10000000000000000 ХЗ как это получается
        $this->assertEquals('0.000110320343221', $tcache->sm()->getNumberFormatter(0, 100)->format(
            $string->getCorrectValue(0.0258963147 - 0.025785994356779)
        ));

    }

    public function testTimestampCorrectValuesMaker()
    {
        $tcache = new View();

        $timestamp = $tcache->scheme()->getCriterias()->add("field" . Corrector::VTYPE_TIMESTAMP, Corrector::VTYPE_TIMESTAMP);
        $tsCur = time();
        /** @var \MongoDate $tsValueCur */
        $tsValueCur = $timestamp->getCorrectValue($tsCur);
        $this->assertInstanceOf('MongoDate', $tsValueCur);
        $this->assertEquals($tsCur, $tsValueCur->sec);

        $tsCur = false;
        /** @var \MongoDate $tsValueCur */
        $tsValueCur = $timestamp->getCorrectValue($tsCur);
        $this->assertInstanceOf('MongoDate', $tsValueCur);
        $this->assertEquals(0, $tsValueCur->sec);

        $tsCur = 'bukashka';
        /** @var \MongoDate $tsValueCur */
        $tsValueCur = $timestamp->getCorrectValue($tsCur);
        $this->assertInstanceOf('MongoDate', $tsValueCur);
        $this->assertEquals(0, $tsValueCur->sec);
    }

    public function testGetIndexes()
    {
        $data = [
            'isMan' => "false",
            'birthday' => mktime(11, 22, 33, 5, 11, 1982),
            'weight' => '100kg',
            'stars' => 0.0019345,
            'favnumbers' => [1982, 1983, 7098, 7, 777, 35]
        ];

        $tcache = new View();
        $tcache->scheme()->getCriterias()->add("isMan")->setValuesType(Corrector::VTYPE_BOOL);
        $tcache->scheme()->getCriterias()->add("birthday")->setValuesType(Corrector::VTYPE_TIMESTAMP);
        $tcache->scheme()->getCriterias()->add("weight")->setValuesType(Corrector::VTYPE_INT);
        $tcache->scheme()->getCriterias()->add("stars")->setValuesType(Corrector::VTYPE_STRING);
        $tcache->scheme()->getCriterias()->add("favnumbers")->setValuesType(Corrector::VTYPE_STRING)->setTagsMode(true);

        $indexes = $tcache->sm()->getCorrectorHelper()->getIndexes($data);
        $this->assertInternalType("bool", $indexes['isMan']);
        $this->assertTrue($indexes['isMan']); //true == (bool)"false";
        $this->assertInstanceOf("MongoDate", $indexes['birthday']);
        $this->assertEquals($indexes['birthday']->sec, mktime(11, 22, 33, 5, 11, 1982));
        $this->assertInternalType("float", $indexes['weight']);
        $this->assertEquals(100, $indexes['weight']); //100 == (float)"100kg"
        $this->assertInternalType("string", $indexes['stars']);
        $this->assertEquals("0.0019345", $indexes['stars']);
        foreach ($indexes['favnumbers'] as $k => $v) {
            $this->assertInternalType("string", $v);
        }
    }
}
 