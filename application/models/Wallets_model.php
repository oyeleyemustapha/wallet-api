<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
 class Wallets_model extends CI_Model{
 	
 	

 	//GET WALLERS	
 	function get_wallets($wallet_no){
 		$this->db->select('wallets.WALLET_NO, wallets.AMOUNT BALANCE, wallets.DATE_CREATED, users.USER_NO, users.NAME OWNER');
 		$this->db->from('wallets');
 		$this->db->join('users', 'users.USER_NO=wallets.USER_NO', 'left');
 		if($wallet_no=='all'){
 			$this->db->order_by('wallets.WALLET_NO', 'DESC');
 		}
 		else{
 			$this->db->where('wallets.WALLET_NO', $wallet_no);
 		}
 		$query= $this->db->get();
 		if ($query->num_rows()>0) {
 			if($wallet_no=='all'){
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


 	//FETCH WALLET LOGS
 	function logs($wallet_no){
 		$this->db->select('wallets_log.WALLET_NO, wallets_log.TYPE,  wallets_log.AMOUNT, wallets_log.DATE');
 		$this->db->from('wallets_log');
 		if($wallet_no=='all'){
 			$this->db->order_by('DATE', 'DESC');
 		}
 		else{
 			$this->db->where('WALLET_NO', $wallet_no);
 			$this->db->order_by('DATE', 'DESC');
 		}
 		$query= $this->db->get();
 		if ($query->num_rows()>0) {
 			return $query->result_array();
 		}
 		else{
 			return false;
 		}
 	}


 	//FETCH PAYMENTS LOGS
 	function payment_logs($wallet){
 		$this->db->select('WALLET_NO, REF_NUMBER, AMOUNT, DATE, STATUS');
 		$this->db->from('payment_logs');
 		if($wallet=='all'){
 			$this->db->order_by('DATE', 'DESC');
 		}
 		else{
 			$this->db->where('WALLET_NO', $wallet);
 			$this->db->order_by('DATE', 'DESC');
 		}
 		$query= $this->db->get();
 		if ($query->num_rows()>0) {
 			return $query->result_array();
 		}
 		else{
 			return false;
 		}
 	}


 	//LOG PAYMENT ACTIVITIES
 	function log_payment($data){
 		if($this->db->insert('payment_logs', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}




 	//CREATE WALLET
 	function create_wallet($data){
 		if($this->db->insert('wallets', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}

 	//LOG WALLET ACTIVITIES
 	function log_wallet($data){
 		if($this->db->insert('wallets_log', $data)){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}

 	//DELETE WALLET
 	function delete_wallet($wallet_no){
 		$this->db->where('WALLET_NO', $wallet_no);
 		if($this->db->delete('wallets')){
 			return true;
 		}
 		else{
 			return false;
 		}
 	}

 	//CREDIT WALLET
 	function credit_wallet($data){
 		$credit_amount=$data['AMOUNT'];
 		$this->db->where('WALLET_NO', $data['WALLET_NO']);
 		$this->db->set('AMOUNT', "AMOUNT+$credit_amount",FALSE);
 		if($this->db->update('wallets')){
 			$this->db->select('AMOUNT');
 			$this->db->from('wallets');
 			$this->db->where('WALLET_NO', $data['WALLET_NO']);
 			$query= $this->db->get();
 			return $query->row()->AMOUNT;
 		}
 		else{
 			return false;
 		}
 	}


 	//DEBIT WALLET
 	function debit_wallet($data){
 		$debit_amount=$data['AMOUNT'];
 		$this->db->where('WALLET_NO', $data['WALLET_NO']);
 		$this->db->set('AMOUNT', "AMOUNT-$debit_amount",FALSE);
 		if($this->db->update('wallets')){
 			$this->db->select('AMOUNT');
 			$this->db->from('wallets');
 			$this->db->where('WALLET_NO', $data['WALLET_NO']);
 			$query= $this->db->get();
 			return $query->row()->AMOUNT;
 		}
 		else{
 			return false;
 		}
 	}
}


?>