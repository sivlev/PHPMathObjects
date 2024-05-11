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
use PHPUnit\Framework\Attributes\DataProvider;
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
    /**
     * Tolerance used to compare two floats
     */
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
    #[TestWith([[[1, 2], [3, 4]], "PHPMathObjects\Exception\OutOfBoundsException"])]
    #[TestWith([[["1", 2]], "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestWith([[[1, 2], [3]], "PHPMathObjects\Exception\InvalidArgumentException"])]
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
     * @param VectorEnum $expectedVectorType
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[1, 2, 3], VectorEnum::Column, [[1], [2], [3]], VectorEnum::Column])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [[1, 2, 3]], VectorEnum::Row])]
    #[TestWith([[1], VectorEnum::Row, [[1]], VectorEnum::Column])]
    #[TestWith([[1], VectorEnum::Column, [[1]], VectorEnum::Column])]
    #[TestWith([[], VectorEnum::Row, [[1]], VectorEnum::Column, "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestWith([["1"], VectorEnum::Row, [[1]], VectorEnum::Column, "PHPMathObjects\Exception\InvalidArgumentException"])]
    #[TestDox("FromArray() factory method creates a vector from a given plain array")]
    public function testFromArray(array $array, VectorEnum $vectorType, array $expected, VectorEnum $expectedVectorType, ?string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
        }

        $v = Vector::fromArray($array, $vectorType);
        $this->assertEquals($expected, $v->toArray());
        $this->assertEquals($expectedVectorType, $v->vectorType());
    }

    /**
     * @param int $size
     * @param mixed $value
     * @param VectorEnum $vectorType
     * @param MatrixArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
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

        $v = Vector::vectorFill($size, $value, $vectorType);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEquals($expected, $v->toArray());
        $this->assertEquals($vectorType, $v->vectorType());
        $this->assertEquals($size, $v->size());
    }

    /**
     * @param class-string $method
     * @param int $size
     * @param VectorEnum $vectorType
     * @param int|float $min
     * @param int|float $max
     * @param string $exceptionMessage
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith(["vectorRandom", 5, VectorEnum::Column, 0.0, 1.0])]
    #[TestWith(["vectorRandom", 3, VectorEnum::Row, -1, 0])]
    #[TestWith(["vectorRandom", 6, VectorEnum::Column, -10.15, -10.10])]
    #[TestWith(["vectorRandom", 2, VectorEnum::Row, 4.5, 4.5])]
    #[TestWith(["vectorRandom", -3, VectorEnum::Column, 0, 1, "Matrix dimensions must be greater than zero. Rows -3 and columns 1 are given"])]
    #[TestWith(["vectorRandom", 3, VectorEnum::Column, 2.5, 1.2, "The maximum value 1.2 cannot be less than the minimum value 2.5"])]
    #[TestWith(["vectorRandomInt", 1, VectorEnum::Column, 0, 100])]
    #[TestWith(["vectorRandomInt", 5, VectorEnum::Row, -10, -6])]
    #[TestWith(["vectorRandomInt", 2, VectorEnum::Column, -100, 100])]
    #[TestWith(["vectorRandomInt", -10, VectorEnum::Row, 0, 1, "Matrix dimensions must be greater than zero. Rows 1 and columns -10 are given"])]
    #[TestWith(["vectorRandomInt", 10, VectorEnum::Column, 100, 50, "The maximum value 50 cannot be less than the minimum value 100"])]
    #[TestDox("Random() and randomInt() factories create a matrix of the given size and filled with float or integer values within the given range")]
    public function testRandom(string $method, int $size, VectorEnum $vectorType, int|float $min, int|float $max, string $exceptionMessage = ""): void
    {
        if (!empty($exceptionMessage)) {
            $this->expectException(OutOfBoundsException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }
        $m = Vector::$method($size, $min, $max, $vectorType);
        foreach ($m->toArray() as $row) {
            foreach ($row as $element) {
                $this->assertThat(
                    $element,
                    $this->logicalAnd(
                        $this->greaterThanOrEqual($min),
                        $this->lessThanOrEqual($max)
                    )
                );
            }
        }
    }

    /**
     * @param int $size
     * @param MatrixArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
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
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column])]
    #[TestWith([[-0.111, 0.222, 3], VectorEnum::Row])]
    #[TestWith([[5], VectorEnum::Row])]
    #[TestWith([[5], VectorEnum::Column])]
    #[TestDox("ToMatrix() method converts a Vector object into a Matrix object")]
    public function testToMatrix(array $array, VectorEnum $vectorType): void
    {
        $v = Vector::fromArray($array, $vectorType);
        $m = $v->toMatrix();
        $this->assertInstanceOf(Matrix::class, $m);
        $this->assertEquals($v->toArray(), $m->toArray());
        $this->assertEquals($v->rows(), $m->rows());
        $this->assertEquals($v->columns(), $m->columns());
        $this->assertEquals($v->size(), $m->size());
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
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, [6, 7, 8, 9, 10], VectorEnum::Row, [[7, 9, 11, 13, 15]]])]
    #[TestWith([[0.1, 0.2, 0.3], VectorEnum::Column, [-0.1, -0.2, -0.3], VectorEnum::Column, [[0], [0], [0]]])]
    #[TestWith([[100], VectorEnum::Column, [-1000], VectorEnum::Column, [[-900]]])]
    #[TestWith([[100], VectorEnum::Column, [-1000, 100], VectorEnum::Column, [[-900]], "PHPMathObjects\Exception\MatrixException"])]
    #[TestDox("Add() and mAdd() methods add one vector to another")]
    public function testVectorAdd(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, array $expected, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);
        $v = $v1->add($v2);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEquals($expected, $v->toArray());
        $v1->mAdd($v2);
        $this->assertInstanceOf(Vector::class, $v1);
        $this->assertEquals($expected, $v1->toArray());
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param VectorArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[TestWith([[10, 20, 30, 40, 50, 60], VectorEnum::Row, [10, 10, 10, 10, 10, 10], VectorEnum::Row, [[0, 10, 20, 30, 40, 50]]])]
    #[TestWith([[1.1, 1.2, 1.3], VectorEnum::Column, [-1.1, 1.2, -1.3], VectorEnum::Column, [[2.2], [0], [2.6]]])]
    #[TestWith([[5.5], VectorEnum::Column, [4.4], VectorEnum::Column, [[1.1]]])]
    #[TestWith([[5.5, 6.6], VectorEnum::Column, [4.4], VectorEnum::Column, [[1.1]], "PHPMathObjects\Exception\MatrixException"])]
    #[TestDox("Subtract() and mSubtract() methods subtract one vector from another")]
    public function testVectorSubtract(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, array $expected, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);
        $v = $v1->subtract($v2);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEqualsWithDelta($expected, $v->toArray(), self::e);
        $v1->mSubtract($v2);
        $this->assertInstanceOf(Vector::class, $v1);
        $this->assertEqualsWithDelta($expected, $v1->toArray(), self::e);
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param VectorArray $expected
     * @param bool $expectedIsVector
     * @param VectorEnum $expectedVectorType
     * @param class-string<Throwable>|null $exception
     * @param class-string<Throwable>|null $mutatingException
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider("providerVectorMultiply")]
    #[TestDox("Multiply() and mMultiply() methods multiply one vector by another vector or by a matrix")]
    public function testVectorMultiply(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, array $expected, bool $expectedIsVector, VectorEnum $expectedVectorType, ?string $exception = null, ?string $mutatingException = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);

        // Test non-mutating multiplication
        $v = $v1->multiply($v2);
        if ($expectedIsVector) {
            $this->assertInstanceOf(Vector::class, $v);
            $this->assertEquals($expectedVectorType, $v->vectorType());
        } else {
            $this->assertNotInstanceOf(Vector::class, $v);
            $this->assertInstanceOf(Matrix::class, $v);
        }

        $this->assertEqualsWithDelta($expected, $v->toArray(), self::e);

        // Test mutating multiplication
        if (isset($mutatingException)) {
            $this->expectException($mutatingException);
        }
        $v1->mMultiply($v2);
        $this->assertInstanceOf(Vector::class, $v1);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($v1 instanceof Vector) {
            $this->assertEquals($expectedVectorType, $v1->vectorType());
        }
        $this->assertEqualsWithDelta($expected, $v1->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, null|bool|String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerVectorMultiply(): array
    {
        return [
            [
                [1, 2, 3], VectorEnum::Row,
                [1, 2, 3], VectorEnum::Column,
                [
                    [14],
                ], true, VectorEnum::Column,
            ],
            [
                [1, 2, 3], VectorEnum::Column,
                [1, 2, 3], VectorEnum::Row,
                [
                    [1, 2, 3],
                    [2, 4, 6],
                    [3, 6, 9],
                ], false, VectorEnum::Column, null, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2, 3, 4, 5], VectorEnum::Column,
                [1, 2, 3], VectorEnum::Row,
                [
                    [1, 2, 3],
                    [2, 4, 6],
                    [3, 6, 9],
                    [4, 8, 12],
                    [5, 10, 15],
                ], false, VectorEnum::Column, null, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1], VectorEnum::Column,
                [1, 2, 3, 4, 5], VectorEnum::Row,
                [
                    [1, 2, 3, 4, 5],
                ], true, VectorEnum::Row,
            ],
            [
                [1], VectorEnum::Row,
                [1, 2, 3, 4, 5], VectorEnum::Row,
                [
                    [1, 2, 3, 4, 5],
                ], true, VectorEnum::Row,
            ],
            [
                [1, 2, 3, 4, 5], VectorEnum::Column,
                [1], VectorEnum::Column,
                [
                    [1],
                    [2],
                    [3],
                    [4],
                    [5],
                ], true, VectorEnum::Column,
            ],
            [
                [1, 2, 3, 4, 5], VectorEnum::Column,
                [1], VectorEnum::Row,
                [
                    [1],
                    [2],
                    [3],
                    [4],
                    [5],
                ], true, VectorEnum::Column,
            ],
            [
                [1], VectorEnum::Row,
                [1, 2, 3, 4, 5], VectorEnum::Column,
                [
                    [1, 2, 3, 4, 5],
                ], true, VectorEnum::Row, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2, 3, 4, 5, 6], VectorEnum::Row,
                [1, 2, 3, 4, 5], VectorEnum::Column,
                [
                    [1, 2, 3, 4, 5],
                ], true, VectorEnum::Row, "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType
     * @param MatrixArray $array2
     * @param MatrixArray $expected
     * @param bool $expectedIsVector
     * @param VectorEnum $expectedVectorType
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider("providerVectorMatrixMultiplication")]
    #[TestDox("Multiplication of a vector by a matrix with multiply() method returns either a vector, or a matrix, or throws an exception")]
    public function testVectorMatrixMultiplication(array $array1, VectorEnum $vectorType, array $array2, array $expected, bool $expectedIsVector, VectorEnum $expectedVectorType, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }
        $v = Vector::fromArray($array1, $vectorType);
        $m = new Matrix($array2);
        $result = $v->multiply($m);
        $this->assertEqualsWithDelta($expected, $result->toArray(), self::e);
        if ($expectedIsVector) {
            $this->assertInstanceOf(Vector::class, $result);
            $this->assertEquals($expectedVectorType, $result->vectorType());
        } else {
            $this->assertNotInstanceOf(Vector::class, $result);
            $this->assertInstanceOf(Matrix::class, $result);
        }
    }

    /**
     * @return array<int, array<int, null|bool|String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerVectorMatrixMultiplication(): array
    {
        return [
            [
                [1, 2, 3], VectorEnum::Row,
                [
                    [1, 2, 3, 4, 5],
                    [6, 7, 8, 9, 10],
                    [11, 12, 13, 14, 15],
                ],
                [
                    [46, 52, 58, 64, 70],
                ], true, VectorEnum::Row,
            ],
            [
                [1, 2, 3], VectorEnum::Column,
                [
                    [1, 2, 3, 4, 5],
                ],
                [
                    [1, 2, 3, 4, 5],
                    [2, 4, 6, 8, 10],
                    [3, 6, 9, 12, 15],
                ], false, VectorEnum::Row,
            ],
            [
                [0], VectorEnum::Column,
                [
                    [1, 2, 3, 4, 5],
                ],
                [
                    [0, 0, 0, 0, 0],
                ], true, VectorEnum::Row,
            ],
            [
                [0], VectorEnum::Row,
                [
                    [1, 2, 3, 4, 5],
                ],
                [
                    [0, 0, 0, 0, 0],
                ], true, VectorEnum::Row,
            ],
            [
                [2, 4, 6, 8, 10], VectorEnum::Column,
                [
                    [3],
                ],
                [
                    [6],
                    [12],
                    [18],
                    [24],
                    [30],
                ], true, VectorEnum::Column,
            ],
            [
                [5], VectorEnum::Column,
                [
                    [2],
                ],
                [
                    [10],
                ], true, VectorEnum::Column,
            ],
            [
                [2], VectorEnum::Row,
                [
                    [5.5],
                ],
                [
                    [11],
                ], true, VectorEnum::Column,
            ],
            [
                [2, 1, 2], VectorEnum::Row,
                [
                    [10, 11],
                    [12, 13],
                ],
                [
                    [11],
                ], true, VectorEnum::Column, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [2, 1, 2], VectorEnum::Column,
                [
                    [10, 11],
                    [12, 13],
                ],
                [
                    [11],
                ], true, VectorEnum::Column, "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType
     * @param MatrixArray $array2
     * @param VectorArray $expectedPlain
     * @param VectorEnum $expectedVectorType
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider("providerVectorMatrixMMultiply")]
    #[TestDox("MMultiply() method of vector with matrix returns either a vector or throws an exception")]
    public function testVectorMatrixMMultiply(array $array1, VectorEnum $vectorType, array $array2, array $expectedPlain, VectorEnum $expectedVectorType, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }
        $v = Vector::fromArray($array1, $vectorType);
        $m = new Matrix($array2);
        $v->mMultiply($m);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEqualsWithDelta($expectedPlain, $v->toPlainArray(), self::e);
        $this->assertEquals($expectedVectorType, $v->vectorType());
    }

    /**
     * @return array<int, array<int, String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerVectorMatrixMMultiply(): array
    {
        return [
            [
                [1.1, 2.2, 3.3], VectorEnum::Row,
                [
                    [1.1, 2.2, 3.3],
                    [-1.1, -2.2, -3.3],
                    [0, 0, 0],
                ],
                [-1.21, -2.42, -3.63], VectorEnum::Row,
            ],
            [
                [1.1], VectorEnum::Row,
                [
                    [0],
                ],
                [0], VectorEnum::Column,
            ],
            [
                [1.1, 2.2, 3.3], VectorEnum::Column,
                [
                    [1.1, 2.2, 3.3],
                    [-1.1, -2.2, -3.3],
                    [0, 0, 0],
                ],
                [-1.21, -2.42, -3.63], VectorEnum::Row, "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType
     * @param MatrixArray $expected
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider("providerMatrixVectorMultiplication")]
    #[TestDox("Multiplication of matrix by a vector with multiply() and mMultiply() methods either returns a matrix, or throws an exception")]
    public function testMatrixVectorMultiplication(array $array1, array $array2, VectorEnum $vectorType, array $expected, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }

        $m = new Matrix($array1);
        $v = Vector::fromArray($array2, $vectorType);

        // Test non-mutating multiplication
        $m1 = $m->multiply($v);
        $this->assertInstanceOf(Matrix::class, $m1);
        $this->assertNotInstanceOf(Vector::class, $m1);
        $this->assertEqualsWithDelta($expected, $m1->toArray(), self::e);

        // Test mutating multiplication
        $m->mMultiply($v);
        $this->assertInstanceOf(Matrix::class, $m);
        $this->assertNotInstanceOf(Vector::class, $m);
        $this->assertEqualsWithDelta($expected, $m->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, null|bool|String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerMatrixVectorMultiplication(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [1, 2, 3], VectorEnum::Column,
                [
                    [14],
                    [32],
                    [50],
                ],
            ],
            [
                [
                    [1.1, 2.2, 3.3],
                ],
                [-1.1, -2.2, -3.3], VectorEnum::Column,
                [
                    [-16.94],
                ],
            ],
            [
                [
                    [50],
                ],
                [0.1, 0.2, 0.3, 0.4], VectorEnum::Row,
                [
                    [5, 10, 15, 20],
                ],
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                ],
                [-1, -2, -3], VectorEnum::Row,
                [
                    [-1, -2, -3],
                    [-2, -4, -6],
                    [-3, -6, -9],
                ],
            ],
            [
                [
                    [1, 2],
                    [2, 3],
                    [3, 4],
                ],
                [-1, -2, -3], VectorEnum::Row,
                [
                    [-1, -2, -3],
                    [-2, -4, -6],
                    [-3, -6, -9],
                ], "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                ],
                [-1, -2, -3], VectorEnum::Column,
                [
                    [-1, -2, -3],
                    [-2, -4, -6],
                    [-3, -6, -9],
                ], "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @param VectorArray $expected
     * @param VectorEnum $expectedVectorType
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[1, 2, 3], VectorEnum::Row, [[1], [2], [3]], VectorEnum::Column])]
    #[TestWith([[1, 2, 3], VectorEnum::Column, [[1, 2, 3]], VectorEnum::Row])]
    #[TestWith([[1], VectorEnum::Column, [[1]], VectorEnum::Column])]
    #[TestWith([[1], VectorEnum::Row, [[1]], VectorEnum::Column])]
    #[TestDox("Transpose() and mTranspose methods convert a row vector into a column vector and vice versa")]
    public function testTranspose(array $array, VectorEnum $vectorType, array $expected, VectorEnum $expectedVectorType): void
    {
        $v = Vector::fromArray($array, $vectorType);

        // Test non-mutating method
        $v1 = $v->transpose();
        $this->assertEquals($expected, $v1->toArray());
        $this->assertEquals($expectedVectorType, $v1->vectorType());

        // Test mutating method
        $v->mTranspose();
        $this->assertEquals($expected, $v->toArray());
        $this->assertEquals($expectedVectorType, $v->vectorType());
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param int|float $expected
     * @param class-string<Throwable>|null $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[TestWith([[1, 2, 3], VectorEnum::Column, [4, -5, 6], VectorEnum::Column, 12])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [4, -5, 6], VectorEnum::Column, 12])]
    #[TestWith([[1, 2, 3], VectorEnum::Column, [4, -5, 6], VectorEnum::Row, 12])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [4, -5, 6], VectorEnum::Row, 12])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [1.2, -5.6, 2.03], VectorEnum::Row, -3.91])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [0, 0, 0], VectorEnum::Row, 0])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [4, -5], VectorEnum::Row, 12, "PHPMathObjects\Exception\MatrixException"])]
    #[TestDox("DotProduct() method returns the value of the dot (scalar) product of two vectors")]
    public function testDotProduct(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, int|float $expected, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);
        $this->assertEqualsWithDelta($expected, $v1->dotProduct($v2), self::e);
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param VectorArray $expected
     * @param bool $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[TestWith([[1, 2, 3], VectorEnum::Column, [1, 2, 3], VectorEnum::Column, [0, 0, 0]])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [3, 6, 9], VectorEnum::Column, [0, 0, 0]])]
    #[TestWith([[1, 2, 3], VectorEnum::Column, [-1, -2, -3], VectorEnum::Row, [0, 0, 0]])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [5, -7, 12], VectorEnum::Row, [45, 3, -17]])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [5, -7, 12], VectorEnum::Column, [45, 3, -17]])]
    #[TestWith([[-0.81, 65.273, 93], VectorEnum::Column, [0, 2.573, -3.2234], VectorEnum::Row, [-449.6899882, -2.610954, -2.08413]])]
    #[TestWith([[1, 2, 3], VectorEnum::Row, [3, 6], VectorEnum::Column, [], true])]
    #[TestWith([[1, 2, 3, 4], VectorEnum::Row, [3, 6, 9], VectorEnum::Column, [], true])]
    #[TestDox("CrossProduct() method returns the cross product of two vectors")]
    public function testCrossProduct(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, array $expected, bool $exception = false): void
    {
        if ($exception) {
            $this->expectException(MatrixException::class);
        }
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);
        $v = $v1->crossProduct($v2);
        $this->assertEqualsWithDelta($expected, $v->toPlainArray(), self::e);
    }

    /**
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @param VectorArray $expected
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[1, 2, 3], VectorEnum::Column, [-1, -2, -3]])]
    #[TestWith([[0.1, -0.2, 0.3, -0.4], VectorEnum::Row, [-0.1, 0.2, -0.3, 0.4]])]
    #[TestDox("ChangeSign() and mChangeSign() change signs of all elements of a vector")]
    public function testChangeSign(array $array, VectorEnum $vectorType, array $expected): void
    {
        $v = Vector::fromArray($array, $vectorType);

        // Test non-mutating method
        $v1 = $v->changeSign();
        $this->assertInstanceOf(Vector::class, $v1);
        $this->assertEqualsWithDelta($expected, $v1->toPlainArray(), self::e);

        // Test mutating method
        $v->mChangeSign();
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEqualsWithDelta($expected, $v->toPlainArray(), self::e);
    }

    /**
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @param int|float $scalar
     * @param VectorArray $expected
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[1, 2, 3.3], VectorEnum::Column, 5.5, [5.5, 11, 18.15]])]
    #[TestWith([[1, 2, 3.3], VectorEnum::Row, 5.5, [5.5, 11, 18.15]])]
    #[TestWith([[1, 2, 3.3, -28.4, -0.1238], VectorEnum::Column, 0, [0, 0, 0, 0, 0]])]
    #[TestDox("MultiplyByScalar() and mMultiplyByScalar() method multiply each element of a vector by a scalar")]
    public function testMultiplyByScalar(array $array, VectorEnum $vectorType, int|float $scalar, array $expected): void
    {
        $v = Vector::fromArray($array, $vectorType);

        // Test non-mutating method
        $v1 = $v->multiplyByScalar($scalar);
        $this->assertInstanceOf(Vector::class, $v1);
        $this->assertEqualsWithDelta($expected, $v1->toPlainArray(), self::e);

        // Test mutating method
        $v->mMultiplyByScalar($scalar);
        $this->assertInstanceOf(Vector::class, $v);
        $this->assertEqualsWithDelta($expected, $v->toPlainArray(), self::e);
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param string $method
     * @param bool $expected
     * @param float $tolerance
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, [1, 2, 3, 4, 5], VectorEnum::Column, "float", true])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, [1, 2, 3, 4, 5], VectorEnum::Column, "int", true])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, [1, 2, 3, 4, 5], VectorEnum::Row, "float", false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, [1, 2, 3, 4, 5], VectorEnum::Row, "int", false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, [1, 2, 3, 4], VectorEnum::Column, "int", false])]
    #[TestWith([[1], VectorEnum::Column, [1], VectorEnum::Column, "int", true])]
    #[TestWith([[1], VectorEnum::Column, [1], VectorEnum::Row, "int", true])]
    #[TestWith([[1], VectorEnum::Row, [1], VectorEnum::Column, "int", true])]
    #[TestWith([[1], VectorEnum::Row, [1], VectorEnum::Row, "int", true])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, [1, 2, 3, 4], VectorEnum::Column, "float", false])]
    #[TestWith([[0.000001, 0.000002, 0.000003, 0.000004, 0.000005], VectorEnum::Column, [0.000001, 0.000002, 0.000003, 0.000004, 0.000006], VectorEnum::Column, "float", false])]
    #[TestWith([[0.000001, 0.000002, 0.000003, 0.000004, 0.000005], VectorEnum::Column, [0.000001, 0.000002, 0.000003, 0.000004, 0.000006], VectorEnum::Column, "float", true, 1e-5])]
    #[TestDox("IsEqual() and isEqualExactly() method check the equality of two vectors")]
    public function testIsEqualVectorVector(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, string $method, bool $expected, float $tolerance = self::e): void
    {
        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);
        if ($method === "float") {
            $this->assertEquals($expected, $v1->isEqual($v2, $tolerance));
        } else {
            $this->assertEquals($expected, $v1->isEqualExactly($v2));
        }
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param MatrixArray $array2
     * @param string $method
     * @param bool $expected
     * @param float $tolerance
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider("providerIsEqualVectorMatrix")]
    #[TestDox("IsEqual() and isEqualExactly() method check the equality of a vector with a matrix")]
    public function testIsEqualVectorMatrix(array $array1, VectorEnum $vectorType1, array $array2, string $method, bool $expected, float $tolerance = self::e): void
    {
        $v = Vector::fromArray($array1, $vectorType1);
        $m = new Matrix($array2);
        if ($method === "float") {
            $this->assertEquals($expected, $v->isEqual($m, $tolerance));
        } else {
            $this->assertEquals($expected, $v->isEqualExactly($m));
        }
    }

    /**
     * @return array<int, array<int, bool|String|float|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerIsEqualVectorMatrix(): array
    {
        return [
            [
                [1, 2, 3], VectorEnum::Column,
                [
                    [1],
                    [2],
                    [3],
                ], "int", true,
            ],
            [
                [1, 2, 3], VectorEnum::Column,
                [
                    [1],
                    [2],
                ], "int", false,
            ],
            [
                [1, 2, 3], VectorEnum::Row,
                [
                    [1],
                    [2],
                    [3],
                ], "int", false,
            ],
            [
                [1, 2, 3], VectorEnum::Row,
                [
                    [1, 2, 3],
                ], "int", true,
            ],
            [
                [0.00001, 0.00002, 0.00003], VectorEnum::Row,
                [
                    [0.00001, 0.00002, 0.00004],
                ], "float", false,
            ],
            [
                [0.00001, 0.00002, 0.00003], VectorEnum::Row,
                [
                    [0.00001, 0.00002, 0.00004],
                ], "float", true, 1e-3,
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param string $method
     * @param bool $expected
     * @param float $tolerance
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider("providerIsEqualMatrixVector")]
    #[TestDox("IsEqual() and isEqualExactly() method check the equality of a vector with a matrix")]
    public function testIsEqualMatrixVector(array $array1, array $array2, VectorEnum $vectorType2, string $method, bool $expected, float $tolerance = self::e): void
    {
        $m = new Matrix($array1);
        $v = Vector::fromArray($array2, $vectorType2);
        if ($method === "float") {
            $this->assertEquals($expected, $m->isEqual($v, $tolerance));
        } else {
            $this->assertEquals($expected, $m->isEqualExactly($v));
        }
    }

    /**
     * @return array<int, array<int, bool|String|float|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerIsEqualMatrixVector(): array
    {
        return [
            [
                [
                    [1],
                    [2],
                    [3],
                ],
                [1, 2, 3], VectorEnum::Column, "int", true,
            ],
            [
                [
                    [1],
                    [2],
                ],
                [1, 2, 3], VectorEnum::Column, "int", false,
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                ],
                [1, 2, 3], VectorEnum::Row, "int", false,
            ],
            [
                [
                    [1, 2, 3],
                ],
                [1, 2, 3], VectorEnum::Row, "int", true,
            ],
            [
                [
                    [0.00001, 0.00002, 0.00004],
                ],
                [0.00001, 0.00002, 0.00003], VectorEnum::Row, "float", false,
            ],
            [
                [
                    [0.00001, 0.00002, 0.00004],
                ],
                [0.00001, 0.00002, 0.00003], VectorEnum::Row, "float", true, 1e-3,
            ],
        ];
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param MatrixArray $expected
     * @param class-string $method1
     * @param class-string $method2
     * @param bool $isVector
     * @param null|class-string<Throwable> $exception1
     * @param null|class-string<Throwable> $exception2
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider("providerJoinVectorVector")]
    #[TestDox("JoinRight(), joinBottom(), mJoinRight() and mJoinBottom on two vectors return either a vector, or a matrix, or throws exception")]
    public function testJoinVectorVector(array $array1, VectorEnum $vectorType1, array $array2, VectorEnum $vectorType2, array $expected, string $method1, string $method2, bool $isVector, ?string $exception1 = null, ?string $exception2 = null): void
    {
        if (isset($exception1)) {
            $this->expectException($exception1);
        }

        $v1 = Vector::fromArray($array1, $vectorType1);
        $v2 = Vector::fromArray($array2, $vectorType2);

        // Non-mutating method
        $v = $v1->{$method1}($v2);
        if ($isVector) {
            $this->assertInstanceOf(Vector::class, $v);
        } else {
            $this->assertNotInstanceOf(Vector::class, $v);
            $this->assertInstanceOf(Matrix::class, $v);
        }
        $this->assertEquals($expected, $v->toArray());

        // Mutating method
        if (isset($exception2)) {
            $this->expectException($exception2);
        }
        $v1->{$method2}($v2);
        $this->assertInstanceOf(Vector::class, $v1);
        $this->assertEquals($expected, $v1->toArray());
    }

    /**
     * @return array<int, array<int, null|bool|String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerJoinVectorVector(): array
    {
        return [
            [
                [1, 2, 3], VectorEnum::Row,
                [1, 2, 3, 4], VectorEnum::Row,
                [[1, 2, 3, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", true,
            ],
            [
                [1], VectorEnum::Column,
                [1, 2, 3, 4], VectorEnum::Row,
                [[1, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", true,
            ],
            [
                [1, 2], VectorEnum::Column,
                [1, 2, 3, 4], VectorEnum::Row,
                [[1, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", true, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Column,
                [1, 2], VectorEnum::Column,
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinRight", "mJoinRight", false, null, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Column,
                [1, 2, 3], VectorEnum::Column,
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinRight", "mJoinRight", false, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2, 3], VectorEnum::Column,
                [1, 2, 3, 4], VectorEnum::Column,
                [
                    [1],
                    [2],
                    [3],
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                "joinBottom", "mJoinBottom", true,
            ],
            [
                [1], VectorEnum::Row,
                [1, 2, 3, 4], VectorEnum::Column,
                [
                    [1],
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                "joinBottom", "mJoinBottom", true,
            ],
            [
                [1, 2], VectorEnum::Row,
                [1, 2, 3, 4], VectorEnum::Column,
                [[1, 1, 2, 3, 4]],
                "joinBottom", "mJoinBottom", true, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Row,
                [1, 2], VectorEnum::Row,
                [
                    [1, 2],
                    [1, 2],
                ],
                "joinBottom", "mJoinBottom", false, null, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Row,
                [1, 2, 3], VectorEnum::Row,
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinBottom", "mJoinBottom", false, "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param VectorArray $array1
     * @param VectorEnum $vectorType1
     * @param MatrixArray $array2
     * @param MatrixArray $expected
     * @param class-string $method1
     * @param class-string $method2
     * @param bool $isVector
     * @param null|class-string<Throwable> $exception1
     * @param null|class-string<Throwable> $exception2
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider("providerJoinVectorMatrix")]
    #[TestDox("JoinRight(), joinBottom(), mJoinRight() and mJoinBottom on vector with matrix return either a vector, or a matrix, or throws exception")]
    public function testJoinVectorMatrix(array $array1, VectorEnum $vectorType1, array $array2, array $expected, string $method1, string $method2, bool $isVector, ?string $exception1 = null, ?string $exception2 = null): void
    {
        if (isset($exception1)) {
            $this->expectException($exception1);
        }

        $v1 = Vector::fromArray($array1, $vectorType1);
        $m1 = new Matrix($array2);

        // Non-mutating method
        $v = $v1->{$method1}($m1);
        if ($isVector) {
            $this->assertInstanceOf(Vector::class, $v);
        } else {
            $this->assertNotInstanceOf(Vector::class, $v);
            $this->assertInstanceOf(Matrix::class, $v);
        }
        $this->assertEquals($expected, $v->toArray());

        // Mutating method
        if (isset($exception2)) {
            $this->expectException($exception2);
        }
        $v1->{$method2}($m1);
        $this->assertInstanceOf(Vector::class, $v1);
        $this->assertEquals($expected, $v1->toArray());
    }

    /**
     * @return array<int, array<int, null|bool|String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerJoinVectorMatrix(): array
    {
        return [
            [
                [1, 2, 3], VectorEnum::Row,
                [
                    [1, 2, 3, 4],
                ],
                [[1, 2, 3, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", true,
            ],
            [
                [1], VectorEnum::Column,
                [
                    [1, 2, 3, 4],
                ],
                [[1, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", true,
            ],
            [
                [1, 2], VectorEnum::Column,
                [
                    [1, 2, 3, 4],
                ],
                [[1, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", true, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Column,
                [
                    [1],
                    [2],
                ],
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinRight", "mJoinRight", false, null, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Column,
                [
                    [1],
                    [2],
                    [3],
                ],
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinRight", "mJoinRight", false, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2, 3], VectorEnum::Column,
                [
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                [
                    [1],
                    [2],
                    [3],
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                "joinBottom", "mJoinBottom", true,
            ],
            [
                [1], VectorEnum::Row,
                [
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                [
                    [1],
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                "joinBottom", "mJoinBottom", true,
            ],
            [
                [1, 2], VectorEnum::Row,
                [
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                [[1, 1, 2, 3, 4]],
                "joinBottom", "mJoinBottom", true, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Row,
                [
                    [1, 2],
                ],
                [
                    [1, 2],
                    [1, 2],
                ],
                "joinBottom", "mJoinBottom", false, null, "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [1, 2], VectorEnum::Row,
                [
                    [1, 2, 3],
                ],
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinBottom", "mJoinBottom", false, "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param VectorArray $array2
     * @param VectorEnum $vectorType2
     * @param MatrixArray $expected
     * @param class-string $method1
     * @param class-string $method2
     * @param null|class-string<Throwable> $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider("providerJoinMatrixVector")]
    #[TestDox("JoinRight(), joinBottom(), mJoinRight() and mJoinBottom on matrix with vector return a matrix or throw exception")]
    public function testJoinMatrixVector(array $array1, array $array2, VectorEnum $vectorType2, array $expected, string $method1, string $method2, ?string $exception = null): void
    {
        if (isset($exception)) {
            $this->expectException($exception);
        }

        $m1 = new Matrix($array1);
        $v1 = Vector::fromArray($array2, $vectorType2);

        // Non-mutating method
        $m = $m1->{$method1}($v1);
        $this->assertNotInstanceOf(Vector::class, $m);
        $this->assertInstanceOf(Matrix::class, $m);
        $this->assertEquals($expected, $m->toArray());

        // Mutating method
        $m1->{$method2}($v1);
        $this->assertInstanceOf(Matrix::class, $m1);
        $this->assertEquals($expected, $m1->toArray());
    }

    /**
     * @return array<int, array<int, String|VectorArray|VectorEnum|MatrixArray>>
     */
    public static function providerJoinMatrixVector(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                ],
                [1, 2, 3, 4], VectorEnum::Row,
                [[1, 2, 3, 1, 2, 3, 4]],
                "joinRight", "mJoinRight",
            ],
            [
                [
                    [1],
                ],
                [1, 2, 3, 4], VectorEnum::Row,
                [[1, 1, 2, 3, 4]],
                "joinRight", "mJoinRight",
            ],
            [
                [
                    [1],
                    [2],
                ],
                [1, 2, 3, 4], VectorEnum::Row,
                [[1, 1, 2, 3, 4]],
                "joinRight", "mJoinRight", "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [
                    [1],
                    [2],
                ],
                [1, 2], VectorEnum::Column,
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinRight", "mJoinRight",
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                ],
                [1, 2], VectorEnum::Column,
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinRight", "mJoinRight", "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                [1, 2, 3], VectorEnum::Column,
                [
                    [1],
                    [2],
                    [3],
                    [4],
                    [1],
                    [2],
                    [3],
                ],
                "joinBottom", "mJoinBottom",
            ],
            [
                [
                    [1],
                ],
                [1, 2, 3, 4], VectorEnum::Column,
                [
                    [1],
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                "joinBottom", "mJoinBottom",
            ],
            [
                [
                    [1],
                    [2],
                    [3],
                    [4],
                ],
                [1, 2], VectorEnum::Row,
                [[1, 1, 2, 3, 4]],
                "joinBottom", "mJoinBottom", "PHPMathObjects\Exception\MatrixException",
            ],
            [
                [
                    [1, 2],
                ],
                [1, 2], VectorEnum::Row,
                [
                    [1, 2],
                    [1, 2],
                ],
                "joinBottom", "mJoinBottom",
            ],
            [
                [
                    [1, 2, 3],
                ],
                [1, 2], VectorEnum::Row,
                [
                    [1, 1],
                    [2, 2],
                ],
                "joinBottom", "mJoinBottom", "PHPMathObjects\Exception\MatrixException",
            ],
        ];
    }

    /**
     * @param VectorArray $array
     * @param VectorEnum $vectorType
     * @param int $start
     * @param int $length
     * @param MatrixArray $expected
     * @param bool $expectException
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, 1, 3, [[2], [3], [4]], false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, 0, 4, [[1], [2], [3], [4], [5]], false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, 1, 3, [[2, 3, 4]], false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, 0, 4, [[1, 2, 3, 4, 5]], false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, 1, 1, [[2]], false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, 1, 1, [[2]], false])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Column, -1, 0, [], true])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, -1, 0, [], true])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, 0, 5, [], true])]
    #[TestWith([[1, 2, 3, 4, 5], VectorEnum::Row, 3, 2, [], true])]
    #[TestDox("Subvector() method returns a subvector of a vector")]
    public function testSubvector(array $array, VectorEnum $vectorType, int $start, int $length, array $expected, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(OutOfBoundsException::class);
        }
        $v = Vector::fromArray($array, $vectorType);
        $v1 = $v->subvector($start, $length);
        $this->assertEquals($expected, $v1->toArray());
    }
}
