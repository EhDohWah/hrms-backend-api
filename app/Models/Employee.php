<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Employment;

class Employee extends Model
{
    use HasFactory;

    //
    protected $fillable = [
        'staff_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'status',
        'religion',
        'birth_place',
        'identification_number',
        'passport_number',
        'bank_name',
        'bank_branch',
        'bank_account_name',
        'bank_account_number',
        'office_phone',
        'mobile_phone',
        'height',
        'weight',
        'permanent_address',
        'current_address',
        'stay_with',
        'military_status',
        'marital_status',
        'spouse_name',
        'spouse_occupation',
        'father_name',
        'father_occupation',
        'mother_name',
        'mother_occupation',
        'driver_license_number',
        'created_by',
        'updated_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employments()
    {
        return $this->hasMany(Employment::class, 'employee_id');
    }
}
