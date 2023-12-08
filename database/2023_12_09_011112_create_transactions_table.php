<?php

declare(strict_types=1);

use Superern\Wallet\Enum\TransactionType;
use Superern\Wallet\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // transactions table
        Schema::create($this->table(), static function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->morphs('payable'); // type: User

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('delivery_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', TransactionType::toValues())->index(); // deposit or withdraw
            $table->decimal('amount', 64, 0);
            $table->boolean('confirmed');
            $table->json('meta')->nullable();

            $table->string('reference')->nullable(); // generated paystack-reference

            $table->index(['payable_type', 'payable_id'], 'payable_type_payable_id_ind');
            $table->index(['payable_type', 'payable_id', 'type'], 'payable_type_ind');
            $table->index(['payable_type', 'payable_id', 'confirmed'], 'payable_confirmed_ind');
            $table->index(['payable_type', 'payable_id', 'type', 'confirmed'], 'payable_type_confirmed_ind');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop($this->table());
    }

    private function table(): string
    {
        return (new Transaction())->getTable();
    }
};
