<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2020;

use \Peast\Syntax\Node;

/**
 * ES2020 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2019\Parser
{
    /**
     * Dynamic import feature activation
     *
     * @var bool
     */
    protected $featureDynamicImport = true;
}