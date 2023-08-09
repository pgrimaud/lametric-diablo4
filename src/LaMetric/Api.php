<?php

declare(strict_types=1);

namespace LaMetric;

use LaMetric\Response\{Frame, FrameCollection};
use GuzzleHttp\Client as GuzzleClient;
use Predis\Client as PredisClient;

class Api
{
    public function __construct(
        private GuzzleClient $guzzleClient,
        private PredisClient $predisClient
    ) {
    }

    public function fetchData(): FrameCollection
    {
        $redisKey = 'lametric:diablo4';

        $body = $this->predisClient->get($redisKey);
        $ttl = $this->predisClient->ttl($redisKey);

        if (!$body || $ttl < 0) {
            $response = $this->guzzleClient->get('https://helltides.com/api/schedule');

            $body = (string)$response->getBody();

            $this->predisClient->set($redisKey, $body);
            $this->predisClient->expireat($redisKey, strtotime('+1 hour'));
        }

        $data = json_decode($body, true);

        $now = new \DateTime();
        $nextWorldBoss = new \DateTime($data['world_boss'][0]['startTime']);
        $diffWorldBoss = $now->diff($nextWorldBoss);

        $nextHelltide = new \DateTime($data['helltide'][0]['startTime']);
        $diffHelltide = $now->diff($nextHelltide);

        $frames = [
            'world_boss' => $diffWorldBoss->format('%H:%I:%S'),
            'helltide' => $diffHelltide->format('%H:%I:%S'),
        ];

        return $this->mapData($frames);
    }

    private function mapData(array $data = []): FrameCollection
    {
        $frameCollection = new FrameCollection();

        foreach ($data as $key => $value) {
            $frame = new Frame();
            $frame->setText($value);
            $frame->setIcon($key === 'world_boss' ? 'i34284' : 'i7627');

            $frameCollection->addFrame($frame);
        }

        return $frameCollection;
    }
}
