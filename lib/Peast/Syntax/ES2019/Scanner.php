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
     * Paragraph and line sepeartor in strings feature activation
     *
     * @var bool
     */
    protected $featureParagraphLineSepInStrings = true;
}