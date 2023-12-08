<?php

namespace Superern\Wallet\Enum;

enum TransferStatus: string
{
    /**
     * PENDING:
     * The transaction has been initiated but is awaiting further processing or confirmation. It has not been completed yet.
     */
    case PENDING = 'pending';

    /**
     * AUTHORIZED: The payment has been authorized by the payment processor but is pending settlement or capture. Funds are
     * earmarked
     * but not yet captured.
     */
    case AUTHORIZED = 'authorized';

    /**
     * FAILED: The transaction failed due to various reasons, such as insufficient funds, declined by the bank, or technical
     * issues. The payment was not successful.
     */
    case FAILED = 'failed';

    /**
     * REFUNDED: A partial or full refund has been processed for the transaction, returning the funds to the customer
     * after a successful transaction.
     */
    case REFUNDED = 'refunded';

    /**
     * VOIDED: The transaction was canceled before it was completed, usually without capturing any funds.
     */
    case VOIDED = 'voided';

    case EXCHANGE = 'exchange';

    /**
     * TRANSFER: sending balance to other user's wallet
     */
    case TRANSFER = 'transfer';

    /**
     * PAID: transaction has been successfully paid to merchant
     */
    case PAID = 'paid';

    case GIFT = 'gift';

    public static function toValues(): array
    {
        return [
            'pending',
            'authorized',
            'failed',
            'refunded',
            'voided',
            'exchange',
            'transfer',
            'paid',
            'gift',
        ];
    }
}
