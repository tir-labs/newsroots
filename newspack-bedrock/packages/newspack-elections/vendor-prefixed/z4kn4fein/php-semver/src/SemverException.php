<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Govpack\Vendor\z4kn4fein\SemVer;

use Exception;

/**
 * Version and Constraint parsing throws this exception when the parsing fails due to an invalid format.
 */
class SemverException extends Exception {}
