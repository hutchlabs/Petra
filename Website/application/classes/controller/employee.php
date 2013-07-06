<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Employee extends Controller_User {
        
    public function action_index()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('employee');

        if ($user->has_role('admin') && isset($_REQUEST['cid']))
        {
            $c = Jelly::select('client')->load($_REQUEST['cid']);
        } else {
            $c = $user->client; 
        }

        $tier3b =  str_replace(',','',$c->tier3_balance());
        $dealinfo = $c->dealinfo();

        $this->template->title = 'Employee Home';
        $this->template->content = View::factory('employee/index') 
                        ->set('tier3b', $tier3b)
                        ->set('dealinfo', $dealinfo)
                        ->set('employee', $c)
                        ->set('clist', $c->id)
                        ->set('user', $user)
                        ->set('logged_in', true); 
    }

    public function action_beneficiaries()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('employee');

        if ($user->has_role('admin') && isset($_REQUEST['cid']))
        {
            $c = Jelly::select('client')->load($_REQUEST['cid']);
        } else {
            $c = Jelly::select('client')->load($user->client->id);
        }

        $this->template->title = 'Employee Home';
        $this->template->content = View::factory('employee/beneficiaries') 
                        ->set('employee', $c)
                        ->set('beneficiaries', array())
                        ->set('user', $user)
                        ->set('logged_in', true); 
    }
} // End Controller_Employee
