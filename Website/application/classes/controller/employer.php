<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Employer extends Controller_User {

    public function action_index()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('manager');

        if ($user->has_role('admin') && isset($_REQUEST['cid']))
        {
            $client = Jelly::select('client')->load($_REQUEST['cid']);
        } else {
            $client = $user->client;
        }

        $clist = '';
        $employees = $client->employees();
        $accounters = $client->usersWithAccounts();

        $currentbal = $client->balance();
        $contribs = $client->contributions();

        # Get a list of employee ids
        foreach($employees as $m) { $clist .= $m->id.','; }
        $clist = preg_replace('/,$/','',$clist);

        # Set tier flags
        $tiers = array('Tier 2'=>0, 'Tier 3'=>0, 'Tier 4'=>0);
        if ($client->has_tier('Tier 2')) { $tiers['Tier 2']=true; } 
        if ($client->has_tier('Tier 3')) { $tiers['Tier 3']=true; } 
        if ($client->has_tier('Tier 4')) { $tiers['Tier 4']=true; } 
        $tierinfo = $client->userTiers();

        $this->template->title = 'Employer Home';
        $this->template->content = View::factory('employer/index') 
                        ->set('employees', $employees)
                        ->set('contribs', $contribs)
                        ->set('currentbal', $currentbal)
                        ->set('tiers', $tiers)
                        ->set('clist', $clist)
                        ->set('accounters', $accounters)
                        ->set('tierinfo', $tierinfo)
                        ->set('user', $user)
                        ->set('client', $client)
                        ->set('logged_in', true);
    }

    public function action_usercontribs()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('manager');

        if ($user->has_role('admin') && isset($_REQUEST['cid']))
        {
            $client = Jelly::select('client')->load($_REQUEST['cid']);
        } else {
            $client = $user->client;
        }

        $worker = Jelly::select('client')->load($_REQUEST['eid']);
        $tierinfo = $worker->userTiers();
        $contribinfo = $client->usercontributions();
        
        $dealinfo = array();
        if (in_array($worker->id,array_keys($contribinfo))){ 
            $dealinfo = $contribinfo[$worker->id]; 
        }

        $di = array();
        $idx = 0;
        foreach($dealinfo as $d => $arr1) 
        { 
            foreach($arr1 as $t => $arr2) 
            { 
                foreach($arr2 as $fid => $i) 
                { 
                    @$di[$d][$idx]['tier'] = $t;
                    @$di[$d][$idx]['scheme'] = $i['name'];
                    @$di[$d][$idx]['employee'] += $i['employee']; 
                    @$di[$d][$idx]['employer'] += $i['employer']; 
                    @$di[$d][$idx]['total'] += $i['total']; 
                    @$di[$d][$idx]['redemption'] += $i['redemption']; 
                }
                $idx++;
            }
        }
        krsort($di);

        $this->template->content = 
		        View::factory('employer/employee_contributions')
								->set('idx', uniqid()) 
								->bind('m', $worker) 
								->bind('tierinfo', $tierinfo) 
								->bind('dealinfo', $di); 
    }


    public function action_contribdetails()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('manager');

        if ($user->has_role('admin') && isset($_REQUEST['cid']))
        {
            $client = Jelly::select('client')->load($_REQUEST['cid']);
        } else {
            $client = $user->client;
        }


        $tier= $_REQUEST['tier'];
        $date= $_REQUEST['date'];

        $employees = $client->employees();
        $schemes = explode(',',$_REQUEST['scheme']);
        $ci = $client->usercontributions($schemes);

        $seen = array();
        $userids = array_keys($ci);
        $contribinfo = array();

        foreach($employees as $m)
        { 
            if (!in_array($m->id, $seen)) {
                if(in_array($m->id, $userids)) 
                { 
                  if(in_array($date, array_keys($ci[$m->id]))) 
                  {   
                      if(in_array($tier, array_keys($ci[$m->id][$date]))) 
                      { 
                         $info = $ci[$m->id][$date][$tier];

                         @$contribinfo[$m->id]['name'] = $m->name;
                         @$contribinfo[$m->id]['employee'] = 0; 
                         @$contribinfo[$m->id]['employer'] = 0; 
                         @$contribinfo[$m->id]['total'] = 0; 
                         @$contribinfo[$m->id]['redemption'] = 0; 
                         
                         foreach($info as $i)
                         {
                             @$contribinfo[$m->id]['employee'] += $i['employee']; 
                             @$contribinfo[$m->id]['employer'] += $i['employer']; 
                             @$contribinfo[$m->id]['total'] += $i['total']; 
                             @$contribinfo[$m->id]['redemption'] += $i['redemption']; 
                         }
                      }
                  }
                }
            }
            array_push($seen, $m->id);
        }

        $this->template->content = 
		        View::factory('employer/_contributiondetails')
								->set('idx', uniqid()) 
								->bind('date', $date) 
								->bind('tier', $tier) 
								->bind('scheme', $schemes) 
								->bind('ci', $contribinfo) 
                                ->set('logged_in', true)
                                ->bind('employees', $employees); 
    }
} // End Controller_Employer
