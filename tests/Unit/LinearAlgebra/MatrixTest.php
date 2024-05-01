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

namespace PHPMathObjects\Tests\Unit\LinearAlgebra;

use PHPMathObjects\Exception\DivisionByZeroException;
use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Exception\MathObjectsException;
use PHPMathObjects\Exception\MatrixException;
use PHPMathObjects\Exception\OutOfBoundsException;
use PHPMathObjects\LinearAlgebra\AbstractMatrix;
use PHPMathObjects\LinearAlgebra\Matrix;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the Matrix class as well as for its parent class AbstractMatrix
 *
 * @phpstan-type MatrixArray array<int, array<int, int|float>>
 */
class MatrixTest extends TestCase
{
    // Tolerance used to compare two floats
    protected const e = 1e-8;

    /**
     * @throws InvalidArgumentException
     */
    #[TestDox("Construct creates an instance of the expected classes")]
    public function testConstructor(): void
    {
        $m = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);
        $this->assertInstanceOf(Matrix::class, $m);
        $this->assertInstanceOf(AbstractMatrix::class, $m);
    }

    /**
     * @param MatrixArray $matrix
     * @param string $exceptionMessage
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([[], "Matrix cannot be empty or contain empty rows or rows with non-array elements."])]
    #[TestWith([[[], [1]], "Matrix cannot be empty or contain empty rows or rows with non-array elements."])]
    #[TestWith([[1, 2, 3], "Matrix cannot be empty or contain empty rows or rows with non-array elements."])]
    #[TestWith([[[1], [2, 3]], "All matrix rows must have the same number of columns. The row [1] has a different number of columns."])]
    #[TestWith([[[1], [2], 3], "The matrix array must be two-dimensional array (array of arrays). The row [2] is not an array."])]
    #[TestDox("Abstract constructor throws exceptions if the given array is invalid")]
    public function testConstructorException(array $matrix, string $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        new Matrix($matrix);
    }

    /**
     * @param int $rows
     * @param int $columns
     * @param int|float $value
     * @param MatrixArray $expected
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider('providerFillFactory')]
    #[TestDox("Fill() factory creates a matrix of the given size and filled with the given value")]
    public function testFillFactory(int $rows, int $columns, int|float $value, array $expected): void
    {
        $m = Matrix::fill($rows, $columns, $value);
        $this->assertEqualsWithDelta($expected, $m->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, int|float|MatrixArray>>
     */
    public static function providerFillFactory(): array
    {
        return [
            [
                1, 1, 0.1,
                [[0.1]],
            ],
            [
                5, 1, -2,
                [
                    [-2],
                    [-2],
                    [-2],
                    [-2],
                    [-2],
                ],
            ],
            [
                1, 10, 0,
                [[0, 0, 0, 0, 0, 0, 0, 0, 0, 0]],
            ],
            [
                5, 3, -100.635,
                [
                    [-100.635, -100.635, -100.635],
                    [-100.635, -100.635, -100.635],
                    [-100.635, -100.635, -100.635],
                    [-100.635, -100.635, -100.635],
                    [-100.635, -100.635, -100.635],
                ],
            ],
        ];
    }

    /**
     * @param int $rows
     * @param int $columns
     * @param mixed $value
     * @param class-string<MathObjectsException> $exceptionClass
     * @param string $exceptionMessage
     * @return void
     * @throws OutOfBoundsException
     * @throws InvalidArgumentException
     */
    #[TestWith([-4, 5, 0, "PHPMathObjects\Exception\OutOfBoundsException", "Matrix dimensions must be greater than zero. Rows -4 and columns 5 are given"])]
    #[TestWith([3, 0, -0.1, "PHPMathObjects\Exception\OutOfBoundsException", "Matrix dimensions must be greater than zero. Rows 3 and columns 0 are given"])]
    #[TestWith([3, 3, "1", "PHPMathObjects\Exception\InvalidArgumentException", "Elements of a numeric matrix must be either integer or float. Element [0][0] is of type 'string'."])]
    #[TestDox("Fill() factory throws an exception if the given dimensions or value type are invalid")]
    public function testFillFactoryException(int $rows, int $columns, mixed $value, string $exceptionClass, string $exceptionMessage): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);
        Matrix::fill($rows, $columns, $value);
    }

    /**
     * @param int $size
     * @param array<int, array<int, int>> $expected
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider('providerIdentityFactory')]
    #[TestDox("Identity() factory creates an identity matrix of the given size")]
    public function testIdentityFactory(int $size, array $expected): void
    {
        $m = Matrix::identity($size);
        $this->assertEquals($expected, $m->toArray());
    }

    /**
     * @return array<int, array<int, int|array<int, array<int, int>>>>
     */
    public static function providerIdentityFactory(): array
    {
        return [
            [
                1,
                [[1]],
            ],
            [
                2,
                [
                    [1, 0],
                    [0, 1],
                ],
            ],
            [
                4,
                [
                    [1, 0, 0, 0],
                    [0, 1, 0, 0],
                    [0, 0, 1, 0],
                    [0, 0, 0, 1],
                ],
            ],
        ];
    }

    /**
     * @param int $size
     * @param string $exceptionMessage
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith([0, "Size of identity matrix must greater than zero. Size 0 is given."])]
    #[TestWith([-10, "Size of identity matrix must greater than zero. Size -10 is given."])]
    #[TestDox("Identity() factory throws an exception if the given size is non-positive")]
    public function testIdentityFactoryException(int $size, string $exceptionMessage): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage($exceptionMessage);
        Matrix::identity($size);
    }

    /**
     * @param class-string $method
     * @param int $rows
     * @param int $columns
     * @param int|float $min
     * @param int|float $max
     * @param string $exceptionMessage
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[TestWith(["random", 5, 4, 0.0, 1.0])]
    #[TestWith(["random", 3, 3, -1, 0])]
    #[TestWith(["random", 6, 2, -10.15, -10.10])]
    #[TestWith(["random", 2, 2, 4.5, 4.5])]
    #[TestWith(["random", -3, 4, 0, 1, "Matrix dimensions must be greater than zero. Rows -3 and columns 4 are given"])]
    #[TestWith(["random", 8, -1, 0, 1, "Matrix dimensions must be greater than zero. Rows 8 and columns -1 are given"])]
    #[TestWith(["random", 3, 3, 2.5, 1.2, "The maximum value 1.2 cannot be less than the minimum value 2.5"])]
    #[TestWith(["randomInt", 1, 1, 0, 100])]
    #[TestWith(["random", 5, 5, -10, -6])]
    #[TestWith(["random", 2, 3, -100, 100])]
    #[TestWith(["randomInt", -10, 2, 0, 1, "Matrix dimensions must be greater than zero. Rows -10 and columns 2 are given"])]
    #[TestWith(["randomInt", 20, -100, 0, 1, "Matrix dimensions must be greater than zero. Rows 20 and columns -100 are given"])]
    #[TestWith(["randomInt", 10, 10, 100, 50, "The maximum value 50 cannot be less than the minimum value 100"])]
    #[TestDox("Random() and randomInt() factories create a matrix of the given size and filled with float or integer values within the given range")]
    public function testRandom(string $method, int $rows, int $columns, int|float $min, int|float $max, string $exceptionMessage = ""): void
    {
        if (!empty($exceptionMessage)) {
            $this->expectException(OutOfBoundsException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }
        $m = Matrix::$method($rows, $columns, $min, $max);
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
     * @param MatrixArray $matrix
     * @param string $exceptionMessage
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerConstructorNumericMatrixException')]
    #[TestDox("Data validation method of numeric matrix class throws exceptions if the given array is contains elements other then int or float")]
    public function testDataValidationNumericMatrixException(array $matrix, string $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        new Matrix($matrix);
    }

    /**
     * @return array<int, array<int, array<int, array<int, mixed>>|string>>
     * @throws InvalidArgumentException
     */
    public static function providerConstructorNumericMatrixException(): array
    {
        return [
            [
                [
                    [1, -2, 3.4],
                    [4, 5.0, -6.2],
                    [7.1, "8", 9],
                ], "Elements of a numeric matrix must be either integer or float. Element [2][1] is of type 'string'.",
            ],
            [
                [
                    [1, -2, 3.4],
                    [4, 5.0, new Matrix([[1]])],
                    [7.1, 8, 9],
                ], "Elements of a numeric matrix must be either integer or float. Element [1][2] is of type 'object'.",
            ],
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    #[TestDox("Strange objects can be created if data validation is avoided")]
    public function testConstructorWithoutValidation(): void
    {
        /* @phpstan-ignore-next-line */
        $m = new Matrix([
            [],
            [-1, 2.4, -3.5],
            [5, "6"],
            [7, 0, 9.1],
        ], false);
        $this->assertInstanceOf(Matrix::class, $m);
        $this->assertInstanceOf(AbstractMatrix::class, $m);
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestDox("IsCacheEnabled() and setCacheEnabled() method work properly")]
    public function testCacheEnabled(): void
    {
        $m = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        $this->assertTrue($m->isCacheEnabled());
        $m->setCacheEnabled(false);
        $this->assertFalse($m->isCacheEnabled());
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     * @throws OutOfBoundsException
     */
    #[TestDox("ClearCache() method is called when the matrix is modified")]
    public function testClearCache(): void
    {
        $m = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        // First trace() call
        $this->assertEquals(15, $m->trace());

        // Second method call returns cached property
        $this->assertEquals(15, $m->trace());

        // Now modify the matrix to trigger protected clearCache() method
        $m->set(1, 1, 4);
        $this->assertEquals(14, $m->trace());
    }

    /**
     * @param MatrixArray $array
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerToArray')]
    #[TestDox("ToArray() method returns the matrix as an array")]
    public function testToArray(array $array): void
    {
        $m = new Matrix($array);
        $this->assertEquals($array, $m->toArray());
    }

    /**
     * @return array<int, array<int, MatrixArray>>
     */
    public static function providerToArray(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
            ],
            [
                [
                    [1.5],
                    [4],
                    [7.2],
                ],
            ],
            [
                [
                    [-1, 0, 3.1],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int $rows
     * @param int $columns
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerRowsAndColumns')]
    #[TestDox("rows() and columns() getters return correct values")]
    public function testRowsAndColumns(array $array, int $rows, int $columns): void
    {
        $m = new Matrix($array);
        $this->assertEquals($rows, $m->rows());
        $this->assertEquals($columns, $m->columns());
    }

    /**
     * @return array<int, array<int, int|MatrixArray>>
     */
    public static function providerRowsAndColumns(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], 3, 3,
            ],
            [
                [
                    [1, 2, 3, 10],
                    [4, 5, 6, 11],
                    [7, 8, 9, 12],
                ], 3, 4,
            ],
            [
                [
                    [1.5],
                    [4],
                    [7.2],
                ], 3, 1,
            ],
            [
                [
                    [-1, 0, 3.1, 6, 4, 2.22],
                ], 1, 6,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int $size
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerSizeAndCount')]
    #[TestDox("size() and count() methods return correct values for number elements")]
    public function testSizeAndCount(array $array, int $size): void
    {
        $m = new Matrix($array);
        $this->assertEquals($size, $m->size());
        $this->assertCount($size, $m);
    }

    /**
     * @return array<int, array<int, int|MatrixArray>>
     */
    public static function providerSizeAndCount(): array
    {
        return [
            [
                [
                    [1, 2, 3, 4],
                    [4, 5, 6, 7],
                    [7, 8, 9, 10],
                    [10, 11, 12, 13],
                ], 16,
            ],
            [
                [
                    [1, 2, 3, 10],
                    [4, 5, 6, 11],
                    [7, 8, 9, 12],
                ], 12,
            ],
            [
                [
                    [1.5],
                    [4],
                    [7.2],
                    [-5.1],
                ], 4,
            ],
            [
                [
                    [-1, 0, 0.1, 3.1, 6, 4, 2.22],
                ], 7,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int $row
     * @param int $column
     * @param float|int $valueToSet
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider('providerIsSetGetSet')]
    #[TestDox("The methods set(), get(), isSet() and corresponding ArrayAccess interface methods properly operate on matrix class")]
    public function testIsSetGetSet(array $array, int $row, int $column, float|int $valueToSet): void
    {
        $m = new Matrix($array);
        $this->assertEquals($array[$row][$column], $m->get($row, $column));
        $this->assertEquals($array[$row][$column], $m[[$row, $column]]);
        $m->set($row, $column, $valueToSet);
        $this->assertEquals($valueToSet, $m->get($row, $column));
        $this->assertEquals($valueToSet, $m[[$row, $column]]);
        $this->assertTrue($m->isSet($row, $column));
        $this->assertFalse($m->isSet($m->rows(), $m->columns()));
        $this->assertTrue(isset($m[[$row, $column]]));
        $this->assertFalse(isset($m[[$m->rows(), $m->columns()]]));
    }

    /**
     * @return array<int, array<int, float|int|MatrixArray>>
     */
    public static function providerIsSetGetSet(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], 2, 2, -9.3,
            ],
            [
                [
                    [1, 2, 3, 4, 5],
                ], 0, 3, 1002,
            ],
            [
                [
                    [1],
                    [4],
                    [7],
                    [301.2],
                ], 2, 0, -10203.452,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int $row
     * @param int $column
     * @param string $method
     * @param mixed $value
     * @param class-string<MathObjectsException> $exceptionClass
     * @param string $exceptionMessage
     * @return void
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    #[DataProvider('providerIsSetGetSetException')]
    #[TestDox("The methods set(), get(), isSet() and corresponding ArrayAccess interface methods throw exceptions upon wrong indices or wrong data types")]
    public function testIsSetGetSetException(array $array, int $row, int $column, string $method, mixed $value, string $exceptionClass, string $exceptionMessage = ""): void
    {
        $m = new Matrix($array);
        $this->expectException($exceptionClass);
        if (!empty($exceptionMessage)) {
            $this->expectExceptionMessage($exceptionMessage);
        }
        switch ($method) {
            case "get":
                $m->get($row, $column);
                break;
            case "offsetGet":
                /* @phpstan-ignore-next-line */
                $m[[$row, $column]];
                break;
            case "set":
                $m->set($row, $column, $value);
                break;
            case "offsetSet":
                $m[[$row, $column]] = $value;
                break;
            default:
                unset($m[[$row, $column]]);
        }
    }

    /**
     * @return array<int, array<int, string|float|int|array<float|int>|MatrixArray>>
     */
    public static function providerIsSetGetSetException(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], 3, 1, "get", 0, "PHPMathObjects\Exception\OutOfBoundsException", "The element [3][1] does not exist.",
            ],
            [
                [
                    [1],
                    [4],
                    [7],
                ], 1, 2, "set", -1.1, "PHPMathObjects\Exception\OutOfBoundsException", "The element [1][2] does not exist.",
            ],
            [
                [
                    [1],
                    [4],
                    [7],
                ], 1, 0, "set", "1", "PHPMathObjects\Exception\InvalidArgumentException", "The type 'string' is incompatible with the given Matrix instance.",
            ],
            [
                [
                    [1, -1],
                    [4, -8],
                    [7, 20],
                ], 5, 1, "offsetSet", 1.1, "PHPMathObjects\Exception\OutOfBoundsException", "The element [5][1] does not exist.",
            ],
            [
                [
                    [1, -1, -1, 5.2, 4.2],
                    [4, -8, 2.5, 2.11, 4],
                    [7, 20, 5, -2, 4],
                ], 1, 1, "offsetSet", [1.2], "PHPMathObjects\Exception\InvalidArgumentException", "The type 'array' is incompatible with the given Matrix instance.",
            ],
            [
                [
                    [1],
                ], 1, 0, "offsetGet", 0, "PHPMathObjects\Exception\OutOfBoundsException", "The element [1][0] does not exist.",
            ],
            [
                [
                    [1],
                ], 0, 0, "unset", 0, "PHPMathObjects\Exception\BadMethodCallException",
            ],
        ];
    }

    /**
     * @param mixed $index
     * @param string $method
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([1, "offsetGet"])]
    #[TestWith([1.1, "offsetSet"])]
    #[TestWith([-11, "offsetExists"])]
    #[TestWith([[1], "offsetGet"])]
    #[TestWith([[2], "offsetSet"])]
    #[TestWith([[5], "offsetExists"])]
    #[TestDox("ArrayAccess interface methods throw exceptions when the indices are passed in wrong format")]
    public function testArrayInterfaceSpecificException(mixed $index, string $method): void
    {
        $m = new Matrix([
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
        ]);

        $this->expectException(InvalidArgumentException::class);

        switch ($method) {
            case "offsetGet":
                /* @phpstan-ignore-next-line */
                $m[$index];
                break;
            case "offsetSet":
                $m[$index] = 1;
                break;
            default:
                /* @phpstan-ignore-next-line */
                isset($m[$index]);
        }
    }

    /**
     * @param MatrixArray $array1
     * @param MatrixArray $array2
     * @param MatrixArray $answer
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider('providerAdd')]
    #[TestDox("add() and mAdd() methods add two matrices correctly")]
    public function testAdd(array $array1, array $array2, array $answer): void
    {
        $m1 = new Matrix($array1);
        $m2 = new Matrix($array2);
        $this->assertEqualsWithDelta($answer, $m1->add($m2)->toArray(), self::e);
        $this->assertEqualsWithDelta($answer, $m1->mAdd($m2)->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, MatrixArray>>
     */
    public static function providerAdd(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [2, 4, 6],
                    [8, 10, 12],
                    [14, 16, 18],
                ],
            ],
            [
                [
                    [1.1, 2.2],
                    [4.4, 5.5],
                    [7.7, 8.8],
                ],
                [
                    [-1.1, -2.2],
                    [-4.4, -5.5],
                    [-7.7, -8.8],
                ],
                [
                    [0, 0],
                    [0, 0],
                    [0, 0],
                ],
            ],
            [
                [
                    [-100, 200.3, 18, 10],
                ],
                [
                    [38.1, -20, 30, -15],
                ],
                [
                    [-61.9, 180.3, 48, -5],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param MatrixArray $array2
     * @param MatrixArray $answer
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider('providerSubtract')]
    #[TestDox("subtract() and mSubtract() methods subtract one matrix from another correctly")]
    public function testSubtract(array $array1, array $array2, array $answer): void
    {
        $m1 = new Matrix($array1);
        $m2 = new Matrix($array2);
        $this->assertEqualsWithDelta($answer, $m1->subtract($m2)->toArray(), self::e);
        $this->assertEqualsWithDelta($answer, $m1->mSubtract($m2)->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, MatrixArray>>
     */
    public static function providerSubtract(): array
    {
        return [
            [
                [
                    [5, 102, 55],
                    [4, 52, 69],
                    [70, 83, 92],
                ],
                [
                    [5, 8, 1],
                    [2, 7, 1],
                    [9, 6, 1],
                ],
                [
                    [0, 94, 54],
                    [2, 45, 68],
                    [61, 77, 91],
                ],
            ],
            [
                [
                    [8.3, -2.2, 9.0],
                    [-1.5, 3.5, 5.6],
                    [1.9, 8.1, -0.8],
                ],
                [
                    [-2.3, -7.2, 0.1],
                    [-9.4, -0.5, 0.5],
                    [-2.7, -1.8, 1.9],
                ],
                [
                    [10.6, 5.0, 8.9],
                    [7.9, 4.0, 5.1],
                    [4.6, 9.9, -2.7],
                ],
            ],
            [
                [
                    [-300, 130.2, 88, 50],
                    [1, 1.2, 2.6, 7.1],
                ],
                [
                    [4.1, -84, 40.1, -20],
                    [3.1, -100.1, 30.6, -30],
                ],
                [
                    [-304.1, 214.2, 47.9, 70],
                    [-2.1, 101.3, -28.0, 37.1],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param MatrixArray $array2
     * @param MatrixArray $answer
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider('providerMultiply')]
    #[TestDox("multiply() and mMultiply() methods multiply one matrix by another correctly")]
    public function testMultiply(array $array1, array $array2, array $answer): void
    {
        $m1 = new Matrix($array1);
        $m2 = new Matrix($array2);
        $multiplied = $m1->multiply($m2);
        $m1->mMultiply($m2);

        $this->assertEqualsWithDelta($answer, $multiplied->toArray(), self::e);
        $this->assertEqualsWithDelta($answer, $m1->toArray(), self::e);

        // Check that the properties of the resulting matrix have proper values
        $this->assertEquals(count($array1), $multiplied->rows());
        $this->assertEquals(count($array1), $m1->rows());
        $this->assertEquals(count($array2[0]), $multiplied->columns());
        $this->assertEquals(count($array2[0]), $m1->columns());
        $this->assertEquals(count($array1) * count($array2[0]), $multiplied->size());
        $this->assertEquals(count($array1) * count($array2[0]), $m1->size());
    }

    /**
     * @return array<int, array<int, MatrixArray>>
     */
    public static function providerMultiply(): array
    {
        return [
            [
                [
                    [1, 6, 7, 15, 0],
                    [9, 3, 9, 2, 3],
                ],
                [
                    [4, 28, 9, 9],
                    [63, 2, 65, 2],
                    [48, 82, 4, 65],
                    [1, 24, 2, 3],
                    [2, 3, 4, 8],
                ],
                [

                    [733, 974, 457, 521],
                    [665, 1053, 328, 702],
                ],
            ],
            [
                [
                    [1.1],
                    [2.4],
                    [6.7],
                ],
                [
                    [-5.3, -2.9, -2.1],
                ],
                [
                    [-5.83, -3.19, -2.31],
                    [-12.72, -6.96, -5.04],
                    [-35.51, -19.43, -14.07],
                ],
            ],
            [
                [
                    [-5.3, -2.9, -2.1],
                ],
                [
                    [1.1],
                    [2.4],
                    [6.7],
                ],
                [
                    [-26.86],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param MatrixArray $array2
     * @param string $method
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerArithmeticException')]
    #[TestDox("Arithmetic methods throw exceptions if the matrices have incompatible dimensions")]
    public function testArithmeticException(array $array1, array $array2, string $method): void
    {
        $m1 = new Matrix($array1);
        $m2 = new Matrix($array2);
        $this->expectException(MatrixException::class);
        $m1->{$method}($m2);
    }

    /**
     * @return array<int, array<int, string|MatrixArray>>
     */
    public static function providerArithmeticException(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                ],
                [
                    [7, 8, 9],
                ], "add",
            ],
            [
                [
                    [1, 2],
                    [4, 5],
                ],
                [
                    [7, 8, 9, 10],
                ], "mAdd",
            ],
            [
                [
                    [1],
                    [4],
                    [10],
                ],
                [
                    [7],
                    [8],
                ], "subtract",
            ],
            [
                [
                    [1, 2, 3, 4, 5],
                ],
                [
                    [1, 2, 3, 4, 5, 6],
                ], "mSubtract",
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3, 4],
                    [4, 5, 6, 7],
                    [7, 8, 9, 10],
                    [11, 12, 13, 14],
                ], "multiply",
            ],
            [
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ],
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ], "mMultiply",
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int|float $multiplier
     * @param MatrixArray $answer
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerMultiplyByScalar')]
    #[TestDox("mSubtract() method subtracts one matrix from another correctly")]
    public function testMultiplyByScalar(array $array, int|float $multiplier, array $answer): void
    {
        $m = new Matrix($array);
        $this->assertEqualsWithDelta($answer, $m->multiplyByScalar($multiplier)->toArray(), self::e);
        $this->assertEqualsWithDelta($answer, $m->mMultiplyByScalar($multiplier)->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, int|float|MatrixArray>>
     */
    public static function providerMultiplyByScalar(): array
    {
        return [
            [
                [
                    [52, 1000, 1.1],
                    [4.4, -12, 1.2],
                    [3, 4, 6.6],
                ],
                0,
                [
                    [0, 0, 0],
                    [0, 0, 0],
                    [0, 0, 0],
                ],
            ],
            [
                [
                    [11, -2, 11.0],
                    [18, 3, 593.1],
                    [-29.334, 2.1, -0.821],
                ],
                1,
                [
                    [11, -2, 11.0],
                    [18, 3, 593.1],
                    [-29.334, 2.1, -0.821],
                ],
            ],
            [
                [
                    [-11, -62.3, 7, 49.1],
                    [0, 1.8, 7.9, -39],
                ],
                2,
                [
                    [-22, -124.6, 14, 98.2],
                    [0, 3.6, 15.8, -78],
                ],
            ],
            [
                [
                    [1.1, 2.23],
                    [-3.345, -4.4567],
                ],
                -0.345,
                [
                    [-0.3795, -0.76935],
                    [1.154025, 1.5375615],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param MatrixArray $answer
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerChangeSign')]
    #[TestDox("changeSign() and mChangeSign() methods change signs of all elements")]
    public function testChangeSign(array $array, array $answer): void
    {
        $m = new Matrix($array);
        $this->assertEqualsWithDelta($answer, $m->changeSign()->toArray(), self::e);
        $this->assertEqualsWithDelta($answer, $m->mChangeSign()->toArray(), self::e);
    }

    /**
     * @return array<int, array<int, MatrixArray>>
     */
    public static function providerChangeSign(): array
    {
        return [
            [
                [[-32.5331]],
                [[32.5331]],
            ],
            [
                [
                    [1, -2, 3],
                    [-4, 5, -6],
                    [7, -8, 9],
                ],
                [
                    [-1, 2, -3],
                    [4, -5, 6],
                    [-7, 8, -9],
                ],
            ],
            [
                [
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                ],
                [
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                ],
            ],
            [
                [
                    [-12.42, 4],
                    [0, -1.53],
                ],
                [
                    [12.42, -4],
                    [-0, 1.53],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param MatrixArray $answer
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerTranspose')]
    #[TestDox("Matrix transpose() and mTranspose() return correct results")]
    public function testTranspose(array $array, array $answer): void
    {
        $m = new Matrix($array);
        $transposed = $m->transpose();
        $m->mTranspose();

        $this->assertEquals($answer, $m->toArray());
        $this->assertEquals($answer, $transposed->toArray());

        // Check that the rows and columns were updated properly
        $this->assertEquals(count($answer), $m->rows());
        $this->assertEquals(count($answer), $transposed->rows());
        $this->assertEquals(count($answer[0]), $m->columns());
        $this->assertEquals(count($answer[0]), $transposed->columns());
    }

    /**
     * @return array<int, array<int, MatrixArray>>
     */
    public static function providerTranspose(): array
    {
        return [
            [
                [[0]],
                [[0]],
            ],
            [
                [
                    [-1.1, 1.2, -1.3, -1.4],
                ],
                [
                    [-1.1],
                    [1.2],
                    [-1.3],
                    [-1.4],
                ],
            ],
            [
                [
                    [100],
                    [200],
                    [300],
                    [400],
                    [500],
                ],
                [
                    [100, 200, 300, 400, 500],
                ],
            ],
            [
                [
                    [100, -200, -300, -400, -500],
                    [200, -300, -400, -400, 500],
                    [300, -300, -400, -400, 500],
                    [400, -500, 100, 200, 700],
                ],
                [
                    [100, 200, 300, 400],
                    [-200, -300, -300, -500],
                    [-300, -400, -400, 100],
                    [-400, -400, -400, 200],
                    [-500, 500, 500, 700],
                ],
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param bool $expected
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerIsSquare')]
    #[TestDox("IsSquare() method returns true if the matrix is square, and false otherwise")]
    public function testIsSquare(array $array, bool $expected): void
    {
        $m = new Matrix($array);
        $this->assertEquals($expected, $m->isSquare());
    }

    /**
     * @return array<int, array<int, bool|MatrixArray>>
     */
    public static function providerIsSquare(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], true,
            ],
            [
                [
                    [6.6],
                ], true,
            ],
            [
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ], false,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                ], false,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param string $answer
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerToString')]
    #[TestDox("Methods __toString() and toString() convert the matrix to a string")]
    public function testToString(array $array, string $answer): void
    {
        $m = new Matrix($array);
        $this->assertEquals($answer, (string) $m);
        $this->assertEquals($answer, $m->toString());
    }

    /**
     * @return array<int, array<int, string|MatrixArray>>
     */
    public static function providerToString(): array
    {
        return [
            [
                [[-100.1]],
                "[-100.1]",
            ],
            [
                [
                    [1.1],
                    [-20.233],
                    [33],
                    [1],
                    [0.1],
                ],
                "[1.1]" . PHP_EOL .
                "[-20.233]" . PHP_EOL .
                "[33]" . PHP_EOL .
                "[1]" . PHP_EOL .
                "[0.1]",
            ],
            [
                [[1, 2, 3, 4, 5]],
                "[1, 2, 3, 4, 5]",
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                "[1, 2, 3]" . PHP_EOL .
                "[4, 5, 6]" . PHP_EOL .
                "[7, 8, 9]",
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param MatrixArray $array2
     * @param bool $expected
     * @param float|null $tolerance
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerIsEqual')]
    #[TestDox("isEqual() method compares two matrices within a given tolerance")]
    public function testIsEqual(array $array1, array $array2, bool $expected, ?float $tolerance): void
    {
        $m1 = new Matrix($array1);
        $m2 = new Matrix($array2);
        if (isset($tolerance)) {
            $this->assertEquals($expected, $m1->isEqual($m2, $tolerance));
        } else {
            $this->assertEquals($expected, $m1->isEqual($m2));
        }
    }

    /**
     * @return array<int, array<int, bool|null|float|MatrixArray>>
     */
    public static function providerIsEqual(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], true, null,
            ],
            [
                [
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                ],
                [
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 0],
                    [0, 0, 0, 0, 1],
                ], false, null,
            ],
            [
                [
                    [1.11112, -1.11113, 1.11114, -1.11115, 1.11116],
                    [1.11117, -1.11118, 1.11119, -1.11120, 1.11121],
                    [1.11122, -1.11123, 1.11124, -1.11125, 1.11126],
                    [1.11127, -1.11128, 1.11129, -1.11130, 1.11131],
                ],
                [
                    [1.11112, -1.11113, 1.11114, -1.11115, 1.11116],
                    [1.11117, -1.11118, 1.11119, -1.11120, 1.11121],
                    [1.11122, -1.11123, 1.11124, -1.11125, 1.11126],
                    [1.11127, -1.11128, 1.11129, -1.11130, 1.11132],
                ], false, null,
            ],
            [
                [
                    [1.11112, -1.11113, 1.11114, -1.11115, 1.11116],
                    [1.11117, -1.11118, 1.11119, -1.11120, 1.11121],
                    [1.11122, -1.11123, 1.11124, -1.11125, 1.11126],
                    [1.11127, -1.11128, 1.11129, -1.11130, 1.11131],
                ],
                [
                    [1.11112, -1.11113, 1.11114, -1.11115, 1.11116],
                    [1.11117, -1.11118, 1.11119, -1.11120, 1.11121],
                    [1.11122, -1.11123, 1.11124, -1.11125, 1.11126],
                    [1.11127, -1.11128, 1.11129, -1.11130, 1.11132],
                ], true, 0.001,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3],
                    [4, 5, 6],
                ], false, null,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ], false, null,
            ],
        ];
    }

    /**
     * @param MatrixArray $array1
     * @param MatrixArray $array2
     * @param bool $expected
     * @return void
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerIsEqualExactly')]
    #[TestDox("isEqualExactly() method compares two matrices for exact equality")]
    public function testIsEqualExactly(array $array1, array $array2, bool $expected): void
    {
        $m1 = new Matrix($array1);
        $m2 = new Matrix($array2);
        $this->assertEquals($expected, $m1->isEqualExactly($m2));
    }

    /**
     * @return array<int, array<int, bool|MatrixArray>>
     */
    public static function providerIsEqualExactly(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], true,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3],
                    [4, 5, 6],
                ], false,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ], false,
            ],
            [
                [
                    [1.23456, 2.34567, 3.45678, 4.56789, 5.67891],
                    [6.78912, 7.89123, 8.91234, 9.12345, 0.12345],
                    [-1.23456, -2.34578, -3.45678, -4.56789, -5.67891],
                    [-6.78912, -7.89123, -8.91234, -9.12345, -0.12345],
                ],
                [
                    [1.23456, 2.34567, 3.45678, 4.56789, 5.67891],
                    [6.78912, 7.89123, 8.91234, 9.12345, 0.12345],
                    [-1.23456, -2.34578, -3.45678, -4.56789, -5.67891],
                    [-6.78912, -7.89123, -8.91234, -9.12345, -0.12345],
                ], true,
            ],
            [
                [
                    [1.23456, 2.34567, 3.45678, 4.56789, 5.67891],
                    [6.78912, 7.89123, 8.91234, 9.12345, 0.12345],
                    [-1.23456, -2.34578, -3.45678, -4.56789, -5.67891],
                    [-6.78912, -7.89123, -8.91234, -9.12345, -0.12345],
                ],
                [
                    [1.23456, 2.34567, 3.45678, 4.56789, 5.67891],
                    [6.78912, 7.89123, 8.91234, 9.12345, 0.12345],
                    [-1.23456, -2.34578, -3.45678, -4.56789, -5.67891],
                    [-6.78912, -7.89123, -8.91234, -9.12345, -0.123456],
                ], false,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int|float $expected
     * @param bool $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider('providerTrace')]
    #[TestDox("trace() method calculates the trace of the matrix")]
    public function testTrace(array $array, int|float $expected, bool $exception = false): void
    {
        if ($exception) {
            $this->expectException(MatrixException::class);
        }
        $m = new Matrix($array);

        // Do assert twice to check that the cached value (second call) is correct
        $this->assertEqualsWithDelta($expected, $m->trace(), self::e);
        $this->assertEqualsWithDelta($expected, $m->trace(), self::e);
    }

    /**
     * @return array<int, array<int, bool|int|float|MatrixArray>>
     */
    public static function providerTrace(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ],
                15, false,
            ],
            [
                [
                    [0.1, -0.2, 30],
                    [4.1, 50, 3.2],
                    [1.023, 22, -0.654],
                ],
                49.446, false,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                ],
                1, true,
            ],
            [
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ],
                1, true,
            ],
            [
                [
                    [0],
                ],
                0, false,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param MatrixArray $expected
     * @param bool $doSwaps
     * @param int $swapsExpected
     * @param float $zeroTolerance
     * @return void
     * @throws DivisionByZeroException
     * @throws InvalidArgumentException
     */
    #[DataProvider('providerRef')]
    #[TestDox("ref() and mRef() methods return a row echelon form of the matrix")]
    public function testRef(array $array, array $expected, bool $doSwaps, int $swapsExpected, float $zeroTolerance = self::e): void
    {
        // Test mRef()
        $m = new Matrix($array);
        $swaps = 0;
        $this->assertEqualsWithDelta($expected, $m->mRef($doSwaps, $swaps, $zeroTolerance)->toArray(), self::e);
        $this->assertEquals($swapsExpected, $swaps);

        // Test ref()
        $m = new Matrix($array);
        $swaps = 0;

        // Do assert twice to check that the cached value (second call) is correct
        $this->assertEqualsWithDelta($expected, $m->ref($doSwaps, $swaps, $zeroTolerance)->toArray(), self::e);
        $this->assertEqualsWithDelta($expected, $m->ref($doSwaps, $swaps, $zeroTolerance)->toArray(), self::e);
        $this->assertEquals($swapsExpected, $swaps);
    }

    /**
     * @return array<int, array<int, bool|int|float|MatrixArray>>
     */
    public static function providerRef(): array
    {
        return [
            [
                [
                    [5],
                ],
                [
                    [5],
                ], true, 0,
            ],
            [
                [
                    [5, 1, 4],
                    [6, 1, 8],
                ],
                [
                    [5, 1, 4],
                    [0, -0.2, 3.2],
                ], false, 0,
            ],
            [
                [
                    [5, 1, 4],
                    [6, 1, 8],
                ],
                [
                    [6, 1, 8],
                    [0, 1 / 6, 4 - 8 * 5 / 6],
                ], true, 1,
            ],
            [
                [
                    [1.5, 2.8],
                    [7.7, 5.3],
                    [9.8, 7.5],
                ],
                [
                    [9.8, 7.5],
                    [0, 2.8 - 7.5 * 1.5 / 9.8],
                    [0, 0],
                ], true, 2,
            ],
            [
                [
                    [1.5, 2.8],
                    [7.7, 5.3],
                    [9.8, 7.5],
                ],
                [
                    [1.5, 2.8],
                    [0, 5.3 - 2.8 * 7.7 / 1.5],
                    [0, 0],
                ], false, 0,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 4, 5],
                    [7, 8, 9],
                ],
                [
                    [1, 2, 3],
                    [0, -4, -7],
                    [0, 0, -1.5],
                ], false, 0,
            ],
            [
                [
                    [8, 9, 11],
                    [8, 9, 11],
                    [1, 2, 3],
                ],
                [
                    [8, 9, 11],
                    [0, 0.875, 1.625],
                    [0, 0, 0],
                ], true, 1,
            ],
            [
                [
                    [8, 9, 11],
                    [8, 9, 11],
                    [1, 2, 3],
                ],
                [
                    [8, 9, 11],
                    [0, 0, 1.625],
                    [0, 0, 0],
                ], true, 1, 1,
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param class-string $method
     * @return void
     * @throws InvalidArgumentException
     * @throws DivisionByZeroException
     */
    #[DataProvider('providerRefException')]
    #[TestDox("ref() and mRef() methods throw an DivisionByZero exception if no swaps are allowed but the matrix is linearly dependent")]
    public function testRefException(array $array, string $method): void
    {
        $this->expectException(DivisionByZeroException::class);
        $m = new Matrix($array);
        $m->{$method}(false);
    }

    /**
     * @return array<int, array<int, string|MatrixArray>>
     */
    public static function providerRefException(): array
    {
        return [
            [
                [
                    [8, 9, 11],
                    [8, 9, 11],
                    [1, 2, 3],
                ], "mRef",
            ],
            [
                [
                    [-9.9, -6.66, -1.1],
                    [-9.9, -6.66, -1.1],
                    [5.5, 6.6, 7.7],
                ], "ref",
            ],
        ];
    }

    /**
     * @param MatrixArray $array
     * @param int|float $expected
     * @param bool $exception
     * @return void
     * @throws InvalidArgumentException
     * @throws MatrixException
     */
    #[DataProvider('providerDeterminant')]
    #[TestDox("determinant() method returns either the expected value or throws an exception")]
    public function testDeterminant(array $array, int|float $expected, bool $exception = false): void
    {
        if ($exception) {
            $this->expectException(MatrixException::class);
        }

        $m = new Matrix($array);
        $this->assertEqualsWithDelta($expected, $m->determinant(), self::e);
        // Do it twice to check the cached value too
        $this->assertEqualsWithDelta($expected, $m->determinant(), self::e);
    }

    /**
     * @return array<int, array<int, bool|int|float|MatrixArray>>
     */
    public static function providerDeterminant(): array
    {
        return [
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                ], 0, true,
            ],
            [
                [
                    [1, 2],
                    [4, 5],
                    [7, 8],
                ], 0, true,
            ],
            [
                [
                    [-1.6],
                ], -1.6,
            ],
            [
                [
                    [11.5, -20.6],
                    [-7.693, 8],
                ], -66.4758,
            ],
            [
                [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9],
                ], 0,
            ],
            [
                [
                    [1, 1, 1, 1],
                    [1, 1, 1, 1],
                    [1, 1, 1,1],
                    [1, 1, 1,9],
                ], 0,
            ],
            [
                [
                    [5.7, 9.8, 2.5, 9.1],
                    [-1102.44, -1942.556, 292.2184, -283.22],
                    [1.53, 2.28, 3.48, 4.285],
                    [-0.1, -0.2, -0.3, -0.4],
                ], 872.9118664,
            ],
        ];
    }
}
