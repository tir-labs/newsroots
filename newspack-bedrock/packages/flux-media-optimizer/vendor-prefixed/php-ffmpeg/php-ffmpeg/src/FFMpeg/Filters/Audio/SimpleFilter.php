<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FluxMedia\FFMpeg\Filters\Audio;

use FluxMedia\FFMpeg\Media\Audio;
use FluxMedia\FFMpeg\Format\AudioInterface;

class SimpleFilter implements AudioFilterInterface
{
    private $params;
    private $priority;

    public function __construct(array $params, $priority = 0)
    {
        $this->params = $params;
        $this->priority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Audio $audio, AudioInterface $format)
    {
        return $this->params;
    }
}
