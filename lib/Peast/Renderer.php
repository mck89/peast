<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast;

/**
 * Nodes renderer class
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Renderer
{
    /**
     * Formatter to use for the rendering
     * 
     * @var Formatter\Base
     */
    protected $formatter;
    
    /**
     * Sets the formatter to use for the rendering
     * 
     * @param Formatter\Base    $formatter  Formatter
     * 
     * @return $this
     */
    public function setFormatter(Formatter\Base $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }
    
    /**
     * Returns the formatter to use for the rendering
     * 
     * @return Formatter\Base
     */
    public function getFormatter()
    {
        return $this->formatter;
    }
    
    /**
     * Renders the given node.
     * 
     * @param Syntax\Node\Node  $node   Node to render
     * 
     * @return string
     * 
     * @throws Exception
     */
    public function render(Syntax\Node\Node $node)
    {
        if (!$this->formatter) {
            throw new \Exception("Formatter not set");
        }
        return $this->_render($node);
    }
    
    /**
     * Internal render method.
     * 
     * @param Syntax\Node\Node  $node   Node to render
     * 
     * @return string
     */
    protected function _render(Syntax\Node\Node $node)
    {
    }
}