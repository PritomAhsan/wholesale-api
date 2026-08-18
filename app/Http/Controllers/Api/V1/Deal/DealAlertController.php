<?php

namespace App\Http\Controllers\Api\V1\Deal;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Deal\StoreDealAlertSubscriptionRequest;
use App\Models\DealAlertSubscription;

class DealAlertController extends ApiController
{
    public function store(StoreDealAlertSubscriptionRequest $request)
    {
        $data = $request->validated();

        $subscription = DealAlertSubscription::firstOrCreate(
            ['email' => $data['email']],
            ['subscribed_at' => now()]
        );

        return $this->success(
            ['uuid' => $subscription->uuid],
            'You will be notified when new deals go live.',
            201
        );
    }
}
