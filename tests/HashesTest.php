<?php

namespace TCacheTest;


use Tager\Helpers\Corrector;
use Tager\View;

class HashesTest extends \PHPUnit_Framework_TestCase
{
    public function testHashes()
    {
        $arr1 = [
            0 => 76,
            'a' => 'beee',
            'zero' => 'day',
            'кошка' => 'собака',
            'volumes' => [
                1, 2, (0.1 + 0.7) * 10
            ]
        ];

        $arr2 = [
            'кошка' => 'собака',
            'zero' => 'day',
            0 => 76,
            'volumes' => [
                1, 2, (0.1 + 0.7) * 10
            ],
            'a' => 'beee'
        ];

        $tcache = new View();
        $h1 = $tcache->sm()->getHashesHelper()->getArrayHash($arr1);
        $h2 = $tcache->sm()->getHashesHelper()->getArrayHash($arr2);

        $this->assertEquals($h1, $h2);
    }

    public function testQueryHash()
    {
        $view = new View();

        $query = $view->queries()->create();
        $query->add('producer')->in(["Mazda", "Honda", "Lada"]);
        $query->add('fuel')->eq("бензин");
        $query->add('fuel')->ne(0)->setValuesType(Corrector::VTYPE_BOOL);
        $hash1 = $query->getHash();

        $query = $view->queries()->create();
        $query->add('producer')->in(["Honda", "Lada", "Mazda"]);
        $query->add('fuel')->eq("бензин");
        $query->add('fuel')->ne(0)->setValuesType(Corrector::VTYPE_BOOL);
        $hash2 = $query->getHash();

        $this->assertEquals($hash1, $hash2);

        $query = $view->queries()->create();
        $query->add('producer')->in(["Honda", "Lada", "Mazda"]);
        $query->add('fuel')->eq("бензин");
        $query->add('fuel')->ne(0)->setValuesType(Corrector::VTYPE_INT);
        $hash3 = $query->getHash();

        $this->assertNotEquals($hash1, $hash3);

    }
}