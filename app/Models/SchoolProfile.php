<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    protected $fillable = [
        'name',
        'npsn',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'principal_name',
        'principal_nip',
        'description',
    ];
}
