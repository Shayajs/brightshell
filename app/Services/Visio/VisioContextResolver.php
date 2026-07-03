<?php

namespace App\Services\Visio;

use App\Models\ProjectPriceItem;
use App\Models\StudentSubjectFile;
use App\Models\VisioRoom;
use App\Support\StudentMaterials\StudentMaterialsMarkdownRenderer;
use Illuminate\Support\Facades\Storage;

class VisioContextResolver
{
    public function __construct(
        private readonly StudentMaterialsMarkdownRenderer $markdownRenderer
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function roomContext(VisioRoom $room): array
    {
        $room->loadMissing('project.priceItems');
        $project = $room->project;
        $meta = is_array($room->meta) ? $room->meta : [];

        $priceItems = collect();
        $priceTotals = ['ht' => 0.0, 'ttc' => 0.0];

        if ($project !== null) {
            $priceItems = $project->priceItems;
            $priceTotals = [
                'ht' => round($priceItems->sum(fn (ProjectPriceItem $i) => $i->lineTotalHt()), 2),
                'ttc' => round($priceItems->sum(fn (ProjectPriceItem $i) => $i->lineTotalTtc()), 2),
            ];
        }

        return [
            'room' => [
                'id' => $room->id,
                'slug' => $room->slug,
                'title' => $room->title,
                'status' => $room->status,
            ],
            'project' => $project ? [
                'id' => $project->id,
                'slug' => $project->slug,
                'name' => $project->name,
            ] : null,
            'prices' => [
                'items' => $priceItems->map(fn (ProjectPriceItem $item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'quantity' => $item->quantity,
                    'unit_price_ht' => $item->unit_price_ht,
                    'vat_rate' => $item->vat_rate,
                    'line_total_ht' => $item->lineTotalHt(),
                    'line_total_ttc' => $item->lineTotalTtc(),
                ])->values(),
                'totals' => $priceTotals,
            ],
            'shared_document' => [
                'file_id' => $meta['shared_student_subject_file_id'] ?? null,
                'title' => $meta['shared_document_title'] ?? null,
                'html' => $meta['shared_document_html'] ?? null,
                'updated_at' => $meta['shared_document_updated_at'] ?? null,
            ],
        ];
    }

    public function updateSharedDocument(VisioRoom $room, int $fileId): void
    {
        $file = StudentSubjectFile::query()->with('folder.subject')->findOrFail($fileId);
        abort_unless($file->isMarkdown(), 422, 'Le fichier partagé doit être un document markdown.');
        abort_unless(Storage::disk('local')->exists($file->stored_path), 404);

        $raw = Storage::disk('local')->get($file->stored_path);
        $meta = is_array($room->meta) ? $room->meta : [];
        $meta['shared_student_subject_file_id'] = $file->id;
        $meta['shared_document_title'] = $file->original_name;
        $meta['shared_document_html'] = $this->markdownRenderer->toHtml($raw);
        $meta['shared_document_updated_at'] = now()->toIso8601String();

        $room->forceFill(['meta' => $meta])->save();
    }
}
