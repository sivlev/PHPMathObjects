<?php
/*
 * PHPMathObjects Library
 *
 * @see https://github.com/sivlev/PHPMathObjects
 *
 * @author Sergei Ivlev <sergei.ivlev@chemie.uni-marburg.de>
 * @copyright (c) 2024 Sergei Ivlev
 * @license https://opensource.org/license/mit The MIT License
 *
 * @note This software is distributed "as is", with no warranty expressed or implied, and no guarantee for accuracy or applicability to any purpose. See the license text for details.
 */

declare(strict_types=1);

namespace PHPMathObjects\LinearAlgebra;

use ArrayAccess;
use Countable;
use PHPMathObjects\Exception\MatrixException;

use function array_values;
use function array_map;
use function count;
use function is_array;

/**
 * Abstract class to implement matrices of different types
 *
 * @template T
 * @implements ArrayAccess<array<int, int>, T>
 * @phpstan-consistent-constructor
 */
abstract class AbstractMatrix implements Countable, ArrayAccess
{
    /**
     * Two-dimensional array to store the matrix data
     *
     * @var array<int, array<int, T>>
     */
    protected array $matrix;

    /**
     * Number of rows in the matrix
     *
     * @var int
     */
    protected int $rows;

    /**
     * Number of columns in the matrix
     *
     * @var int
     */
    protected int $columns;

    /**
     * Total number of elements in the matrix
     *
     * @var int
     */
    protected int $size;

    /**
     * AbstractMatrix class constructor
     *
     * @param array<int, array<int, T>> $data
     * @param bool $validateData If true, the provided $data array must be validated. It is mostly intended for internal use. All user-input data must be validated.
     * @throws MatrixException if the provided $data array is ill-behaved (e.g. not all rows have same number of columns)
     */
    public function __construct(array $data, bool $validateData = true)
    {
        // Setting up the "matrix" and "rows" properties. The rest is safe to initialize after data validation
        $this->matrix = array_values($data);
        $this->rows = count($data);

        // If requested, perform the validation now
        if ($validateData) {
            $this->validateData();
        }

        // Prevent throwing TypeError for the cases when data validation is not performed
        /* @phpstan-ignore-next-line */
        $this->columns = count(is_array($data[0]) ? $data[0] : []);
        $this->size = $this->rows * $this->columns;
    }

    /**
     * Factory method to create a matrix with the given dimensions and filled with the given value
     *
     * @param int $rows
     * @param int $columns
     * @param T $value
     * @return static
     * @throws MatrixException if the matrix dimensions are non-positive or if the given value is incompatible with the matrix type
     */
    public static function fill(int $rows, int $columns, mixed $value): static
    {
        if ($rows <= 0 || $columns <= 0) {
            throw new MatrixException("Matrix dimensions must be greater than zero. Rows $rows and columns $columns are given");
        }
        return new static(array_fill(0, $rows, array_fill(0, $columns, $value)));
    }

    /**
     * Method for data validation independent of matrix type. Checks that all matrix rows are arrays of equal length. During the cycle a class-specific validation will be called.
     *
     * @throws MatrixException if the matrix is empty, or its rows are empty, or its rows have different sizes
     */
    protected function validateData(): void
    {
        // Check that the matrix is not empty and that the first row is not empty
        if ($this->rows === 0 || !is_array($this->matrix[0]) || count($this->matrix[0]) === 0) {
            throw new MatrixException('Matrix cannot be empty or contain empty rows or rows with non-array elements.');
        }

        $this->columns = count($this->matrix[0]);

        // Check that every row has the same amount of elements (columns), remove the array keys and call class-specific validation
        foreach ($this->matrix as $rowIndex => &$row) {
            if (!is_array($row)) {
                throw new MatrixException("The matrix array must be two-dimensional array (array of arrays). The row [$rowIndex] is not an array.");
            }

            if (count($row) !== $this->columns) {
                throw new MatrixException("All matrix rows must have the same number of columns. The row [$rowIndex] has a different number of columns.");
            }

            $row = array_values($row);

            $exceptionMessage = "";
            $columnIndex = $this->validateDataClassSpecific($row, $rowIndex, $exceptionMessage);
            if ($this->validateDataClassSpecific($row) !== true) {
                throw new MatrixException($exceptionMessage);
            }
        }
    }

    /**
     * Abstract method for class-specific data validation
     *
     * @param array<int, T> $row The current row to be validated
     * @param string &$exceptionMessage Contains a message for the exception to be thrown if the data are found to be invalid
     * @return int|true Returns true if data are valid or the column index where an invalid data entry was found
     */
    abstract protected function validateDataClassSpecific(array $row, int $rowIndex = 0, string &$exceptionMessage = ""): int|true;

    /**
     * Returns the matrix as array
     * @return array<int, array<int, T>>
     */
    public function toArray(): array
    {
        return $this->matrix;
    }

    /**
     * Getter for the "rows" property. Returns number of rows
     *
     * @return int
     */
    public function rows(): int
    {
        return $this->rows;
    }

    /**
     * Getter for the "columns" property. Returns number of columns
     *
     * @return int
     */
    public function columns(): int
    {
        return $this->columns;
    }

    /**
     * Returns the size the matrix, i.e. the number of elements
     *
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Implementation of the "Countable" interface, alias for size() method
     *
     * @return int
     */
    public function count(): int
    {
        return $this->size;
    }


    /**
     * Returns true if the elements with the given indices exist, or false otherwise
     *
     * @param int $row
     * @param int $column
     * @return bool
     */
    public function isSet(int $row, int $column): bool
    {
        return isset($this->matrix[$row][$column]);
    }

    /**
     * Returns the value of a matrix element with given indices.
     *
     * @param int $row
     * @param int $column
     * @return T
     * @throws MatrixException if the element with the given indices does not exist
     */
    public function get(int $row, int $column): mixed
    {
        if (!isset($this->matrix[$row][$column])) {
            throw new MatrixException("The element [$row][$column] does not exist.");
        }
        return $this->matrix[$row][$column];
    }

    /**
     * Sets the matrix element with the given indices to the given value
     *
     * @param int $row
     * @param int $column
     * @param T $value
     * @return $this
     * @throws MatrixException if the element with the given indices does not exist or if the given value has a type incompatible with the matrix instance
     */
    public function set(int $row, int $column, mixed $value): static
    {
        if (!isset($this->matrix[$row][$column])) {
            throw new MatrixException("The element [$row][$column] does not exist.");
        }

        // Check if the type of the given value is compatible with the matrix
        if ($this->validateDataClassSpecific([$value]) !== true) {
            throw new MatrixException("The type '" . gettype($value) . "' is incompatible with the given Matrix instance.");
        }

        $this->matrix[$row][$column] = $value;
        return $this;
    }

    /**
     * OffsetExists() method of the ArrayAccess interface
     *
     * @param array<int, int> $offset
     * @return bool
     * @throws MatrixException if method does not receive an array of the format [row, column]
     */
    public function offsetExists(mixed $offset): bool
    {
        if (!is_array($offset) || count($offset) !== 2) {
            throw new MatrixException("Wrong format of array access. The offsetExists method expects a 1D array [row, column].");
        }

        return isset($this->matrix[(int) $offset[0]][(int) $offset[1]]);
    }

    /**
     * OffsetGet() method of the ArrayAccess interface
     *
     * @param array<int, int> $offset
     * @return T
     * @throws MatrixException if the method does not receive an array of the format [row, column] or if the element does not exist
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!is_array($offset) || count($offset) !== 2) {
            throw new MatrixException("Wrong format of array access. The offsetExists method expects a 1D array [row, column].");
        }

        return $this->get((int) $offset[0], (int) $offset[1]);
    }

    /**
     * OffsetSet() method of the ArrayAccess interface
     *
     * @param array<int, int> $offset
     * @param T $value
     * @return void
     * @throws MatrixException if the method does not receive an array of the format [row, column] or if the element does not exist
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_array($offset) || count($offset) !== 2) {
            throw new MatrixException("Wrong format of array access. The offsetExists method expects a 1D array [row, column].");
        }

        $this->set((int) $offset[0], (int) $offset[1], $value);
    }

    /**
     * Unset() method of the ArrayAccess interface (not implemented)
     *
     * @param mixed $offset
     * @throws MatrixException on call
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new MatrixException("The matrix elements cannot be unset directly.");
    }

    /**
     * Matrix transpose
     *
     * @return static
     * @throws MatrixException (not expected)
     */
    public function transpose(): static
    {
        $newMatrix = new static($this->matrix, false);
        return $newMatrix->mTranspose();
    }

    /**
     * Mutating matrix transpose (the current matrix will be modified)
     *
     * @return $this
     */
    public function mTranspose(): static
    {
        if ($this->rows === 1) {

            // Trivial case 1x1
            if ($this->columns === 1) {
                return $this;
            }

            // Special case of an 1 x n row matrix (vector)
            $result = [];
            foreach ($this->matrix[0] as $element) {
                $result[] = [$element];
            }
            $this->matrix = $result;
            $this->rows = $this->columns;
            $this->columns = 1;
            return $this;
        }

        [$this->rows, $this->columns] = [$this->columns, $this->rows];
        $this->matrix = array_map(null, ...$this->matrix);
        return $this;
    }

    /**
     * Returns a text representation of the matrix
     *
     * @return string
     */
    public function __toString(): string
    {
        return trim(array_reduce(
            array_map(fn(array $value): string => implode(", ", $value), $this->matrix),
            fn($carry, $value): string => $carry .= "[" . $value . "]" . PHP_EOL
        ) ?? "");
    }

    /**
     * Alias to magic method __toString()
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->__toString();
    }
}
