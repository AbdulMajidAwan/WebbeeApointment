<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $table = 'appointments';



    // Define the relationship with Service model
    public function slot()
    {
        return $this->belongsTo(Slot::class);
    }
}
