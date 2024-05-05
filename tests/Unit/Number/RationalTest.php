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

namespace Number;

use PHPMathObjects\Exception\InvalidArgumentException;
use PHPMathObjects\Number\Rational;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * Test case for the Rational class
 */
class RationalTest extends TestCase
{
    /**
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestDox("Rational class constructor creates an instance its class")]
    public function testConstruct(): void
    {
        $r = new Rational(0, 0, 1);
        $this->assertInstanceOf(Rational::class, $r);
    }

    /**
     * @param int $whole
     * @param int $numerator
     * @param int $denominator
     * @param bool $normalize
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([32, 3, 0, true])]
    #[TestWith([0, -12, 0, false])]
    #[TestDox("Rational class constructor throws exception if denominator equals zero")]
    public function testConstructException(int $whole, int $numerator, int $denominator, bool $normalize): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Rational($whole, $numerator, $denominator, $normalize);
    }

    /**
     * @param int $whole
     * @param int $numerator
     * @param int $denominator
     * @param bool $normalize
     * @param int $expectedWhole
     * @param int $expectedNumerator
     * @param int $expectedDenominator
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith([0, 0, 1, true, 0, 0, 1])]
    #[TestWith([0, 0, 2, true, 0, 0, 1])]
    #[TestWith([1, 1, 1, true, 1, 1, 1])]
    #[TestWith([1, 1, 2, true, 1, 1, 2])]
    #[TestWith([1, 2, 4, true, 1, 1, 2])]
    #[TestWith([1, 4, 2, true, 3, 0, 1])]
    #[TestWith([1, 4, 2, true, 3, 0, 1])]
    #[TestWith([-1, -1, 2, true, -1, -1, 2])]
    #[TestWith([-1, 1, 2, true, 0, -1, 2])]
    #[TestWith([1, 1, -2, true, 0, 1, 2])]
    #[TestWith([5, -3, -4, true, 5, 3, 4])]
    #[TestWith([10, -36, 4, true, 1, 0, 1])]
    #[TestWith([1, 8, 6, true, 2, 1, 3])]
    #[TestWith([-1, 1, -2, true, -1, -1, 2])]
    #[TestWith([0, 0, 1, false, 0, 0, 1])]
    #[TestWith([0, 0, -5, false, 0, 0, -5])]
    #[TestWith([-6, 8, 2, false, -6, 8, 2])]
    #[TestWith([15, 9, -63, false, 15, 9, -63])]
    #[TestDox("Rational class getters return the expected whole part, numerator and denominator values")]
    public function testGetters(int $whole, int $numerator, int $denominator, bool $normalize, int $expectedWhole, int $expectedNumerator, int $expectedDenominator): void
    {
        $r = new Rational($whole, $numerator, $denominator, $normalize);
        $this->assertEquals($expectedWhole, $r->whole());
        $this->assertEquals($expectedNumerator, $r->numerator());
        $this->assertEquals($expectedDenominator, $r->denominator());
        $this->assertEquals($normalize, $r->isNormalized());
    }

    /**
     * @param string $string
     * @param bool $normalize
     * @param int $whole
     * @param int $numerator
     * @param int $denominator
     * @param bool $exception
     * @return void
     * @throws InvalidArgumentException
     */
    #[TestWith(["13 3/8", true, 13, 3, 8])]
    #[TestWith(["2 16/8", true, 4, 0, 1])]
    #[TestWith(["2 16/8", false, 2, 16, 8])]
    #[TestWith(["-1 1/5", true, -1, -1, 5])]
    #[TestWith(["8/17", true, 0, 8, 17])]
    #[TestWith(["-6/5", true, -1, -1, 5])]
    #[TestWith(["-17", true, -17, 0, 1])]
    #[TestWith(["5", true, 5, 0, 1])]
    #[TestWith(["0", true, 0, 0, 1])]
    #[TestWith(["   -76    2/5    ", true, -76, -2, 5])]
    #[TestWith(["-10 18/16", false, -10, -18, 16])]
    #[TestWith(["-10 -18/16", false, -10, -18, 16, true])]
    #[TestWith(["-10s", false, -10, -18, 16, true])]
    #[TestWith(["-10 18/-16", false, -10, -18, 16, true])]
    #[TestWith(["--10 -18/16", false, -10, -18, 16, true])]
    #[TestWith(["-10 -18", false, -10, -18, 16, true])]
    #[TestDox("FromString() factory creates a correct rational number from a string")]
    public function testFromString(string $string, bool $normalize, int $whole, int $numerator, int $denominator, bool $exception = false): void
    {
        if ($exception) {
            $this->expectException(InvalidArgumentException::class);
        }

        $r = Rational::fromString($string, $normalize);
        $this->assertEquals($whole, $r->whole());
        $this->assertEquals($numerator, $r->numerator());
        $this->assertEquals($denominator, $r->denominator());
    }
}
