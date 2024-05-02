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

namespace LinearAlgebra;

use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Exception\OutOfBoundsException;
use PHPMathObjects\LinearAlgebra\AbstractMatrix;
use PHPMathObjects\LinearAlgebra\Matrix;
use PHPMathObjects\LinearAlgebra\Vector;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the Vector class
 *
 * @phpstan-type MatrixArray array<int, array<int, int|float>>
 */
class VectorTest extends TestCase
{
    // Tolerance used to compare two floats
    protected const e = 1e-8;

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestDox("Vector class constructor creates an instance of the expected classes")]
    public function testConstruct(): void
    {
        $v = new Vector([
            [1],
            [2],
            [3],
        ]);

        $this->assertInstanceOf(Vector::class, $v);
        $this->assertInstanceOf(Matrix::class, $v);
        $this->assertInstanceOf(AbstractMatrix::class, $v);
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestDox("Vector class constructor throws an exception if the dimensions are incompatible")]
    public function testConstructException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        new Vector([
            [1, 2],
            [3, 4],
        ]);
    }
}
