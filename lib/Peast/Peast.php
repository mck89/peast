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
 * Main class of Peast library.
 * Every function of this class takes two arguments:
 * - The source code to parse
 * - The options array that is an associative array of parser settings.
 *   Available options are:
 *      - "sourceType": one of the source type constants declared in this class.
 *        This option tells the parser to parse the source in script or module
 *        mode. If this option is not provided the parser will work in script
 *        mode.
 *      - "sourceEncoding": the encoding of the source. If not specified the
 *        parser will assume UTF-8.
 *      - "comments": if true it enables comments parsing.
 *      - "jsx": if true it enables parsing of JSX syntax.
 * 
 * @method static Syntax\ES2015\Parser ES2015(string $source, array $options)
 * Returns an instance of the ES2015 parser for the given source. See Peast
 * class documentation to understand the function arguments.
 * 
 * @method static Syntax\ES2015\Parser ES6(string $source, array $options)
 * Returns an instance of the ES2015 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2016\Parser ES2016(string $source, array $options)
 * Returns an instance of the ES2016 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2016\Parser ES7(string $source, array $options)
 * Returns an instance of the ES2016 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2017\Parser ES2017(string $source, array $options)
 * Returns an instance of the ES2017 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2017\Parser ES8(string $source, array $options)
 * Returns an instance of the ES2017 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2018\Parser ES2018(string $source, array $options)
 * Returns an instance of the ES2018 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2018\Parser ES9(string $source, array $options)
 * Returns an instance of the ES2018 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2019\Parser ES2019(string $source, array $options)
 * Returns an instance of the ES2019 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2019\Parser ES10(string $source, array $options)
 * Returns an instance of the ES2019 parser for the given source. See Peast
 * class documentation to understand function arguments.
 * 
 * @method static Syntax\ES2019\Parser latest(string $source, array $options)
 * Returns an instance of the latest parser version for the given source. See
 * Peast class documentation to understand function arguments.
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
     * Valid versions and aliases
     * 
     * @var array
     */
    static protected $versions = array(
        "ES6" => "ES2015",
        "ES7" => "ES2016",
        "ES8" => "ES2017",
        "ES9" => "ES2018",
        "ES10" => "ES2019"
    );
    
    /**
     * Magic method that exposes all the functions to access specific parser
     * versions
     * 
     * @param string    $version   Parser version
     * @param array     $args      Parser arguments
     * 
     * @return mixed
     */
    public static function __callStatic($version, $args)
    {
        $source = $args[0];
        $options = isset($args[1]) ? $args[1] : array();
        
        if (!in_array($version, self::$versions)) {
            if ($version === "latest") {
                $version = end(self::$versions);
            } elseif (isset(self::$versions[$version])) {
                $version = self::$versions[$version];
            } else {
                throw new \Exception("Invalid version $version");
            }
        }
        
        $class = "\\Peast\\Syntax\\$version\\Parser";
        return new $class($source, $options);
    }
}