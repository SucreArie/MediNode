<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sync_logs extends Model
{
    /** @use HasFactory<\Database\Factories\SyncLogsFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'node_id',
        'table_name',
        'record_id',
        'operation',
        'data',
        'sync_status',
        'error_message',
        'synced_at',
    ];
}
