<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;
    protected $table = 'slots';

    // Define the relationship with Service model
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

}
