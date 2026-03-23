<?php

namespace App\Http\Resources\Api\V1;

use App\Models\StudentCourseQuiz;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentCourseQuiz */
class StudentCourseQuizSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'sort_order' => $this->sort_order,
            'is_published' => $this->is_published,
        ];
    }
}
