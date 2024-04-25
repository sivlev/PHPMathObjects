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

declare(strict_types=1);

namespace PHPMathObjects\LinearAlgebra;

use PHPMathObjects\Exception\MatrixException;

use function is_int;
use function is_float;
use function array_fill;

/**
 * Implementation of the AbstractMatrix class to manipulate numeric matrices
 *
 * @extends AbstractMatrix<int|float>
 */
class Matrix extends AbstractMatrix
{
    /**
     * Implementation of the abstract class-specific data validation method for numeric matrices
     *
     * @param array<int, int|float> $row
     * @return int|true
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
     * Matrix addition
     *
     * @param Matrix $term
     * @return self
     * @throws MatrixException if the matrices have unequal dimensions
     */
    public function add(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot add matrices with different dimensions.");
        }

        $array = $this->matrix;
        foreach ($array as $rowIndex => &$row) {
            foreach ($row as $columnIndex => &$element) {
                $element += $term->matrix[$rowIndex][$columnIndex];
            }
        }
        return new Matrix($array, false);
    }

    /**
     * Mutating matrix addition (the result is stored in the current matrix)
     *
     * @param Matrix $term
     * @return $this
     * @throws MatrixException if the matrices have unequal dimensions
     */
    public function mAdd(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot add matrices with different dimensions.");
        }

        foreach ($term->matrix as $rowIndex => $row) {
            foreach ($row as $columnIndex => $element) {
                $this->matrix[$rowIndex][$columnIndex] += $element;
            }
        }
        return $this;
    }

    /**
     * Matrix subtraction
     *
     * @param Matrix $term
     * @return self
     * @throws MatrixException if the matrices have unequal dimensions
     */
    public function subtract(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot subtract matrices with different dimensions.");
        }

        $array = $this->matrix;
        foreach ($array as $rowIndex => &$row) {
            foreach ($row as $columnIndex => &$element) {
                $element -= $term->matrix[$rowIndex][$columnIndex];
            }
        }
        return new Matrix($array, false);
    }

    /**
     * Mutating matrix subtraction (the result is stored in the current matrix)
     *
     * @param Matrix $term
     * @return $this
     * @throws MatrixException if the matrices have unequal dimensions
     */
    public function mSubtract(Matrix $term): self
    {
        if ($this->rows !== $term->rows || $this->columns !== $term->columns) {
            throw new MatrixException("Cannot subtract matrices with different dimensions.");
        }

        foreach ($term->matrix as $rowIndex => $row) {
            foreach ($row as $columnIndex => $element) {
                $this->matrix[$rowIndex][$columnIndex] -= $element;
            }
        }
        return $this;
    }

    /**
     * Matrix multiplication
     *
     * @param Matrix $term
     * @return self
     * @throws MatrixException if the matrices have incompatible dimensions
     */
    public function multiply(Matrix $term): self
    {
        $newMatrix = new static($this->matrix, false);
        return $newMatrix->mMultiply($term);
    }

    /**
     * Mutating matrix multiplication (the result is stored in the current matrix)
     *
     * @param Matrix $term
     * @return $this
     * @throws MatrixException
     */
    public function mMultiply(Matrix $term): self
    {
        if ($this->columns !== $term->rows) {
            throw new MatrixException("Cannot multiply matrices with incompatible dimensions.");
        }

        $result = array_fill(0, $this->rows, array_fill(0, $term->columns, 0));
        $arrayRight = $term->transpose()->toArray();
        foreach ($this->matrix as $rowIndex => $rowLeft) {
            foreach ($arrayRight as $columnIndex => $columnRight) {
                foreach ($rowLeft as $index => $elementLeft) {
                    $result[$rowIndex][$columnIndex] += $elementLeft * $columnRight[$index];
                }
            }
        }

        // Update the matrix and the information about its dimensions
        $this->matrix = $result;
        $this->rows = count($result);
        $this->columns = count($result[0]);
        $this->size = $this->rows * $this->columns;

        return $this;
    }
}
