<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    protected $fillable = ['option_text', 'votes','poll_id'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }
}
