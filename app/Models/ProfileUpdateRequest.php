<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileUpdateRequest extends Model
{
    protected $table      = 'profile_update_requests';
    protected $primaryKey = 'id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id', 'employeeId', 'employeeName', 'field',
        'oldValue', 'newValue', 'reason', 'status',
        'submittedDate', 'reviewedBy', 'reviewDate',
    ];

    protected $casts = [
        'submittedDate' => 'datetime',
        'reviewDate'    => 'datetime',
    ];
}