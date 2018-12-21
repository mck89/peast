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
     * Parses the catch block of a try-catch statement
     * 
     * @return Node\CatchClause|null
     */
    protected function parseCatch()
    {
        if ($token = $this->scanner->consume("catch")) {
            
            $node = $this->createNode("CatchClause", $token);
            
            if ($this->scanner->consume("(")) {
                if (!($param = $this->parseCatchParameter()) ||
                    !$this->scanner->consume(")")) {
                    return $this->error();
                }
                $node->setParam($param);
            }
            
            if (!($body = $this->parseBlock())) {
                return $this->error();
            }
            
            $node->setBody($body);
            
            return $this->completeNode($node);
        }
        return null;
    }
}