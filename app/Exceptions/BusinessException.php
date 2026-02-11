<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Exceptions;

// app/Exceptions/BusinessException.php
namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    public function __construct(
        string $message,
        public readonly array $data = [],
        int $code = 400
    ) {
        parent::__construct($message, $code);
    }

    // Generic factory methods
    public static function notFound(string $resource, int|string $id): self
    {
        return new self(
            "{$resource}  not found",
            [strtolower($resource)],
            404
        );
    }

    // Generic factory methods
    public static function unauthorized(string $message = "Unauthorized"): self
    {
        return new self(
             $message,
            [],
            401
        );
    }

    public static function cannotUpdate(string $resource, string $reason): self
    {
        return new self(
            "Cannot update {$resource}: {$reason}",
            [],
            403
        );
    }

    public static function cannotDelete(string $resource, string $reason): self
    {
        return new self(
            "Cannot delete {$resource}: {$reason}",
            [],
            403
        );
    }

    public static function alreadyExists(string $resource, array $data = []): self
    {
        return new self(
            "{$resource} already exists",
            $data,
            409
        );
    }

    public static function invalidOperation(string $message, array $data = []): self
    {
        return new self($message, $data, 400);
    }

}
