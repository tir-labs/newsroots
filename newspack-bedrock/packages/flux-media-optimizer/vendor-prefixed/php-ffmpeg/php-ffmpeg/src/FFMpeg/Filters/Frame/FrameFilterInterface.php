<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <dev.team@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FluxMedia\FFMpeg\Filters\Frame;

use FluxMedia\FFMpeg\Filters\FilterInterface;
use FluxMedia\FFMpeg\Media\Frame;

interface FrameFilterInterface extends FilterInterface
{
    public function apply(Frame $frame);
}
