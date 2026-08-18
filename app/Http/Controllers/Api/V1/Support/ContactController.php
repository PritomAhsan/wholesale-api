<?php

namespace App\Http\Controllers\Api\V1\Support;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Support\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactController extends ApiController
{
    public function store(StoreContactMessageRequest $request)
    {
        $data = $request->validated();

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {

            $attachmentPath = $request->file('attachment')->store(
                'contact-attachments',
                'public'
            );

        }

        $contactMessage = ContactMessage::create([

            'topic' => $data['topic'],

            'name' => $data['name'],

            'business_email' => $data['business_email'],

            'account_email' => $data['account_email'] ?? null,

            'reference_number' => $data['reference_number'] ?? null,

            'message' => $data['message'],

            'attachment_path' => $attachmentPath,

            'status' => 'open',

        ]);

        return $this->success(
            ['uuid' => $contactMessage->uuid],
            'Your support request has been submitted.',
            201
        );
    }
}
