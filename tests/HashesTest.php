<?php

namespace TCacheTest;


use TCache\TCache;

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

        $tcache = new TCache();
        $h1 = $tcache->getHashes()->getArrayHash($arr1);
        $h2 = $tcache->getHashes()->getArrayHash($arr2);

        $this->assertEquals($h1, $h2);
    }
}
 