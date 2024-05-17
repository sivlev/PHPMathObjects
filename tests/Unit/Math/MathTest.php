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

namespace Math;

use PHPMathObjects\Math\Math;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the Math class
 */
class MathTest extends TestCase
{
    protected const e = 1e-8;

    /**
     * @param int|float $number
     * @param bool $result
     * @param float $tolerance
     * @return void
     */
    #[TestWith([5, false])]
    #[TestWith([0, true])]
    #[TestWith([-10.53, false])]
    #[TestWith([0.00006, false])]
    #[TestWith([0.00006, true, 1e-3])]
    #[TestDox("Math::isZero() method returns true if the number is zero within the tolerance")]
    public function testIsZero(int|float $number, bool $result, float $tolerance = self::e): void
    {
        $this->assertEquals($result, Math::isZero($number, $tolerance));
    }

    /**
     * @param int|float $number
     * @param bool $result
     * @param float $tolerance
     * @return void
     */
    #[TestWith([0.49, true])]
    #[TestWith([0, false])]
    #[TestWith([-0.239, true])]
    #[TestWith([-0.0000013, true])]
    #[TestWith([-0.0000013, false, 1e-5])]
    #[TestDox("Math::isNotZero() method returns true if the number is not zero within the tolerance")]
    public function testIsNotZero(int|float $number, bool $result, float $tolerance = self::e): void
    {
        $this->assertEquals($result, Math::isNotZero($number, $tolerance));
    }

    /**
     * @param int|float $number1
     * @param int|float $number2
     * @param bool $result
     * @param float $tolerance
     * @return void
     */
    #[TestWith([15, 14, false])]
    #[TestWith([-12.459, -12.459, true])]
    #[TestWith([-0.239, -0.238, false])]
    #[TestWith([-0.0000064, -0.0000063, false])]
    #[TestWith([-0.0000064, -0.0000063, true, 1e-7])]
    #[TestDox("Math::isNotZero() method returns true if the number is not zero within the tolerance")]
    public function testAreEqual(int|float $number1, int|float $number2, bool $result, float $tolerance = self::e): void
    {
        $this->assertEquals($result, Math::areEqual($number1, $number2, $tolerance));
    }

    /**
     * @param array<int, int|float> $array1
     * @param array<int, int|float> $array2
     * @param bool $result
     * @param float $tolerance
     * @return void
     */
    #[TestWith([[1, 2, 3], [1, 2, 3], true])]
    #[TestWith([[1, 2, 3], [1, 2, 3, 4], false])]
    #[TestWith([[1, 2, 3], [1, 2, 4], false])]
    #[TestWith([[1, 2, 3], [1, 2, 3.0000001], false])]
    #[TestWith([[1, 2, 3], [1, 2, 3.0000001], true, 1e-6])]
    #[TestDox("Math::areArraysEqual() method returns true if the elements of the two arrays are equal within the tolerance")]
    public function testAreArraysEqual(array $array1, array $array2, bool $result, float $tolerance = self::e): void
    {
        $this->assertEquals($result, Math::areArraysEqual($array1, $array2, $tolerance));
    }

    /**
     * @param int|float $number
     * @param int $expected
     * @return void
     */
    #[TestWith([-0.53123, -1])]
    #[TestWith([0, 0])]
    #[TestWith([-0.0, 0])]
    #[TestWith([31.23432, 1])]
    #[TestDox("Math::sign function returns the sign of the given number")]
    public function testSign(int|float $number, int $expected): void
    {
        $this->assertEquals($expected, Math::sign($number));
    }

    /**
     * @param int $number1
     * @param int $number2
     * @param int $expected
     * @return void
     */
    #[TestWith([1, 0, 1])]
    #[TestWith([0, 1, 1])]
    #[TestWith([0, 0, 0])]
    #[TestWith([28, 35, 7])]
    #[TestWith([12, 25, 1])]
    #[TestWith([615, 861, 123])]
    #[TestDox("Gcd() method calculates the correct greatest common divisor for two numbers")]
    public function testGcd(int $number1, int $number2, int $expected): void
    {
        $this->assertEquals($expected, Math::gcd($number1, $number2));
    }
}
