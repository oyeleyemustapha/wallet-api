<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Wallets extends RestController {

    function __construct()
    {
        parent::__construct();
        $this->load->model('wallets_model');
        $this->load->model('users_model'); 
        $this->load->helper('string');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->helper(['jwt', 'authorization']); 
    }

    private function verify_request(){
        $headers = $this->input->request_headers();
        $token = $headers['Authorization'];
        try {
            $data = AUTHORIZATION::validateToken($token);
            if ($data === false) {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                $this->response($response, $status);
                exit();
            } 
            else {
                return $data;
                if($this->users_model->verify_key($data->key)){
                    return $data;
                }
                else{

                    $status = parent::HTTP_UNAUTHORIZED;
                    $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
                    $this->response($response, $status);
                    exit();
                }
            }
        } catch (Exception $e) {
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
        }
    }


    //FETCH WALLETS
    public function wallets_get($id=null){
        $this->verify_request();
        $wallet_no=trim($id);
        if($wallet_no==null){
            $wallets=$this->wallets_model->get_wallets('all');
            if(!$wallets){
                $this->response( [
                    'status' => false,
                    'message' => 'No wallets found'
                ], 404 ); 
            }
            else{
                $this->response($wallets, 200);
            }
        }
        else{
            $wallet=$this->wallets_model->get_wallets($wallet_no);
            if($wallet){
                $this->response($wallet, 200);
            }
            else{
                 $this->response( [
                    'status' => false,
                    'message' => 'No Wallet found'
                ], 404 ); 
            }
        }    
    }


    //CREATE WALLET
    public function create_wallet_post(){
        $this->verify_request();
        $this->form_validation->set_rules('user_no', 'User no', 'required|is_unique[wallets.USER_NO]', array('is_unique' => 'This user has a wallet already'));
        if($this->form_validation->run()){
            $wallet_data=array(
                'WALLET_NO' => random_string('numeric', 6),
                'USER_NO'=> trim($this->post('user_no'))
            );

            if($this->wallets_model->create_wallet($wallet_data)){
                $this->response( ['status' => 'sucessful', 'message' => array('info'=>'Wallet has been created', 'wallet_no'=>$wallet_data['WALLET_NO'])], 201);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error creating wallet'], 400);    
            }
        }
        else{
            $error=[];
            if(form_error('user_no')){
                $error[]=form_error('user_no');
            }
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
    }


    // DELETE WALLET
    public function delete_wallet_post(){
        $this->verify_request();
        $this->form_validation->set_rules('wallet_no', 'Wallet Number', 'required');
        if($this->form_validation->run()){
            
            if($this->wallets_model->delete_wallet(trim($this->post('wallet_no')))){
                $this->response( ['status' => 'sucessful', 'message' => array('info'=>'Wallet has been deleted', 'wallet_no'=>trim($this->input->post('wallet_no')))], 200);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error deleting wallet'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('wallet_no')){
                $error[]=form_error('wallet_no');
            }
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
    }


    //CREDIT WALLET
    public function credit_wallet_post(){
        $this->verify_request();
        $this->form_validation->set_rules('wallet_no', 'Wallet Number', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
        if($this->form_validation->run()){

            $data=array(
                'WALLET_NO'=>trim($this->input->post('wallet_no')),
                'AMOUNT'=> trim($this->input->post('amount'))
            );

            $wallet_log=array(
                'WALLET_NO'=> $data['WALLET_NO'],
                'TYPE'=>'Credit',
                'AMOUNT'=> $data['AMOUNT']
            );
            $result=$this->wallets_model->credit_wallet($data);
            
            if($result){
                $this->wallets_model->log_wallet($wallet_log);
                $this->response( ['status' => 'sucessful', 'message' => array('info'=>'Wallet has been credited', 'wallet_no'=>$data['WALLET_NO'], 'wallet_balance'=>$result)], 200);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error crediting wallet'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('wallet_no')){
                $error[]=form_error('wallet_no');
            }


            if(form_error('amount')){
                $error[]=form_error('amount');
            }
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
    }    


    //DEBIT WALLET
    public function debit_wallet_post(){
        $this->verify_request();
        $this->form_validation->set_rules('wallet_no', 'Wallet Number', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
        if($this->form_validation->run()){

            $data=array(
                'WALLET_NO'=>trim($this->input->post('wallet_no')),
                'AMOUNT'=> trim($this->input->post('amount'))
            );

            $wallet_log=array(
                'WALLET_NO'=> $data['WALLET_NO'],
                'TYPE'=>'Debit',
                'AMOUNT'=> $data['AMOUNT']
            );
            $result=$this->wallets_model->debit_wallet($data);
            
            if($result){
                $this->wallets_model->log_wallet($wallet_log);
                $this->response( ['status' => 'sucessful', 'message' => array('info'=>'Wallet has been debited', 'wallet_no'=>$data['WALLET_NO'], 'wallet_balance'=>$result)], 200);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error debiting wallet'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('wallet_no')){
                $error[]=form_error('wallet_no');
            }


            if(form_error('amount')){
                $error[]=form_error('amount');
            }
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }
    }


    //FETCH WALLET LOGS
    public function logs_get($wallet=null){
        $this->verify_request();
        if($wallet==null){
            $logs=$this->wallets_model->logs('all');
            if(!$logs){
                $this->response( [
                    'status' => false,
                    'message' => 'No logs found'
                ], 404 ); 
            }
            else{
                $this->response($logs, 200);
            }
        }
        else{
            $logs=$this->wallets_model->logs($wallet);
            if($logs){
                $this->response($logs, 200);
            }
            else{
                 $this->response( [
                    'status' => false,
                    'message' => 'No logs found'
                ], 404 ); 
            }
        }    
    }

    //LOG PAYMENT ACTIVITIES
    public function log_payment_post(){
        $this->verify_request();
        $this->form_validation->set_rules('wallet', 'Wallet Number', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required|numeric');
        $this->form_validation->set_rules('reference', 'Reference Number', 'required');
        $this->form_validation->set_rules('status', 'Status', 'required');
        if($this->form_validation->run()){
            $data=array(
                'REF_NUMBER'=>trim($this->input->post('reference')),
                'WALLET_NO'=>trim($this->input->post('wallet')),
                'AMOUNT'=> trim($this->input->post('amount')),
                'STATUS'=>trim($this->input->post('status'))
            );
            if($this->wallets_model->log_payment($data)){
                $this->response( ['status' => 'sucessful', 'message' => 'Payment logged' ], 201);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error logging Payment'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('wallet')){
                $error[]=form_error('wallet');
            }


            if(form_error('reference')){
                $error[]=form_error('reference');
            }

            if(form_error('status')){
                $error[]=form_error('status');
            }


            if(form_error('amount')){
                $error[]=form_error('amount');
            }
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }
    }

    //FETCH PAYMENT LOGS
    public function payments_get($wallet=null){
        $this->verify_request();
        if($wallet==null){
            $logs=$this->wallets_model->payment_logs('all');
            if(!$logs){
                $this->response( [
                    'status' => false,
                    'message' => 'No logs found'
                ], 404 ); 
            }
            else{
                $this->response($logs, 200);
            }
        }
        else{
            $logs=$this->wallets_model->payment_logs($wallet);
            if($logs){
                $this->response($logs, 200);
            }
            else{
                 $this->response( [
                    'status' => false,
                    'message' => 'No logs found'
                ], 404 ); 
            }
        }    
    }





}


?>