<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'users';
$route['404_override'] = 'error404';
$route['translate_uri_dashes'] = FALSE;


$route['users']['GET']="users/users";
$route['users/id/(:num)']['GET']="users/users/id/$1";


$route['users']['POST']="users/users";


