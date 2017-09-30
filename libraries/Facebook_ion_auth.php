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

		// Load config
		$this->CI->load->config('facebook_ion_auth', TRUE);

		$this->app_id = $this->CI->config->item('app_id', 'facebook_ion_auth');
		$this->app_secret = $this->CI->config->item('app_secret', 'facebook_ion_auth');
		$this->scope = $this->CI->config->item('scope', 'facebook_ion_auth');

		$this->fields = $this->CI->config->item('fields', 'facebook_ion_auth');
		if (empty($this->fields)) {
			$this->fields = 'id,first_name,last_name';
		}

		$this->dummy_password = 'facebookdoesnothavepass123^&*%';

		if($this->CI->config->item('redirect_uri', 'facebook_ion_auth') === '' ) {
			$this->my_url = site_url('');
		} else {
			$this->my_url = $this->CI->config->item('redirect_uri', 'facebook_ion_auth');
		}

	}

	public function login() {

		// null at first
		$code = $this->CI->input->get('code');

		// if is not set go make a facebook connection
		if(!$code) {

			// create a unique state
			$this->CI->session->set_userdata('state', md5(uniqid(rand(), TRUE)));

			// redirect to facebook oauth page
			$url_to_redirect = "https://www.facebook.com/dialog/oauth?client_id="
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

				$graph_url = "https://graph.facebook.com/me?access_token=".$params['access_token'].'&fields='.$this->fields;

				$user = json_decode(file_get_contents($graph_url));

				if (empty($user->email)) {
					// if user did not permit email address
					return false;
				} else {
					// check if this user is already registered
					if(!$this->CI->ion_auth_model->identity_check($user->email)){
						$username = 'user'.$user->id; // generate username with facebook id
						$register = $this->CI->ion_auth->register($username, $this->dummy_password, $user->email, array(
							'first_name'  => $user->first_name,
							'last_name'   => $user->last_name,
							// if you want to add more facebook-related fields, you will have to add them in the user table
							//'fb_id'       => $user->id,
							//'fb_info'     => serialize($user),
						));
					}
					$login = $this->CI->ion_auth->login($user->email, $this->dummy_password, 1);
				}

				return true;
			} else {
				return false;
			}
		}
	}
}

/* End of file Facebook_ion_auth.php */
