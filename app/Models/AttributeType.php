<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeType extends Model
{
    protected $fillable = ['category', 'value', 'label', 'display_type'];
}
