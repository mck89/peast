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

use \Peast\Syntax\Node;

/**
 * ES2018 parser class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Parser extends \Peast\Syntax\ES2017\Parser
{
    /**
     * Async iteration and generators feature activation
     *
     * @var bool
     */
    protected $featureAsyncIterationGenerators = true;

    /**
     * Rest/spread properties feature activation
     *
     * @var bool
     */
    protected $featureRestSpreadProperties = true;

    /**
     * Skip escape sequences checks in tagged template feature activation
     *
     * @var bool
     */
    protected $featureSkipEscapeSeqCheckInTaggedTemplates = true;
}