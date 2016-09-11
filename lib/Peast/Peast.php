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
 * Main class of Peast library
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class Peast
{
    //Source type constants
    /**
     * This source type indicates that the source is a script and import
     * and export keywords are not parsed.
     */
    const SOURCE_TYPE_SCRIPT = "script";
    
    /**
     * This source type indicates that the source is a module, this enables
     * the parsing of import and export keywords.
     */
    const SOURCE_TYPE_MODULE = "module";
    
    /**
     * Returns an instance of the ES6 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the given
     *                          source. If this option is not provided the
     *                          encoding will be detected automatically.
     * 
     * @return \Peast\Syntax\ES6\Parser
     * 
     * @static
     */
    static public function ES6($source, $options = array())
    {
        return new Syntax\ES6\Parser($source, $options);
    }
    
    /**
     * Returns an instance of the ES7 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the given
     *                          source. If this option is not provided the
     *                          encoding will be detected automatically.
     * 
     * @return \Peast\Syntax\ES7\Parser
     * 
     * @static
     */
    static public function ES7($source, $options = array())
    {
        return new Syntax\ES7\Parser($source, $options);
    }
}