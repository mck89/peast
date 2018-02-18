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
    
    /**
     * Parses an object binding pattern
     * 
     * @return Node\ObjectPattern|null
     */
    protected function parseObjectBindingPattern()
    {
        $state = $this->scanner->getState();
        if ($token = $this->scanner->consume("{")) {
            
            $properties = array();
            while ($prop = $this->parseBindingProperty()) {
                $properties[] = $prop;
                if (!$this->scanner->consume(",")) {
                    break;
                }
            }
            
            if ($rest = $this->parseRestProperty()) {
                $properties[] = $rest;
            }
            
            if ($this->scanner->consume("}")) {
                $node = $this->createNode("ObjectPattern", $token);
                if ($properties) {
                    $node->setProperties($properties);
                }
                return $this->completeNode($node);
            }
            
            $this->scanner->setState($state);
        }
        return null;
    }
    
    /**
     * Parses a rest property
     * 
     * @return Node\RestElement|null
     */
    protected function parseRestProperty()
    {
        if ($token = $this->scanner->consume("...")) {
            
            if ($argument = $this->parseIdentifier(static::$bindingIdentifier)) {
                $node = $this->createNode("RestElement", $token);
                $node->setArgument($argument);
                return $this->completeNode($node);
            }
            
            return $this->error();
        }
        return null;
    }
}