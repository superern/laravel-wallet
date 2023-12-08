<?php

declare(strict_types=1);

namespace Superern\Wallet\Test\Units\Domain;

use Superern\Wallet\Exceptions\AmountInvalid;
use Superern\Wallet\Exceptions\BalanceIsEmpty;
use Superern\Wallet\Exceptions\InsufficientFunds;
use Superern\Wallet\Internal\Exceptions\ExceptionInterface;
use Superern\Wallet\Internal\Exceptions\TransactionFailedException;
use Superern\Wallet\Internal\Service\DatabaseServiceInterface;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Services\RegulatorServiceInterface;
use Superern\Wallet\Test\Infra\Factories\UserFactory;
use Superern\Wallet\Test\Infra\Models\User;
use Superern\Wallet\Test\Infra\TestCase;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
use Throwable;

/**
 * @internal
 */
final class WalletTest extends TestCase
{
    public function testDeposit(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(10);
        self::assertSame(10, $user->balanceInt);

        $user->deposit(10);
        self::assertSame(20, $user->balanceInt);

        $user->deposit(980);
        self::assertSame(1000, $user->balanceInt);

        self::assertSame(3, $user->transactions()->count());

        $user->withdraw($user->balanceInt);
        self::assertSame(0, $user->balanceInt);

        self::assertSame(3, $user->transactions()->where([
            'type' => Transaction::TYPE_DEPOSIT,
        ])->count());

        self::assertSame(1, $user->transactions()->where([
            'type' => Transaction::TYPE_WITHDRAW,
        ])->count());

        self::assertSame(4, $user->transactions()->count());
    }

    public function testInvalidDeposit(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::AMOUNT_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.price_positive'));

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(-1);
    }

    public function testInvalidWithdraw(): void
    {
        $this->expectException(AmountInvalid::class);
        $this->expectExceptionCode(ExceptionInterface::AMOUNT_INVALID);
        $this->expectExceptionMessageStrict(trans('wallet::errors.price_positive'));

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1);
        $user->withdraw(-1);
    }

    public function testFindUserByExistsWallet(): void
    {
        /** @var Collection|User[] $users */
        $users = UserFactory::times(10)->create();
        self::assertCount(10, $users);

        /** @var User $user */
        $user = $users->first();
        self::assertSame(0, $user->balanceInt); // create default wallet
        self::assertFalse($user->wallet->exists);

        $ids = [];
        foreach ($users as $other) {
            $ids[] = $other->getKey();
            if ($user !== $other) {
                self::assertFalse($other->wallet->exists);
            }
        }

        self::assertCount(0, User::query()->has('wallet')->whereIn('id', $ids)->get());

        self::assertCount(10, User::query()->has('wallet', '<')->whereIn('id', $ids)->get());
    }

    public function testWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(100);
        self::assertSame(100, $user->balanceInt);

        $user->withdraw(10);
        self::assertSame(90, $user->balanceInt);

        $user->withdraw(81);
        self::assertSame(9, $user->balanceInt);

        $user->withdraw(9);
        self::assertSame(0, $user->balanceInt);

        $user->withdraw(1);
    }

    public function testBalanceIsEmptyWithdraw(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->withdraw(-1);
    }

    public function testInsufficientFundsWithdraw(): void
    {
        $this->expectException(InsufficientFunds::class);
        $this->expectExceptionCode(ExceptionInterface::INSUFFICIENT_FUNDS);
        $this->expectExceptionMessageStrict(trans('wallet::errors.insufficient_funds'));

        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(1);
        $user->withdraw(2);
    }

    public function testTransfer(): void
    {
        /**
         * @var User $first
         * @var User $second
         */
        [$first, $second] = UserFactory::times(2)->create();
        self::assertNotSame($first->getKey(), $second->getKey());
        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);

        $first->deposit(100);
        self::assertSame(100, $first->balanceInt);

        $second->deposit(100);
        self::assertSame(100, $second->balanceInt);

        $first->transfer($second, 100);
        self::assertSame(0, $first->balanceInt);
        self::assertSame(200, $second->balanceInt);

        $second->transfer($first, 100);
        self::assertSame(100, $second->balanceInt);
        self::assertSame(100, $first->balanceInt);

        $second->transfer($first, 100);
        self::assertSame(0, $second->balanceInt);
        self::assertSame(200, $first->balanceInt);

        $first->withdraw($first->balanceInt);
        self::assertSame(0, $first->balanceInt);

        self::assertNull($first->safeTransfer($second, 100));
        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);

        self::assertNotNull($first->forceTransfer($second, 100));
        self::assertSame(-100, $first->balanceInt);
        self::assertSame(100, $second->balanceInt);

        self::assertNotNull($second->forceTransfer($first, 100));
        self::assertSame(0, $first->balanceInt);
        self::assertSame(0, $second->balanceInt);
    }

    /**
     * @see https://github.com/superern/laravel-wallet/issues/286#issue-750353538
     */
    public function testTransferWalletNotExists(): void
    {
        /**
         * @var User $first
         * @var User $second
         */
        [$first, $second] = UserFactory::times(2)->create();
        self::assertNotSame($first->getKey(), $second->getKey());

        self::assertNotNull($first->deposit(1000));
        self::assertSame(1000, $first->balanceInt);

        self::assertNotNull($first->transfer($second, 500));
        self::assertSame(500, $first->balanceInt);
        self::assertSame(500, $second->balanceInt);
    }

    public function testTransferYourself(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(100);
        $user->transfer($user, 100);
        self::assertSame(100, $user->balanceInt);

        $user->withdraw($user->balanceInt);
        self::assertSame(0, $user->balanceInt);
    }

    public function testBalanceIsEmpty(): void
    {
        $this->expectException(BalanceIsEmpty::class);
        $this->expectExceptionCode(ExceptionInterface::BALANCE_IS_EMPTY);
        $this->expectExceptionMessageStrict(trans('wallet::errors.wallet_empty'));

        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame(0, $user->balanceInt);
        $user->withdraw(1);
    }

    public function testConfirmed(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(1);
        self::assertSame(1, $user->balanceInt);

        $user->withdraw(1, null, false);
        self::assertSame(1, $user->balanceInt);

        $user->withdraw(1);
        self::assertSame(0, $user->balanceInt);
    }

    public function testRecalculate(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        self::assertSame(0, $user->balanceInt);

        $user->deposit(100, null, false);
        self::assertSame(0, $user->balanceInt);

        $user->transactions()
            ->update([
                'confirmed' => true,
            ])
        ;
        self::assertSame(0, $user->balanceInt);

        $user->wallet->refreshBalance();
        self::assertSame(100, $user->balanceInt);

        $user->withdraw($user->balanceInt);
        self::assertSame(0, $user->balanceInt);
    }

    public function testCrash(): void
    {
        /** @var User $user */
        $user = UserFactory::new()->create();
        $user->deposit(10000);
        self::assertSame(10000, $user->balanceInt);

        try {
            app(DatabaseServiceInterface::class)->transaction(static function () use ($user): never {
                self::assertSame(0, (int) app(RegulatorServiceInterface::class)->diff($user->wallet));
                $user->withdraw(10000);
                self::assertSame(-10000, (int) app(RegulatorServiceInterface::class)->diff($user->wallet));
                self::assertSame(0, (int) app(RegulatorServiceInterface::class)->amount($user->wallet));

                throw new RuntimeException('hello world');
            });
        } catch (Throwable $throwable) {
            self::assertInstanceOf(TransactionFailedException::class, $throwable);
            self::assertNotNull($throwable->getPrevious());

            self::assertInstanceOf(RuntimeException::class, $throwable->getPrevious());
            self::assertSame('hello world', $throwable->getPrevious()->getMessage());
        }

        self::assertSame(10000, $user->balanceInt);
        self::assertSame(0, (int) app(RegulatorServiceInterface::class)->diff($user->wallet));
    }
}
