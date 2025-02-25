<?php

namespace AledDev\DashLog\Application\Services;

use AledDev\DashLog\Application\DTOs\RequestLogDTO;
use AledDev\DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use AledDev\DashLog\Domain\Entities\RequestLog;
use AledDev\DashLog\Domain\ValueObjects\RequestMethod;
use AledDev\DashLog\Domain\ValueObjects\RequestStatus;

class RequestLogService
{
    public function __construct(
        private RequestLogRepositoryInterface $repository
    ) {}

    public function logRequest(RequestLogDTO $dto): void
    {
        $requestLog = new RequestLog(
            id: uniqid(),
            method: RequestMethod::fromString($dto->method),
            url: $dto->url,
            ip: $dto->ip,
            userId: $dto->userId,
            duration: $dto->duration,
            status: RequestStatus::fromStatusCode($dto->statusCode),
            requestData: $dto->requestData,
            responseData: $dto->responseData,
            headers: $dto->headers,
            cookies: $dto->cookies,
            session: $dto->session,
            stackTrace: $dto->stackTrace,
            createdAt: new \DateTimeImmutable(),
            userAgent: $dto->userAgent
        );

        $this->repository->save($requestLog);
    }
} 