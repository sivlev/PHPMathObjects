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

use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Exception\MatrixException;
use PHPMathObjects\Exception\OutOfBoundsException;

/**
 * Vector class to handle row and column vectors
 */
class Vector extends Matrix
{
    /**
     * Vector type (orientation): row vector or column vector
     *
     * @var VectorEnum
     */
    protected VectorEnum $vectorType;

    /**
     * Vector class constructor
     *
     * @param array<int, array<int, int|float>> $data
     * @param bool $validateData
     * @throws OutOfBoundsException if neither rows nor columns are equal to 1
     * @throws InvalidArgumentException if the data are of incompatible type
     * @see AbstractMatrix::__construct
     */
    public function __construct(array $data, bool $validateData = true)
    {
        parent::__construct($data, $validateData);

        // Check afterward if the vector has proper dimensions
        if ($this->columns === 1) {
            $this->vectorType = VectorEnum::Column;
        } elseif ($this->rows === 1) {
            $this->vectorType = VectorEnum::Row;
        } else {
            throw new OutOfBoundsException("Improper vector dimensions. Either m x 1 or 1 x n are allowed.");
        }
    }

    /**
     * Factory method to create a vector from a plain array
     *
     * @param array<int, int|float> $data
     * @param VectorEnum $vectorType Defines whether the vector is a row vector or a column vector
     * @return self
     * @throws InvalidArgumentException if the array contains elements of incompatible data types
     * @throws OutOfBoundsException (not expected)
     */
    public static function fromArray(array $data, VectorEnum $vectorType = VectorEnum::Column): self
    {
        if ($vectorType === VectorEnum::Column) {
            $data = array_map(fn($value) => [$value], $data);
        } else {
            $data = [$data];
        }

        return new self($data, true);
    }

    /**
     * Factory method to create a vector with the given size and filled with the given value (a wrapper for AbstractMatrix::fill)
     *
     * @param int $size
     * @param int|float $value
     * @param VectorEnum $vectorType Defines whether the vector is a row vector or a column vector
     * @return self
     * @throws InvalidArgumentException (not expected)
     * @throws OutOfBoundsException if the given size is non-positive
     * @see AbstractMatrix::fill()
     */
    public static function vectorFill(int $size, int|float $value, VectorEnum $vectorType = VectorEnum::Column): self
    {
        [$rows, $columns] = $vectorType === VectorEnum::Column ? [$size, 1] : [1, $size];
        return self::fill($rows, $columns, $value);
    }

    /**
     * Wrapper for Matrix::random() factory method
     *
     * @param int $size
     * @param int|float $min
     * @param int|float $max
     * @param VectorEnum $vectorType
     * @return self
     * @throws InvalidArgumentException (not expected)
     * @throws OutOfBoundsException if the given size is non-positive or if $min is greater than $max
     * @see Matrix::random()
     */
    public static function vectorRandom(int $size, int|float $min = 0.0, int|float $max = 1.0, VectorEnum $vectorType = VectorEnum::Column): self
    {
        [$rows, $columns] = $vectorType === VectorEnum::Column ? [$size, 1] : [1, $size];
        return self::random($rows, $columns, $min, $max);
    }

    /**
     * Wrapper for Matrix::randomInt() factory method
     *
     * @param int $size
     * @param int $min
     * @param int $max
     * @param VectorEnum $vectorType
     * @return self
     * @throws InvalidArgumentException (not expected)
     * @throws OutOfBoundsException if the given size is non-positive or if $min is greater than $max
     * @see Matrix::randomInt()
     */
    public static function vectorRandomInt(int $size, int $min = 0, int $max = 100, VectorEnum $vectorType = VectorEnum::Column): self
    {
        [$rows, $columns] = $vectorType === VectorEnum::Column ? [$size, 1] : [1, $size];
        return self::randomInt($rows, $columns, $min, $max);
    }

    /**
     * Returns VectorEnum::Row or VectorEnum::Column depending on the orientation of the vector
     *
     * @return VectorEnum
     */
    public function vectorType(): VectorEnum
    {
        return $this->vectorType;
    }

    /**
     * Returns the vector as a plain 1D array independent of its type
     *
     * @return array<int, int|float>
     * @throws InvalidArgumentException (not expected)
     */
    public function toPlainArray(): array
    {
        return $this->vectorType === VectorEnum::Column ? $this->transpose()->toArray()[0] : $this->toArray()[0];
    }

    /**
     * Converts the current vector into an instance of Matrix class
     *
     * @return Matrix
     * @throws InvalidArgumentException (not expected)
     * @see Matrix
     */
    public function toMatrix(): Matrix
    {
        return new Matrix($this->matrix, false);
    }

    /**
     * Wrapper for isSet() method
     *
     * @param int $index
     * @return bool
     * @see AbstractMatrix::isSet()
     */
    public function vIsSet(int $index): bool
    {
        [$row, $column] = $this->vectorType === VectorEnum::Column ? [$index, 0] : [0, $index];
        return parent::isSet($row, $column);
    }

    /**
     * Wrapper for get() method
     *
     * @param int $index
     * @return int|float
     * @throws OutOfBoundsException
     * @see AbstractMatrix::get()
     */
    public function vGet(int $index): int|float
    {
        [$row, $column] = $this->vectorType === VectorEnum::Column ? [$index, 0] : [0, $index];
        return parent::get($row, $column);
    }

    /**
     * Wrapper for set() method
     *
     * @param int $index
     * @param int|float $value
     * @return self
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @see AbstractMatrix::set()
     * @internal Mutating method
     */
    public function vSet(int $index, int|float $value): self
    {
        [$row, $column] = $this->vectorType === VectorEnum::Column ? [$index, 0] : [0, $index];
        return parent::set($row, $column, $value);
    }

    /**
     * Wrapper for offsetExists() method
     *
     * @param int $offset
     * @return bool
     * @throws InvalidArgumentException
     * @see AbstractMatrix::offsetExists()
     * @phpstan-ignore-next-line
     */
    public function offsetExists(mixed $offset): bool
    {
        $arrayRowColumn = $this->vectorType === VectorEnum::Column ? [$offset, 0] : [0, $offset];
        return parent::offsetExists($arrayRowColumn);
    }

    /**
     * Wrapper for offsetGet() method
     *
     * @param int $offset
     * @return int|float
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @see AbstractMatrix::offsetGet()
     * @phpstan-ignore-next-line
     */
    public function offsetGet(mixed $offset): int|float
    {
        $arrayRowColumn = $this->vectorType === VectorEnum::Column ? [$offset, 0] : [0, $offset];
        return parent::offsetGet($arrayRowColumn);
    }

    /**
     * Wrapper for offsetSet() method
     *
     * @param int $offset
     * @param int|float $value
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @see AbstractMatrix::offsetSet()
     * @internal Mutating method
     * @phpstan-ignore-next-line
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $arrayRowColumn = $this->vectorType === VectorEnum::Column ? [$offset, 0] : [0, $offset];
        parent::offsetSet($arrayRowColumn, $value);
    }

    /**
     * Override matrix multiplication method to account for that "vector x vector" is not always a vector but can also be a matrix
     *
     * @param Matrix $term
     * @return self|Matrix
     * @throws InvalidArgumentException (not expected)
     * @throws MatrixException if the vectors or matrices have incompatible dimensions
     * @throws OutOfBoundsException (not expected)
     * @see Matrix::multiply()
     */
    public function multiply(Matrix $term): self|Matrix
    {
        if ($this->rows > 1 && $term->columns > 1) {
            $newEntity = new Matrix($this->matrix, false);
        } else {
            $newEntity = new Vector($this->matrix, false);
        }
        return $newEntity->mMultiply($term);
    }

    /**
     * Override matrix mMultiply() multiplication method to account for that "vector x vector" is not always a vector but can also be a matrix. A mutation that is incompatible with Vector object is not allowed.
     *
     * @param Matrix $term
     * @return $this
     * @throws InvalidArgumentException (not expected)
     * @throws MatrixException if a vector has to be converted to a matrix after mutation or if the vector dimensions are incompatible
     * @see Matrix::mMultiply()
     * @internal Mutating method
     */
    public function mMultiply(Matrix $term): static
    {
        if ($this->rows > 1 && $term->columns > 1) {
            throw new MatrixException("The result of multiplication is a matrix, not a vector. Cannot mutate the given vector object, use multiply() instead.");
        }

        // Check the resulting vector dimensions and assign the correct vector type
        $this->vectorType = ($term->columns > 1) ? VectorEnum::Row : VectorEnum::Column;
        return parent::mMultiply($term);
    }

    /**
     * Override AbstractMatrix mTranspose() method to account for change of $vectorType
     *
     * @return $this
     * @see AbstractMatrix::mTranspose()
     */
    public function mTranspose(): static
    {
        if ($this->size !== 1) {
            $this->vectorType = $this->vectorType->transpose();
        }
        return parent::mTranspose();
    }

    /**
     * Calculates the dot (scalar) product of two vectors
     *
     * @param Vector $anotherVector
     * @return int|float
     * @throws InvalidArgumentException (not expected)
     * @throws MatrixException if vectors have different numbers of components
     */
    public function dotProduct(Vector $anotherVector): int|float
    {
        if ($this->size !== $anotherVector->size) {
            throw new MatrixException("Both vectors must have equal number of components for dot product calculation");
        }

        $v1 = $this->toPlainArray();
        $v2 = $anotherVector->toPlainArray();

        $dotProduct = 0;
        foreach ($v1 as $index => $element) {
            $dotProduct += $element * $v2[$index];
        }

        return $dotProduct;
    }

    /**
     * Override joinRight() method to account for that "vector x vector" is not always a vector but can also be a matrix
     *
     * @param Matrix $anotherMatrix
     * @return self|Matrix
     * @throws InvalidArgumentException if the vectors or matrices have incompatible types
     * @throws MatrixException if the vectors or matrices have incompatible dimensions
     * @throws OutOfBoundsException (not expected)
     * @see Matrix::joinBottom()
     */
    public function joinRight(AbstractMatrix $anotherMatrix): self|Matrix
    {
        if ($this->rows > 1) {
            $newEntity = new Matrix($this->matrix, false);
        } else {
            $newEntity = new Vector($this->matrix, false);
        }
        return $newEntity->mJoinRight($anotherMatrix);
    }

    /**
     * Override matrix mJoinRight() method to account for that "vector x vector" is not always a vector but can also be a matrix. A mutation that is incompatible with Vector object is not allowed.
     *
     * @param Matrix $anotherMatrix
     * @return $this
     * @throws MatrixException if a vector has to be converted to a matrix after mutation or if the vector dimensions are incompatible
     * @throws InvalidArgumentException if the vectors or matrices have incompatible types
     * @see Matrix::mJoinRight()
     * @internal Mutating method
     */
    public function mJoinRight(AbstractMatrix $anotherMatrix): static
    {
        if ($this->rows > 1) {
            throw new MatrixException("The result of mJoinRight() is a matrix, not a vector. Cannot mutate the given vector object, use joinRight() instead.");
        }

        // Assign the correct vector type
        $this->vectorType = VectorEnum::Row;
        return parent::mJoinRight($anotherMatrix);
    }

    /**
     * Override joinBottom() method to account for that "vector x vector" is not always a vector but can also be a matrix
     *
     * @param Matrix $anotherMatrix
     * @return self|Matrix
     * @throws InvalidArgumentException if the vectors or matrices have incompatible types
     * @throws MatrixException if the vectors or matrices have incompatible dimensions
     * @throws OutOfBoundsException (not expected)
     * @see Matrix::joinBottom()
     */
    public function joinBottom(AbstractMatrix $anotherMatrix): self|Matrix
    {
        if ($this->columns > 1) {
            $newEntity = new Matrix($this->matrix, false);
        } else {
            $newEntity = new Vector($this->matrix, false);
        }
        return $newEntity->mJoinBottom($anotherMatrix);
    }

    /**
     * Override matrix mJoinBottom() method to account for that "vector x vector" is not always a vector but can also be a matrix. A mutation that is incompatible with Vector object is not allowed.
     *
     * @param Matrix $anotherMatrix
     * @return $this
     * @throws MatrixException if a vector has to be converted to a matrix after mutation or if the vector dimensions are incompatible
     * @throws InvalidArgumentException if the vectors or matrices have incompatible types
     * @see Matrix::mJoinBottom()
     * @internal Mutating method
     */
    public function mJoinBottom(AbstractMatrix $anotherMatrix): static
    {
        if ($this->columns > 1) {
            throw new MatrixException("The result of mJoinBottom() is a matrix, not a vector. Cannot mutate the given vector object, use joinBottom() instead.");
        }

        // Assign the correct vector type
        $this->vectorType = VectorEnum::Column;
        return parent::mJoinBottom($anotherMatrix);
    }

    /**
     * Returns a subvector of the current vector
     *
     * @param int $start
     * @param int $end
     * @return self
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @see AbstractMatrix::submatrix()
     */
    public function subvector(int $start, int $end): self
    {
        if ($this->rows === 1) {
            $rowStart = 0;
            $rowEnd = 0;
            $columnStart = $start;
            $columnEnd = $end;
        } else {
            $rowStart = $start;
            $rowEnd = $end;
            $columnStart = 0;
            $columnEnd = 0;
        }

        return self::submatrix($rowStart, $columnStart, $rowEnd, $columnEnd);
    }
}
