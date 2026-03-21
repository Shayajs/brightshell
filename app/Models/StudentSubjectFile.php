<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StudentSubjectFile extends Model
{
    protected $fillable = [
        'student_subject_folder_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::deleting(function (StudentSubjectFile $file): void {
            if ($file->stored_path && Storage::disk('local')->exists($file->stored_path)) {
                Storage::disk('local')->delete($file->stored_path);
            }
        });
    }

    /** @return BelongsTo<StudentSubjectFolder, $this> */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(StudentSubjectFolder::class, 'student_subject_folder_id');
    }

    public function humanSize(): string
    {
        $bytes = (int) ($this->size ?? 0);
        if ($bytes < 1024) {
            return $bytes.' o';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' Ko';
        }

        return round($bytes / 1024 / 1024, 1).' Mo';
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function isMarkdown(): bool
    {
        if (in_array($this->extension(), ['md', 'markdown'], true)) {
            return true;
        }

        $mime = strtolower((string) ($this->mime_type ?? ''));

        return str_contains($mime, 'markdown');
    }
}
