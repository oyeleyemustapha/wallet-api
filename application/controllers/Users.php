<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Users extends RestController {

    function __construct()
    {
        parent::__construct();
        $this->load->model('users_model'); 
        $this->load->model('wallets_model'); 
        $this->load->helper('string');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');
        $this->load->helper(['jwt', 'authorization']); 
    }



    //LOGIN USER TO GENERATE API KEY AND TOKEN
    public function grant_access_post(){
        $this->form_validation->set_rules('public_key', 'Public Key', 'required');
        if($this->form_validation->run()){

            $private_key=$this->users_model->verify_key($this->input->post('public_key'))->PRIVATE_KEY;
            if($private_key){

                //GENERATE JWT CONATAINING THE PRIVATE KEY AS A PAYLOAD
                $token = AUTHORIZATION::generateToken(['key' => $private_key]);
                $status = parent::HTTP_OK;
                $response = ['status' => $status, 'token' => $token];
                $this->response($response, $status);
            }
            else{
                $this->response(['status' => 'error', 'message' => 'You not authorized.'], 401); 
            }
        }
        else{
            $error=[];
            if(form_error('public_key')){
                $error[]=form_error('public_key');
            }
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
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


    //FETCH USERS
    public function users_get($id=null){
        $this->verify_request();
        $user_no=$id;
        if($user_no==null){
            $users=$this->users_model->get_users('all');
            if(!$users){
                $this->response( [
                    'status' => false,
                    'message' => 'No users were found'
                ], 404 ); 
            }
            else{
                $this->response($users, 200);
            }
        }
        else{
            $user=$this->users_model->get_users($user_no);
            if($user){
                $this->response($user, 200);
            }
            else{
                 $this->response( [
                    'status' => false,
                    'message' => 'No user found'
                ], 404 ); 
            }
        }    
    }


    //ADD USER
    public function users_post(){
        $this->verify_request();
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('phone', 'Phone', 'required');
        $this->form_validation->set_rules('email', 'email', 'required|is_unique[users.EMAIL]', array('is_unique' => 'This email is associated to an account already'));
        if($this->form_validation->run()){
           
            $user_no=random_string('numeric', 6);
            $user_data=array(
                'NAME' => trim($this->post('name')),
                'PHONE'=> trim($this->post('phone')),
                'EMAIL'=> trim($this->post('email')),
                'USER_NO'=> $user_no
            );


            //RANDOMLY GENERATED PASSWORD
            $generated_password=random_string('alnum', 10);
            $user_login=array(
                'USER_NO'=>$user_no,
                'USERNAME'=> $user_data['EMAIL'],
                'PASSWORD'=> md5($generated_password)
            );


            $wallet_data=array(
                'WALLET_NO' => random_string('numeric', 6),
                'USER_NO'=> $user_no
            );
        

            if($this->users_model->add_user($user_data)){
                //CREATE USER LOGIN
                $this->users_model->create_login($user_login); 

                //CREATE WALLET
                $this->wallets_model->create_wallet($wallet_data);
                $this->response( ['status' => 'sucessful', 'message' => array('Info'=>'User has added', 'user_no'=>$user_no, 'wallet_no'=>$wallet_data['WALLET_NO'])], 201);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error adding user'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('name')){
                $error[]=form_error('name');
            }

            if(form_error('email')){
                $error[]=form_error('email');
            }

            if(form_error('phone')){
                $error[]=form_error('phone');
            }
        
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
    }


    //UPDATE USER DATA
    public function user_put(){
        $this->verify_request();
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('phone', 'Phone', 'required');
        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('user_no', 'User No', 'required|numeric');
        if($this->form_validation->run()){
            $user_data=array(
                'NAME' => trim($this->post('name')),
                'PHONE'=> trim($this->post('phone')),
                'EMAIL'=> trim($this->post('email')),
                'USER_NO'=>$this->post('user_no')
            );

            if($this->users_model->update_user($user_data)){
                $this->response( ['status' => 'sucessful', 'message' => 'User data has been updated'], 200);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error updating user data'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('name')){
                $error[]=form_error('name');
            }

            if(form_error('email')){
                $error[]=form_error('email');
            }

            if(form_error('phone')){
                $error[]=form_error('phone');
            }

            if(form_error('user_no')){
                $error[]=form_error('user_no');
            }        
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
    }


    //UPDATE USER LOGIN
    public function user_login_post(){
        $this->verify_request();
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('user_no', 'User No', 'required|numeric');
        if($this->form_validation->run()){
            $user_login=array(
                'USERNAME' => trim($this->post('username')),
                'PASSWORD'=> md5(trim($this->post('password'))),
                'USER_NO'=>$this->post('user_no')
            );

            if($this->users_model->update_login($user_login)){
                $this->response( ['status' => 'sucessful', 'message' => 'User login has been updated'], 200);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error updating user login'], 400);    
            }
        }
        else{
            $error=[];

            if(form_error('username')){
                $error[]=form_error('username');
            }

            if(form_error('password')){
                $error[]=form_error('password');
            }

            if(form_error('user_no')){
                $error[]=form_error('user_no');
            }        
            $this->response(['status' => 'error', 'message' => $error], 400);   
        }  
    }

    //CHANGE THE STATUS OF USER LOGIN
    public function change_login_status_post(){
        $this->verify_request();
        $this->form_validation->set_rules('user_no', 'User No', 'required|numeric');
        if($this->form_validation->run()){
            //FETCH THE CURRENT STATUS OF USER LOGIN
            $user_no=$this->input->post('user_no');
            
            $status=$this->users_model->get_users($user_no)[0]['STATUS'];

            $data=array(
                'USER_NO'=>$user_no
            );

            if($status=="ACTIVE"){
                $data['STATUS']='INACTIVE';
                $msg="User account has been deactivated";
            }
            else{
                $data['STATUS']="ACTIVE";
                $msg="User account has been activated";
            }

            if($this->users_model->update_login($data)){
                $this->response( ['status' => 'sucessful', 'message' => $msg], 200);       
            }
            else{
               $this->response(['status' => 'error', 'message' => 'There is an error updating user account'], 400);    
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



}


?>