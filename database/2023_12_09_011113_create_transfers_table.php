<?php

declare(strict_types=1);

use Superern\Wallet\Enum\TransferStatus;
use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // transfers table
        Schema::create($this->table(), function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->morphs('from'); // type: User
            $table->morphs('to');   // type: User

            $table->enum('status', TransferStatus::toValues())->default(TransferStatus::PENDING);
            $table->enum('status_last', TransferStatus::toValues())->nullable();

            $table->decimal('discount', 64, 0)->default(0);
            $table->decimal('fee', 64, 0)->default(0);

            $table->foreignId('deposit_id')
                ->constrained($this->transactionTable())
                ->cascadeOnDelete();

            $table->foreignId('withdraw_id')
                ->constrained($this->transactionTable())
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop($this->table());
    }

    private function table(): string
    {
        return (new Transfer())->getTable();
    }

    private function transactionTable(): string
    {
        return (new Transaction())->getTable();
    }
};
