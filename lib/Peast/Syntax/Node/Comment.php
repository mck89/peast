<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\Node;

/**
 * A node that represents a comment.
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Comment extends Node
{
    
    //Comment type constants
    /**
     * Inline comment
     */
    const INLINE = "inline";
    
    /**
     * Multiline comment
     */
    const MULTILINE = "multiline";
    
    /**
     * Html open comment
     */
    const HTML_OPEN = "html-open";
    
    /**
     * Html close comment
     */
    const HTML_CLOSE = "html-close";
    
    /**
     * Map of node properties
     * 
     * @var array 
     */
    protected $propertiesMap = array(
        "type" => false,
        "text" => false
    );
    
    /**
     * The comment type
     * 
     * @var string 
     */
    protected $type;
    
    /**
     * The comment text
     * 
     * @var string 
     */
    protected $text;
    
    /**
     * Returns the comment type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Sets the comment type
     * 
     * @param string $type Comment type
     * 
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Returns the comment text
     * 
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * Sets the comment text
     * 
     * @param string $text Comment text
     * 
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }
    
    /**
     * Returns the comment raw text
     * 
     * @return string
     */
    public function getRawText()
    {
        $text = $this->getText();
        $type = $this->getType();
        
        if ($type === self::INLINE) {
            return "//" . $text;
        } elseif ($type === self::HTML_OPEN) {
            return "<!--" . $text;
        } elseif ($type === self::HTML_CLOSE) {
            return "-->" . $text;
        } else {
            return "/*" . $text . "*/";
        }
    }
    
    /**
     * Sets the comment raw text
     * 
     * @param string $text Comment text
     * 
     * @return $this
     */
    public function setRawText($rawText)
    {
        $start = substr($rawText, 0, 2);
        if ($start === "//") {
            $type = self::INLINE;
            $text = substr($rawText, 2);
        } elseif ($start === "/*" && substr($rawText, -2) === "*/") {
            $type = self::MULTILINE;
            $text = substr($rawText, 2, -2);
        } elseif ($start === "<!" && substr($rawText, 2, 2) === "--") {
            $type = self::HTML_OPEN;
            $text = substr($rawText, 4);
        } elseif ($start === "--" && substr($rawText, 2, 1) === ">") {
            $type = self::HTML_CLOSE;
            $text = substr($rawText, 3);
        } else {
            throw new \Exception("Invalid comment");
        }
        return $this->setType($type)->setText($text);
    }
    
    /**
     * Sets leading comments array
     * 
     * @param Comments[] $comments Comments array
     * 
     * @return $this
     */
    public function setLeadingComments($comments)
    {
        return $this;
    }
    
    /**
     * Sets trailing comments array
     * 
     * @param Comments[] $comments Comments array
     * 
     * @return $this
     */
    public function setTrailingComments($comments)
    {
        return $this;
    }
    
    /**
     * Returns a serializable version of the node
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        unset($ret["leadingComments"]);
        unset($ret["trailingComments"]);
        return $ret;
    }
}