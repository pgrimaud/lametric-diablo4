<?php

declare(strict_types=1);

namespace LaMetric\Response;

class Frame
{
    private $text = '';

    private $icon = '';

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }
}
