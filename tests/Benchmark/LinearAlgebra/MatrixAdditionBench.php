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

namespace PHPMathObjects\Tests\Benchmark\LinearAlgebra;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Exception\MatrixException;
use PHPMathObjects\Exception\OutOfBoundsException;
use PHPMathObjects\LinearAlgebra\Matrix;

/**
 * Benchmark for matrix addition with add() and mAdd() methods
 */
#[Groups(["LinearAlgebra"])]
#[BeforeMethods('setUp')]
class MatrixAdditionBench
{
    /**
     * @var Matrix A
     */

    protected Matrix $a;

    /**
     * @var Matrix B
     */
    protected Matrix $b;

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    public function setUp(): void
    {
        $this->a = Matrix::fill(1000, 1000, 17);
        $this->b = Matrix::fill(1000, 1000, -52.11);
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[Iterations(5)]
    #[Revs(10)]
    public function benchAdd(): void
    {
        $this->a->add($this->b);
    }

    /**
     * @return void
     * @throws MatrixException
     */
    #[Iterations(5)]
    #[Revs(10)]
    public function benchMAdd(): void
    {
        $this->a->mAdd($this->b);
    }
}
