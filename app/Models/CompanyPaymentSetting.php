<?php

namespace Crater\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'payment_manager',
        'payment_domain_url',
        'payment_tenant_id',
        'payment_context',
        'payment_status'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function getSettings($company_id)
    {
        return self::where('company_id', $company_id)->first();
    }
} 