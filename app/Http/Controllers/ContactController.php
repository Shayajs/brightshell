<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Services\MarkdownRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(Request $request): View
    {
        $type = (string) $request->query('type', ContactMessage::TYPE_GENERAL);
        if (! array_key_exists($type, ContactMessage::typeChoices())) {
            $type = ContactMessage::TYPE_GENERAL;
        }

        return view('contact.index', [
            'activeType' => $type,
            'prefillUser' => $request->user(),
        ]);
    }

    public function store(StoreContactMessageRequest $request, MarkdownRenderer $markdown): RedirectResponse
    {
        if ($request->isHoneypotTriggered()) {
            return redirect()
                ->route('contact')
                ->with('success', 'Message envoyé. Merci de m’avoir écrit !');
        }

        $data = $request->validated();
        $type = $request->resolvedType();

        $payload = [
            'user_id' => $request->user()?->id,
            'type' => $type,
            'status' => ContactMessage::STATUS_OPEN,
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'subject' => $data['subject'] ?? null,
            'reference' => $data['reference'] ?? null,
            'project_title' => $data['project_title'] ?? null,
            'project_kind' => $data['project_kind'] ?? null,
            'budget_range' => $data['budget_range'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'body' => $data['body'],
            'body_html' => $type === ContactMessage::TYPE_PROJECT
                ? $markdown->renderSafe($data['body'])
                : null,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ];

        $message = DB::transaction(function () use ($payload, $request, $type): ContactMessage {
            $message = ContactMessage::create($payload);

            if ($type === ContactMessage::TYPE_PROJECT && $request->hasFile('attachments')) {
                foreach ((array) $request->file('attachments', []) as $file) {
                    if (! $file || ! $file->isValid()) {
                        continue;
                    }
                    $stored = $file->store('contact-attachments/'.$message->id, 'local');
                    $message->attachments()->create([
                        'disk' => 'local',
                        'path' => $stored,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize() ?: 0,
                    ]);
                }
            }

            return $message;
        });

        try {
            $recipient = (string) (config('brightshell.contact_recipient') ?: config('mail.from.address'));
            if ($recipient !== '') {
                Mail::to($recipient)->send(new ContactMessageReceived($message->load('attachments')));
            }
        } catch (\Throwable $e) {
            Log::warning('contact.mail_failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('contact')
            ->with('success', 'Message envoyé. Je reviens vers vous très vite !');
    }

    public function markdownPreview(Request $request, MarkdownRenderer $markdown): JsonResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:25000'],
        ]);

        $html = $markdown->renderSafe((string) ($data['body'] ?? ''));

        return response()->json(['html' => $html]);
    }
}
