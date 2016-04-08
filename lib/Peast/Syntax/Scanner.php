<?php
namespace Peast\Syntax;

class Scanner
{
    protected $source;
    
    protected $encoding;
    
    protected $column = 0;
    
    protected $line = 0;
    
    protected $index = 0;
    
    protected $length;
    
    function __construct($source, $encoding = null)
    {
        if ($encoding && !preg_match("/UTF-8/i", $encoding)) {
            $source = mb_convert_encoding($source, "UTF-8", $encoding);
        }
        $this->source = $source;
        $this->length = count($this->source);
    }
    
    public function getColumn()
    {
        return $this->column;
    }
    
    public function getLine()
    {
        return $this->line;
    }
    
    public function getIndex()
    {
        return $this->index;
    }
    
    public function getPosition()
    {
        return new Position(
            $this->getLine(),
            $this->getColumn(),
            $this->getIndex()
        );
    }
    
    public function setPosition(Position $position)
    {
        $this->line = $position->getLine();
        $this->column = $position->getColumn();
        $this->index = $position->getIndex();
        return $this;
    }
    
    public function consume($string)
    {
        $this->consumeWhitespacesAndComments();
    }
    
    public function consumeArray($sequence)
    {
        $position = $this->getPosition();
        foreach ($sequence as $string) {
            if ($this->consume($string) === false) {
                $this->setPosition($position);
                return false;
            }
        }
        return true;
    }
    
    public function notBefore($tests)
    {
        $position = $this->getPosition();
        foreach ($tests as $test) {
            $testFn = is_array($test) ? "consumeArray" : "consume";
            if ($this->$testFn($test)) {
                $this->setPosition($position);
                return false;
            }
        }
        return true;
    }
    
    public function conumeOneOf($tests)
    {
        foreach ($tests as $test) {
            if ($this->scanner->consume()) {
                return $test;
            }
        }
        return null;
    }
    
    public function consumeWhitespacesAndComments($lineTerminator = true)
    {
        
    }
}