<?php

namespace Database\Seeders;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Seeder;

class NewsletterSeeder extends Seeder
{
    public function run(): void
    {
        $emails = [
            'subscriber1@example.com', 'subscriber2@example.com', 'subscriber3@example.com',
            'buyer.news@example.com', 'procurement@retailsolutions.com', 'imports@beckerimport.com',
            'deals@leecommerce.com', 'updates@parkwholesale.com', 'contact@osei-retail.com',
            'news@mendesdist.com',
        ];

        foreach ($emails as $i => $email) {
            NewsletterSubscriber::updateOrCreate(
                ['email' => $email],
                [
                    'topics' => [['deals', 'new_arrivals'], ['deals'], ['new_arrivals', 'rfq_tips'], ['deals', 'new_arrivals', 'rfq_tips']][$i % 4],
                    'frequency' => ['weekly', 'twice_monthly', 'monthly'][$i % 3],
                    'subscribed_at' => now()->subDays(rand(5, 200)),
                    'unsubscribed_at' => $i === 9 ? now()->subDays(2) : null,
                ]
            );
        }
    }
}
