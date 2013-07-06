<?php defined('SYSPATH') or die('No direct script access.');

class Controller_User extends Controller_Base {

    public function checklogin($role='login')
    {
        if ($this->auth->logged_in())
        {
            $user =  $this->auth->get_user();

            if ($user->has_role($role) or $user->has_role('admin')) {
                return $user;
            }
            
            // Redirect user to user index page if role does not match
            $this->request->redirect(Route::get('default')
                                    ->uri(array('action'=>'index'))); 
        }

        // Force user to sign in
        $this->request->redirect(Route::get('default')
                                    ->uri(array('action'=>'signin')));
    }

    public function action_index()
    {
        $user = $this->checklogin();

        $control = ($user->has_role('admin')) ? 'admin' : 'employee';
        $control = ($user->has_role('manager')) ? 'employer' : $control;

        $this->request->redirect(Route::get('default')
                                  ->uri(array('action' => 'index',
                                              'controller'=>$control)));
    }

    public function action_signin()
    {
        $this->template->page = 'Signin';

        // Redirect to the index page if the user is already logged in
        if ($this->auth->logged_in())
        {
            $this->request->redirect(Route::get('default')
                                        ->uri(array('action' => 'index')));
        }

        // No login error by default
        $error = FALSE;

        // Check if the form was submitted
        if ($_POST)
        {
            // See if user checked 'Stay logged in'
            $remember = isset($_POST['remember']) ? TRUE : FALSE;

            // Try to log the user in
            if (! $this->auth->login($_POST['username'], $_POST['password'], $remember))
            {
                // There was a problem logging in
                $error = TRUE;
            }

            // Redirect to the index page if the user was logged in successfully
            if ($this->auth->logged_in())
            {
                $this->request->redirect(Route::get('default')->uri(array('action' => 'index')));
            }
        }

        // Set template title
        $this->template->title = 'Sign In';

        // Display the 'login' template
        $this->template->content = View::factory('user/signin')
            ->set('error', $error);
    }

    public function action_logout()
    {
        // Log the user out if he is logged in
        if ($this->auth->logged_in())
        {
            $this->auth->logout();
        }

        // Redirect to the index page
        $this->request->redirect(Route::get('default')->uri(array('action' => 'signin')));
    }

    public function action_signup()
    {
        // There are no errors by default
        $errors = FALSE;

        // Create an instance of Model_Auth_User
        $user = Jelly::factory('user');

        // Check if the form was submitted
        if ($_POST)
        {
            /**
             * Load the $_POST values into our model.
             * 
             * We use Arr::extract() and specify the fields to add
             * by hand so that a malicious user can't do (for example)
             * `$_POST['roles'][] = 2;` and make themselves an administrator.
             */
            // Add the user's client id
            $cid = $this->getClientID($_POST);

            $user->set(Arr::extract($_POST, array(
                'username', 'password', 'password_confirm'
            )));

            // Add the 'login' role to the user model
            $user->add('roles', 1);
            
            // add manager role
            if ($_POST['account_type']=='manager') {
                $user->add('roles',3); 
            } else {
                $user->add('roles',4); 
            }
        

            try
            {
                // Try to save our user model
                $user->save();

                $this->connect();
                mysql_query("UPDATE users set client_id='$cid' where id=$user->id");

                // Redirect to the index page
                $this->request->redirect(Route::get('default')->uri(array('action' => 'signin')));
            }
            // There were errors saving our user model
            catch (Validate_Exception $e)
            {
                // Load custom error messages from `messages/forms/user/signup.php`
                $errors = $e->array->errors('forms/user/signup');
            }
        }

        // Set template title
        $this->template->title = 'Sign Up';

        // Display the 'register' template
        $this->template->content = View::factory('user/signup')
            ->set('user', $user)
            ->set('errors', $errors);
    }

    private function getClientID($params)
    {
        if ($params['account_type']=='manager') {
            $cid = $params['company_id'];
            $sql = "SELECT id from clients WHERE entitykey='$cid'"; 
        } else {
            $value = ($params['email']=='') ? $params['phone_number'] : $params['email'];
            $entity = ($params['email']=='') ? 'phone' : 'email';
            $sql="SELECT client_id from attributes_clients 
                where attribute='$entity' and value='$value'"; 
        }    

        $this->connect();
        $res = mysql_query($sql);
        $num = mysql_num_rows($res);
        $row = ($num>0) ? mysql_fetch_row($res) : null;
        return ($row==null) ? null : $row[0];
    }
    
    public function action_change_password()
    {        
        // Redirect to the index page if the user is not logged in
        if (! $this->auth->logged_in())
        {
            $this->request->redirect(Route::get('default')->uri(array('action' => 'index')));
        }
        
        // There are no errors by default
        $errors = FALSE;
        
        // Check if the form was submitted
        if ($_POST)
        {
            // Retrieve current user
            $user = $this->auth->get_user();
            
            // Load the $_POST values into our model.
            $user->set(Arr::extract($_POST, array(
                'password', 'password_confirm'
            )));
            
            try
            {
                // Try to save our user model
                $user->save();

                // Redirect to the index page
                $this->request->redirect(Route::get('default')->uri(array('action' => 'index')));
            }
            // There were errors saving our user model
            catch (Validate_Exception $e)
            {
                // Load custom error messages from `messages/forms/user/signup.php`
                $errors = $e->array->errors('forms/user/signup');
            }
        }
        
        $this->template->title = 'Change Password';

        // Display the 'change_password' template
        $this->template->content = View::factory('user/change_password')
            ->set('errors', $errors);
    }

} // End Controller_User
