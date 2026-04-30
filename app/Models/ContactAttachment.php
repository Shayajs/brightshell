<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactAttachment extends Model
{
    protected $fillable = [
        'contact_message_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /** @return BelongsTo<ContactMessage, $this> */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class, 'contact_message_id');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size;
        if ($bytes < 1024) {
            return $bytes.' o';
        }
        $units = ['Ko', 'Mo', 'Go'];
        $i = -1;
        $value = $bytes;
        do {
            $value /= 1024;
            $i++;
        } while ($value >= 1024 && $i < count($units) - 1);

        return number_format($value, $value >= 10 ? 0 : 1, ',', ' ').' '.$units[$i];
    }
}
