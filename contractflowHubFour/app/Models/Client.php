<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\HubspotSnapshot;

class Client extends Model 
{
    protected $fillable = [
        'name',
        'email',
        'hubspot_id',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo( Company::class );
    }

    public function deals()
    {
        return $this->hasMany(HubspotSnapshot::class, 'portal_id', 'hubspot_id');
    }
}