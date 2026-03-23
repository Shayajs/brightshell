<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Une seule ligne attendue : surcharge vitrine + thème e-mails (prioritaire sur config / .env).
 */
class SiteAppearance extends Model
{
    protected $fillable = [
        'favicon_path',
        'site_logo_path',
        'mail_layout_partial',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'mail_layout_partial' => 'array',
        ];
    }

    public static function settings(): self
    {
        $row = static::query()->first();
        if ($row !== null) {
            return $row;
        }

        return static::query()->create([
            'favicon_path' => null,
            'site_logo_path' => null,
            'mail_layout_partial' => null,
        ]);
    }
}
