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

namespace PHPMathObjects\Number;

use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Math\Math;

/**
 * Class to handle rational numbers
 */
readonly class Rational
{
    /**
     * The whole part of the rational number
     *
     * @var int
     */
    protected int $whole;

    /**
     * The numerator of the rational number
     *
     * @var int
     */
    protected int $numerator;

    /**
     * The numerator of the rational number
     *
     * @var int
     */
    protected int $denominator;

    /**
     * Class constructor
     *
     * @param int $whole
     * @param int $numerator
     * @param int $denominator
     * @throws InvalidArgumentException if the denominator equals zero
     */
    public function __construct(int $whole, int $numerator, int $denominator)
    {
        // Denominator cannot be equal to zero
        if ($denominator === 0) {
            throw new InvalidArgumentException("Denominator cannot be equal to zero");
        }

        // Make the denominator positive by default
        if ($denominator < 0) {
            $denominator = -$denominator;
            $numerator = -$numerator;
        }

        // Get the whole part
        if (abs($numerator) > $denominator) {
            $whole += intdiv($numerator, $denominator);
            $numerator = $numerator % $denominator;
        }

        // Make the signs of whole and numerator equal
        $wholeSign = Math::sign($whole);
        if ($wholeSign !== Math::sign($numerator) && $whole !== 0 && $numerator !== 0) {
            $whole -= $wholeSign;
            $numerator = ($denominator - abs($numerator)) * $wholeSign;
        }

        // Reduce the fraction if the numerator is greater than the denominator
        $gcd = 0;
        while ($gcd !== 1 && $numerator !== 0) {
            $gcd = abs(Math::gcd($numerator, $denominator));
            $numerator = intdiv($numerator, $gcd);
            $denominator = intdiv($denominator, $gcd);
        }

        // If the numerator is zero, then the denominator value play no role, i.e. the rational is integer
        if ($numerator === 0) {
            $denominator = 1;
        }

        $this->whole = $whole;
        $this->numerator = $numerator;
        $this->denominator = $denominator;
    }

    /**
     * @param string $string
     * @return self
     * @throws InvalidArgumentException if the string is not a valid rational number or if the denominator is zero
     */
    public static function fromString(string $string): self
    {
        $result = preg_match("/^(-?\d+(?=$|\s))*\s*(?:((?(?<=^)-)?\d+)(?=\/)\/?(\d+))*$/", trim($string), $matches, PREG_UNMATCHED_AS_NULL);
        if (!$result) {
            throw new InvalidArgumentException("Cannot parse the given string into a rational number");
        }

        $whole = (int) ($matches[1] ?? 0);
        $numerator = (int) ($matches[2] ?? 0);
        $denominator = (int) ($matches[3] ?? 1);

        // Make the numerator negative if the whole is negative
        if ($whole < 0) {
            $numerator = -$numerator;
        }

        return new self($whole, $numerator, $denominator);
    }

    /**
     * Returns the whole part of the rational
     *
     * @return int
     */
    public function whole(): int
    {
        return $this->whole;
    }

    /**
     * Returns the numerator of the rational
     *
     * @return int
     */
    public function numerator(): int
    {
        return $this->numerator;
    }

    /**
     * Returns the denominator of the rational
     *
     * @return int
     */
    public function denominator(): int
    {
        return $this->denominator;
    }
}
