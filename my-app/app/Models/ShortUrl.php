<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    //

    protected $fillable = [
        'company_id',
        'created_by',
        'original_url',
        'short_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
