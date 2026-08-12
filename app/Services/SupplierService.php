<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupplierService
{
    public function register(User $user, array $data): Supplier
    {
        if ($user->supplier()->exists()) {
            throw ValidationException::withMessages([
                'supplier' => [
                    'You already own a supplier account.'
                ]
            ]);
        }

        $supplier = Supplier::create([

            'user_id' => $user->id,

            'uuid' => (string) Str::uuid(),

            'company_name' => $data['company_name'],

            'company_slug' => Str::slug($data['company_name']),

            'business_type' => $data['business_type'],

            'contact_person' => $data['contact_person'],

            'email' => $data['email'],

            'phone' => $data['phone'],

            'website' => $data['website'] ?? null,

            'registration_number' => $data['registration_number'] ?? null,

            'tax_number' => $data['tax_number'] ?? null,

            'description' => $data['description'] ?? null,

            'status' => 'pending',

        ]);

        return $supplier;
    }

    public function approve(Supplier $supplier, User $admin): Supplier
    {
        if ($supplier->status === 'approved') {
            throw ValidationException::withMessages([
                'supplier' => ['Supplier is already approved.'],
            ]);
        }

        $supplier->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        // Access is granted here, at approval — not at application.
        // Until an admin approves, the applicant should not be able
        // to reach any Supplier-gated endpoint.
        $supplier->user->assignRole('Supplier');

        return $supplier->fresh();
    }

    public function reject(
        Supplier $supplier,
        User $admin,
        ?string $reason = null
    ): Supplier {

        $supplier->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        return $supplier->fresh();
    }
}
