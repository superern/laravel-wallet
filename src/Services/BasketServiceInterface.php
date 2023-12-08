<?php

declare(strict_types=1);

namespace Superern\Wallet\Services;

use Superern\Wallet\Internal\Dto\AvailabilityDtoInterface;

/**
 * @api
 */
interface BasketServiceInterface
{
    /**
     * A quick way to check stock. Able to check in batches, necessary for quick payments.
     */
    public function availability(AvailabilityDtoInterface $availabilityDto): bool;
}
