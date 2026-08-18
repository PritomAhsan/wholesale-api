<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /** @var Supplier[] */
    public array $suppliers = [];

    /** @var User[] */
    public array $customers = [];

    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@bulkare.com'],
            [
                'first_name' => 'Bulkare',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $superAdmin->syncRoles(['Super Admin']);

        $admin = User::updateOrCreate(
            ['email' => 'ops@bulkare.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Ops',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $admin->syncRoles(['Admin']);

        $supplierDefs = [
            ['company' => 'Meridian Electronics Trading', 'type' => 'wholesaler', 'contact' => 'James Chen', 'region' => 'Shenzhen, China'],
            ['company' => 'Atlas Industrial Supply Co.', 'type' => 'distributor', 'contact' => 'Robert Klein', 'region' => 'Hamburg, Germany'],
            ['company' => 'Coastal Textiles Manufacturing', 'type' => 'manufacturer', 'contact' => 'Priya Nair', 'region' => 'Tiruppur, India'],
            ['company' => 'Summit Home Goods Exporters', 'type' => 'exporter', 'contact' => 'Wei Zhang', 'region' => 'Ningbo, China'],
            ['company' => 'Prime Packaging Solutions', 'type' => 'manufacturer', 'contact' => 'Lucas Moreira', 'region' => 'Sao Paulo, Brazil'],
            ['company' => 'Northgate Office Supplies', 'type' => 'wholesaler', 'contact' => 'Emma Wilson', 'region' => 'Manchester, UK'],
            ['company' => 'Bloom Beauty Manufacturing', 'type' => 'manufacturer', 'contact' => 'Yuki Tanaka', 'region' => 'Osaka, Japan'],
            ['company' => 'Harvest Foods Trading', 'type' => 'distributor', 'contact' => 'Ahmed Farouk', 'region' => 'Cairo, Egypt'],
        ];

        foreach ($supplierDefs as $i => $def) {
            $slugBase = strtolower(str_replace(' ', '.', explode(' ', $def['contact'])[0] . '.' . explode(' ', $def['company'])[0]));
            $email = $slugBase . '@' . strtolower(str_replace([' ', '.', ','], '', explode(' ', $def['company'])[0])) . '.com';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => explode(' ', $def['contact'])[0],
                    'last_name' => explode(' ', $def['contact'])[1] ?? '',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]
            );
            $user->syncRoles(['Supplier']);

            $supplier = Supplier::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $def['company'],
                    'business_type' => $def['type'],
                    'contact_person' => $def['contact'],
                    'email' => $email,
                    'phone' => '+1' . rand(2000000000, 9999999999),
                    'website' => 'https://' . strtolower(str_replace([' ', ',', '.'], '', $def['company'])) . '.example.com',
                    'registration_number' => 'REG-' . strtoupper(substr(md5($def['company']), 0, 8)),
                    'tax_number' => 'TAX-' . strtoupper(substr(md5($def['company'] . 'tax'), 0, 8)),
                    'description' => "{$def['company']} is a verified {$def['type']} based in {$def['region']}, supplying wholesale buyers worldwide with quality-inspected inventory and flexible bulk order terms.",
                    'fulfillment_region' => $def['region'],
                    'typical_lead_time' => [3, 5, 7, 10, 14][$i % 5] . ' business days',
                    'commission_rate' => [8, 10, 10, 12, 9, 10, 11, 10][$i],
                    'logo' => 'https://picsum.photos/seed/bulkare-logo-' . ($i % 10 + 1) . '/300/300',
                    'banner' => 'https://picsum.photos/seed/bulkare-banner-' . ($i % 10 + 1) . '/1200/300',
                    'status' => 'approved',
                    'approved_at' => now()->subDays(rand(30, 200)),
                    'approved_by' => $superAdmin->id,
                ]
            );

            $this->suppliers[] = $supplier;
        }

        // One pending and one rejected supplier application, so the
        // admin approvals queue isn't empty during the demo.
        $pendingUser = User::updateOrCreate(
            ['email' => 'newseller@example.com'],
            [
                'first_name' => 'Daniel',
                'last_name' => 'Reyes',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $pendingUser->syncRoles(['Supplier']);
        Supplier::updateOrCreate(
            ['user_id' => $pendingUser->id],
            [
                'company_name' => 'Reyes Auto Parts Trading',
                'business_type' => 'distributor',
                'contact_person' => 'Daniel Reyes',
                'email' => 'newseller@example.com',
                'phone' => '+15551234567',
                'description' => 'New applicant awaiting review.',
                'fulfillment_region' => 'Manila, Philippines',
                'status' => 'pending',
            ]
        );

        $rejectedUser = User::updateOrCreate(
            ['email' => 'declined@example.com'],
            [
                'first_name' => 'Olga',
                'last_name' => 'Petrova',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $rejectedUser->syncRoles(['Supplier']);
        Supplier::updateOrCreate(
            ['user_id' => $rejectedUser->id],
            [
                'company_name' => 'Petrova Import Export',
                'business_type' => 'wholesaler',
                'contact_person' => 'Olga Petrova',
                'email' => 'declined@example.com',
                'phone' => '+15559876543',
                'description' => 'Rejected — incomplete registration documents.',
                'fulfillment_region' => 'Warsaw, Poland',
                'status' => 'rejected',
            ]
        );

        $customerDefs = [
            ['Michael', 'Turner', 'Retail Solutions LLC'],
            ['Aisha', 'Khan', 'Khan General Trading'],
            ['Carlos', 'Mendes', 'Mendes Distribution'],
            ['Linda', 'Park', 'Park Wholesale Mart'],
            ['Thomas', 'Becker', 'Becker Import Co.'],
            ['Fatima', 'Al-Sayed', 'Al-Sayed Trading House'],
            ['Kevin', 'O\'Brien', "O'Brien Supply Chain"],
            ['Grace', 'Lee', 'Lee Commerce Group'],
            ['Samuel', 'Osei', 'Osei Retail Network'],
            ['Nina', 'Kowalski', 'Kowalski & Partners'],
            ['Diego', 'Fernandez', 'Fernandez Bulk Buyers'],
            ['Hannah', 'Schmidt', 'Schmidt Warehousing'],
            ['Victor', 'Alves', 'Alves Trading Co.'],
            ['Sophie', 'Martin', 'Martin Retail Group'],
            ['Ravi', 'Patel', 'Patel Import House'],
            ['Elena', 'Popescu', 'Popescu Distribution'],
            ['Marcus', 'Johnson', 'Johnson Wholesale Inc.'],
            ['Yuna', 'Kim', 'Kim General Store'],
            ['Isaac', 'Adeyemi', 'Adeyemi Trading Ltd'],
            ['Clara', 'Rossi', 'Rossi Retail Solutions'],
        ];

        foreach ($customerDefs as $i => [$first, $last, $company]) {
            $email = strtolower($first . '.' . preg_replace('/[^a-zA-Z]/', '', $last)) . '@example.com';

            $customer = User::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'avatar' => 'https://picsum.photos/seed/bulkare-avatar-' . ($i % 10 + 1) . '/200/200',
                ]
            );
            $customer->syncRoles(['Customer']);
            $this->customers[] = $customer;
        }
    }
}
