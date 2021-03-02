<?php

namespace App\Models\Globals;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'global_documents';
    protected $fillable = [
        'type_document',
    ];

}
