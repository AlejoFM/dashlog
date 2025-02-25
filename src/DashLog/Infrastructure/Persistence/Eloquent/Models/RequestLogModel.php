<?php

namespace AledDev\DashLog\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLogModel extends Model
{
    protected $table = 'request_logs';
    
    protected $fillable = [
        'id',
        'method',
        'url',
        'headers',
        'body',
        'ip',
        'status_code',
        'response_headers',
        'response_body',
        'duration',
        'cookies',
        'session',
        'stack_trace',
        'user_agent',
        'user_id',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;
    
} 