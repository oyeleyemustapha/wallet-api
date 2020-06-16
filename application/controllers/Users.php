<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

class Users extends RestController {

    function __construct()
    {
        parent::__construct();
        $this->load->model('users_model'); 
        $this->load->helper('string');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');
    }



    // public function users_get(){
    //     $faker = Faker\Factory::create();

    //     for ($i = 0; $i < 30; $i++) {
    //       $user_no=random_string('numeric', 6);
    //             $user_data=array(
    //                 'NAME' => $faker->name,
    //                 'PHONE'=> $faker->e164PhoneNumber,
    //                 'EMAIL'=> $faker->email,
    //                 'USER_NO'=> $user_no
    //             );


    //             //RANDOMLY GENERATED PASSWORD
    //             $generated_password=random_string('alnum', 10);
    //             $user_login=array(
    //                 'USER_NO'=>$user_no,
    //                 'USERNAME'=> $user_data['EMAIL'],
    //                 'PASSWORD'=> md5($generated_password)
    //             );

    //             $this->users_model->add_user($user_data);
    //             $this->users_model->create_login($user_login); 
            

    //     }



         
           


    // }


    public function users_get(){
        $user_no=$this->get('id');
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


    public function users_post(){
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
        

            if($this->users_model->add_user($user_data)){

                        //CREATE USER LOGIN
                $this->users_model->create_login($user_login); 
                $this->response( ['status' => 'sucessful', 'message' => 'User has added'], 201); 
                       
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
}


?>