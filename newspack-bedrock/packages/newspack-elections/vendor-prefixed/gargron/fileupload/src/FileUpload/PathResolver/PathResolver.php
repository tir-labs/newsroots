<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Govpack\Vendor\FileUpload\PathResolver;

interface PathResolver
{
    /**
     * Get absolute final destination path
     * @param  string $name
     * @return string
     */
    public function getUploadPath($name = null);

    /**
     * Ensure consistent name
     * @param  string $name
     * @return string
     */
    public function upcountName($name);
}
