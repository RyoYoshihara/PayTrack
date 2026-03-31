<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'bank_name',
        'balance',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionRules(): HasMany
    {
        return $this->hasMany(TransactionRule::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * 表示用の名前（名前（銀行名））
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name}（{$this->bank_name}）";
    }
}
