<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Govpack\Vendor\z4kn4fein\SemVer\Constraints;

/**
 * @internal
 */
class Op
{
    const EQUAL = '=';
    const NOT_EQUAL = '!=';
    const LESS_THAN = '<';
    const LESS_THAN_OR_EQUAL = '<=';
    const LESS_THAN_OR_EQUAL2 = '=<';
    const GREATER_THAN = '>';
    const GREATER_THAN_OR_EQUAL = '>=';
    const GREATER_THAN_OR_EQUAL2 = '=>';
}
