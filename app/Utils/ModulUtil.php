<?php

namespace App\Utils;
use App\Models\Order;
use App\Models\OrderPayment;

class ModulUtil {

	public function orderPayment($order, $payments=[]){

		return true;


	}

	public function orderstatus($order){
		$amount=$order->final_amount;
		$paid=$order->payments->sum('amount');

		if ($paid >= $amount) {
			$status='paid';
		}else if($paid ==0){
			$status='due';
		}else{
			$status='partial';
		}
		$order->payment_status=$status;
		$order->save();
		return true;

	}
	
	public function orderDue($order){

		$amount=$order->final_amount;
		$paid=$order->payments->sum('amount');

		$due=$amount-$paid;
		return $due;

	}

}