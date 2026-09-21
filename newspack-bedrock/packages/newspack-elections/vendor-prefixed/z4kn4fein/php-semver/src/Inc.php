<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Govpack\Vendor\z4kn4fein\SemVer;

/**
 * Determines by which identifier the given Version should be incremented.
 */
class Inc
{
    /**
     * Indicates that the Version should be incremented by its MAJOR number.
     */
    const MAJOR = 0;

    /**
     * Indicates that the Version should be incremented by its MINOR number.
     */
    const MINOR = 1;

    /**
     * Indicates that the Version should be incremented by its PATCH number.
     */
    const PATCH = 2;

    /**
     * Indicates that the Version should be incremented by its PRE-RELEASE identifier.
     */
    const PRE_RELEASE = 3;
}
