<?php
/**
 * UoY_DateTest test case
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

// Imports, in turn, UoY_DateConstants.php
require_once dirname(__FILE__) . '/../UoY_Date.php';

/**
 * Test class for UoY_Date.
 * Generated by PHPUnit on 2011-07-07 at 14:50:04.
 * 
 * @category UoY
 * @package  UoY
 * 
 * @author   Matt Windsor <mattwindsor@btinternet.com>
 * 
 * @license  ? ?
 * @link     https://github.com/UniversityRadioYork/UoYSocsLib
 */
class UoY_DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UoY_Date
     */
    protected $object;

    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * 
     * @return mixed Nothing.
     */
    protected function setUp()
    {
        $this->object = new UoY_Date(
            2010,
            UoY_DateConstants::TERM_AUTUMN,
            false,
            1,
            strtotime('2010-10-11')
        );
    }

    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return mixed Nothing.
     */
    protected function tearDown()
    {
        
    }

    
    /**
     * Tests the getYear function.
     * 
     * @return mixed Nothing.
     */
    public function testGetYear()
    {
        $this->assertSame($this->object->getYear(), 2010);

        // Ensure the year is properly stored for next-calendar-year terms.
        $summerTest = new UoY_Date(
            2010,
            UoY_DateConstants::TERM_SUMMER,
            false,
            1,
            UoY_DateConstants::DAY_MONDAY,
            0 // Don't need epoch for this test
        );

        $this->assertSame($summerTest->getYear(), 2010);
    }

    
    /**
     * Data provider for testGetTerm.
     * 
     * @return array An array of data for term function tests.
     */
    public function dataForGetTerm()
    {
        return array(
            array(
                UoY_DateConstants::TERM_AUTUMN,
                UoY_DateConstants::NAME_TERM_AUTUMN,
                false
            ),
            array(
                UoY_DateConstants::TERM_SPRING,
                UoY_DateConstants::NAME_TERM_SPRING,
                false
            ),
            array(
                UoY_DateConstants::TERM_SUMMER,
                UoY_DateConstants::NAME_TERM_SUMMER,
                false
            ),
            array(
                UoY_DateConstants::BREAK_WINTER,
                UoY_DateConstants::NAME_BREAK_WINTER,
                true
            ),
            array(
                UoY_DateConstants::BREAK_SPRING,
                UoY_DateConstants::NAME_BREAK_SPRING,
                true
            ),
            array(
                UoY_DateConstants::BREAK_SUMMER,
                UoY_DateConstants::NAME_BREAK_SUMMER,
                true
            ),
        );
    }

    
    /**
     * Tests the getTerm function.
     * 
     * @param integer $term    The term for which the test object should be 
     *                         created.
     * 
     * @param integer $name    The expected name of the above term.
     * 
     * @param integer $isBreak Whether or not the test object's term is a break.
     * 
     * @dataProvider dataForGetTerm
     * @return mixed Nothing.
     */
    public function testGetTerm($term, $name, $isBreak)
    {
        $test = new UoY_Date(
            2010,
            $term,
            $isBreak,
            1,
            0 // Don't need epoch for this test
        );

        $this->assertEquals($test->getTerm(), $term);
    }

    
    /**
     * Tests the getTermName function.
     * 
     * @param integer $term    The term for which the test object should be 
     *                         created.
     * 
     * @param integer $name    The expected name of the above term.
     * 
     * @param integer $isBreak Whether or not the test object's term is a break.
     * 
     * @return mixed Nothing.
     * 
     * @dataProvider dataForGetTerm
     */
    public function testGetTermName($term, $name, $isBreak)
    {
        $test = new UoY_Date(
            2010,
            $term,
            $isBreak,
            1,
            0 // Don't need epoch for this test
        );

        $this->assertEquals($test->getTermName(), $name);
    }

    
    /**
     * Tests the isInBreak function.
     * 
     * @param integer $term    The term for which the test object should be 
     *                         created.
     * 
     * @param integer $name    The expected name of the above term.
     * 
     * @param integer $isBreak Whether or not the test object's term is a break.
     *
     * @dataProvider dataForGetTerm
     * @return mixed Nothing.
     */
    public function testIsInBreak($term, $name, $isBreak)
    {
        $test = new UoY_Date(
            2010,
            $term,
            $isBreak,
            1,
            0 // Don't need epoch for this test
        );

        $this->assertEquals($test->isInBreak(), $isBreak);
    }

    
    /**
     * Tests the getWeek function.
     * 
     * @return mixed Nothing.
     */
    public function testGetWeek()
    {
        $this->assertSame($this->object->getWeek(), 1);

        // Ensure the week is properly retrieved for break dates.
        $breakTest = new UoY_Date(
            2010,
            UoY_DateConstants::BREAK_SUMMER,
            true,
            2,
            0 // Don't need epoch for this test
        );

        $this->assertSame($breakTest->getWeek(), 2);
    }

    
    /**
     * Data provider for testGetDay.
     * 
     * @return array An array of data for day function tests.
     */
    public function dataForGetDay()
    {
        return array(
            array(
                strtotime("2010-10-11"),
                UoY_DateConstants::DAY_MONDAY,
                UoY_DateConstants::NAME_DAY_MONDAY,
                false
            ),
            array(
                strtotime("2010-10-12"),
                UoY_DateConstants::DAY_TUESDAY,
                UoY_DateConstants::NAME_DAY_TUESDAY,
                false
            ),
            array(
                strtotime("2010-10-13"),
                UoY_DateConstants::DAY_WEDNESDAY,
                UoY_DateConstants::NAME_DAY_WEDNESDAY,
                false
            ),
            array(
                strtotime("2010-10-14"),
                UoY_DateConstants::DAY_THURSDAY,
                UoY_DateConstants::NAME_DAY_THURSDAY,
                false
            ),
            array(
                strtotime("2010-10-15"),
                UoY_DateConstants::DAY_FRIDAY,
                UoY_DateConstants::NAME_DAY_FRIDAY,
                false
            ),
            array(
                strtotime("2010-10-16"),
                UoY_DateConstants::DAY_SATURDAY,
                UoY_DateConstants::NAME_DAY_SATURDAY,
                false
            ),
            array(
                strtotime("2010-10-17"),
                UoY_DateConstants::DAY_SUNDAY,
                UoY_DateConstants::NAME_DAY_SUNDAY,
                false
            ),
        );
    }

    
    /**
     * Tests the getDay function.
     * 
     * @param integer $epoch   The UNIX epoch to test with.
     * @param integer $day     The expected identifier of the day.
     * @param integer $dayName The expected name of the day (unused).
     *
     * @dataProvider dataForGetDay
     * @return mixed Nothing.
     */
    public function testGetDay($epoch, $day, $dayName)
    {
        $test = new UoY_Date(
            2010,
            UoY_DateConstants::TERM_AUTUMN,
            false,
            1,
            $epoch
        );
        $this->assertSame($test->getDay(), $day);
    }

    
    /**
     * Tests the getDayName function.
     * 
     * @param integer $epoch   The UNIX epoch to test with.
     * @param integer $day     The expected identifier of the day (unused).
     * @param integer $dayName The expected name of the day.
     * 
     * @dataProvider dataForGetDay
     * @return mixed nothing.
     */
    public function testGetDayName($epoch, $day, $dayName)
    {
        $test = new UoY_Date(
            2010,
            UoY_DateConstants::TERM_AUTUMN,
            false,
            1,
            $epoch
        );
        $this->assertSame($test->getDayName(), $dayName);
    }

    
    /**
     * Tests the getEpoch function.
     * 
     * @return nothing.
     */
    public function testGetEpoch()
    {
        $this->assertSame($this->object->getEpoch(), strtotime("2010-10-11"));
    }
    
    
    /**
     * Tests the toString function.
     * 
     * @return nothing.
     */
    public function testToString()
    {
        $this->assertSame(
            $this->object->toString(),
            'Monday Week 1, Autumn Term 2010/11'
        );
    }
}

?>
