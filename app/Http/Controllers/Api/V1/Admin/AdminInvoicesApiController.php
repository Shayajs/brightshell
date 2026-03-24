<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInvoicesApiController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorizeAdmin();

        $invoices = Invoice::with('company:id,name')
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(25);

        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $invoice = Invoice::create($this->validated($request));

        return response()->json(['data' => ['id' => $invoice->id]], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorizeAdmin();

        $invoice->load('company');

        return response()->json(['data' => $this->invoicePayload($invoice)]);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeAdmin();

        $invoice->update($this->validated($request));

        return response()->json(['message' => 'Facture mise à jour.', 'data' => ['id' => $invoice->id]]);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorizeAdmin();

        $invoice->delete();

        return response()->json(['message' => 'Facture archivée.']);
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        abort_unless($u && ($u->isAdmin() || $u->hasRole('admin')), 403);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'number' => ['required', 'string', 'max:50'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'amount_ht' => ['required', 'numeric', 'min:0'],
            'tva_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,sent,paid,cancelled'],
            'label' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /** @return array<string, mixed> */
    private function invoicePayload(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'company_id' => $invoice->company_id,
            'company' => $invoice->company ? ['id' => $invoice->company->id, 'name' => $invoice->company->name] : null,
            'amount_ht' => $invoice->amount_ht,
            'tva_rate' => $invoice->tva_rate,
            'status' => $invoice->status,
            'label' => $invoice->label,
            'issued_at' => $invoice->issued_at?->toDateString(),
            'due_at' => $invoice->due_at?->toDateString(),
            'paid_at' => $invoice->paid_at?->toDateString(),
            'notes' => $invoice->notes,
        ];
    }
}
