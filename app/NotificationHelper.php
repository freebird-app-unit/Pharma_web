<?php

namespace App;
use App\NotificationLog;

class NotificationHelper
{
	public static function sendNotification($reg_ids, $message, $title, $sender_id, $sendor_type, $reciever_id, $reciever_type, $fcm_token = null) {
		
		$notification = array('title' =>$title , 'body' => $message, 'sound' => 'default');
		$arrayToSend = array('registration_ids' => $reg_ids, 'notification' => $notification,'priority'=>'high');
		$json = json_encode($arrayToSend);
		
		$url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAAKIqNu8Q:APA91bEJSvjmr9TiUjAtQRc1PosKmb3nqRqQULAFUXHnujLmTw4zLmiSLD27gFffQeqxSR7U75JXUO-V65WIcMKorV7OjZ2boepBanPFwPFnxBEyCp7Uv0OwMVjnhMHp1ib_GtFiEwI8',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
		
		$res = json_decode($result);
		
        for ($x = 0; $x < count($reciever_id); $x++) {
			$status = isset($res->results[$x]->message_id) ? 1 : 0;
			
            $notification = new NotificationLog();
            $notification->sender_id=$sender_id;
            $notification->sender_type=$sendor_type;
            $notification->fcm_token = $fcm_token[$x];
            $notification->receiver_id=$reciever_id[$x];
            $notification->reciever_type=$reciever_type;
            $notification->message=$message;
            $notification->status=$status;
            $notification->created_at=date('Y-m-d H:i:s');
            $notification->updated_at=date('Y-m-d H:i:s');
            $notification->save();
        }

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);

        
        return $result; 
	}
	
	public static function sendNotificationUser($reg_ids, $message, $title, $sender_id, $sendor_type, $reciever_id, $reciever_type, $fcm_token = null) {
		
        $notification = array('title' =>$title , 'body' => $message, 'sound' => 'default');
		$arrayToSend = array('registration_ids' => $reg_ids, 'notification' => $notification,'priority'=>'high');
		$json = json_encode($arrayToSend);
		
		$url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAAl25oxFs:APA91bG5CBSlEjVS_42u4Kt3JIZZYmWbfEb-ZjfQtXbgqLzZZbWcmmkvxrsroWxNN9JNuNdcBGwNAUzPZx14wp1B9UjQS_Js-YDbFrCLBRZCtm9RmAGrd8-RpJRV7S8TR0V_E3Tf98_c',
            'Content-Type: application/json'
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
		
		// dd($result);
		
		$res = json_decode($result);
		$status = isset($res->success) ? 1 : 0;
		
        $notification = new NotificationLog();
        $notification->sender_id=$sender_id;
        $notification->sender_type=$sendor_type;
        $notification->fcm_token = $fcm_token;
        $notification->receiver_id=$reciever_id;
        $notification->reciever_type=$reciever_type;
        $notification->message=$message;
        $notification->status=$status;
        $notification->created_at=date('Y-m-d H:i:s');
        $notification->updated_at=date('Y-m-d H:i:s');
        $notification->save();

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
          return $result; 
	}
	
	public static function sendNotificationDeliveryboy($reg_ids, $message, $title, $sender_id, $sendor_type, $reciever_id, $reciever_type, $fcm_token = null) {
		
		$notification = array('title' =>$title , 'body' => $message, 'sound' => 'default');
		$arrayToSend = array('registration_ids' => $reg_ids, 'notification' => $notification,'priority'=>'high');
		$json = json_encode($arrayToSend);
		
		$url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAA-mRxROI:APA91bGgKa1Znu-pnOUQlnBVEX65jC-O6N1aNZK26c7owecQsogxjyFKy2S4Fb7-p0CxBUETphRrgoH9c2tb90OCUu-iGJJq7TQ0PHyLBCRM3Bsz0NNjJqxTIQ0gF16l98rXEGFJ9qN5',
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
		
		// dd($result);
		$res = json_decode($result);
		$status = isset($res->success) ? 1 : 0;

        $notification = new NotificationLog();
        $notification->sender_id=$sender_id;
        $notification->sender_type=$sendor_type;
        $notification->fcm_token = $fcm_token;
        $notification->receiver_id=$reciever_id;
        $notification->reciever_type=$reciever_type;
        $notification->message=$message;
        $notification->status=$status;
        $notification->created_at=date('Y-m-d H:i:s');
        $notification->updated_at=date('Y-m-d H:i:s');
        $notification->save();

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
          return $result; 
	}    

}