<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\JSX;

use Peast\Syntax\Token;

/**
 * JSX scanner trait
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
trait Scanner
{
    /**
     * Tries to reconsume the current token as a jsx text if possible
     * 
     * @return Token|null
     */
    public function reconsumeCurrentTokenAsJSXText()
    {
        $this->nextToken = null;
        $this->currentToken = null;
        $startPosition = $this->getPosition();
        $this->setScanPosition($startPosition);
        $result = $this->consumeUntil(array("{", "<", ">", "}"), false, false);
        if ($result) {
            $this->currentToken = new Token(Token::TYPE_JSX_TEXT, $result[0]);
            $this->currentToken->setStartPosition($startPosition)
                               ->setEndPosition($this->getPosition(true));
        }
        return $this->currentToken;
    }
    
    
    /**
     * Tries to reconsume the current token as a valid jsx string  if possible
     * 
     * @return Token|null
     */
    public function reconsumeCurrentTokenAsJSXString()
    {
        $this->nextToken = null;
        $this->currentToken = null;
        $startPosition = $this->getPosition();
        $this->setScanPosition($startPosition);
        $this->currentToken = $this->scanString(false);
        return $this->currentToken;
    }
    
    /**
     * Tries to reconsume the current token as a valid jsx identifier if
     * possible
     * 
     * @return Token|null
     */
    public function reconsumeCurrentTokenAsJSXIdentifier()
    {
        $this->nextToken = null;
        $this->currentToken = null;
        $startPosition = $this->getPosition();
        $this->setScanPosition($startPosition);
        
        $char = $this->charAt();
        if ($char !== null && $this->isIdentifierStart($char)) {
            
            $buffer = "";
            do {
                $buffer .= $char;
                $this->index++;
                $this->column++;
                $char = $this->charAt();
            } while (
                $char !== null &&
                ($this->isIdentifierPart($char) || $char === "-")
            );
            
            $this->currentToken = new Token(Token::TYPE_JSX_IDENTIFIER, $buffer);
            $this->currentToken->setStartPosition($startPosition)
                               ->setEndPosition($this->getPosition(true));
        }
        
        return $this->currentToken;
    }
}
