<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breaks extends Model
{
    protected $table = 'breaks';
    public $timestamps = false;

    // Define the relationship with Service model
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
