<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeImage extends Model
{
    protected $fillable = [

        'dispute_id',

        'uploaded_by',

        'image',

    ];

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
