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
use PHPMathObjects\LinearAlgebra\VectorEnum;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Throwable;

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
     * @param MatrixArray $array
     * @param class-string<Throwable> $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[[1,2], [3,4]], "PHPMathObjects\Exception\OutOfBoundsException"])]
    #[TestWith([[["1",2]], "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestWith([[[1,2], [3]], "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestDox("Vector class constructor throws an exception if the dimensions are incompatible or if the data are in wrong format")]
    public function testConstructException(array $array, string $exception): void
    {
        $this->expectException($exception);
        new Vector($array);
    }

    /**
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestDox("Strange objects can be created if data validation is avoided")]
    public function testConstructorWithoutValidation(): void
    {
        /* @phpstan-ignore-next-line */
        $v = new Vector([
            [1],
            ["1"],
            [[1, 2, 3]],
        ], false);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertInstanceOf(Matrix::class, $v);
        $this->assertInstanceOf(AbstractMatrix::class, $v);
    }

    /**
     * @param int $size
     * @param mixed $value
     * @param VectorEnum $vectorType
     * @param MatrixArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([5, -2.3, VectorEnum::Row, [[-2.3, -2.3, -2.3, -2.3, -2.3]]])]
    #[TestWith([3, 15, VectorEnum::Column, [[15], [15], [15]]])]
    #[TestWith([-1, 15, VectorEnum::Column, [[15], [15], [15]], "PHPMathObjects\Exception\OutOfBoundsException"])]
    #[TestWith([0, 15, VectorEnum::Column, [[15], [15], [15]], "PHPMathObjects\Exception\OutOfBoundsException"])]
    #[TestDox("VectorFill() factory method creates a vector of a given size and type, filled with the given value")]
    public function testVectorFill(int $size, mixed $value, VectorEnum $vectorType, array $expected, ?string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $v = Vector::fillVector($size, $value, $vectorType);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEquals($expected, $v->toArray());
        $this->assertEquals($vectorType, $v->vectorType());
        $this->assertEquals($size, $v->size());
    }

    /**
     * @param int $size
     * @param MatrixArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([1, [[1]]])]
    #[TestWith([2, [[1]], "PHPMathObjects\Exception\OutOfBoundsException"])]
    #[TestWith([0, [[1]], "PHPMathObjects\Exception\OutOfBoundsException"])]
    #[TestDox("Vector::identity() method returns a [1] vector or throws an exception")]
    public function testIdentity(int $size, array $expected, ?string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $v = Vector::identity($size);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEquals($expected, $v->toArray());
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestDox("VectorType() method returns the orientation of the vector")]
    public function testVectorType(): void
    {
        $v = new Vector([
            [1],
            [2],
            [3],
        ]);
        $this->assertEquals(VectorEnum::Column, $v->vectorType());

        $v = new Vector([
            [1, 2, 3],
        ]);
        $this->assertEquals(VectorEnum::Row, $v->vectorType());

        // Vector with a single element is by default a column vector
        $v = new Vector([
            [1],
        ]);
        $this->assertEquals(VectorEnum::Column, $v->vectorType());
    }

    /**
     * @param MatrixArray $array
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[[1, 2, 3]]])]
    #[TestWith([[[1], [2], [3]]])]
    #[TestDox("ToArray() method returns the vector as 2D array")]
    public function testToArray(array $array): void
    {
        $v = new Vector($array);
        $this->assertEquals($array, $v->toArray());
    }

    /**
     * @param MatrixArray $array
     * @param array<int, int|float> $expected
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[[1, 2, 3]], [1, 2, 3]])]
    #[TestWith([[[1], [2], [3]], [1, 2, 3]])]
    #[TestDox("ToPlainArray() method returns the vector as 1D array")]
    public function testToPlainArray(array $array, array $expected): void
    {
        $v = new Vector($array);
        $this->assertEquals($expected, $v->toPlainArray());
    }

    /**
     * @param MatrixArray $array
     * @param string $expected
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[[1.2, 2.3, -3.5]], "[1.2, 2.3, -3.5]"])]
    #[TestWith([[[1], [2], [3]], "[1]" . PHP_EOL . "[2]" . PHP_EOL . "[3]"])]
    #[TestDox("ToString() method returns the vector as a string")]
    public function testToString(array $array, string $expected): void
    {
        $v = new Vector($array);
        $this->assertEquals($expected, $v->toString());
        $this->assertEquals($expected, (string) $v);
    }
}
