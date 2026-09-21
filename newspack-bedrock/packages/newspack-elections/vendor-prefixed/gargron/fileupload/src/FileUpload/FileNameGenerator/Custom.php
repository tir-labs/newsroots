<?php
/**
 * @license MIT
 *
 * Modified by govpack on 11-September-2026 using {@see https://github.com/BrianHenryIE/strauss}.
 */


namespace Govpack\Vendor\FileUpload\FileNameGenerator;

use Closure;
use Govpack\Vendor\FileUpload\FileUpload;

class Custom implements FileNameGenerator
{

    protected $generator;

    /**
     * @param string|callable|Closure $nameGenerator
     */
    public function __construct($nameGenerator)
    {
        $this->generator = $nameGenerator;
    }

    public function getFileName($source_name, $type, $tmp_name, $index, $content_range, FileUpload $upload)
    {

        if (is_string($this->generator) && !is_callable($this->generator)) {
            return $this->generator;
        }

        return call_user_func_array(
            $this->generator,
            func_get_args()
        );
    }
}
