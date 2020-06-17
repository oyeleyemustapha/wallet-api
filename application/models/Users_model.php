<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
 class Users_model extends CI_Model{
 	
 	//VERIFY KEY
 	function verify_key($key){
 		$this->db->select('*');
 		$this->db->from('keys');
 		$this->db->where('PUBLIC_KEY', $key);
 		$this->db->or_where('PRIVATE_KEY', $key);
 		$query= $this->db->get();
 		if($query->num_rows()==1){
 			return $query->row();
 		}
 		else{
 			return false;
 		}

 	}

 	function user_log($user_number){
 		$this->db->insert('logs', array('USER_NO'=>$user_number));
 	}


 	//GET LIST OF USERS
 	function get_users($user_no){
 		$this->db->select('users.NAME, users.PHONE, users.EMAIL, users.USER_NO, users.DATE_CREATED, login.USERNAME, login.PASSWORD, login.STATUS');
 		$this->db->from('users');
 		$this->db->join('login', 'users.USER_NO=login.USER_NO', 'left');
 		if($user_no=='all'){
 			
 			$this->db->order_by('users.NAME', 'DESC');
 		}
 		else{
 			$this->db->where('users.USER_NO', $user_no);
 		}
 		$query= $this->db->get();
 		if ($query->num_rows()>0) {
 			if(isset($user_no)){
 				return $query->result_array();
 			}
 			else{
 				return $query->row_array();
 			}
 		}
 		else{
 			return false;
 		}
 	}

 	//ADD USERS
 	function add_user($data){
 		if($this->db->insert('users', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}

 	//CREATE USER LOGIN
 	function create_login($data){
 		if($this->db->insert('login', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}

 	//UPDATE USER DATA
 	function update_user($data){
 		$this->db->where('user_no', $data['USER_NO']);
 		if($this->db->update('users', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}

 	//UPDATE USER LOGIN DATA 
 	function update_login($data){
 		$this->db->where('user_no', $data['USER_NO']);
 		if($this->db->update('login', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}
	
}


?>