<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class VendorItem extends Model
{
    use HasFactory;
    use RevisionableTrait;

    protected $table = 'vendor_item';
}
