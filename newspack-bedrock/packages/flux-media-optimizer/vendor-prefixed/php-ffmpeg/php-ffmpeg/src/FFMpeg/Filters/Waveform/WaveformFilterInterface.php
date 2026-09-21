<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Strime <contact@strime.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FluxMedia\FFMpeg\Filters\Waveform;

use FluxMedia\FFMpeg\Filters\FilterInterface;
use FluxMedia\FFMpeg\Media\Waveform;

interface WaveformFilterInterface extends FilterInterface
{
    public function apply(Waveform $waveform);
}
