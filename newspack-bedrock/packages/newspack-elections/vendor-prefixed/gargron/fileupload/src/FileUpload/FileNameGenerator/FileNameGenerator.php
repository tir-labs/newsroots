<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Govpack\Vendor\FileUpload\FileNameGenerator;

use Govpack\Vendor\FileUpload\FileUpload;

interface FileNameGenerator
{

    /**
     * Get file_name
     * @param  string     $source_name
     * @param  string     $type
     * @param  string     $tmp_name
     * @param  integer    $index
     * @param  string     $content_range
     * @param  FileUpload $upload
     * @return string
     */
    public function getFileName($source_name, $type, $tmp_name, $index, $content_range, FileUpload $upload);
}
