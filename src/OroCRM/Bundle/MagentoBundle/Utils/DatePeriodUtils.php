<?php

namespace OroCRM\Bundle\MagentoBundle\Utils;

use DateTime;

class DatePeriodUtils
{
    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param mixed $initialValue
     *
     * @return array
     */
    public static function getDays(DateTime $from, DateTime $to, $initialValue = 0)
    {
        $result = [];

        $years = range($from->format('Y'), $to->format('Y'));
        $yearsIt = 0;
        $yearsCount = count($years);
        foreach ($years as $year) {
            $firstMonth = static::getFirstMonth($yearsIt, $yearsCount, $from);
            $lastMonth = static::getLastMonth($yearsIt, $yearsCount, $to);
            $months = range($firstMonth, $lastMonth);

            $monthsIt = 0;
            $monthsCount = count($months);
            foreach ($months as $month) {
                $firstDay = !$yearsIt && !$monthsIt ? (int) $from->format('d') : 1;
                $lastDay = $yearsCount - 1 === $yearsIt && $monthsCount - 1 === $monthsIt
                    ? (int) $to->format('d')
                    : (int) date('t', mktime(0, 0, 0, $month, 1, $year));
                $days = range($firstDay, $lastDay);

                $result[$year][$month] = array_combine($days, array_fill(reset($days), count($days), $initialValue));
                $monthsIt++;
            }

            $yearsIt++;
        }

        return $result;
    }

    /**
     * @param int $yearOrder
     * @param int $yearsCount
     * @param DateTime $from
     *
     * @return int
     */
    protected static function getFirstMonth($yearOrder, $yearsCount, DateTime $from)
    {
        return !$yearOrder || ($yearOrder && !($yearsCount - 1)) ? (int) $from->format('m') : 1;
    }

    /**
     * @param int $yearOrder
     * @param int $yearsCount
     * @param DateTime $to
     *
     * @return int
     */
    protected static function getLastMonth($yearOrder, $yearsCount, DateTime $to)
    {
        return $yearsCount - 1 === $yearOrder ? (int) $to->format('m') : 12;
    }
}
