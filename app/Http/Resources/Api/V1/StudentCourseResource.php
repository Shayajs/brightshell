<?php

namespace App\Http\Resources\Api\V1;

use App\Models\StudentCourse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentCourse */
class StudentCourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'sort_order' => $this->sort_order,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'schedule_weekday' => $this->schedule_weekday,
            'schedule_weekday_label' => $this->scheduleWeekdayLabel(),
            'schedule_time_start' => $this->scheduleTimeStartShort(),
            'schedule_time_end' => $this->scheduleTimeEndShort(),
            'notes' => $this->notes,
            'quizzes' => $this->whenLoaded(
                'quizzes',
                fn () => StudentCourseQuizSummaryResource::collection($this->quizzes)
            ),
        ];
    }
}
