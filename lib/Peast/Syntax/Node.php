<?php
namespace Peast\Syntax;

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
    
    abstract public function compile();
    
    public function __toString()
    {
        return $this->compile();
    }
    
    protected function compileNodeList($list, $separator = "")
    {
        if (!count($list)) {
            return "";
        }
        $sources = array();
        foreach ($list as $item) {
            $sources[] = $item->compile();
        }
        return implode($separator, $sources);
    }
    
    protected function assertArrayOf($params, $classes, $allowNull = false)
    {
        if (!is_array($classes)) {
            $classes = array($classes);
        }
        if (!is_array($params)) {
            $this->typeError($params, $classes, $allowNull, true);
        } else {
            foreach ($params as $param) {
                foreach ($classes as $class) {
                    if ($param === null && $allowNull) {
                        continue 2;
                    } else {
                        $c = $this->addNamespace($class);
                        if ($param instanceof $c) {
                            continue 2;
                        }
                    }
                }
                $this->typeError($params, $classes, $allowNull, true);
            }
        }
    }
    
    protected function assertType($param, $classes, $allowNull = false)
    {
        if (!is_array($classes)) {
            $classes = array($classes);
        }
        if ($param === null && !$allowNull) {
            $this->typeError($param, $classes, $allowNull);
        } else {
            foreach ($classes as $class) {
                $c = $this->addNamespace($class);
                if ($param instanceof $c) {
                    return;
                }
            }
            $this->typeError($param, $classes, $allowNull);
        }
    }
    
    protected function addNamespace($class)
    {
        $parts = explode("\\", get_class($this));
        $parts[count($parts) -1] = $class;
        return implode("\\", $parts);
    }
    
    protected function typeError($var, $allowedTypes, $allowNull = false,
                                 $array = false)
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
        if (is_object($var)) {
            $type = get_class($var);
        } else {
            $type = gettype($var);
        }
        $msg .= ", $type given";
        if (version_compare(phpversion(), '7', '>=')) {
            throw new \TypeError($msg);
        } else {
            trigger_error($msg, E_USER_ERROR);
        }
    }
}