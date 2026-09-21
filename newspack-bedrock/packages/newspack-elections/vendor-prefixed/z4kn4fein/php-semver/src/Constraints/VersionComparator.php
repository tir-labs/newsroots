<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Govpack\Vendor\z4kn4fein\SemVer\Constraints;

use Govpack\Vendor\z4kn4fein\SemVer\Version;

/**
 * @internal
 */
interface VersionComparator
{
    public function __toString(): string;

    public function isSatisfiedBy(Version $version): bool;

    public function opposite(): string;
}
