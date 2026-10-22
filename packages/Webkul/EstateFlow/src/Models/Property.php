<?php
namespace Webkul\EstateFlow\Models;
use Illuminate\Database\Eloquent\Model;
class Property extends Model { protected $table='estateflow_properties'; protected $guarded=['id']; protected $casts=['facts'=>'array','active'=>'boolean']; }
