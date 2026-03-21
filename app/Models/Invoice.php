<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number', 'company_id', 'amount_ht', 'tva_rate',
        'status', 'label', 'issued_at', 'due_at', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_ht' => 'decimal:2',
            'tva_rate' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'date',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function amountTtc(): float
    {
        if ($this->tva_rate === null) {
            return (float) $this->amount_ht;
        }

        return round((float) $this->amount_ht * (1 + (float) $this->tva_rate / 100), 2);
    }

    public static function nextNumber(): string
    {
        $year = now()->year;
        $last = self::withTrashed()
            ->where('number', 'like', "BS-{$year}-%")
            ->count();

        return sprintf('BS-%d-%03d', $year, $last + 1);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyée',
            'paid' => 'Payée',
            'cancelled' => 'Annulée',
            default => $this->status,
        };
    }
}
