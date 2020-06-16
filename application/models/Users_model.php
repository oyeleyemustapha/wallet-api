<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
 class Users_model extends CI_Model{
 	
 	//PROCESS LOGIN
 	function process_login($username, $password){
 		$this->db->select('staff.STAFF_NO, staff.NAME, staff.EMAIL, staff.PHONE, staff.ROLE, login.STATUS, login.PASSWORD, login.USERNAME, outlets.OUTLET_ID, outlets.NAME OUTLET');
 		$this->db->from('login');
 		$this->db->join('staff', 'staff.STAFF_NO=login.USER_NO', 'left');
 		$this->db->join('outlets', 'staff.OUTLET=outlets.OUTLET_ID', 'left');
 		$this->db->where('login.USERNAME', $username);
 		$this->db->where('login.PASSWORD', $password);
 		$query= $this->db->get();
 		if ($query->num_rows()==1) {
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

 	 	
}


?>