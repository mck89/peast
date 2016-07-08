<?php
/**
 * This file is part of the Peast package
 *
 * (c) Marco Marchiò <marco.mm89@gmail.com>
 *
 * For the full copyright and license information refer to the LICENSE file
 * distributed with this source code
 */
namespace Peast\Syntax\ES6\Node;

/**
 * A node that represents a namespace import specifier.
 * For example "* as test" in: import * as test from "test.js".
 * 
 * @author Marco Marchiò <marco.mm89@gmail.com>
 */
class ImportDefaultSpecifier extends ModuleSpecifier
{
}