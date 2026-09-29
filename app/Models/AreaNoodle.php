<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaNoodle extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = ['code', 'description', 'created_by', 'updated_by'];
}
