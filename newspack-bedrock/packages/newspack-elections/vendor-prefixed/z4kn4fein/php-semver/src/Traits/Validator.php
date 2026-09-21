<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Govpack\Vendor\z4kn4fein\SemVer\Traits;

use Govpack\Vendor\z4kn4fein\SemVer\SemverException;

/**
 * @internal
 */
trait Validator
{
    /**
     * @param bool   $condition the condition to evaluate
     * @param string $message   the exception message when the condition evaluates to false
     *
     * @throws SemverException when the condition evaluates to false
     */
    private static function ensure(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new SemverException($message);
        }
    }
}
