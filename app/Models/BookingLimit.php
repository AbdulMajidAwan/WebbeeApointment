<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLimit extends Model
{
    use HasFactory;
    protected $table = 'booking_limits';
    public $timestamps = false;

    // Define the relationship with Service model
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
