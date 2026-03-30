<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FundTransfer extends Model
{
    protected $fillable = [
        'user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'scheduled_date',
        'memo',
        'from_confirmed',
        'to_confirmed',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'scheduled_date' => 'date',
            'from_confirmed' => 'boolean',
            'to_confirmed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('scheduled_date', $year)
                     ->whereMonth('scheduled_date', $month);
    }

    /**
     * 振出元の表示名
     */
    public function getFromAccountNameAttribute(): ?string
    {
        return $this->fromAccount?->display_name;
    }

    /**
     * 振込先の表示名
     */
    public function getToAccountNameAttribute(): ?string
    {
        return $this->toAccount?->display_name;
    }
}
