<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Newsletter\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends ApiController
{
    public function index(Request $request)
    {
        $subscribers = NewsletterSubscriber::query()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where('email', 'like', '%' . $request->search . '%')
            )
            ->whereNull('unsubscribed_at')
            ->latest('subscribed_at')
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'subscribers' => NewsletterSubscriberResource::collection($subscribers),
            'pagination' => [
                'current_page' => $subscribers->currentPage(),
                'last_page' => $subscribers->lastPage(),
                'per_page' => $subscribers->perPage(),
                'total' => $subscribers->total(),
            ],
        ]);
    }
}
