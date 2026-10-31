<?php

namespace Webkul\EstateFlow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'estateflow_properties';

    protected $guarded = ['id'];

    protected $casts = [
        'facts' => 'array',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $property): void {
            $property->agency_id = auth()->guard('user')->id();
        });

        static::addGlobalScope('agency', function (Builder $builder): void {
            $agencyId = auth()->guard('user')->id();

            if ($agencyId === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('agency_id'), $agencyId);
        });
    }
}
