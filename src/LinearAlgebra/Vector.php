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
use PHPMathObjects\Exception\OutOfBoundsException;

class Vector extends Matrix
{
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
        parent::__construct($data, $validateData = true);

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
     * Returns VectorEnum::Row or VectorEnum::Column depending on the orientation of the vector
     *
     * @return VectorEnum
     */
    public function vectorType(): VectorEnum
    {
        return $this->vectorType;
    }
}
