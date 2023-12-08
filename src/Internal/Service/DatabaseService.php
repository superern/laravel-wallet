<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Exceptions\TransactionRollbackException;
use Illuminate\Database\RecordsNotFoundException;
use Throwable;

final class DatabaseService implements DatabaseServiceInterface
{
    public function __construct(
        private readonly ConnectionServiceInterface $connectionService
    ) {
    }

    /**
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transaction(callable $callback): mixed
    {
        try {
            $connection = $this->connectionService->get();
            if ($connection->transactionLevel() > 0) {
                return $callback();
            }

            return $connection->transaction(function () use ($callback) {
                $result = $callback();

                if ($result === false || (is_countable($result) && count($result) === 0)) {
                    throw new TransactionRollbackException($result);
                }

                return $result;
            });
        } catch (TransactionRollbackException $rollbackException) {
            return $rollbackException->getResult();
        } catch (RecordsNotFoundException|ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new TransactionFailedException(
                'Transaction failed. Message: ' . $throwable->getMessage(),
                ExceptionInterface::TRANSACTION_FAILED,
                $throwable
            );
        }
    }
}
