<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Util\Tests\Core;

use PHPUnit\Framework\TestCase;
use PHPCompatibility\Util\Tests\TestHelperPHPCompatibility;
use PHPCSUtils\BackCompat\Helper;

/**
 * Tests for various stand-alone utility functions.
 *
 * @group utilityMiscFunctions
 * @group utilityFunctions
 *
 * @since 7.0.6
 */
class FunctionsUnitTest extends TestCase
{

    /**
     * A wrapper for the abstract PHPCompatibility sniff.
     *
     * @var \PHPCompatibility\Sniff
     */
    protected $helperClass;


    /**
     * Sets up this unit test.
     *
     * @before
     *
     * @return void
     */
    protected function setUpHelper()
    {
        $this->helperClass = new TestHelperPHPCompatibility();
    }

    /**
     * Clean up after finished test.
     *
     * @after
     *
     * @return void
     */
    protected function resetTestVersion()
    {
        // Only really needed for the testVersion related tests, but doesn't harm the other test in this file.
        Helper::setConfigData('testVersion', null, true);
        Helper::setConfigData('testversion', null, true);
    }


    /**
     * testGetTestVersion
     *
     * @dataProvider dataGetTestVersion
     *
     * @covers \PHPCompatibility\Sniff::getTestVersion
     *
     * @param string $testVersion The testVersion as normally set via the command line or ruleset.
     * @param string $expected    The expected testVersion array.
     *
     * @return void
     */
    public function testGetTestVersion($testVersion, $expected)
    {
        if (isset($testVersion)) {
            Helper::setConfigData('testVersion', $testVersion, true);
        }

        $this->assertSame($expected, $this->invokeMethod($this->helperClass, 'getTestVersion'));

        // Verify that the caching of the function results is working correctly.
        $this->assertSame($expected, $this->invokeMethod($this->helperClass, 'getTestVersion'));
    }

    /**
     * testGetTestVersionCaseLowercase
     *
     * @dataProvider dataGetTestVersion
     *
     * @covers \PHPCompatibility\Sniff::getTestVersion
     *
     * @param string $testVersion The testVersion as normally set via the command line or ruleset.
     * @param string $expected    The expected testVersion array.
     *
     * @return void
     */
    public function testGetTestVersionCaseLowercase($testVersion, $expected)
    {
        if (isset($testVersion)) {
            Helper::setConfigData('testversion', $testVersion, true);
        }

        $this->assertSame($expected, $this->invokeMethod($this->helperClass, 'getTestVersion'));

        // Verify that the caching of the function results is working correctly.
        $this->assertSame($expected, $this->invokeMethod($this->helperClass, 'getTestVersion'));
    }

    /**
     * dataGetTestVersion
     *
     * @see testGetTestVersion()
     *
     * @return array
     */
    public function dataGetTestVersion()
    {
        return array(
            array(null, array(null, null)), // No testVersion provided.
            array('5.0', array('5.0', '5.0')), // Single version.
            array('7.1', array('7.1', '7.1')), // Single version.
            array('4.0-99.0', array('4.0', '99.0')), // Range of versions.
            array('5.1-5.5', array('5.1', '5.5')), // Range of versions.
            array('7.0-7.5', array('7.0', '7.5')), // Range of versions.
            array('5.6-5.6', array('5.6', '5.6')), // Range of versions - min & max the same.
            array('4.0 - 99.0', array('4.0', '99.0')), // Range of versions with spaces around dash.
            array('-5.6', array('4.0', '5.6')), // Range, with no minimum.
            array('7.0-', array('7.0', '99.9')), // Range, with no maximum.

            // Whitespace tests.  Shouldn't really come up in standard command-line use,
            // but could occur if command-line argument is quoted or added via
            // ruleset.xml.
            array(' 5.0', array('5.0', '5.0')), // Single version.
            array('5.0 ', array('5.0', '5.0')), // Single version.
            array('5.1 - 5.5', array('5.1', '5.5')), // Range of versions.
            array(' 5.1 - 5.5 ', array('5.1', '5.5')), // Range of versions.
        );
    }


    /**
     * testGetTestVersionInvalidRange
     *
     * @dataProvider dataGetTestVersionInvalidRange
     *
     * @covers \PHPCompatibility\Sniff::getTestVersion
     *
     * @param string $testVersion The testVersion as normally set via the command line or ruleset.
     *
     * @return void
     */
    public function testGetTestVersionInvalidRange($testVersion)
    {
        $message = sprintf('Invalid range in testVersion setting: \'%s\'', $testVersion);
        $this->phpWarningTestHelper($message);

        $this->testGetTestVersion($testVersion, array(null, null));
    }

    /**
     * dataGetTestVersionInvalidRange
     *
     * @see testGetTestVersionInvalidRange()
     *
     * @return array
     */
    public function dataGetTestVersionInvalidRange()
    {
        return array(
            array('5.6-5.4'), // Range of versions - min > max.
            array('-3.0'), // Range of versions - min > max. Absolute minimum is 4.0.
            array('105.0-'), // Range of versions - min > max. Absolute maximum is 99.9.
        );
    }


    /**
     * testGetTestVersionInvalidVersion
     *
     * @dataProvider dataGetTestVersionInvalidVersion
     *
     * @covers \PHPCompatibility\Sniff::getTestVersion
     *
     * @param string $testVersion The testVersion as normally set via the command line or ruleset.
     *
     * @return void
     */
    public function testGetTestVersionInvalidVersion($testVersion)
    {
        $message = sprintf('Invalid testVersion setting: \'%s\'', trim($testVersion));
        $this->phpWarningTestHelper($message);

        $this->testGetTestVersion($testVersion, array(null, null));
    }

    /**
     * dataGetTestVersionInvalidVersion
     *
     * @see testGetTestVersionInvalidVersion()
     *
     * @return array
     */
    public function dataGetTestVersionInvalidVersion()
    {
        return array(
            array('5'), // Not in major.minor format.
            array('568'), // Not in major.minor format.
            array('5.6.28'), // Not in major.minor format.
            array('seven.one'), // Non numeric.

            array('-'), // Blank range.
            array('5.4-5.5-5.6'), // Multiple ranges.

            array('5-7.0'), // Invalid left half.
            array('5.1.2-7.0'), // Invalid left half.
            array('5AndJunk-7.0'), // Invalid left half.

            array('5.5-7'), // Invalid right half.
            array('5.5-7.0.5'), // Invalid right half.
            array('5.5-7AndJunk'), // Invalid right half.
        );
    }


    /**
     * testSupportsAbove
     *
     * @dataProvider dataSupportsAbove
     *
     * @covers \PHPCompatibility\Sniff::supportsAbove
     *
     * @param string $phpVersion  The PHP version we want to test.
     * @param string $testVersion The testVersion as normally set via the command line or ruleset.
     * @param string $expected    Expected result.
     *
     * @return void
     */
    public function testSupportsAbove($phpVersion, $testVersion, $expected)
    {
        if (isset($testVersion)) {
            Helper::setConfigData('testVersion', $testVersion, true);
        }

        $this->assertSame($expected, $this->helperClass->supportsAbove($phpVersion));
    }

    /**
     * dataSupportsAbove
     *
     * @see testSupportsAbove()
     *
     * @return array
     */
    public function dataSupportsAbove()
    {
        return array(
            array('5.0', null, true),
            array('5.0', '5.2', true),
            array('5.0', '5.1-5.4', true),
            array('5.0', '5.3-7.0', true),

            array('7.1', null, true),
            array('7.1', '5.2', false),
            array('7.1', '5.1-5.4', false),
            array('7.1', '5.3-7.0', false),
        );
    }


    /**
     * testSupportsBelow
     *
     * @dataProvider dataSupportsBelow
     *
     * @covers \PHPCompatibility\Sniff::supportsBelow
     *
     * @param string $phpVersion  The PHP version we want to test.
     * @param string $testVersion The testVersion as normally set via the command line or ruleset.
     * @param string $expected    Expected result.
     *
     * @return void
     */
    public function testSupportsBelow($phpVersion, $testVersion, $expected)
    {
        if (isset($testVersion)) {
            Helper::setConfigData('testVersion', $testVersion, true);
        }

        $this->assertSame($expected, $this->helperClass->supportsBelow($phpVersion));
    }

    /**
     * dataSupportsBelow
     *
     * @see testSupportsBelow()
     *
     * @return array
     */
    public function dataSupportsBelow()
    {
        return array(
            array('5.0', null, false),
            array('5.0', '5.2', false),
            array('5.0', '5.1-5.4', false),
            array('5.0', '5.3-7.0', false),

            array('7.1', null, false),
            array('7.1', '5.2', true),
            array('7.1', '5.1-5.4', true),
            array('7.1', '5.3-7.0', true),
        );
    }


    /**
     * testStringToErrorCode
     *
     * @dataProvider dataStringToErrorCode
     *
     * @covers \PHPCompatibility\Sniff::stringToErrorCode
     *
     * @param string $input    The input string.
     * @param string $expected The expected error code.
     *
     * @return void
     */
    public function testStringToErrorCode($input, $expected)
    {
        $this->assertSame($expected, $this->helperClass->stringToErrorCode($input));
    }

    /**
     * dataStringToErrorCode
     *
     * @see testStringToErrorCode()
     *
     * @return array
     */
    public function dataStringToErrorCode()
    {
        return array(
            array('dir_name', 'dir_name'), // No change.
            array('soap.wsdl_cache', 'soap_wsdl_cache'), // No dot.
            array('arbitrary-string with space', 'arbitrary_string_with_space'), // No dashes, no spaces.
            array('^%*&%*€à?', '____________'), // No non alpha-numeric characters.
        );
    }


    /**
     * testStripVariables
     *
     * @dataProvider dataStripVariables
     *
     * @covers \PHPCompatibility\Sniff::stripVariables
     *
     * @param string $input    The input string.
     * @param string $expected The expected function output.
     *
     * @return void
     */
    public function testStripVariables($input, $expected)
    {
        $this->assertSame($expected, $this->helperClass->stripVariables($input));
    }

    /**
     * dataStripVariables
     *
     * @see testStripVariables()
     *
     * @return array
     */
    public function dataStripVariables()
    {
        return array(
            // These would need to be matched when testing double quoted strings for variables.
            array('"He drank some $juice juice."', '"He drank some  juice."'),
            array('"He drank some juice made of $juices."', '"He drank some juice made of ."'),
            array('"He drank some juice made of ${juice}s."', '"He drank some juice made of s."'),
            array('"He drank some $juices[0] juice."', '"He drank some  juice."'),
            array('"He drank some $juices[1] juice."', '"He drank some  juice."'),
            array('"He drank some $juices[koolaid1] juice."', '"He drank some  juice."'),
            array('"$people->john drank some $juices[0] juice."', '" drank some  juice."'),
            array('"$people->john then said hello to $people->jane."', '" then said hello to ."'),
            array('"$people->john\'s wife greeted $people->robert."', '"\'s wife greeted ."'),
            array('"The element at index -3 is $array[-3]."', '"The element at index -3 is ."'),
            array('"This is {$great}"', '"This is "'),
            array('"This square is {$square->width}00 centimeters broad."', '"This square is 00 centimeters broad."'),
            array('"This works: {$arr[\'key\']}"', '"This works: "'),
            array('"This works: {$arr[4][3]}"', '"This works: "'),
            array('"This is wrong: {$arr[foo][3]}"', '"This is wrong: "'),
            array('"This works: {$arr[\'foo\'][3]}"', '"This works: "'),
            array('"This works too: {$obj->values[3]->name}"', '"This works too: "'),

            array('"This is the value of the var named $name: {${$name}}"', '"This is the value of the var named : "'),
            array('"This is the value of the var named \$name: {${$name}}"', '"This is the value of the var named \$name: "'),
            array('"This is the value of the var named by the return value of getName(): {${getName()}}"', '"This is the value of the var named by the return value of getName(): "'),
            array('"This is the value of the var named by the return value of getName(): {${getName( $test )}}"', '"This is the value of the var named by the return value of getName(): "'),
            array('"This is the value of the var named by the return value of getName(): {${getName( \'abc\' )}}"', '"This is the value of the var named by the return value of getName(): "'),
            array('"This is the value of the var named by the return value of \$object->getName(): {${$object->getName()}}"', '"This is the value of the var named by the return value of \$object->getName(): "'),
            array('"{$foo->$bar}\n"', '"\n"'),
            array('"I\'d like an {${beers::softdrink}}\n"', '"I\'d like an \n"'),
            array('"I\'d like an {${beers::$ale}}\n"', '"I\'d like an \n"'),

            array('"{$foo->{$baz[1]}}\n"', '"{->}\n"'), // Problem var, only one I haven't managed to work out properly.

            // These shouldn't match and should be returned as is.
            array('"He drank some \\\\$juice juice."', '"He drank some \\\\$juice juice."'),
            array('"This is { $great}"', '"This is { }"'),
            array('"This is the return value of getName(): {getName()}"', '"This is the return value of getName(): {getName()}"'),
        );
    }


    /**
     * Helper function for testing PHP warnings.
     *
     * @since 10.0.0
     *
     * @param string $message The warning message to expect.
     *
     * @return void
     */
    public function phpWarningTestHelper($message)
    {
        if (method_exists($this, 'expectWarning')) {
            // PHPUnit 9.0+.
            $this->expectWarning();
            $this->expectWarningMessage($message);

            return;
        }

        if (\method_exists($this, 'expectException') && class_exists('PHPUnit\Framework\Error\Warning')) {
            // PHPUnit 5.7/6/7/8.
            $this->expectException('PHPUnit\Framework\Error\Warning');
            $this->expectExceptionMessage($message);

            return;
        }

        // PHPUnit 4/5.7.
        $this->setExpectedException('PHPUnit_Framework_Error_Warning', $message);
    }


    /**
     * Test helper: Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on.
     * @param string $methodName Method name to call.
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(\get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
