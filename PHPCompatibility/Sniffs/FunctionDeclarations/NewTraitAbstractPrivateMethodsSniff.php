<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Sniffs\FunctionDeclarations;

use PHP_CodeSniffer\Files\File;
use PHPCompatibility\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

/**
 * As of PHP 8.0, traits are allowed to declare abstract private methods.
 *
 * PHP version 8.0
 *
 * @link https://wiki.php.net/rfc/abstract_trait_method_validation
 *
 * @see \PHPCompatibility\Sniffs\Classes\ForbiddenAbstractPrivateMethodsSniff Sniff handling abstract private methods
 *                                                                            in other OO constructs.
 *
 * @since 10.0.0
 */
class NewTraitAbstractPrivateMethodsSniff extends Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @since 10.0.0
     *
     * @return array
     */
    public function register()
    {
        return [
            \T_FUNCTION,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @since 10.0.0
     *
     * @param \PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                   $stackPtr  The position of the current token in the
     *                                         stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if ($this->supportsBelow('7.4') === false) {
            return;
        }

        if (Scopes::validDirectScope($phpcsFile, $stackPtr, [\T_TRAIT]) === false) {
            // Not a trait method.
            return;
        }

        $properties = FunctionDeclarations::getProperties($phpcsFile, $stackPtr);
        if ($properties['scope'] !== 'private' || $properties['is_abstract'] !== true) {
            // Not an abstract private method.
            return;
        }

        $phpcsFile->addError(
            'Traits cannot declare "abstract private" methods in PHP 7.4 or below.',
            $stackPtr,
            'Found'
        );
    }
}
