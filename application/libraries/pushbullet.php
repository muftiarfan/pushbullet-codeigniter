<?php if (!defined('BASEPATH')) exit('No direct access allowed.');

class Pushbullet {

/**
* Name:  Push Bullet
*
* Version: 1.0
*
* Author: Mufti Arfan Farooqi
*         @maf626
*
*
* Location: http://github.com/muftiarfan/pushbullet-codeigniter
*
* Created:  18 June 2015
*
* Description:  Pushbullet library for CodeIgniter, basically skeleton for actual PushBullet composer package
* To make it a little easier to use with code igniter. 
*
* Requirements: Composer,Pushbullet for PHP(https://github.com/ivkos/Pushbullet-for-PHP) PHP5 or above, CodeIgniter
*
*/

	public function init(){

		$pb = new Pushbullet\Pushbullet('YOUR-API-KEY');
		Pushbullet\Connection::setCurlCallback(function ($curl) {
    // Get a CA certificate bundle here:
    // https://raw.githubusercontent.com/bagder/ca-bundle/master/ca-bundle.crt
    //curl_setopt($curl, CURLOPT_CAINFO, 'E:/xampp/api.del.icio.us.crt');

    // Not recommended! Makes communication vulnerable to MITM attacks:
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
});
		return $pb;
	}

	public function queue($title,$body,$url='',$type='link'){
		$CI =& get_instance();

		$data = array(
			'title'=>$title,
			'body'=>$body,
			'url'=>$url,
			'type'=>$type,
			'status'=>0,
			'created_at'=>time(),
			'updated_at'=>time()
			);
		$CI->db->insert('pushbullet_notifications',$data);
	}

	public function process(){
		$CI =& get_instance();

		$query = $CI->db->query("SELECT * FROM pushbullet_notifications WHERE status=0 ORDER BY ID DESC LIMIT 5"); //process 5 at time...
		$query = $query->result();
		$pb = $this->init();		
		foreach($query as $push){
			switch($push->type){

				case 'link':
				$send = $pb->channel('osmthemes')->pushlink($push->title,$push->url,$push->body);
				break;

				case 'add_contact':
				//get email from body col and name from title col :D
				//check if its existent in PB api.
				$exists = $this->check_contact($push->body,$this->get_username($push->body)); 
				if($exists==TRUE){
					$send = TRUE;
				}else{
					$send = $pb->createContact($push->title, $push->body);
				}
				break;

				case 'send_contact': //used for only notification purpose no link nothing :)
				//get email from url here :P
				$exists = $this->check_contact($push->url,$this->get_username($push->url));
				///first check if this user is in pushbullet contacts list, else firsr add new queue to add contact using check_contact()
				if($exists==TRUE){
					$send = $pb->contact($push->url)->pushNote($push->title, $push->body);
				}else{
					$send = FALSE;
				}
				break;
			}
			if($send){
				$CI->db->query("UPDATE pushbullet_notifications SET status=1,updated_at='".time()."',processed_at='".time()."' WHERE ID='$push->ID'");
			}
		}
	}
	public function get_username($email){
		//it was my app dependent method, modifiy it according to your app to get username of the user for Pushbullet contact naming.
		$CI =& get_instance();
		$query = $CI->db->query("SELECT * FROM users WHERE email='$email' LIMIT 1");
		$query = $query->row_array();
		return $query['username'];
	}
	public function check_contact($email,$username=''){
		$CI =& get_instance();
		$pb = $this->init();
		$contacts = $pb->getContacts();
		foreach($contacts as $contact){
			$arr[] = $contact->email;
		}

		if(in_array($email, $arr)){
			//contact exists in Pushbullet API ^_^
			return TRUE;
		}else{
			//else we add in db to process to create new user ^_^
			 //first check if already such entry exists for the user
			//workaround for duplicate entry of add_contact when calling this method.. :D
			$query = $CI->db->query("SELECT * FROM pushbullet_notifications WHERE title='$username' AND body='$email'");
			if($query->num_rows()>0){
			}else{
				$this->queue($username,$email,'','add_contact');
			}
		}
	}

}