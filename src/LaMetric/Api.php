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
            $this->predisClient->expireat($redisKey, strtotime('+30 minutes'));
        }

        $data = json_decode($body, true);

        $now = new \DateTime();
        $nextWorldBoss = new \DateTime($data['world_boss'][0]['startTime']);
        $diffWorldBoss = $now->diff($nextWorldBoss);

        $nextHelltide = new \DateTime($data['helltide'][0]['startTime']);
        $diffHelltide = $now->diff($nextHelltide);

        $frames = [
            'world_boss' => $diffWorldBoss,
            'helltide' => $diffHelltide,
        ];

        return $this->mapData($frames);
    }

    private function mapData(array $data = []): FrameCollection
    {
        $frameCollection = new FrameCollection();

        foreach ($data as $key => $value) {
            if ($key === 'helltide' && $value->invert === 1) {
                $value->invert = 0;

                $now = new \DateTime();
                $endHelltime = new \DateTime('+1 hour');
                $endHelltime->sub($value);

                $diff = $now->diff($endHelltime);

                $frame = new Frame();
                $frame->setText('ON AIR');
                $frame->setIcon('i7627');

                $frameCollection->addFrame($frame);

                $frame = new Frame();
                $frame->setText($diff->format('%H:%I:%S'));
                $frame->setIcon('i7627');

            } else {
                $frame = new Frame();
                $frame->setText($value->format('%H:%I:%S'));
                $frame->setIcon($key === 'world_boss' ? 'i34284' : 'i7627');
            }

            $frameCollection->addFrame($frame);
        }

        return $frameCollection;
    }
}
