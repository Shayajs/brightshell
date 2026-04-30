<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactAttachment;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactMessagesController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');

        $query = ContactMessage::query()
            ->with(['user', 'attachments'])
            ->orderByDesc('id');

        if (array_key_exists($type, ContactMessage::typeChoices())) {
            $query->where('type', $type);
        }
        if (array_key_exists($status, ContactMessage::statusChoices())) {
            $query->where('status', $status);
        }

        $messages = $query->paginate(20)->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'currentType' => $type,
            'currentStatus' => $status,
            'unreadCount' => ContactMessage::query()->unread()->count(),
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->load(['user', 'attachments']);

        if ($contactMessage->status === ContactMessage::STATUS_OPEN) {
            $contactMessage->update(['status' => ContactMessage::STATUS_READ]);
        }

        return view('admin.contact-messages.show', [
            'message' => $contactMessage,
        ]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(ContactMessage::statusChoices()))],
        ]);

        $contactMessage->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function downloadAttachment(ContactMessage $contactMessage, ContactAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->contact_message_id === $contactMessage->id, 404);

        $disk = Storage::disk($attachment->disk ?: 'local');
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, $attachment->original_name);
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        foreach ($contactMessage->attachments as $attachment) {
            try {
                Storage::disk($attachment->disk ?: 'local')->delete($attachment->path);
            } catch (\Throwable $e) {
                // best effort
            }
        }

        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'Message supprimé.');
    }
}
