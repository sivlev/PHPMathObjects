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
    #[TestDox("Rational class getters return the expected whole part, numerator and denominator values")]
    public function testGetters(int $whole, int $numerator, int $denominator, bool $normalize, int $expectedWhole, int $expectedNumerator, int $expectedDenominator): void
    {
        $r = new Rational($whole, $numerator, $denominator, $normalize);
        $this->assertEquals($expectedWhole, $r->whole());
        $this->assertEquals($expectedNumerator, $r->numerator());
        $this->assertEquals($expectedDenominator, $r->denominator());
    }
}
