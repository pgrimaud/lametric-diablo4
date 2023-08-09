<?php

declare(strict_types=1);

namespace LaMetric\Response;

class FrameCollection
{
    private array $frames;

    public function addFrame(Frame $frame): void
    {
        $this->frames[] = $frame;
    }

    public function getFrames(): array
    {
        return $this->frames;
    }
}
