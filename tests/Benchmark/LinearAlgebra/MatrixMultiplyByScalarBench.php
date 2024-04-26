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

namespace PHPMathObjects\Tests\Benchmark\LinearAlgebra;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PHPMathObjects\Exception\MatrixException;
use PHPMathObjects\LinearAlgebra\Matrix;

/**
 * Benchmark for matrix multiplication by a scalar with multiplyByScalar() and mMultiplyByScalar() methods
 */
#[Groups(["LinearAlgebra"])]
#[BeforeMethods('setUp')]
class MatrixMultiplyByScalarBench
{
    /**
     * @var Matrix A
     */

    protected Matrix $a;

    /**
     * Scalar multiplier to be used
     * @var int|float
     */
    protected int|float $multiplier;

    /**
     * @throws MatrixException
     */
    public function setUp(): void
    {
        $this->a = Matrix::fill(1000, 1000, 1.1);
        $this->multiplier = -16.55;
    }

    /**
     * @return void
     * @throws MatrixException
     */
    #[Iterations(5)]
    #[Revs(10)]
    public function benchMultiplyByScalar(): void
    {
        $this->a->multiplyByScalar($this->multiplier);
    }

    /**
     * @return void
     */
    #[Iterations(5)]
    #[Revs(10)]
    public function benchMMultiplyByScalar(): void
    {
        $this->a->mMultiplyByScalar($this->multiplier);
    }
}
