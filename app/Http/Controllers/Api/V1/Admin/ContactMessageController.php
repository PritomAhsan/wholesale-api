<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Support\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactMessageController extends ApiController
{
    public function index(Request $request)
    {
        $messages = ContactMessage::query()
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('business_email', 'like', '%' . $request->search . '%')
                        ->orWhere('message', 'like', '%' . $request->search . '%');
                })
            )
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'messages' => ContactMessageResource::collection($messages),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function show(string $uuid)
    {
        $message = ContactMessage::where('uuid', $uuid)->firstOrFail();

        return $this->success([
            'message' => new ContactMessageResource($message),
        ]);
    }

    public function updateStatus(Request $request, string $uuid)
    {
        $message = ContactMessage::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'resolved'])],
        ]);

        $message->update($data);

        return $this->success([
            'message' => new ContactMessageResource($message->fresh()),
        ], 'Status updated.');
    }
}
