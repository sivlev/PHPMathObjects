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
use PHPMathObjects\Exception\MatrixException;
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
 * @phpstan-type VectorArray array<int, int|float>
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
     * @param array<int, mixed> $array
     * @param VectorEnum $vectorType
     * @param MatrixArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[1, 2, 3], VectorEnum::Column, [[1], [2], [3]]])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [[1, 2, 3]]])]
    #[TestWith([[1], VectorEnum::Row, [[1]]])]
    #[TestWith([[1], VectorEnum::Column, [[1]]])]
    #[TestWith([[], VectorEnum::Row, [[1]], "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestWith([["1"], VectorEnum::Row, [[1]], "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestDox("FromArray() factory method creates a vector from a given plain array")]
    public function testFromArray(array $array, VectorEnum $vectorType, array $expected, ?string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $v = Vector::fromArray($array, $vectorType);
        $this->assertEquals($expected, $v->toArray());
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
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @param int $expectedRows
     * @param int $expectedColumns
     * @param int $expectedSize
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, 5, 1, 5])]
    #[TestWith([[0.1, -0.2, 0.3, -0.4], VectorEnum::Row, 1, 4, 4])]
    #[TestWith([[0], VectorEnum::Row, 1, 1, 1])]
    #[TestDox("Rows(), columns(), size() and count() methods return correct values for Vector object")]
    public function testDimensions(array $array, VectorEnum $vectorType, int $expectedRows, int $expectedColumns, int $expectedSize): void
    {
        $v = Vector::fromArray($array, $vectorType);
        $this->assertEquals($expectedRows, $v->rows());
        $this->assertEquals($expectedColumns, $v->columns());
        $this->assertEquals($expectedSize, $v->size());
        $this->assertCount($expectedSize, $v);
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
     * @param VectorArray $expected
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

    /**
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @param int $index
     * @param float|int $valueToSet
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, 2, -5.5])]
    #[TestWith([[10, 9.3, 8], VectorEnum::Column, 1, 0])]
    #[TestWith([[0], VectorEnum::Column, 0, 1.1])]
    #[TestDox("Methods vSet(), vGet(), vIsSet() and corresponding ArrayAccess interface methods properly operate on Vector class")]
    public function testIsSetGetSet(array $array, VectorEnum $vectorType, int $index, float|int $valueToSet): void
    {
        $v = Vector::fromArray($array, $vectorType);
        $this->assertEquals($array[$index], $v->vGet($index));
        $this->assertEquals($array[$index], $v[$index]);
        $v->vSet($index, $valueToSet);
        $this->assertEquals($valueToSet, $v->vGet($index));
        $this->assertEquals($valueToSet, $v[$index]);
        $this->assertTrue($v->vIsSet($index));
        $this->assertFalse($v->vIsSet($v->size()));
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertTrue(isset($v[$index]));
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertFalse(isset($v[$v->size()]));
        $v[$v->size() - 1] = $valueToSet + 1;
        $this->assertEquals($valueToSet + 1, $v[$v->size() - 1]);
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param VectorArray $expected
     * @param class-string|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws MatrixException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, [6, 7, 8, 9, 10], VectorEnum::Row, [7, 9, 11, 13, 15]])]
    #[TestDox("Add() and mAdd() methods add one vector to another")]
    public function testVectorAdd(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, array $expected, ?string $exception = null): void
    {
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);
        $v = $v1->add($v2);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEquals($expected, $v->toArray());
        $v1->mAdd($v2);
        $this->assertEquals($expected, $v1->toArray());
    }
}
