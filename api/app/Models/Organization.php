<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $fillable = [
      'organization_name',
      'organization_phone',
      'organization_email',
      'directors_vrb',
      'isk_number',
      'created_by',
      'idemnity_amount',
      'idemnity_expiry'
  ];
    public function users()
    {
      return $this->belongsToMany(User::class, 'organization_users')->with("roles")->withPivot('status');
    }
    public function ReportConsumerusers()
    {
      return $this->belongsToMany(User::class, 'report_consumer_users')->with("roles")->withPivot('status');
    }
}
