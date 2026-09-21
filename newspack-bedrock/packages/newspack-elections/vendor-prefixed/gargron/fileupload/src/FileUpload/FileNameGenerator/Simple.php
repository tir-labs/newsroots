<?php
/**
 * Created by PhpStorm.
 * User: decola
 * Date: 11.07.14
 * Time: 14:00
 *
 * @license MIT
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace Govpack\Vendor\FileUpload\FileNameGenerator;

use Govpack\Vendor\FileUpload\FileSystem\FileSystem;
use Govpack\Vendor\FileUpload\PathResolver\PathResolver;
use Govpack\Vendor\FileUpload\FileUpload;
use Govpack\Vendor\FileUpload\Util;

class Simple implements FileNameGenerator
{

    /**
     * Pathresolver
     * @var PathResolver
     */
    private $pathresolver;

    /**
     * Filesystem
     * @var FileSystem
     */
    private $filesystem;

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
    public function getFileName($source_name, $type, $tmp_name, $index, $content_range, FileUpload $upload)
    {
        $this->filesystem = $upload->getFileSystem();
        $this->pathresolver = $upload->getPathResolver();

        return ($this->getUniqueFilename($source_name, $type, $index, $content_range));
    }

    /**
     * Get unique but consistent name
     * @param  string  $name
     * @param  string  $type
     * @param  integer $index
     * @param  array   $content_range
     * @return string
     */
    protected function getUniqueFilename($name, $type, $index, $content_range)
    {
        while ($this->filesystem->isDir($this->pathresolver->getUploadPath($name))) {
            $name = $this->pathresolver->upcountName($name);
        }

        $uploaded_bytes = Util::fixIntegerOverflow(intval($content_range[1]));

        while ($this->filesystem->isFile($this->pathresolver->getUploadPath($name))) {
            if ($uploaded_bytes == $this->filesystem->getFilesize($this->pathresolver->getUploadPath($name))) {
                break;
            }

            $name = $this->pathresolver->upcountName($name);
        }

        return $name;
    }
}
