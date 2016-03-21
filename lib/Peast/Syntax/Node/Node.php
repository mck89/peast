<?php
namespace Peast\Syntax\Node;

use Peast\Syntax\SourceLocation;
use Peast\Syntax\Position;

abstract class Node
{
    protected $loc;
    
    protected $type;
    
    function __construct()
    {
        $class = explode("\\", __CLASS__);
        $this->type = array_pop($class);
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getLocation()
    {
        return $this->loc;
    }
    
    public function setStartPosition(Position $position)
    {
        if (!$this->loc) {
            $this->loc = new SourceLocation;
        }
        $this->loc->setStart($position);
        return $this;
    }
    
    public function setEndPosition(Position $position)
    {
        if (!$this->loc) {
            $this->loc = new SourceLocation;
        }
        $this->loc->setEnd($position);
        return $this;
    }
    
    protected function nodeListToSource($list)
    {
        $source = "";
        foreach ($list as $item) {
            $source .= $item->getSource();
        }
        return $source;
    }
    
    protected function assertArrayOf($params, $class)
    {
        $error = function () {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $method = $backtrace[2]["class"] . "::" . $backtrace[2]["function"];
            $error = "Argument 0 passed to $method must be an array of $class";
            throw new \ErrorException($error);
        };
        if (!is_array($params)) {
            $error();
        } else {
            foreach ($params as $param) {
                if (!($param instanceof $class)) {
                    $error();
                }
            }
        }
    }
    
    abstract public function getSource();
}