<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class TransactionRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'amount',
        'type',
        'recurrence',
        'day_of_month',
        'start_month',
        'end_month',
        'bank_account_id',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'day_of_month' => 'integer',
            'start_month' => 'date',
            'end_month' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'rule_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * start_monthのYYYY-MM表示
     */
    public function getStartMonthDisplayAttribute(): ?string
    {
        return $this->start_month?->format('Y-m');
    }

    /**
     * end_monthのYYYY-MM表示
     */
    public function getEndMonthDisplayAttribute(): ?string
    {
        return $this->end_month?->format('Y-m');
    }
}
