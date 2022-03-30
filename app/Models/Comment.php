<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;



class Comment extends Model
{
    use HasFactory, SoftDeletes;
    use RevisionableTrait;
    
    protected $fillable = [
        'comment','tax_invoice_id', 'user_id','read_by', 'status'
    ];

   
}
