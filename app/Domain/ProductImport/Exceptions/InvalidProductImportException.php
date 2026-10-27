<?php

namespace App\Domain\ProductImport\Exceptions;

use RuntimeException;

class InvalidProductImportException extends RuntimeException
{
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }
}
