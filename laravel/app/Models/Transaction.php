<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'rule_id',
        'title',
        'amount',
        'type',
        'scheduled_date',
        'actual_date',
        'status',
        'carried_over_from',
        'bank_account_id',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'scheduled_date' => 'date',
            'actual_date' => 'date',
        ];
    }

    /**
     * 有効なステータス遷移マップ
     */
    public const VALID_TRANSITIONS = [
        'scheduled' => ['completed', 'carried_over', 'cancelled'],
        'carried_over' => ['completed', 'cancelled'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(TransactionRule::class, 'rule_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function carriedOverFrom(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'carried_over_from');
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

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    /**
     * ステータス遷移が有効かチェック
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::VALID_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    /**
     * 編集可能かチェック
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['scheduled', 'carried_over']);
    }
}
