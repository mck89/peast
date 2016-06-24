<?php
namespace Peast\Syntax;

/**
 * @codeCoverageIgnore
 */
class Exception extends \Exception
{
    protected $position;
    
    public function __construct($message, Position $position)
    {
        parent::__construct($message);
        $this->position = $position;
    }
    
    public function getPosition()
    {
        return $this->position;
    }
}