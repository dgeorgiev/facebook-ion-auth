<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Settings.
| -------------------------------------------------------------------------
*/
$config['app_id'] 		= ''; 		// Your app id
$config['app_secret'] 	= ''; 		// Your app secret key
$config['scope'] 		= 'email'; 	// custom permissions check - http://developers.facebook.com/docs/reference/login/#permissions
$config['redirect_uri'] = ''; 		// url to redirect back from facebook. If set to '', site_url('') will be used