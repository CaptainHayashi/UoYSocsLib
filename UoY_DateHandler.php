<?php
/**
 * UoY_DateHandler class
 * 
 * Part of the University of York Society Common Library
 * 
 * PHP version 5.3
 * 
 * @category UoY
 * @package  UoY
 * 
 * @author   Gareth Andrew Lloyd <gareth@ignition-web.co.uk>
 * @author   Matt Windsor <mattwindsor@btinternet.com>
 * 
 * @license  ? ?
 * @link     https://github.com/UniversityRadioYork/UoYSocsLib
 */

require_once 'UoY_Date.php';
require_once 'UoY_Cache.php';

date_default_timezone_set('Europe/London');

/**
 * Class for handling University of York term dates.
 * 
 * This class provides a series of static functions that facilitate the 
 * creation and manipulation of dates in the university's Year/Term/Week/Day 
 * format.
 * 
 * It relies on the presence of term start information.
 * 
 * @category UoY
 * @package  UoY
 * 
 * @author   Gareth Andrew Lloyd <gareth@ignition-web.co.uk>
 * @author   Matt Windsor <mattwindsor@btinternet.com>
 * 
 * @license  ? ?
 * @link     https://github.com/UniversityRadioYork/UoYSocsLib
 */
class UoY_DateHandler
{ 
    /**
     * Converts a date in Unix timestamp format to the format used by the
     * University of York.
     * 
     * @param integer $date The date to convert, as a Unix timestamp.
     * 
     * @return UoY_Date The date, in University of York date format. 
     */
    public static function termInfo($date)
    {
        $year = self::yearNumber($date);
        if (!self::yearExists($year, true)) {
            return false;
        }
        $tmpxml = UoY_Cache::cacheHandle();
        $xmlRes = UoY_Cache::getYearResource($tmpxml, $year);
        $feature[] = @strtotime("1st September $year");//inclusive
        $feature[] = @strtotime("1st September " . ($year + 1));//exclusive
        foreach ($xmlRes[0]->term as $t) {
            $feature[] = self::floorMonday($t->start);//inclusive
            $feature[] = @strtotime("next Monday ".($t->end));//exclusive
        }
        sort($feature, SORT_NUMERIC);
        //TODO rename to ??? $term isn't correct
        $term = 0;
        for ($i = 0; $i < count($feature) - 1; $i = $i + 1) {
            if (($date >= $feature[$i]) && ($date < $feature[$i + 1])) {
                $term = $i;
                break;
            }
        }
        //0 - $year-1 summer break
        //1 - term 1
        //2 - $year christmas break
        //3 - term 2
        //4 - $year easter break
        //5 - term 3
        //6 - $year summer break
        if ($term != 0) {
            $relativetoterm = $date - $feature[$term];
            $relativetoterm /= 60 * 60 * 24 * 7;
            $week = (int) $relativetoterm + 1;
        } else {
            $start = @strtotime("31st August " . $year);
            $weekdayoffset = @strtotime("last Monday", $start);
            $term_details = self::termInfo($weekdayoffset);
            if (!$term_details) {
                return false; //can't infer any information for the week number
            }
            $relativetoterm = $date - $weekdayoffset;
            $relativetoterm /= 60 * 60 * 24 * 7;
            $week = (int) $relativetoterm + $term_details->getWeek();
        }
        $weeknum = $week;
        $termnum = (($term % 2) == 1) ? ($term + 1) / 2 : 0;
        $breaknum = (($term % 2) == 0) ? ($term) / 2 : 0;
        if ($term == 0) {
            $breaknum = 3;
        }
        $yearnum = ($term != 0) ? $year : $year - 1;

        return new UoY_Date(
            intval($yearnum),
            intval($termnum) === 0 ? intval($breaknum) : intval($termnum),
            (intval($termnum) === 0), // Whether or not this is a break
            intval($weeknum),
            intval($date) 
        );
    }

    
    /**
     * Function used to test the date handler.
     * 
     * @todo Unit test?
     * @return nothing.
     */
    public static function test()
    {
        $day = @strtotime("1st September 2010");
        for ($i = 0; $i < 365*2; $i++) {
            echo @date("Y-m-d", $day) . "\n";
            if (self::termInfo($day) === false) {
                echo "not convertable using given data.\n";
            } else {
                echo self::termInfo($day)->toString() . "\n";
            }
            $day = @strtotime(@date("Y-m-d", $day) . " +1 day");
        }
    }
    
    
    /**
     * Checks whether or not the given academic year exists in the system.
     * 
     * Data for the year existing in the system is a necessary prerequisite
     * for the 
     * 
     * @param integer $year   The year to look up.
     * @param boolean $update If true, the system will update itself. (?)
     * 
     * @return boolean Whether or not the year exists in the system.
     */
    public static function yearExists($year, $update = false)
    {
        if (!UoY_Cache::cacheExists()) {
            return false; //cache file missing and can't be made
        }
        $tmpxml = UoY_Cache::cacheHandle();
        $xmlRes = UoY_Cache::getYearResource($tmpxml, $year);
        if (($xmlRes == array()) && $update) {
            UoY_Cache::updateCache();
            $xmlRes = UoY_Cache::getYearResource($tmpxml, $year);
        }
        return $xmlRes != array(); //no year exist in xml even after update
    }

    
    /**
     * Returns the academic year of the given date.
     * 
     * @param integer $date The date, as a Unix timestamp.
     * 
     * @return integer The academic year of the given date, as defined as the
     *                 calendar year upon which Monday Week 1 Autumn falls.
     */
    public static function yearNumber($date)
    {
        // assumption 01-Sept is the earliest academic year start
        return @date("Y", $date - @strtotime("1st September 1970"));
    }
    
    
    /**
     * Adds a given offset, in days, to a university date.
     * 
     * This function effectively brings the given date forwards by the given 
     * number of days.  As one would expect, a negative number puts the given
     * date backwards.
     * 
     * @param UoY_Date $date   The date to which the offset is to be added.
     * @param integer  $offset The offset, in days, to be added to the date.
     *                         The offset may be negative, in which case the
     *                         offset is subtracted from the date as expected.
     * 
     * @return UoY_Date A date object representing the inputted date with the
     *                  offset added or subtracted.  This is not necessarily
     *                  the same object instance as the inputted object.
     * 
     * @throw InvalidArgumentException if the offset is not an integer.
     */
    public static function addDayOffset(UoY_Date $date, $offset)
    {
        return self::addOffset($date, $offset, 'days');
    }
    
    
    /**
     * Adds a given offset, in weeks, to a university date.
     * 
     * This function effectively brings the given date forwards by the given 
     * number of weeks.  As one would expect, a negative number puts the given
     * date backwards.
     * 
     * @param UoY_Date $date   The date to which the offset is to be added.
     * @param integer  $offset The offset, in weeks, to be added to the date.
     *                         The offset may be negative, in which case the
     *                         offset is subtracted from the date as expected.
     * 
     * @return UoY_Date A date object representing the inputted date with the
     *                  offset added or subtracted.  This is not necessarily
     *                  the same object instance as the inputted object.
     * 
     * @throw InvalidArgumentException if the offset is not an integer.
     */
    public static function addWeekOffset(UoY_Date $date, $offset)
    {
        return self::addOffset($date, $offset, 'weeks');
    }
    
    
    /**
     * Adds a given offset, in weeks, to a university date.
     * 
     * This function effectively brings the given date forwards by the given 
     * number of weeks.  As one would expect, a negative number puts the given
     * date backwards.
     * 
     * @param UoY_Date $date   The date to which the offset is to be added.
     * @param integer  $offset The offset, in weeks, to be added to the date.
     *                         The offset may be negative, in which case the
     *                         offset is subtracted from the date as expected.
     * @param string   $unit   The name of the unit; this must be a plural.
     *                         Examples include 'days' and 'weeks'.
     * 
     * @return UoY_Date A date object representing the inputted date with the
     *                  offset added or subtracted.  This is not necessarily
     *                  the same object instance as the inputted object.
     * 
     * @throw InvalidArgumentException if the offset is not an integer, or the
     *                                 unit is not a string.
     */  
    protected static function addOffset(UoY_Date $date, $offset, $unit)
    {
        if (is_integer($offset)) {
            throw new InvalidArgumentException('Offset must be an integer.');
        } else if (is_string($unit)) {
            throw new InvalidArgumentException('Unit not a string.');
        }
        
        $oldTimestamp = $date->getEpoch();
        $offsetString = sprintf('%+u %s', $oldTimestamp, $unit);
        $newTimestamp = strtotime($offsetString, $oldTimestamp);
        
        return self::termInfo($newTimestamp);
    }
    
    
    /**
     * Floors the given date string to the previous Monday. (?)
     * 
     * @param string $datestr A string representing the date.
     * 
     * @return integer A Unix timestamp representing the floored date. 
     */
    protected static function floorMonday($datestr)
    {
        $prevMon = @strtotime("last Monday" . $datestr);
        $m1week = @strtotime($datestr . " -1 week");
        if ($prevMon == $m1week) {
            return @strtotime($datestr);
        } else {
            return $prevMon;
        }
    }
}

?>
