<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourse extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
        'schedule_weekday',
        'schedule_time_start',
        'schedule_time_end',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<StudentCourseQuiz, $this> */
    public function quizzes(): HasMany
    {
        return $this->hasMany(StudentCourseQuiz::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Créneau récurrent défini (même jour + heures). */
    public function hasWeeklySchedule(): bool
    {
        return $this->schedule_weekday !== null
            && $this->schedule_time_start !== null
            && $this->schedule_time_end !== null;
    }

    /** Libellé du jour (1=lundi … 7=dimanche). */
    public function scheduleWeekdayLabel(): ?string
    {
        if ($this->schedule_weekday === null) {
            return null;
        }

        $map = [
            1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
            5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',
        ];

        return $map[(int) $this->schedule_weekday] ?? null;
    }

    /**
     * Heure au format HH:MM pour affichage (colonne TIME SQL).
     */
    public function scheduleTimeStartShort(): ?string
    {
        return self::formatTimeShort($this->schedule_time_start);
    }

    public function scheduleTimeEndShort(): ?string
    {
        return self::formatTimeShort($this->schedule_time_end);
    }

    private static function formatTimeShort(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (strlen($value) >= 5) {
            return substr($value, 0, 5);
        }

        return $value;
    }

    public static function statuses(): array
    {
        return [
            'planned' => 'Planifié',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'archived' => 'Archivé',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }
}
