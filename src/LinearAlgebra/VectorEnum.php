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

namespace PHPMathObjects\LinearAlgebra;

/**
 * Enum for the vector orientation.
 */
enum VectorEnum
{
    case Column;
    case Row;

    public function transpose(): self
    {
        return match ($this) {
            self::Column => self::Row,
            self::Row => self::Column,
        };
    }
}
