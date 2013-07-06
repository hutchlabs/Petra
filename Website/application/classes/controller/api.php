<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api extends Controller_Base {

    public function before()
    {
        $this->auto_render = FALSE;
        parent::before();
    }

    public function action_balance_history()
    {
        $client_id = $_REQUEST['params']['id'];
        
        // Get the session instance
        //$session = Session::instance();
        //$key = 'client-balancehistory-'.$client_id; 
        //$data = $session->get($key,null);
        //if ($data != null) { return json_encode($data); }
        
        $client = Jelly::select('client')->load($client_id);
        $bal = $client->client_balance();

        # get dates
        $m = array();
        $sd = (date('Y')-1).'-'.date('m').'-'.date('d');
        $alldates = $this->createDateRangeArray($sd, date('Y-m-d'));

        foreach($alldates as $date) { 
                $datef = date_format(date_create($date),'jS F Y');
                array_push($m, $datef);
        }
        
        $fd = array();
        for($j = 2; $j < 5; $j++)
        {
            $tier = 'Tier '.$j;
            $tier1 = 'Tier'.$j;
            $fd[$tier1] = array();
            $s = sizeof($m);
            for($i = 0; $i < $s; $i++) {
                $t = strtotime($m[$i]) * 1000;
                array_push($fd[$tier1], array($t,$bal[$tier][$m[$i]]['balance']));
            }
        }
        $fd['dates'] = $m;

        //$session->set($key,$fd);

        echo json_encode($fd);
    }

 
    public function action_schemes()
    {
        $com_id = $_REQUEST['employer_id'];

        # get funds and their company names
        $sql = "SELECT fc.fund_id, f.name
                  FROM funds_clients fc
                  JOIN clients c ON c.id = fc.client_id
                  JOIN funds f ON f.id = fc.fund_id
                 WHERE fc.client_id IN (".DB::Expr($com_id).")";
        $pc = DB::query(Database::SELECT, $sql, TRUE)->execute();
         
        $fundcomp = array();
        $fids = '';
        foreach($pc as $i) { 
            @$fundcomp[$i['fund_id']] = $i['name']; 
            $fids .= $i['fund_id'].",";
        }
        $fids = preg_replace('/,$/','',$fids);

        # get funds and holders (employees)
        $sql = "SELECT fc.fund_id, fc.client_id, c.name
                  FROM funds_clients fc
                  JOIN clients c ON c.id = fc.client_id
                 WHERE fc.client_id NOT IN (".DB::Expr($com_id).") 
                   AND c.name NOT LIKE '%Unassigned%'
                   AND fc.fund_id IN (".DB::Expr($fids).")";
        $pe = DB::query(Database::SELECT, $sql, TRUE)->execute();

        $fundemp = array();
        foreach($pe as $i) { 
            if(in_array($i['fund_id'],array_keys($fundemp))) {
                array_push($fundemp[$i['fund_id']],array('name'=>$i['name'],
                                                      'id'=>$i['client_id']));

            } else {
                $fundemp[$i['fund_id']] = array();
                array_push($fundemp[$i['fund_id']],array('name'=>$i['name'],
                                                      'id'=>$i['client_id']));
            }
            
        }

        echo json_encode(array('schemes'=>$fundcomp, 
                               'holders'=>$fundemp));
    }


    public function action_employee_fundinfo()
    {
            $client_id = $_REQUEST['params']['id'];
            $tier = $_REQUEST['params']['tier'];
            $tier = ($tier=='Tier 4') ? 'Tier 3 Post' : $tier;
            $tier = ($tier=='Tier 3') ? 'Tier 3 Pre' : $tier;
        
            $client = Jelly::select('client')->load($client_id);
            $pi = $client->price_index();
            $holdings = $client->holdings();

            $data = $cats = $res = array();
            foreach($holdings as $h) 
            {
                    $f = Jelly::select('fund')->load($h['fund_id']);
                    $prices = (isset($pi[$h['fund_id']]))
                            ? $pi[$h['fund_id']]
                            : array();

                    foreach($prices as $date => $price)
                    {
                            if ($tier=='Tier 2')
                            {
                                if (preg_match("/$tier.*?/",$f->name))  {
                                    $res[$f->name][$date] = $price;
                                }
                            } else {

                            if (preg_match('/(.*?)Employee/',$f->name) && preg_match("/$f->tier/",$tier))  {
                                    $res[$f->name][$date] = $price;
                                }
                            }
                    }
            }
        
            foreach($res as $f => $p)
            {
                    $v = array();
                    foreach($p as $d => $c)
                    {
                        $k = date_format(date_create($d),'jS F Y');
                        if (!in_array($k, $cats)) {array_push($cats, $k); }
                        $t = strtotime($d) * 1000;
                        array_push($v, array($t,floatval($c)));
                    }
                    array_push($data, array('name'=> $f, 'data'=> $v));
            }

            echo json_encode(array('categories'=> $cats,'data'=> $data));
        }

        public function action_employer_fundinfo()
        {
            $client_ids = explode(',',$_REQUEST['params']['id']);
            $tier = $_REQUEST['params']['tier'];
            $tier = ($tier=='Tier 4') ? 'Tier 3 Post' : $tier;
            $tier = ($tier=='Tier 3') ? 'Tier 3 Pre' : $tier;

            $data = $cats = $res = array();
            foreach($client_ids as $id)
            {
                $client = Jelly::select('client')->load($id);
                $pi = $client->price_index();
                $holdings = $client->holdings();

                foreach($holdings as $h) 
                {
                        $f = Jelly::select('fund')->load($h['fund_id']);
                        $prices = (isset($pi[$h['fund_id']]))
                                ? $pi[$h['fund_id']]
                                : array();

                        foreach($prices as $date => $price)
                        {
                            if ($tier=='Tier 2')
                            {
                                if (preg_match("/$tier/",$f->tier))  {
                                    $res[$f->name][$date] = $price;
                                }
                            } else {
                                if (preg_match('/(.*?)Employer/',$f->name) && preg_match("/$f->tier/",$tier))  {
                                    $res[$f->name][$date] = $price;
                                }
                            }
                        }
                }
            }

            foreach($res as $f => $p)
            {
                    $v = array();
                    foreach($p as $d => $c)
                    {
                        $k = date_format(date_create($d),'jS F Y');
                        if (!in_array($k, $cats)) { array_push($cats,$k);}
                        $t = strtotime($d) * 1000;
                        array_push($v, array($t,floatval($c)));
                    }
                    array_push($data, array('name'=> $f, 'data'=> $v));
            }

            echo json_encode(array('categories'=> $cats,'data'=> $data));
    }

    public function action_verify_companyid()
    {
            $id = $_REQUEST['company_id'];

            if ($id == 'noone') { echo json_encode(true); return; }

            $this->connect();
            $sql = "SELECT COUNT(*) 
                      FROM clients 
                     WHERE entitykey = '$id' AND type='employer'";
            $res = mysql_query($sql);
            $row = mysql_fetch_row($res);
            $msg = "The id cannot be found.";
            $val = ($row[0]>0 || $id=='noone') ? true:$msg;
            echo json_encode($val);
    }

    public function action_verify_username()
    {
            $this->connect();
            $username = $_REQUEST['username'];
            $sql = "SELECT COUNT(*) FROM users where username = '$username'";
            $res = mysql_query($sql);
            $row = mysql_fetch_row($res);
            $msg = "The username has already been taken. Please try another one.";
            $val = ($row[0]>0) ? $msg: true;
            echo json_encode($val);
            
    }

    public function action_verify_password()
    {
        $pass = $_REQUEST['password'];
        $val = (strlen($pass) >= 6 && preg_match('/\d/',$pass) && preg_match('/[a-zA-z]/',$pass) )
                ? true
                : 'Password must be at least 6 characters long and contain '.
                     'at least one number and one character.';
        echo json_encode($val);
    }

    public function action_verify_number()
    {
        $number = $_REQUEST['phone_number']; 

        if ($number == '0123456789') { echo json_encode(true); return; }

        if(preg_match("/^0[0-9]{9}$/", $number)) {
            $this->connect();
            $sql = "SELECT COUNT(*) 
                      FROM attributes_clients 
                     WHERE attribute='phone' AND value='$number'";
            $res = mysql_query($sql);
            $row = mysql_fetch_row($res);
            $val = ($row[0]<=0) 
                 ? 'This number was not found for any account. Please '.
                   'enter the same number provided when the fund '.
                   'was created' : true;
        } else {
            $val = "Number must be 10 digits starting with a '0'";
        }

        echo json_encode($val);
    }

    public function action_verify_email()
    {
        $email = $_REQUEST['email']; 

        if ($email=='client@company.com') { echo json_encode(true);return;} 
        $this->connect();
        $sql = "SELECT COUNT(*) 
                  FROM attributes_clients 
                 WHERE attribute='email' AND value='$email'";
        $res = mysql_query($sql);
        $row = mysql_fetch_row($res);
        $c = $row[0];
        $val = ($c <= 0) ? 'No account was found for this email address. '.
                           'Please enter the same email address provided '.
                           'when the fund was created'
                          : true;
        echo json_encode($val);
    }

    public function action_verify_code()
    {
        $c = $_REQUEST['code'];
        $e = $_REQUEST['entity'];
        
        $this->connect();
        $sql = "SELECT code, IF(expires < NOW(),'expired','valid') 
                  FROM registerationcodes 
                 WHERE entity='$e' and code='$c'";
        $res = mysql_query($sql);
        $num = mysql_num_rows($res);
        if ($num > 0)
        {
            $row = mysql_fetch_row($res);
            $val = ($row[1]=='expired') ? 'Your code has expired. Please generate a new one and re-enter it.' : true;
        } else {
            $val = 'Invalid code. Please enter the correct code.';
        }
        echo json_encode($val);
    }

    private function get_code($entity)
    {
        $this->connect();
        $sql = "SELECT code 
                  FROM registerationcodes 
                 WHERE expires > NOW() AND entity='$entity'";
        $res = mysql_query($sql);
        $numrows = mysql_num_rows($res);

        if ($numrows > 0) { 
            $r = mysql_fetch_row($res);
            $code = $r[0];
        } else {
            $code = uniqid();
            $sql = "delete from registerationcodes where entity='$entity'";
            $res = mysql_query($sql);
            $sql = "Insert into registerationcodes (entity,code,expires) values('$entity','$code',DATE_ADD(NOW(), INTERVAL 60 MINUTE))";
            $res = mysql_query($sql);
        }            

        return $code;
    }

    public function action_create_code()
    {
        $type = $_REQUEST['type'];
        $entity = $_REQUEST['entity'];
        $code = $this->get_code($entity);

        if ($type=='phone')
        {
            $v= $this->SendSMS('Registration code: '.$code, $entity);
        } else {
            $sql = "SELECT ac.attribute, ac.value, c.name 
                      FROM attributes_clients ac
                      JOIN clients c ON c.id = ac.client_id 
                     WHERE c.entitykey='$entity'";
            $res = mysql_query($sql);
            $numrows = mysql_num_rows($res);

            $v = false; 
            if ($numrows > 0) { 
                $va = false;
                while(list($at, $e, $n) = mysql_fetch_row($res))
                {
                    if ($at=='phone')
                    {
                        $a= $this->SendSMS('Registration code: '.$code,$e);
                        $va = ($a==true) ? true : $va; 
                    }
                    if ($at=='email')
                    {
                        $msg="Dear $n,\r\n\r\nYour registration code is: ".
                            "$code. This code will expire in an hour.\r\n\r\n".
                                "Regards,\r\nPetra Trust team";
                        $a=$this->SendEmail($msg,
                                   'Petra Trust account registration code',
                                   $e);
                        $va = ($a==true) ? true : $va; 
                    }
                }
                $v = $va;
            } else {
                $v='Cannot find contact information. Please contact Petra Trust.';
            }
        }
        $val = ($v===true) ? 'true' : 'Message could not be sent: '.$v; 
        echo json_encode($val);
    }

    public function action_sende()
    {
           $msg="Dear client,\r\n\r\nYour registration code is: ".
                "1234\r\nThis code will expire in an hour.\r\n\r\n".
                "Regards,\r\nPetra Trust team";
           $a=$this->SendEmail($msg,'Petra Trust account registration code', 'dhutchful@gmail.com');
           echo ($a==true) ? 'Sent' : $a; 

    }

    public function action_employee_resetpasslink()
    {
        $this->connect();
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $c = Jelly::select('client')->load($id);
        if (is_numeric($c->user->id))
        {
            $id = $c->user->id;
            $sql = "DELETE from users where id=$id";
            $sql2 = "DELETE from roles_users where user_id=$id";
            $sql3 = "DELETE from user_tokens where user_id=$id";
            mysql_query($sql);
            mysql_query($sql2);
            mysql_query($sql3);
        }
        $this->request->redirect(Route::get('default')
                                  ->uri(array('action' => $type,
                                              'controller'=>'admin'))); 
    }

    public function action_employee_resetpass()
    {
        $this->connect();
        $id = $_REQUEST['params']['id'];
        $c = Jelly::select('client')->load($id);
        if (is_numeric($c->user->id))
        {
            $id = $c->user->id;
            $sql = "DELETE from users where id=$id";
            $sql2 = "DELETE from roles_users where user_id=$id";
            $sql3 = "DELETE from user_tokens where user_id=$id";
            mysql_query($sql);
            mysql_query($sql2);
            mysql_query($sql3);
            echo json_encode('done');
        } else {
            echo json_encode('not found');
        }
    }
} 
