<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Facebook_ion_auth {

	/*
		Library for login with facebook and create a ion_auth compatibility session. 

		author: Daniel Georgiev
		website: http://dgeorgiev.biz
	*/

	public function __construct() {

		// get Codeigniter instance
	    $this->CI =& get_instance();

		// config
		$this->app_id = ""; // your app id
		$this->app_secret = ""; // your app secret key
		$this->my_url = site_url(''); // url to redirect back from facebook
		$this->scope = 'email'; // custom permissions check - http://developers.facebook.com/docs/reference/login/#permissions
	}

    public function login() {

    	// null at first
		$code = $this->CI->input->get('code');
		
		// if is not set go make a facebook connection
		if(!$code) {

			// create a unique state
	   		$this->CI->session->set_userdata('state', md5(uniqid(rand(), TRUE)));

	   		// redirect to facebook oauth page
	   		$url_to_redirect =  "https://www.facebook.com/dialog/oauth?client_id=" 
	       						.$this->app_id
	       						."&redirect_uri=".urlencode($this->my_url)
	       						."&state=".$this->CI->session->userdata('state').'&scope='.$this->scope;

	       	redirect($url_to_redirect);

	   	} else {

	   		// check if session state is equal to the returned state

			if($this->CI->session->userdata('state') && ($this->CI->session->userdata('state') === $this->CI->input->get('state'))) {


				$token_url = "https://graph.facebook.com/oauth/access_token?"
			       . "client_id=" . $this->app_id . "&redirect_uri=" . urlencode($this->my_url)
			       . "&client_secret=" . $this->app_secret . "&code=" . $code;

				$response = file_get_contents($token_url);
				
				$params = null;

				parse_str($response, $params);

				$this->CI->session->set_userdata('access_token', $params['access_token']);

				$graph_url = "https://graph.facebook.com/me?access_token=".$params['access_token'];

				$user = json_decode(file_get_contents($graph_url));

				// check if this user is already registered
				if(!$this->CI->ion_auth_model->identity_check($user->email)){
					$name = explode(" ", $user->name);
					$register = $this->CI->ion_auth->register($user->username, 'facebookdoesnothavepass123^&*%', $user->email, array('first_name' => $name[0], 'last_name' => $name[1]));
				} else {
					$login = $this->CI->ion_auth->login($user->email, 'facebookdoesnothavepass123^&*%', 1);
				}

				return true;
		    }
		    else {
		   		return false;
		    }
	    }
    }
}

/* End of file Facebook_ion_auth.php */