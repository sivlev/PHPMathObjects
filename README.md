# PHPMathObjects - A PHP library to handle mathematical objects

![Tests](https://github.com/sivlev/PHPMathObjects/actions/workflows/tests.yml/badge.svg) ![GitHub License](https://img.shields.io/github/license/sivlev/PHPMathObjects)

The PHPMathObjects library was created with crystallographic applications in mind but should be suitable for broad variety of projects. 
The library has 100 % coverage with unit tests and is performance-optimized. 
Being actively developed, it is not yet suitable for production environment since the current API is subject to change.

## Installation

Install PHPMathObjects using [Composer](https://getcomposer.org):
```sh
composer require sivlev/phpmathobjects
```
or include the following line to your `composer.json` file:
```json
"sivlev/phpmathobjects": "^0.1.0"
```

### Requirements

The library requires PHP 8.2 or above. No other external dependencies are required.

## How to use

### Contents

 * Linear Algebra
   - [Matrix](#matrix)

### Linear Algebra

#### Matrix

Many matrix methods in PHPMathObjects are implemented in two versions: non-mutating (return a new matrix object) and mutating (change the existing matrix).
The latter method names have a letter "m" in the beginning, e.g. add() and mAdd(), transpose() and mTranspose(), etc.
You can decide which method is more suitable for a particular task. Usually the mutating methods are slightly faster than non-mutating ones because the no new object instantiation is needed.

```php
// Create a new matrix object using class constructor
$matrix = new Matrix([
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
]);

// Or use a suitable factory method
$matrix = Matrix::fill(3, 4, 0.1);  // Make a 3x4 matrix and fill its elements with 0.1
$matrix = Matrix::identity(3);      // Make a 3x3 identity matrix

// Matrix dimensions
$rows = $matrix->rows();
$columns = $matrix->columns();
$numberOfElements = $matrix->size(); 
$numberOfElements = count($matrix); // Alternative way as Matrix implements "Countable" interface

// Get matrix as array
$array = $matrix->toArray(); 

// Element getters and setters (zero-based)
$element = $matrix->get(1, 2);
$matrix->set(1, 2, -15.6);
$element = $matrix->set(1, 2, 100)->get(1, 2); // Set() method returns $this so it can be chained
$doesElementExist = $matrix->isSet(2, 1);

// Matrix properties
$isSquare = $matrix->isSquare(); 

// Alternative getters and setters via "ArrayAccess" interface
$element = $matrix[[1, 2]];    // Note the format of the index. The problem is that PHP supports native ArrayAccess for 1D arrays only
$matrix[[1, 2]] = 15;
$doesElementExist = isset($matrix[[1, 2]]);

// Matrix unary operations
$transpose = $matrix->transpose();
$matrix->mTranspose();
$trace = $matrix->trace();
$determinant = $matrix->determinant();

// Matrix arithmetics
$sum = $matrix->add($anotherMatrix);
$matrix->mAdd($anotherMatrix);
$difference = $matrix->subtract($anotherMatrix);
$matrix->mSubtract($anotherMatrix);
$multiplication = $matrix->multiply($anotherMatrix);
$matrix->mMultiply($anotherMatrix);
$multiplicationByScalar = $matrix->multiplyByScalar(2.5);
$matrix->mMultiplyByScalar(2.5);
$signsChanged = $matrix->changeSign();
$matrix->mChangeSign();

// Compare matrices
$equal = $matrix->isEqual($anotherMatrix);          // Compare elementwise within a default tolerance of 1.0e^-6
$equal = $matrix->isEqual($anotherMatrix, 1e-8);    // Or set the tolerance explicitly
$equal = $matrix->isEqualExactly($anotherMatrix);   // Compare matrices elementwise with '===' operator

// Conversion to a string representation
$string = $matrix->toString();
$string = (string) $matrix;
// Both ways will return
// [1, 2, 3]
// [4, 5, 6]
// [7, 8, 9]
```
