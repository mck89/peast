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
    
    protected function assertArrayOf($params, $classes)
    {
        if (!is_array($classes)) {
            $classes = array($classes);
        }
        if (!is_array($params)) {
            $this->typeError($classes, true);
        } else {
            foreach ($params as $param) {
                foreach ($classes as $class) {
                    if ($param instanceof $class) {
                        continue 2;
                    }
                }
                $this->typeError($classes, true);
            }
        }
    }
    
    protected function assertType($param, $classes, $allowNull = false)
    {
        if (!is_array($classes)) {
            $classes = array($classes);
        }
        if ($param === null && !$allowNull) {
            $this->typeError($classes, false);
        }
        foreach ($classes as $class) {
            if ($param instanceof $class) {
                return;
            }
        }
        $this->typeError($classes, false, $allowNull);
    }
    
    protected function typeError($allowedTypes, $array = false, $allowNull = false)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $method = $backtrace[2]["class"] . "::" . $backtrace[2]["function"];
        $msg = "Argument 0 passed to $method must be ";
        if ($array) {
            $msg .= "array of $allowedTypes";
        } else {
            $msg .= implode(" or ", $allowedTypes);
        }
        if ($allowNull) {
            $msg .= " or null";
        }
        if (version_compare(phpversion(), '7', '>=')) {
            throw new \TypeError($msg);
        } else {
            trigger_error($msg, E_RECOVERABLE_ERROR);
        }
    }
    
    abstract public function getSource();
}