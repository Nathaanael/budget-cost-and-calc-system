<?php

namespace App\Models;

use App\Models\Concerns\HasRandomFiveDigitId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noodle extends Model
{
    use HasRandomFiveDigitId, SoftDeletes;

    protected $fillable = ['code', 'description', 'unit', 'created_by', 'updated_by'];
}
