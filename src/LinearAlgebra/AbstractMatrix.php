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
use PHPMathObjects\Exception\BadMethodCallException;
use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Exception\OutOfBoundsException;

use function array_values;
use function array_map;
use function array_reduce;
use function count;
use function is_array;
use function gettype;

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
     * If true, then at least one cacheable property is cached, and the cache must be cleared by any mutation of the matrix
     *
     * @see self::clearCache()
     * @var bool
     */
    protected bool $cachePresent = false;

    /**
     * Controls caching of properties that may require heavy calculations (determinant, row echelon form, etc.)
     *
     * @var bool
     */
    protected bool $cacheEnabled = true;

    /**
     * AbstractMatrix class constructor
     *
     * @param array<int, array<int, T>> $data
     * @param bool $validateData If true, the provided $data array must be validated. It is mostly intended for internal use. All user-input data must be validated.
     * @throws InvalidArgumentException if the provided $data array is ill-behaved (e.g. not all rows have same number of columns)
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
     * @throws InvalidArgumentException if the given value is incompatible with the matrix type
     * @throws OutOfBoundsException if the matrix dimensions are non-positive
     */
    public static function fill(int $rows, int $columns, mixed $value): static
    {
        if ($rows <= 0 || $columns <= 0) {
            throw new OutOfBoundsException("Matrix dimensions must be greater than zero. Rows $rows and columns $columns are given");
        }
        return new static(array_fill(0, $rows, array_fill(0, $columns, $value)));
    }

    /**
     * Method for data validation independent of matrix type. Checks that all matrix rows are arrays of equal length. During the cycle a class-specific validation will be called.
     *
     * @throws InvalidArgumentException if the matrix is empty, or its rows are empty, or its rows have different sizes
     */
    protected function validateData(): void
    {
        // Check that the matrix is not empty and that the first row is not empty
        if ($this->rows === 0 || !is_array($this->matrix[0]) || count($this->matrix[0]) === 0) {
            throw new InvalidArgumentException('Matrix cannot be empty or contain empty rows or rows with non-array elements.');
        }

        $this->columns = count($this->matrix[0]);

        // Check that every row has the same amount of elements (columns), remove the array keys and call class-specific validation
        foreach ($this->matrix as $rowIndex => &$row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException("The matrix array must be two-dimensional array (array of arrays). The row [$rowIndex] is not an array.");
            }

            if (count($row) !== $this->columns) {
                throw new InvalidArgumentException("All matrix rows must have the same number of columns. The row [$rowIndex] has a different number of columns.");
            }

            $row = array_values($row);

            $exceptionMessage = "";
            $columnIndex = $this->validateDataClassSpecific($row, $rowIndex, $exceptionMessage);
            if ($columnIndex !== true) {
                throw new InvalidArgumentException($exceptionMessage);
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
     * Abstract method for flushing cached properties, such as determinants etc.
     * Some matrix properties (e.g. the determinant) are cached after being calculated. When the corresponding getter is
     * called once again later, the property is immediately returned from the cache and is not calculated again.
     *
     * @return void
     */
    abstract protected function clearCache(): void;

    /**
     * Enables or disables caching of some matrix properties
     *
     * @param bool $cacheEnabled
     * @return void
     */
    public function setCacheEnabled(bool $cacheEnabled): void
    {
        $this->cacheEnabled = $cacheEnabled;
    }

    /**
     * Returns true if caching is enabled and false otherwise
     *
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

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
     * @throws OutOfBoundsException if the element with the given indices does not exist
     */
    public function get(int $row, int $column): mixed
    {
        if (!isset($this->matrix[$row][$column])) {
            throw new OutOfBoundsException("The element [$row][$column] does not exist.");
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
     * @throws OutOfBoundsException if the element with the given indices does not exist
     * @throws InvalidArgumentException if the given value has a type incompatible with the matrix instance
     * @internal Mutating method
     */
    public function set(int $row, int $column, mixed $value): static
    {
        if (!isset($this->matrix[$row][$column])) {
            throw new OutOfBoundsException("The element [$row][$column] does not exist.");
        }

        // Check if the type of the given value is compatible with the matrix
        if ($this->validateDataClassSpecific([$value]) !== true) {
            throw new InvalidArgumentException("The type '" . gettype($value) . "' is incompatible with the given Matrix instance.");
        }

        // Flush cache before return
        $this->matrix[$row][$column] = $value;
        $this->clearCache();
        return $this;
    }

    /**
     * OffsetExists() method of the ArrayAccess interface
     *
     * @param array<int, int> $offset
     * @return bool
     * @throws InvalidArgumentException if method does not receive an array of the format [row, column]
     */
    public function offsetExists(mixed $offset): bool
    {
        if (!is_array($offset) || count($offset) !== 2) {
            throw new InvalidArgumentException("Wrong format of array access. The offsetExists method expects a 1D array [row, column].");
        }

        return isset($this->matrix[(int) $offset[0]][(int) $offset[1]]);
    }

    /**
     * OffsetGet() method of the ArrayAccess interface
     *
     * @param array<int, int> $offset
     * @return T
     * @throws InvalidArgumentException if the method does not receive an array of the format [row, column]
     * @throws OutOfBoundsException if the element does not exist
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!is_array($offset) || count($offset) !== 2) {
            throw new InvalidArgumentException("Wrong format of array access. The offsetExists method expects a 1D array [row, column].");
        }

        return $this->get((int) $offset[0], (int) $offset[1]);
    }

    /**
     * OffsetSet() method of the ArrayAccess interface
     *
     * @param array<int, int> $offset
     * @param T $value
     * @return void
     * @throws InvalidArgumentException if the method does not receive an array of the format [row, column]
     * @throws OutOfBoundsException if the element does not exist
     * @internal Mutating method
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_array($offset) || count($offset) !== 2) {
            throw new InvalidArgumentException("Wrong format of array access. The offsetExists method expects a 1D array [row, column].");
        }

        $this->set((int) $offset[0], (int) $offset[1], $value);
    }

    /**
     * Unset() method of the ArrayAccess interface (not implemented)
     *
     * @param mixed $offset
     * @throws BadMethodCallException on call
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException("The matrix elements cannot be unset directly.");
    }

    /**
     * Returns true if the matrix is square (i.e. number of rows is equal to number of columns)
     *
     * @return bool
     */
    public function isSquare(): bool
    {
        return $this->rows === $this->columns;
    }

    /**
     * Matrix transpose
     *
     * @return static
     * @throws InvalidArgumentException (not expected)
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
     * @internal Mutating method
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

        // Clear cache before return
        $this->clearCache();

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
