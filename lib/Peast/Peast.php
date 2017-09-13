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
     * Returns an instance of the ES2015 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the source.
     *                          If not specified the parser will assume UTF-8.
     *                        - "comments": if true it enables comments parsing.
     * 
     * @return \Peast\Syntax\ES2015\Parser
     * 
     * @static
     */
    static public function ES2015($source, $options = array())
    {
        return new Syntax\ES2015\Parser($source, $options);
    }
    
    /**
     * Returns an instance of the ES2015 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the source.
     *                          If not specified the parser will assume UTF-8.
     * 
     * @return \Peast\Syntax\ES2015\Parser
     * 
     * @static
     */
    static public function ES6($source, $options = array())
    {
        return self::ES2015($source, $options);
    }
    
    /**
     * Returns an instance of the ES2016 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the source.
     *                          If not specified the parser will assume UTF-8.
     *                        - "comments": if true it enables comments parsing.
     * 
     * @return \Peast\Syntax\ES2016\Parser
     * 
     * @static
     */
    static public function ES2016($source, $options = array())
    {
        return new Syntax\ES2016\Parser($source, $options);
    }
    
    /**
     * Returns an instance of the ES2016 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the source.
     *                          If not specified the parser will assume UTF-8.
     *                        - "comments": if true it enables comments parsing.
     * 
     * @return \Peast\Syntax\ES2016\Parser
     * 
     * @static
     */
    static public function ES7($source, $options = array())
    {
        return self::ES2016($source, $options);
    }
    
    /**
     * Returns an instance of the ES2017 parser for the given source
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the source.
     *                          If not specified the parser will assume UTF-8.
     *                        - "comments": if true it enables comments parsing.
     * 
     * @return \Peast\Syntax\ES2017\Parser
     * 
     * @static
     */
    static public function ES2017($source, $options = array())
    {
        return new Syntax\ES2017\Parser($source, $options);
    }
    
    /**
     * Returns an instance of the parser for the latest EcmaScript version
     * implemented 
     * 
     * @param string $source  The source to parse
     * @param array  $options Associative array for parser options. Available
     *                        options are:
     *                        - "sourceType": one of the source type constants
     *                          declared in this class. This option tells the
     *                          parser to parse the source in script or module
     *                          mode. If this option is not provided the parser
     *                          will work in script mode.
     *                        - "sourceEncoding": the encoding of the source.
     *                          If not specified the parser will assume UTF-8.
     *                        - "comments": if true it enables comments parsing.
     * 
     * @return \Peast\Syntax\ES2017\Parser
     * 
     * @static
     */
    static public function latest($source, $options = array())
    {
        return self::ES2017($source, $options);
    }
}