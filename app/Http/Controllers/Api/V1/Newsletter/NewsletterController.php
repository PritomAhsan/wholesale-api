<?php

namespace App\Http\Controllers\Api\V1\Newsletter;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Newsletter\StoreNewsletterSubscriptionRequest;
use App\Models\NewsletterSubscriber;

class NewsletterController extends ApiController
{
    public function subscribe(StoreNewsletterSubscriptionRequest $request)
    {
        $data = $request->validated();

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            [
                'topics' => $data['topics'] ?? [],
                'frequency' => $data['frequency'] ?? 'twice_monthly',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return $this->success(
            ['uuid' => $subscriber->uuid],
            'If this is a new email address, you are now subscribed.',
            201
        );
    }
}
