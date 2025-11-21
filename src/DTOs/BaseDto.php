<?php

namespace Rawnoq\HMVC\DTOs;

use Illuminate\Http\Request;

abstract readonly class BaseDto
{
    /**
     * Create a DTO instance from an array.
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Create a DTO instance from a request.
     */
    public static function fromRequest(Request $request): static
    {
        return static::fromArray($request->validated());
    }

    /**
     * Convert the DTO to an array.
     */
    abstract public function toArray(): array;
}

