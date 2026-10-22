<?php
namespace Webkul\EstateFlow\Models;
use Illuminate\Database\Eloquent\Model;
class Event extends Model { protected $table='estateflow_events'; protected $guarded=['id']; protected $casts=['payload'=>'array','occurred_at'=>'datetime']; }
