<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class new_orders extends Model
{
    protected $table = 'new_orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'payment_order_id','customer_id','prescription_id','order_number','order_status','order_note','address_id','audio','audio_info','order_type','total_days','reminder_days','pharmacy_id','process_user_type','process_user_id','logistic_user_id','deliveryboy_id','second_attempt_delivery_id','create_datetime','accept_datetime','assign_datetime','pickup_datetime','deliver_datetime','reject_datetime','second_attempt_delivery_datetime','return_datetime','cancel_datetime','rejectby_user','reject_user_id','checking_by','reject_cancel_reason','logistic_reject_reason','leave_neighbour','neighbour_info','is_external_delivery','external_delivery_initiatedby','order_amount','delivery_charges_id','is_delivery_charge_collect','is_amount_collect','is_refund_intiated','return_confirmtime','is_intersection', 'created_at', 'updated_at'
     ];

    public function prescriptions()
    {
        return $this->belongsTo('App\Prescription', 'prescription_id', 'id'); 
    }
    public function pharmacy()
    {
        return $this->hasOne('App\new_pharmacies','id','pharmacy_id');
    }

}
