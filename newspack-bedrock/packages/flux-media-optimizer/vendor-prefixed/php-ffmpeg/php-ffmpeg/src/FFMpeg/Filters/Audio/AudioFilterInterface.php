<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <dev.team@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FluxMedia\FFMpeg\Filters\Audio;

use FluxMedia\FFMpeg\Filters\FilterInterface;
use FluxMedia\FFMpeg\Format\AudioInterface;
use FluxMedia\FFMpeg\Media\Audio;

interface AudioFilterInterface extends FilterInterface
{
    /**
     * Applies the filter on the the Audio media given an format.
     *
     * @param Audio          $audio
     * @param AudioInterface $format
     *
     * @return array An array of arguments
     */
    public function apply(Audio $audio, AudioInterface $format);
}
