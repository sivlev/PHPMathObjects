<?php
/*
 * PHPMathObjects Library
 *
 * @see https://github.com/sivlev/PHPMathObjects
 *
 * @author Sergei Ivlev <s.ivlev.me@gmail.com>
 * @copyright (c) 2024 Sergei Ivlev
 * @license https://opensource.org/license/mit The MIT License
 *
 * @note This software is distributed "as is", with no warranty expressed or implied, and no guarantee for accuracy or applicability to any purpose. See the license text for details.
 */

declare(strict_types=1);

namespace PHPMathObjects\LinearAlgebra;

use PHPMathObjects\Exception\DivisionByZeroException;
use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Exception\MatrixException;

use PHPMathObjects\Exception\OutOfBoundsException;

use Random\Randomizer;

use function is_int;
use function is_float;
use function array_fill;
use function abs;
use function rand;
use function getrandmax;

/**
 * Implementation of the AbstractMatrix class to manipulate numeric matrices
 *
 * @extends AbstractMatrix<int|float>
 */
class Matrix extends AbstractMatrix
{
    /**
     * This constant is used as the default tolerance: If a float point number is below the tolerance, then it is considered being equal to zero.
     */
    protected const DEFAULT_TOLERANCE = 1e-8;

    /**
     * Cached value of the trace of the matrix
     *
     * @var int|float|null
     */
    protected int|float|null $cacheTrace = null;

    /**
     * Cached value of the determinant of the matrix
     *
     * @var int|float|null
     */
    protected int|float|null $cacheDeterminant = null;

    /**
     * Cached value of the row echelon form
     *
     * @var Matrix|null
     */
    protected self|null $cacheRef = null;

    /**
     * Cached value of number of swaps used to make the row echelon form
     *
     * @var int|null
     */
    protected ?int $cacheRefSwaps = null;

    /**
     * Factory method to create an identity matrix with dimensions of size x size
     *
     * @param int $size
     * @return self
     * @throws OutOfBoundsException if the given size is non-positive
     * @throws InvalidArgumentException (not expected)
     */
    public static function identity(int $size): self
    {
        if ($size <= 0) {
            throw new OutOfBoundsException("Size of identity matrix must greater than zero. Size $size is given.");
        }

        // Create a 2D array filled with zeros
        $array = array_fill(0, $size, array_fill(0, $size, 0));

        // Replace the diagonal elements with ones
        for ($i = 0; $i < $size; $i++) {
            $array[$i][$i] = 1;
        }

        return new self($array, false);
    }

    /**
     * Factory method to create a matrix filled with random float numbers between the given limits
     *
     * TODO: Replace with getFloat() when migrating to PHP 8.3
     *
     * @param int $rows
     * @param int $columns
     * @param int|float $min
     * @param int|float $max
     * @return self
     * @throws InvalidArgumentException (not expected)
     * @throws OutOfBoundsException if the rows or columns are non-positive, or if $min is greater than $max
     */
    public static function random(int $rows, int $columns, int|float $min = 0.0, int|float $max = 1.0): self
    {
        // Check if the dimensions are correct
        if ($rows <= 0 || $columns <= 0) {
            throw new OutOfBoundsException("Matrix dimensions must be greater than zero. Rows $rows and columns $columns are given");
        }

        if ($min > $max) {
            throw new OutOfBoundsException("The maximum value $max cannot be less than the minimum value $min");
        }

        $array = [];
        $maxNumber = getrandmax();
        for ($i = 0; $i < $rows; $i++) {
            $row = [];
            for ($j = 0; $j < $columns; $j++) {
                $row[] = rand() / $maxNumber * ($max - $min) + $min;
            }
            $array[] = $row;
        }

        return new Matrix($array, false);
    }

    /**
     * Factory method to create a matrix filled with random integer numbers between the given limits
     *
     * @param int $rows
     * @param int $columns
     * @param int $min
     * @param int $max
     * @return self
     * @throws InvalidArgumentException (not expected)
     * @throws OutOfBoundsException if the rows or columns are non-positive, or if $min is greater than $max
     */
    public static function randomInt(int $rows, int $columns, int $min = 0, int $max = 100): self
    {
        // Check if the dimensions are correct
        if ($rows <= 0 || $columns <= 0) {
            throw new OutOfBoundsException("Matrix dimensions must be greater than zero. Rows $rows and columns $columns are given");
        }

        if ($min > $max) {
            throw new OutOfBoundsException("The maximum value $max cannot be less than the minimum value $min");
        }

        $r = new Randomizer();
        $array = [];
        for ($i = 0; $i < $rows; $i++) {
            $row = [];
            for ($j = 0; $j < $columns; $j++) {
                $row[] = $r->getInt($min, $max);
            }
            $array[] = $row;
        }

        return new Matrix($array, false);
    }

    /**
     * Implementation of the abstract class-specific data validation method for numeric matrices
     *
     * @param array<int, int|float> $row
     * @param int $rowIndex
     * @param string $exceptionMessage
     * @return int|true
     * @see AbstractMatrix::validateDataClassSpecific()
     */
    protected function validateDataClassSpecific(array $row, int $rowIndex = 0, string &$exceptionMessage = ""): int|true
    {
        foreach ($row as $columnIndex => $element) {
            /* @phpstan-ignore-next-line */
            if (!is_int($element) && !is_float($element)) {
                $exceptionMessage = "Elements of a numeric matrix must be either integer or float. Element [$rowIndex][$columnIndex] is of type '" . gettype($this->matrix[$rowIndex][$columnIndex]) . "'.";
                return $columnIndex;
            }
        }
        return true;
    }

    /**
     * Implementation of abstract clearCache() method for numeric matrices
     *
     * @return void
     * @see AbstractMatrix::clearCache()
     */
    protected function clearCache(): void
    {
        // If cache flag is not set, then nothing to clear
        if ($this->cachePresent === false) {
            return;
        }

        // Set all cached properties to zero
        $this->cacheTrace = null;
        $this->cacheRef = null;
        $this->cacheRefSwaps = null;
        $this->cacheDeterminant = null;

        // Set the cache flag to false
        $this->cachePresent = false;
    }

    /**
     * Matrix addition
     *
     * @param Matrix $term
     * @return self
     * @throws MatrixException if the matrices have unequal dimensions
     * @throws InvalidArgumentException (not expected)
     */
    public function add(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot add matrices with different dimensions.");
        }

        $newMatrix = new Matrix($this->matrix, false);
        return $newMatrix->mAdd($term);
    }

    /**
     * Mutating matrix addition (the result is stored in the current matrix)
     *
     * @param Matrix $term
     * @return $this
     * @throws MatrixException if the matrices have unequal dimensions
     * @internal Mutating method
     */
    public function mAdd(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot add matrices with different dimensions.");
        }

        // Micro-optimized cycles
        $count = $this->columns;
        foreach ($this->matrix as $rowIndex => &$rowLeft) {
            $rowRight = $term->matrix[$rowIndex];
            for ($i = 0; $i < $count; $i++) {
                $rowLeft[$i] += $rowRight[$i];
            }
        }

        // Clear cache before return
        $this->clearCache();

        return $this;
    }

    /**
     * Matrix subtraction
     *
     * @param Matrix $term
     * @return self
     * @throws MatrixException if the matrices have unequal dimensions
     * @throws InvalidArgumentException (not expected)
     */
    public function subtract(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot subtract matrices with different dimensions.");
        }

        $newMatrix = new Matrix($this->matrix, false);
        return $newMatrix->mSubtract($term);
    }

    /**
     * Mutating matrix subtraction (the result is stored in the current matrix)
     *
     * @param Matrix $term
     * @return $this
     * @throws MatrixException if the matrices have unequal dimensions
     * @internal Mutating method
     */
    public function mSubtract(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot subtract matrices with different dimensions.");
        }

        // Micro-optimized cycles
        $count = $this->columns;
        foreach ($this->matrix as $rowIndex => &$rowLeft) {
            $rowRight = $term->matrix[$rowIndex];
            for ($i = 0; $i < $count; $i++) {
                $rowLeft[$i] -= $rowRight[$i];
            }
        }

        // Clear cache before return
        $this->clearCache();

        return $this;
    }

    /**
     * Matrix multiplication
     *
     * @param Matrix $term
     * @return self
     * @throws MatrixException if the matrices have incompatible dimensions
     * @throws InvalidArgumentException (not expected)
     */
    public function multiply(Matrix $term): self
    {
        $newMatrix = new Matrix($this->matrix, false);
        return $newMatrix->mMultiply($term);
    }

    /**
     * Mutating matrix multiplication (the result is stored in the current matrix)
     *
     * @param Matrix $term
     * @return $this
     * @throws MatrixException if the matrices have incompatible dimensions
     * @throws InvalidArgumentException (not expected)
     * @internal Mutating method
     */
    public function mMultiply(Matrix $term): self
    {
        if ($this->columns !== $term->rows) {
            throw new MatrixException("Cannot multiply matrices with incompatible dimensions.");
        }

        // Classic algorithm using three cycles but micro-optimized
        $result = [];
        $count = $this->columns;   // Stores number of columns in left matrix = number of rows in right matrix
        $arrayRight = $term->transpose()->toArray();   // Transpose right matrix to use foreach
        foreach ($this->matrix as $rowLeft) {
            $resultRow = [];    // Temporary array to store a row of resulting matrix
            foreach ($arrayRight as $columnRight) {
                $sum = 0;
                // Using a for inner cycle is slightly faster that foreach
                for ($i = 0; $i < $count; $i++) {
                    $sum += $rowLeft[$i] * $columnRight[$i];
                }
                $resultRow[] = $sum;
            }
            $result[] = $resultRow;
        }

        // Update the matrix and the information about its dimensions
        $this->matrix = $result;
        $this->columns = $term->columns;
        $this->size = $this->rows * $this->columns;

        // Clear cache before return
        $this->clearCache();

        return $this;
    }

    /**
     * Multiplication of a matrix by a scalar elementwise
     *
     * @param int|float $multiplier
     * @return self
     * @throws InvalidArgumentException (not expected)
     */
    public function multiplyByScalar(int|float $multiplier): self
    {
        $newMatrix = new Matrix($this->matrix, false);
        return $newMatrix->mMultiplyByScalar($multiplier);
    }

    /**
     * Mutating multiplication of a matrix by a scalar elementwise (result stored in the current matrix)
     *
     * @param int|float $multiplier
     * @return $this
     * @internal Mutating method
     */
    public function mMultiplyByScalar(int|float $multiplier): self
    {
        // Micro-optimized
        $count = $this->columns;
        foreach ($this->matrix as &$row) {
            for ($i = 0; $i < $count; $i++) {
                $row[$i] *= $multiplier;
            }
        }

        // Clear cache before return
        $this->clearCache();

        return $this;
    }

    /**
     * Change of signs of all elements
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function changeSign(): self
    {
        $newMatrix = new Matrix($this->matrix, false);
        return $newMatrix->mMultiplyByScalar(-1);
    }

    /**
     * Mutating change of signs of all elements (result stored in the current matrix)
     *
     * @return $this
     * @internal Mutating method
     */
    public function mChangeSign(): self
    {
        return $this->mMultiplyByScalar(-1);
    }

    public function isEqual(Matrix $term, float $tolerance = self::DEFAULT_TOLERANCE): bool
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            return false;
        }

        $countColumns = $this->columns;
        foreach ($this->matrix as $rowIndex => $rowLeft) {
            $rowRight = $term->matrix[$rowIndex];
            for ($i = 0; $i < $countColumns; $i++) {
                if (abs($rowLeft[$i] - $rowRight[$i]) > $tolerance) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isEqualExactly(Matrix $term): bool
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            return false;
        }

        $countColumns = $this->columns;
        foreach ($this->matrix as $rowIndex => $rowLeft) {
            $rowRight = $term->matrix[$rowIndex];
            for ($i = 0; $i < $countColumns; $i++) {
                if ($rowLeft[$i] !== $rowRight[$i]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Calculation of the trace of a matrix (defined for square matrices only)
     *
     * @return int|float
     * @throws MatrixException if the matrix is not square
     * @internal May return a cached property
     */
    public function trace(): int|float
    {
        // Check if the trace has been once already calculated
        if (isset($this->cacheTrace)) {
            return $this->cacheTrace;
        }

        // Check if the trace can be calculated for the given matrix
        if ($this->rows !== $this->columns) {
            throw new MatrixException("The trace is only defined for a square matrix.");
        }

        $trace = 0;
        foreach ($this->matrix as $index => $row) {
            $trace += $row[$index];
        }

        // Set cache
        if ($this->cacheEnabled) {
            $this->cachePresent = true;
            $this->cacheTrace = $trace;
        }

        return $trace;
    }

    /**
     * Returns row echelon form of the matrix
     *
     * @param bool $doSwaps If true, the method will swap the rows so that the maximal element of the column will be moved to top
     * @param int &$swaps Returns number of swaps done
     * @param float $zeroTolerance If the resulting value after subtraction is less than $zeroTolerance, it will be made equal to zero
     * @return self
     * @throws DivisionByZeroException if $doSwaps = false and some of the rows are linearly dependent
     * @throws InvalidArgumentException (not expected)
     * @internal May return a cached property
     */
    public function ref(bool $doSwaps = true, int &$swaps = 0, float $zeroTolerance = self::DEFAULT_TOLERANCE): self
    {
        // Check if the row echelon form is cached
        if (isset($this->cacheRef) && isset($this->cacheRefSwaps)) {
            $swaps = $this->cacheRefSwaps;
            return $this->cacheRef;
        }

        $ref = (new Matrix($this->matrix, false))->mRef($doSwaps, $swaps, $zeroTolerance);

        // Set cache
        if ($this->cacheEnabled) {
            $this->cacheRef = $ref;
            $this->cacheRefSwaps = $swaps;
            $this->cachePresent = true;
        }
        return $ref;
    }

    /**
     * Row echelon form. Mutating method (the initial matrix will be overwritten)
     *
     * @param bool $doSwaps If true, the method will swap the rows so that the maximal element of the column will be moved to top
     * @param int &$swaps Returns number of swaps done
     * @param float $zeroTolerance If the resulting value after subtraction is less than $zeroTolerance, it will be made equal to zero
     * @return $this|self
     * @throws DivisionByZeroException if $doSwaps = false and some of the rows are linearly dependent
     * @internal Mutating method
     */
    public function mRef(bool $doSwaps = true, int &$swaps = 0, float $zeroTolerance = self::DEFAULT_TOLERANCE): self
    {
        // Calculate the row echelon form by Gaussian elimination
        $rowIndex = $columnIndex = 0;
        $maxRowIndex = $this->rows - 1;
        $maxColumnIndex = $this->columns;

        while ($rowIndex < $maxRowIndex && $columnIndex < $maxColumnIndex) {

            // Go through the column to find the max absolute value
            $maxValue = $this->matrix[$rowIndex][$columnIndex];
            $maxValueRow = null;
            for ($i = $rowIndex; $i < $this->rows; $i++) {
                if (abs($this->matrix[$i][$columnIndex]) > $maxValue) {
                    $maxValue = $this->matrix[$i][$columnIndex];
                    $maxValueRow = $i;
                }
            }

            // If the current row does not have the maximum value in the current column, then swap the rows
            if ($doSwaps && isset($maxValueRow)) {
                [$this->matrix[$rowIndex], $this->matrix[$maxValueRow]] = [$this->matrix[$maxValueRow], $this->matrix[$rowIndex]];
                $swaps++;
            }

            // If all remaining elements in the current column are zeros, then go to the next element in the current row
            if ($maxValue === 0) {
                $columnIndex++;
                continue;
            }

            if ($this->matrix[$rowIndex][$columnIndex] === 0) {
                throw new DivisionByZeroException("Row echelon form requires division by zero. Call ref() or mRef() method with $doSwaps = true.");
            }

            // Go through the remaining rows
            for ($i = $rowIndex + 1; $i < $this->rows; $i++) {
                // Calculate the multiplier
                $multiplier = $this->matrix[$i][$columnIndex] / $this->matrix[$rowIndex][$columnIndex];

                // Replace the current element with zero
                $this->matrix[$i][$columnIndex] = 0;

                // Go through the rest of the row
                for ($j = $columnIndex + 1; $j < $this->columns; $j++) {
                    $this->matrix[$i][$j] -= $this->matrix[$rowIndex][$j] * $multiplier;

                    // If the result is smaller than $zeroTolerance, then consider it equal zero
                    if (abs($this->matrix[$i][$j]) < $zeroTolerance) {
                        $this->matrix[$i][$j] = 0;
                    }
                }
            }

            $rowIndex++;
            $columnIndex++;
        }

        // Clear cache before return
        $this->clearCache();

        return $this;
    }

    /**
     * Calculates the determinant of the matrix
     *
     * @return int|float
     * @throws InvalidArgumentException (not expected)
     * @throws MatrixException if the matrix is not square
     * @internal May return a cached property
     */
    public function determinant(): int|float
    {
        // Check if the value already present in cache
        if (isset($this->cacheDeterminant)) {
            return $this->cacheDeterminant;
        }

        if (!$this->isSquare()) {
            throw new MatrixException("The determinant is defined only for square matrices.");
        }

        // Consider small matrices as special cases
        switch ($this->rows) {
            case 1:
                $determinant = $this->matrix[0][0];
                break;
            case 2:
                $determinant = $this->matrix[0][0] * $this->matrix[1][1] - $this->matrix[0][1] * $this->matrix[1][0];
                break;

            case 3:
                $determinant =
                      $this->matrix[0][0] * $this->matrix[1][1] * $this->matrix[2][2]
                    - $this->matrix[0][0] * $this->matrix[1][2] * $this->matrix[2][1]
                    - $this->matrix[0][1] * $this->matrix[1][0] * $this->matrix[2][2]
                    + $this->matrix[0][1] * $this->matrix[1][2] * $this->matrix[2][0]
                    + $this->matrix[0][2] * $this->matrix[1][0] * $this->matrix[2][1]
                    - $this->matrix[0][2] * $this->matrix[1][1] * $this->matrix[2][0];
                break;

            default:
                // General case handled by calculating the row echelon form and multiplying elements on the main diagonal
                // If DivisionByZero exception is triggered, then the matrix contain linearly dependent rows and its determinant is zero
                try {
                    $swaps = 0;
                    $ref = $this->ref(false, $swaps);
                } catch (DivisionByZeroException) {
                    // Store the value in cache
                    if ($this->cacheEnabled) {
                        $this->cacheDeterminant = 0;
                    }
                    return 0;
                }

                $determinant = (-1) ** $swaps;
                for ($i = 0; $i < $this->rows; $i++) {
                    $determinant *= $ref->matrix[$i][$i];
                }
        }

        // Store the value in cache
        if ($this->cacheEnabled) {
            $this->cacheDeterminant = $determinant;
        }

        return $determinant;
    }
}
