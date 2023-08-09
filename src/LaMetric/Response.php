<?php

declare(strict_types=1);

namespace LaMetric;

use LaMetric\Response\{Frame, FrameCollection};

class Response
{
    public function printError(string $value = 'INTERNAL ERROR'): string
    {
        return $this->asJson([
            'frames' => [
                [
                    'index' => 0,
                    'text'  => $value,
                    'icon'  => 'null',
                ],
            ],
        ]);
    }

    private function asJson(array $data = []): string
    {
        return (string) json_encode($data);
    }

    public function printData(FrameCollection $frameCollection): string
    {
        $response = [
            'frames' => [],
        ];

        /** @var Frame $frame */
        foreach ($frameCollection->getFrames() as $key => $frame) {
            $response['frames'][] = [
                'index' => $key,
                'icon'  => $frame->getIcon() !== '' ? $frame->getIcon() : null,
                'text'  => $frame->getText(),
            ];
        }

        return $this->asJson($response);
    }
}
