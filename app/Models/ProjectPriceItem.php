<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPriceItem extends Model
{
    protected $fillable = [
        'project_id', 'label', 'quantity', 'unit_price_ht', 'vat_rate', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price_ht' => 'decimal:4',
            'vat_rate' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lineTotalHt(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price_ht, 4);
    }

    public function lineTotalTtc(): float
    {
        $ht = $this->lineTotalHt();
        if ($this->vat_rate === null) {
            return $ht;
        }

        return round($ht * (1 + (float) $this->vat_rate / 100), 2);
    }
}
