<?php

namespace App\Models\Dynasty;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynastyMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'message'
    ];

    public function getMessageTitle()
    {
        return match ($this->type) {
            'requester_confirmation_message' => 'پیام تایید درخواست کننده',
            'reciever_message' => 'پیام دریافت کننده درخواست',
            'reciever_accept_message' => 'پیام تایید پذیرش پیوستن به سلسله',
            'requester_accept_message' => 'پیام ارسالی به درخواست کننده مبنی بر پذیرش درخواست',
            'dynasty_prize' => 'پیام پاداش برای درخواست کننده سلسله',
        };
    }
}
