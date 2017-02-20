Peast
==========
**Peast** _(PHP ECMAScript Abstract Syntax Tree)_ is a PHP 5.4+ library that parses JavaScript code, according to [ECMAScript specification](http://www.ecma-international.org/publications/standards/Ecma-262.htm), and generates an abstract syntax tree following the [ESTree standard](https://github.com/estree/estree).

Installation
-------------
Include the following requirement to your composer.json:
```
{
	"require": {
		"mck89/peast": "dev-master"
	}
}
```

Run `composer install` to install the package.

Then in your script include the autoloader and you can start using Peast:

```php
require_once "vendor/autoload.php";

$source = "var a = 1"; //Your JavaScript code
$ast = Peast\Peast::latest($source, $options)->parse(); //Parse it!
```

Documentation
-------------
Read the documentation for more examples and explanations:

 1. [AST generation and tokenization](doc/ast-and-tokenization.md)
 2. [Tree Traversing](doc/tree-traversing.md)
 3. [Rendering](doc/rendering.md)

Changelog
-------------

#### 1.0
* First release with ES6 and ES7 parsers

#### 1.1
* Added Traverser class

#### 1.2
* Added Renderer class

#### 1.3
* Refactored parser to make it more extensible
* More accurate parsing of identifiers
* Added parsing of HTML comments if source is not a module
* Added some validations:
    * Disallowed legacy octal escape syntax (\07) in templates
    * Disallowed legacy octal escape syntax (\07) in strings if strict mode
    * Disallowed legacy octal syntax (077) for numbers if strict mode
    * Disallowed `delete` followed by single identifiers in strict mode
    * Disallowed labelled function declarations in strict mode
    * Allowed `if (...) function () {}` syntax if not in strict mode
* __BC break__: removed Function_ and Class_ interfaces and traits and replaced them with abstract classes
* __BC break__: if sourceEncoding is not specified, the parser won't try to autodetect it, but will assume UTF-8
* __BC break__: Literal is now an abstract class that is extended by the new classes for literals: StringLiteral, NumericLiteral, BooleanLiteral and NullLiteral

#### 1.4
* Since EcmaScript dropped support for ES(Number) in favour of ES(Year) versions:
    * `ES6` namespace have been replaced by `ES2015`
    * `Peast::ES2015` method have been added to Peast main class, `Peast::ES6` method still exists to preserve BC and calls `Peast::ES2015` internally
    * `ES7` namespace have been replaced by `ES2016`
    * `Peast::ES2016` method have been added to Peast main class, `Peast::ES7` method still exists to preserve BC and calls `Peast::ES2016` internally
    * `Peast::latest` method have been added to Peast main class to allow parsing with the latest EcmaScript version implemented