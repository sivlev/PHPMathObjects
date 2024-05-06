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

namespace PHPMathObjects\Math;

/**
 * Class implementing some common math functions as static methods
 */
class Math
{
    /**
     * This constant is used as the default tolerance: If a float point number is below the tolerance, then it is considered being equal to zero.
     */
    protected const DEFAULT_TOLERANCE = 1e-8;

    /**
     * Returns true if the number equals zero within the given tolerance
     *
     * @param int|float $number
     * @param float $tolerance
     * @return bool
     */
    public static function isZero(int|float $number, float $tolerance = self::DEFAULT_TOLERANCE): bool
    {
        return abs($number) <= $tolerance;
    }

    /**
     * Returns true if the number does not equal zero within the given tolerance
     *
     * @param int|float $number
     * @param float $tolerance
     * @return bool
     */
    public static function isNotZero(int|float $number, float $tolerance = self::DEFAULT_TOLERANCE): bool
    {
        return abs($number) > $tolerance;
    }

    /**
     * Returns true if the two numbers are equal with the given tolerance
     *
     * @param int|float $number1
     * @param int|float $number2
     * @param float $tolerance
     * @return bool
     */
    public static function areEqual(int|float $number1, int|float $number2, float $tolerance = self::DEFAULT_TOLERANCE): bool
    {
        return abs($number1 - $number2) <= $tolerance;
    }

    /**
     * Returns sign of the number (-1 if negative, 1 if positive and 0 if zero)
     *
     * @param int|float $number
     * @return int
     */
    public static function sign(int|float $number): int
    {
        return $number <=> 0;
    }

    /**
     * Calculates the greatest common divisor (GCD) of two numbers
     *
     * @param int $number1
     * @param int $number2
     * @return int
     */
    public static function gcd(int $number1, int $number2): int
    {
        if ($number1 === 0) {
            return $number2;
        }

        if ($number2 === 0) {
            return $number1;
        }

        return self::gcd($number2 % $number1, $number1);
    }
}
