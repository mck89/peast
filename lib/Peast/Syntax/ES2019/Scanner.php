<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2019;

use \Peast\Syntax\Utils;

/**
 * ES2019 scanner.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Scanner extends \Peast\Syntax\ES2018\Scanner
{
    /**
     * Class constructor
     * 
     * @param string $source   Source code
     * @param string $encoding Source code encoding, if not specified it
     *                         will assume UTF-8
     */
    function __construct($source, $encoding = null)
    {
        parent::__construct($source, $encoding);
        
        //Allow paragraph and line separators in strings
        $this->stringsStopsLSM->remove(Utils::unicodeToUtf8(0x2028));
        $this->stringsStopsLSM->remove(Utils::unicodeToUtf8(0x2029));
    }
}