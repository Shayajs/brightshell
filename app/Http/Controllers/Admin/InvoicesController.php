<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoicesController extends Controller
{
    public function index(): View
    {
        $invoices = Invoice::with('company')
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(25);

        $totalPaid = Invoice::where('status', 'paid')->sum('amount_ht');

        return view('admin.invoices.index', compact('invoices', 'totalPaid'));
    }

    public function create(): View
    {
        $companies = Company::orderBy('name')->get();
        $nextNumber = Invoice::nextNumber();

        return view('admin.invoices.form', [
            'invoice' => null,
            'companies' => $companies,
            'nextNumber' => $nextNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $invoice = Invoice::create($data);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Facture créée.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load('company');

        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $companies = Company::orderBy('name')->get();

        return view('admin.invoices.form', ['invoice' => $invoice, 'companies' => $companies, 'nextNumber' => $invoice->number]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $invoice->update($this->validated($request));

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Facture archivée.');
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
}
