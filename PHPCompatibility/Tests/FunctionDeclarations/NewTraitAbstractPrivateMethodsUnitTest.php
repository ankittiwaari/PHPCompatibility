<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Tests\FunctionDeclarations;

use PHPCompatibility\Tests\BaseSniffTest;

/**
 * Test the NewTraitAbstractPrivateMethods sniff.
 *
 * @group newTraitAbstractPrivateMethods
 * @group functionDeclarations
 *
 * @covers \PHPCompatibility\Sniffs\FunctionDeclarations\NewTraitAbstractPrivateMethodsSniff
 *
 * @since 10.0.0
 */
class NewTraitAbstractPrivateMethodsUnitTest extends BaseSniffTest
{

    /**
     * Verify that the sniff throws the expected error.
     *
     * @dataProvider dataNewTraitAbstractPrivateMethods
     *
     * @param int $line The line number where a warning is expected.
     *
     * @return void
     */
    public function testNewTraitAbstractPrivateMethods($line)
    {
        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertError($file, $line, 'Traits cannot declare "abstract private" methods in PHP 7.4 or below.');
    }

    /**
     * Data provider.
     *
     * @see testNewTraitAbstractPrivateMethods()
     *
     * @return array
     */
    public function dataNewTraitAbstractPrivateMethods()
    {
        return array(
            array(31),
        );
    }


    /**
     * Verify the sniff does not throw false positives for valid code.
     *
     * @dataProvider dataNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives()
     *
     * @return array
     */
    public function dataNoFalsePositives()
    {
        $cases = array();
        // No errors expected on the first 25 lines.
        for ($line = 1; $line <= 25; $line++) {
            $cases[] = array($line);
        }

        return $cases;
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertNoViolation($file);
    }
}
