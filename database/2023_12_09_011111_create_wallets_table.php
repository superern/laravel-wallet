<?php

declare(strict_types=1);

use Superern\Wallet\Models\Transaction;
use Superern\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // wallets table
        Schema::create($this->table(), static function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->morphs('holder'); // type: User

            $table->string('name');
            $table->string('slug')->index();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->decimal('balance', 64, 0)->default(0);
            $table->unsignedSmallInteger('decimal_places')->default(2);

            $table->string('paystack_user_code')->nullable();
            $table->string('paystack_user_id')->nullable();
            $table->string('stripe_user_id')->nullable();

            $table->timestamps();

            $table->unique(['holder_type', 'holder_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::drop($this->table());
    }

    private function table(): string
    {
        return (new Wallet())->getTable();
    }

    private function transactionTable(): string
    {
        return (new Transaction())->getTable();
    }
};
