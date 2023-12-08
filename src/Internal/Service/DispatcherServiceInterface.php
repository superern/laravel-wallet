<?php

declare(strict_types=1);

namespace Superern\Wallet\Internal\Service;

use Superern\Wallet\Internal\Events\EventInterface;

interface DispatcherServiceInterface
{
    public function dispatch(EventInterface $event): void;

    public function forgot(): void;

    public function flush(): void;

    public function lazyFlush(): void;
}
