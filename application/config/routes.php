<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'users';
$route['404_override'] = 'error404';
$route['translate_uri_dashes'] = FALSE;



//ADD USER
$route['users']['POST']="users/users";

//FETCH SPECIFIC USER
$route['users/(:any)']['GET']="users/users/$1";

//FETCH USERS
$route['users']['GET']="users/users";

//UPDATE USER DATA
$route['user']['POST']="users/user";

//UPDATE USER LOGIN DETAILS
$route['user-login']['POST']="users/user_login";

$route['update-login-status']['POST']="users/change_login_status";





$route['grant-access']['POST']="users/grant_access";


$route['wallets']="wallets/wallets";
$route['wallets/(:any)']="wallets/wallets/$1";
$route['create-wallet']="wallets/create_wallet";
$route['delete-wallet']="wallets/delete_wallet";
$route['credit-wallet']="wallets/credit_wallet";
$route['debit-wallet']="wallets/debit_wallet";
$route['logs']="wallets/logs";
$route['logs/(:any)']="wallets/logs/$1";
$route['log-payment']="wallets/log_payment";
$route['payments']="wallets/payments";
$route['payments/(:any)']="wallets/payments/$1";
