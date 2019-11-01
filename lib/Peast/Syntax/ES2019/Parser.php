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

use \Peast\Syntax\Node;

/**
 * ES2019 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2018\Parser
{
    /**
     * Optional catch binding feature activation
     *
     * @var bool
     */
    protected $featureOptionalCatchBinding = true;
}