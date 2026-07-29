<?php

namespace App\DTOs;

class FaceMatchResult
{
    public float $score = 0.0;
    public ?string $faceToken = null;

    public function __construct(
        float $score,
        public readonly bool    $isMatch,
        public readonly ?string $error = null,
        public readonly array   $rawResponse = [],
    ) {
        $this->score = $score;
    }

    public static function success(): self
    {
        return new self(score: 100.0, isMatch: true);
    }

    public static function error(string $message): self
    {
        return new self(score: 0.0, isMatch: false, error: $message);
    }

    public static function fromApiResponse(array $response, float $threshold): self
    {
        $score = (float) ($response['confidence'] ?? 0.0);
        return new self(
            score: $score,
            isMatch: $score >= $threshold,
            rawResponse: $response,
        );
    }
}
