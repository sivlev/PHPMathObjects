<?php
/*
 * PHPMathObjects Library
 *
 * @see https://github.com/sivlev/PHPMathObjects
 *
 * @author Sergei Ivlev <sergei.ivlev@chemie.uni-marburg.de>
 * @copyright 2024 Sergei Ivlev
 * @license https://opensource.org/license/mit The MIT License
 *
 * @note This software is distributed "as is", with no warranty expressed or implied, and no guarantee for accuracy or applicability to any purpose. See the license text for details.
 */

/* @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

use PHPMathObjects\LinearAlgebra\Matrix;

require_once __DIR__ . '/../../vendor/autoload.php';

/*
 * This example shows how to create a simple numeric matrix, get information about its properties and perform basic arithmetic operations on it
 */

// Let's create a new matrix
$matrix = new Matrix([
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
    [10, 11, 12],
]);

// You can print it out in the console
echo "This is our matrix:" . PHP_EOL . $matrix->toString() . PHP_EOL . PHP_EOL;
/* Output looks like
 *  [1, 2, 3]
 *  [4, 5, 6]
 *  [7, 8, 9]
 *  [10, 11, 12]
*/

// The access to its basic properties
echo "Number of rows: {$matrix->rows()}" . PHP_EOL;             // 4
echo "Number of columns: {$matrix->columns()}" . PHP_EOL;       // 3
echo "Total number of elements: {$matrix->size()}" . PHP_EOL;   // 12

// A Matrix object implements a "Countable interface", so you can use count() function to get the number of elements
echo "Count() function returns: " . count($matrix) . PHP_EOL;   // 12

// You can get the value of an element using get() method
echo "Element [1][2] has the value: " . $matrix->get(1, 2) . PHP_EOL;       // 6

// Matrices in PHPMathObjects are mutable. You can assign a value to a given element with the set() method
$matrix->set(1, 2, -15.1);
echo "New value of element [1][2] is: " . $matrix->get(1, 2) . PHP_EOL;     // -15.1

// To find out if a given element is present in the matrix, use isSet() method:
echo "Element [0][0] is present: " . ($matrix->isSet(0, 0) ? "true" : "false") . PHP_EOL;   // true
echo "Element [3][0] is present: " . ($matrix->isSet(4, 0) ? "true" : "false") . PHP_EOL;   // false

/*
 * Matrix object implements "ArrayAccess" interface.
 * The problem is, however, that this interface in PHP can be properly implemented for 1D arrays only.
 * As a workaround, you should pass the row and column indices as a two-element array.
 * The following methods are implemented:
 *  - offsetGet() = alias of get()
 * - offsetSet() = alias of set()
 * - offsetExists() = alias of isSet()
 * The unset() method is not implemented and will throw an exception
*/
echo "Element [2][1] can be accessed via ArrayAccess, the value is: {$matrix[[2, 1]]}" . PHP_EOL;   // 8
$matrix[[2, 1]] = 0;
echo "Element [2][1] can be set via ArrayAccess, the new value is: {$matrix[[2, 1]]}" . PHP_EOL;   // 0
echo "Existence of the element [2][1] checked via ArrayAccess: " . (isset($matrix[[2, 1]]) ? "true" : "false") . PHP_EOL;   // true
echo "Existence of the element [2][6] checked via ArrayAccess: " . (isset($matrix[[2, 6]]) ? "true" : "false") . PHP_EOL;   // true

// Matrix supports arithmetic operations. Let's create a second matrix
$matrix2 = new Matrix([
    [3, 2, 1],
    [6, 5, 4],
    [9, 8, 7],
    [12, 11, 10],
]);

// Addition
$result = $matrix->add($matrix2);
echo "Result of addition is:" . PHP_EOL . $result->toString() . PHP_EOL . PHP_EOL;

// Subtraction
$result = $matrix->subtract($matrix2);
echo "Result of subtraction is:" . PHP_EOL . $result->toString() . PHP_EOL . PHP_EOL;

// To demonstrate the matrix multiplication, let's create a matrix with compatible dimensions
$matrix3 = new Matrix([
    [1, 2],
    [4, 5],
    [7, 8],
]);
$result = $matrix->multiply($matrix3);
echo "Result of multiplication is:" . PHP_EOL . $result->toString() . PHP_EOL . PHP_EOL;

/*
 * The operations above return a new Matrix instance.
 * But sometimes we don't need the original matrix, and only the result is important.
 * In such cases we can use mutating versions of the arithmetic methods to increase performance a bit because of eliminating the overhead of instantiating new objects.
 */

// Mutating addition
$matrix->mAdd($matrix2);
echo "Result of mutating addition is:" . PHP_EOL . $matrix->toString() . PHP_EOL . PHP_EOL;

// Mutating subtraction
$matrix->mSubtract($matrix2);
echo "Result of mutating subtraction is:" . PHP_EOL . $matrix->toString() . PHP_EOL . PHP_EOL;

// Mutating multiplication
$matrix->mMultiply($matrix3);
echo "Result of mutating multiplication is:" . PHP_EOL . $matrix->toString() . PHP_EOL . PHP_EOL;
