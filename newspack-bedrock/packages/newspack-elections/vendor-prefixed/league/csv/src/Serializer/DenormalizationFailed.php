<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace Govpack\Vendor\League\Csv\Serializer;

use ReflectionProperty;
use RuntimeException;

final class DenormalizationFailed extends RuntimeException implements SerializationFailed
{
    public static function dueToUninitializedProperty(ReflectionProperty $reflectionProperty): self
    {
        return new self('The property '.$reflectionProperty->getDeclaringClass()->getName().'::'.$reflectionProperty->getName().' is not initialized; its value is missing from the source data.');
    }
}
