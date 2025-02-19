<?php

namespace DashLog\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLogModel extends Model
{
    protected $table = 'request_logs';
    
    protected $fillable = [
        'method',
        'url',
        'ip',
        'user_id',
        'duration',
        'status',
        'request_data',
        'response_data'
    ];
} 