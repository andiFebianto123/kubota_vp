<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;



class LogBatchProcess extends Model
{
    protected $table = 'log_batch_process';
    
    protected $fillable = [
        'mail_to','mail_cc', 'mail_reply_to','po_num', 'type', 'error_message'
    ];

}
