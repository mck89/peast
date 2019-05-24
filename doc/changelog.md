Changelog
==========

#### 1.9.2
* Fixed rendering of nested "if" statements with Compact formatter

#### 1.9.1
* Fixed rendering of arrow functions that generates invalid code

#### 1.9
* Added ES2019 parser

#### 1.8.1
* Fixed parsing of regular expressions by disabling scan errors inside them
* Added LSM utility class to handle correctly punctutators and strings stop characters

#### 1.8
* Implemented parsing of JSX syntax

#### 1.7
* Implemented missing features of es2018: object rest and spread, async generators and async iteration

#### 1.6
* Fixed a lot of bugs and now Peast is compatible with all the [ECMAScript official tests](https://github.com/tc39/test262) for the implemented features. You can test Peast against ECMAScript tests using the [peast-test262](https://github.com/mck89/peast-test262) repository.
* Added ES2018 parser

#### 1.5
* Enabled JSON serialization of nodes and tokens using json_encode()
* Added parsing and handling of comments

#### 1.4
* Since EcmaScript dropped support for ES(Number) in favour of ES(Year) versions:
    * `ES6` namespace have been replaced by `ES2015`
    * `Peast::ES2015` method have been added to Peast main class, `Peast::ES6` method still exists to preserve BC and calls `Peast::ES2015` internally
    * `ES7` namespace have been replaced by `ES2016`
    * `Peast::ES2016` method have been added to Peast main class, `Peast::ES7` method still exists to preserve BC and calls `Peast::ES2016` internally
    * `Peast::latest` method have been added to Peast main class to allow parsing with the latest EcmaScript version implemented
* Added ES2017 parser

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

#### 1.2
* Added Renderer class

#### 1.1
* Added Traverser class

#### 1.0
* First release with ES6 and ES7 parsers
