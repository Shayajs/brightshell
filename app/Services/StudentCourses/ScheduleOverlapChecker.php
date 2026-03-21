<?php

namespace App\Services\StudentCourses;

use App\Models\StudentCourse;
use Illuminate\Support\Collection;

final class ScheduleOverlapChecker
{
    /**
     * @param  array{schedule_weekday?: int|null, schedule_time_start?: string|null, schedule_time_end?: string|null}  $slot
     * @return Collection<int, StudentCourse>
     */
    public function conflictingCourses(int $userId, array $slot, ?int $excludeCourseId = null): Collection
    {
        $weekday = $slot['schedule_weekday'] ?? null;
        $start = $slot['schedule_time_start'] ?? null;
        $end = $slot['schedule_time_end'] ?? null;

        if ($weekday === null || $start === null || $end === null || $start === '' || $end === '') {
            return collect();
        }

        $startM = self::toMinutes((string) $start);
        $endM = self::toMinutes((string) $end);
        if ($endM <= $startM) {
            return collect();
        }

        $query = StudentCourse::query()
            ->where('user_id', $userId)
            ->where('status', '!=', 'archived')
            ->whereNotNull('schedule_weekday')
            ->whereNotNull('schedule_time_start')
            ->whereNotNull('schedule_time_end')
            ->where('schedule_weekday', (int) $weekday);

        if ($excludeCourseId !== null) {
            $query->where('id', '!=', $excludeCourseId);
        }

        return $query->get()->filter(function (StudentCourse $other) use ($startM, $endM) {
            $oStart = self::toMinutes((string) $other->schedule_time_start);
            $oEnd = self::toMinutes((string) $other->schedule_time_end);
            if ($oEnd <= $oStart) {
                return false;
            }

            return $startM < $oEnd && $endM > $oStart;
        })->values();
    }

    /**
     * @return array<int, list<string>> course_id => titres des cours en conflit
     */
    public function conflictMap(int $userId): array
    {
        $courses = StudentCourse::query()
            ->where('user_id', $userId)
            ->where('status', '!=', 'archived')
            ->whereNotNull('schedule_weekday')
            ->whereNotNull('schedule_time_start')
            ->whereNotNull('schedule_time_end')
            ->get();

        $map = [];
        foreach ($courses as $c) {
            $others = $this->conflictingCourses($userId, [
                'schedule_weekday' => $c->schedule_weekday,
                'schedule_time_start' => $c->schedule_time_start,
                'schedule_time_end' => $c->schedule_time_end,
            ], $c->id);
            if ($others->isNotEmpty()) {
                $map[$c->id] = $others->pluck('title')->all();
            }
        }

        return $map;
    }

    private static function toMinutes(string $time): int
    {
        $time = trim($time);
        $parts = explode(':', $time);

        return ((int) $parts[0]) * 60 + ((int) ($parts[1] ?? 0));
    }
}
