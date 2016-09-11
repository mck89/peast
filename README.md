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

Run `composer install` and include the autoloader:

```php
require_once "vendor/autoload.php";
```

AST generation
-------------
To generate AST for your JavaScript code just write:

```php
$source = "var a = 1"; //JavaScript code
$ast = Peast\Peast::ES7($source, $options)->parse();
```

The previous code generates this structure:
```
Peast\Syntax\Node\Program
    getSourceType() => "script"
    getBody() => array(
        Peast\Syntax\Node\VariableDeclaration
            getKind() => "var"
            getDeclarations() => array(
                Peast\Syntax\Node\VariableDeclarator
                    getId() => Peast\Syntax\Node\Identifier
                        getName() => "a"
                    getInit() => Peast\Syntax\Node\Literal
                        getKind() => "decimal"
                        getValue() => 1
            )
    )
```
    
Tokenization
-------------
```php
$source = "var a = 1"; //JavaScript code
$tokens = Peast\Peast::ES7($source, $options)->tokenize();
```
The `$tokens` array is:
```
array(
    Peast\Syntax\Token
        getType() => "Keyword"
        getValue() => "var"
    Peast\Syntax\Token
        getType() => "Identifier"
        getValue() => "a"
    Peast\Syntax\Token
        getType() => "Punctuator"
        getValue() => "="
    Peast\Syntax\Token
        getType() => "Numeric"
        getValue() => "1"
)
```

EcmaScript version
-------------
Peast can parse different versions of EcmaScript, you can choose the version by using the relative method on the main class.
Available methods are:
* ```Peast\Peast::ES6(source, options)```: parse using EcmaScript 6 syntax
* ```Peast\Peast::ES7(source, options)```: parse using EcmaScript 7 syntax

Options
-------------

In the examples above you may have noticed the `$options` parameter. This parameter is an associative array that specifies parsing settings for the parser. Available options are:
* "sourceEncoding": to specify the encoding of the code to parse
* "sourceType": this can be one of the source type constants defined in the Peast class:
    * `Peast\Peast::SOURCE_TYPE_SCRIPT`: this is the default source type and indicates that the code is a script, this means that `import` and `export` keywords are not parsed
    * `Peast\Peast::SOURCE_TYPE_SCRIPT`: this indicates that the code is a module and it activates the parsing of `import` and `export` keywords

Differences from Esprima and ESTree
-------------
Peast is not a porting of [Esprima](https://github.com/jquery/esprima) to PHP, but since they both respect the ESTree standard the output is almost identical and Peast tests are created using Esprima results.

There is only one big difference from ESTree and Esprima: parenthesized expressions. This type of expressions have been introduced to let the user know if when an expression is wrapped in round brackets. For example `(a + b)` is a parenthesized expression and generates a ParenthesizedExpression node.