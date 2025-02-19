<?php

namespace DashLog\Domain\Entities;

use DateTimeImmutable;
use DashLog\Domain\ValueObjects\RequestMethod;
use DashLog\Domain\ValueObjects\RequestStatus;

class RequestLog
{
    public function __construct(
        private readonly string $id,
        private readonly RequestMethod $method,
        private readonly string $url,
        private readonly ?string $ip,
        private readonly ?string $userId,
        private readonly ?float $duration,
        private readonly RequestStatus $status,
        private readonly array $requestData,
        private readonly array $responseData,
        private readonly array $headers,
        private readonly array $cookies,
        private readonly array $session,
        private readonly array $stackTrace,
        private readonly DateTimeImmutable $createdAt
    ) {}

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getMethod(): RequestMethod
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function getStatus(): RequestStatus
    {
        return $this->status;
    }

    public function getRequestData(): array
    {
        return $this->requestData;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getSession(): array
    {
        return $this->session;
    }

    public function getStackTrace(): array
    {
        return $this->stackTrace;
    }
}