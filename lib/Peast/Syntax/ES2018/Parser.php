<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2018;

/**
 * ES2018 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2017\Parser
{
    /**
     * Checks if the given string or number contains invalid esape sequences
     * 
     * @param string  $val                      Value to check
     * @param bool    $number                   True if the value is a number
     * @param bool    $forceLegacyOctalCheck    True to force legacy octal
     *                                          form check
     * @param bool    $taggedTemplate           True if the value is a tagged
     *                                          template
     * 
     * @return void
     */
    protected function checkInvalidEscapeSequences(
        $val, $number = false, $forceLegacyOctalCheck = false,
        $taggedTemplate = false
    ) {
        if (!$taggedTemplate) {
            parent::checkInvalidEscapeSequences(
                $val, $number, $forceLegacyOctalCheck, $taggedTemplate
            );
        }
    }
}