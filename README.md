facebook-ion-auth
=================

Facebook login working with [CodeIgniter Ion Auth](http://benedmunds.com/ion_auth/)

## Requirement

- [CodeIgniter Ion Auth](http://benedmunds.com/ion_auth/)

## Installation

- Copy `config/facebook_ion_auth.php` to `application/config/facebook_ion_auth.php`.
- Copy `libraries/Facebook_ion_auth.php` to `application/libraries/Facebook_ion_auth.php`.
- Configure your Facebook API settings in `application/config/facebook_ion_auth.php` for
    - `app_id` - Your app id
    - `app_scret` - Your app secret key
    - `scope` - custom permissions check - http://developers.facebook.com/docs/reference/login/#permissions
    - `fields` - fields to retrieve from Facebook; if set to `''`, default is `id,first_name,last_name`; See https://developers.facebook.com/docs/graph-api/reference/user
    - `redirect_uri` - url to redirect back from facebook. If set to `''`, `site_url('')` will be used

## Example Usage

Assuming that you have installed [CodeIgniter Ion Auth](http://benedmunds.com/ion_auth/), add this in `application/config/autoload.php`.

    $autoload['libraries'] = array('ion_auth', 'Facebook_ion_auth');

Create `application/core/MY_AuthController.php` and put this code into the file

    <?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    /**
     * AuthController
     */
    class MY_AuthController extends CI_Controller
    {
        public function __construct()
        {
            parent::__construct();

            $this->load->config('ion_auth', true);

            if (uri_string() != 'auth/login') {
                $this->_is_login();
            }
        }

        private function _is_login()
        {
            if (!$this->ion_auth->logged_in()) {
                redirect('auth/login');
            }
        }
    }

To auto-load all files in `application/core/`, add this code in `application/config/config.php`.

    /**
    | -------------------------------------------------------------------
    |  Native Auto-load
    | -------------------------------------------------------------------
    |
    | Nothing to do with config/autoload.php, this allows PHP autoload to work
    | for base controllers and some third-party libraries.
    |
    */
    function __autoload($class)
    {
        if(strpos($class, 'CI_') !== 0) {
            require(APPPATH . 'core/'. $class . '.php');
        }
    }

Extends the default controller `application/controllers/Welcome.php` to `MY_AuthController.php` for authentication check.

    class Welcome extends MY_AuthController {
        // ....
    }

Create a new controller file `application/controllers/Facebook_login.php` with the following code:

    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    class Facebook_login extends CI_Controller
    {
        /**
         * Index Page for this controller.
         * You will be redirected to the facebook login page
         */
        function index()
        {
            $this->facebook_ion_auth->login();
        }

        /**
         * Controller that is redirected back from facebook after login
         */
        public function action()
        {
            $code = $this->input->get('code');
            if ($code) {
                $this->facebook_ion_auth->login();
                redirect('/');
            } else {
                redirect('auth/login');
            }
        }
    }

In `application/views/auth/login.php`, add "Login with Facebook" button using the Facebook_login controller created above.

    <a href="<?php echo site_url('facebook_login'); ?>">Login with Facebook</a>

Update `application/config/facebook_ion_auth.php` for `redirect_uri`.

    $config['redirect_uri'] = site_url('facebook_login/action'); // url to redirect back from facebook.

Then, when you access your application in the browser, you will see the login form with facebook login button.
