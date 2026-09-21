<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Govpack\Vendor\FileUpload\Validator;

use Govpack\Vendor\FileUpload\File;

interface Validator
{
    /**
     * Overwrite the default error messages
     * @param array $messages
     * @return void
     */
    public function setErrorMessages(array $messages);

    /**
     * Validate upload
     * @param  File     $file
     * @param  null|int $current_size
     * @return bool
     */
    public function validate(File $file, $current_size = null);
}
