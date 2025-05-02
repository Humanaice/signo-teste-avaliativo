<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['title', 'start_date', 'end_date'];

    public function pollOptions()
    {
        return $this->hasMany(PollOption::class);
    }
}
