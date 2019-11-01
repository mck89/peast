<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES2017;

use \Peast\Syntax\Node;
use \Peast\Syntax\Token;

/**
 * ES2017 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2016\Parser
{

    /**
     * Async/await feature activation
     *
     * @var bool
     */
    protected $featureAsyncAwait = true;

    /**
     * Trailing comma in function calls and declarations feature activation
     *
     * @var bool
     */
    protected $featureTrailingCommaFunctionCallDeclaration = true;

    /**
     * For-in initializer feature activation
     *
     * @var bool
     */
    protected $featureForInInitializer = true;
    
    /**
     * Array of keywords that depends on a context property
     * 
     * @var array 
     */
    protected $contextKeywords = array(
        "yield" => "allowYield",
        "await" => "allowAwait"
    );
    
    /**
     * Initializes parser context
     * 
     * @return void
     */
    protected function initContext()
    {
        parent::initContext();
        $this->context->allowAwait = false;
    }
}