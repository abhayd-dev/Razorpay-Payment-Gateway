<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

protected $fillable = [
    'name', 
    'email', 
    'order_id',
     'payment_id', 
     'amount', 
     'status', 
     'signature',
      'invoice_downloaded'
];

}
