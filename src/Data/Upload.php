<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class Upload {
    private string $name;
    private string $red;
    private string $blue;
    private int $uploaderId;
    private string $hash;

    public function __construct(string $name, string $red, string $blue, int $uploaderId, string $hash) {
        $this->name = $name;
        $this->red = $red;
        $this->blue = $blue;
        $this->uploaderId = $uploaderId;
        $this->hash = $hash;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRed(): string {
        return $this->red;
    }

    public function getBlue(): string {
        return $this->blue;
    }

    public function getUploaderId(): int {
        return $this->uploaderId;
    }

    public function getHash(): string {
        return $this->hash;
    }
}
