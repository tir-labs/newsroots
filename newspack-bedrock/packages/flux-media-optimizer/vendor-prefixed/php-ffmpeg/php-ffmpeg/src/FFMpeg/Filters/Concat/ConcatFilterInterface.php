<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Strime <contact@strime.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FluxMedia\FFMpeg\Filters\Concat;

use FluxMedia\FFMpeg\Filters\FilterInterface;
use FluxMedia\FFMpeg\Media\Concat;

interface ConcatFilterInterface extends FilterInterface
{
    public function apply(Concat $concat);
}
