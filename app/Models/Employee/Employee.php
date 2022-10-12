<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'fname',
        'lname',
        'melli_code',
        'birthdate',
        'hometown',
        'father_name',
        'gender',
        'marriage_status',
        'home_phone',
        'phone',
        'address',
        'employee_code',
        'entry_date',
        'email'
    ];
}
