<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Utils;

use DateTime;

use OroCRM\Bundle\MagentoBundle\Utils\DatePeriodUtils;

class DatePeriodUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDaysWithMonthOver()
    {
        $from = new DateTime('2013-03-29');
        $to = new DateTime('2013-04-02');

        $expectedResult = [
            2013 => [
                3 => [
                    29 => 0,
                    30 => 0,
                    31 => 0,
                ],
                4 => [
                    1 => 0,
                    2 => 0,
                ],
            ],
        ];

        $this->assertEquals($expectedResult, DatePeriodUtils::getDays($from, $to));
    }

    public function testGetDaysWithYearOver()
    {
        $from = new DateTime('2013-12-29');
        $to = new DateTime('2014-01-02');

        $expectedResult = [
            2013 => [
                12 => [
                    29 => 0,
                    30 => 0,
                    31 => 0,
                ],
            ],
            2014 => [
                1 => [
                    1 => 0,
                    2 => 0,
                ],
            ],
        ];

        $this->assertEquals($expectedResult, DatePeriodUtils::getDays($from, $to));
    }

    public function testGetDateWith3Years()
    {
        $from = new DateTime('2014-10-03');
        $to = new DateTime('2016-07-05');

        $expectedResult = [
            2014=> [
                10 => array_combine(range(3, 31), array_fill(3, 29, 0)),
                11 => array_combine(range(1, 30), array_fill(1, 30, 0)),
                12 => array_combine(range(1, 31), array_fill(1, 31, 0)),
            ],
            2015=> [
                1  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                2  => array_combine(range(1, 28), array_fill(1, 28, 0)),
                3  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                4  => array_combine(range(1, 30), array_fill(1, 30, 0)),
                5  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                6  => array_combine(range(1, 30), array_fill(1, 30, 0)),
                7  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                8  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                9  => array_combine(range(1, 30), array_fill(1, 30, 0)),
                10 => array_combine(range(1, 31), array_fill(1, 31, 0)),
                11 => array_combine(range(1, 30), array_fill(1, 30, 0)),
                12 => array_combine(range(1, 31), array_fill(1, 31, 0)),
            ],
            2016 => [
                1  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                2  => array_combine(range(1, 29), array_fill(1, 29, 0)),
                3  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                4  => array_combine(range(1, 30), array_fill(1, 30, 0)),
                5  => array_combine(range(1, 31), array_fill(1, 31, 0)),
                6  => array_combine(range(1, 30), array_fill(1, 30, 0)),
                7  => array_combine(range(1, 5), array_fill(1, 5, 0)),
            ],
        ];

        $this->assertEquals($expectedResult, DatePeriodUtils::getDays($from, $to));
    }
}
