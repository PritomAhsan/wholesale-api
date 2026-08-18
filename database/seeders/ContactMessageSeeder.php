<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            ['topic' => 'Order Issue', 'name' => 'Michael Turner', 'email' => 'michael.turner@retailsolutions.com', 'message' => 'My order #ORD-4F2A9C1D has not shipped after 5 business days. Can someone check the status?', 'status' => 'open'],
            ['topic' => 'Billing Question', 'name' => 'Aisha Khan', 'email' => 'aisha.khan@khantrading.com', 'message' => 'I was charged twice for the same invoice reference. Please advise on refund process.', 'status' => 'open'],
            ['topic' => 'Become a Seller', 'name' => 'Daniel Reyes', 'email' => 'newseller@example.com', 'message' => 'I submitted a supplier application last week and wanted to check on the review timeline.', 'status' => 'open'],
            ['topic' => 'General Inquiry', 'name' => 'Grace Lee', 'email' => 'grace.lee@leecommerce.com', 'message' => 'Do you support bulk RFQ submissions for multiple product categories in one request?', 'status' => 'resolved'],
            ['topic' => 'Technical Support', 'name' => 'Thomas Becker', 'email' => 'thomas.becker@beckerimport.com', 'message' => 'The checkout page fails to load shipping options for addresses outside the US.', 'status' => 'open'],
            ['topic' => 'Partnership', 'name' => 'Ravi Patel', 'email' => 'ravi.patel@patelimport.com', 'message' => 'Interested in a bulk-buyer partnership program if one exists for high-volume accounts.', 'status' => 'resolved'],
        ];

        foreach ($messages as $msg) {
            ContactMessage::updateOrCreate(
                ['business_email' => $msg['email'], 'message' => $msg['message']],
                [
                    'topic' => $msg['topic'],
                    'name' => $msg['name'],
                    'account_email' => $msg['email'],
                    'reference_number' => 'REF-' . strtoupper(substr(md5($msg['email']), 0, 8)),
                    'status' => $msg['status'],
                ]
            );
        }
    }
}
