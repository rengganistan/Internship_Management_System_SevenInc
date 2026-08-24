<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'division', 
        'company',
        'description',
        'background_image', 
        'start_date', 
        'end_date', 
        'city', 
        'brand', 
        'serial_number', 
        'logo1', 
        'logo2', 
        'signature_image1', 
        'signature_image2', 
        'name_signatory1', 
        'name_signatory2', 
        'role1', 
        'role2',
    ];
}
