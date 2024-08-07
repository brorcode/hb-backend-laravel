<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int id
 * @property string name
 * @property string description
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 */
class Category extends Model
{
    use HasFactory;
}
