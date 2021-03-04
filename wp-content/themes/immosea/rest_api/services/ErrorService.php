<?php

interface ErrorService
{
    public function setStatusCode(int $code): ErrorService;

    public function setMessage(string $message): ErrorService;

    public function getStatusCode(): int;

    public function getMessage(): string;

    public function report(): array;
}
