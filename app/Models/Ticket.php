<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'reciever_id',
        'attachment',
        'status',
        'responser_name',
        'department',
        'importance'
    ];

    public function sender() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reciever() {
        return $this->belongsTo(User::class, 'reciever_id');
    }

    public function responser() {
        return $this->hasMany(TicketResponse::class);
    }

    public function responses() {
        return $this->hasMany(TicketResponse::class);
    }
}
