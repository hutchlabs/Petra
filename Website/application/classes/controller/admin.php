<?php defined('SYSPATH') or die('No direct script access.');

date_default_timezone_set('Africa/Accra');

class Controller_Admin extends Controller_User {

    public function action_index()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        $this->template->title = 'Admin :: Home';
        $this->template->content = View::factory('admin/index')
                        ->set('ptitle', 'Admin Console')
                        ->set('user', $user)
                        ->set('logged_in', true);
    }

    public function action_view()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        $type = $_REQUEST['type'];
        $cid = $_REQUEST['eid'];

        $control = ($type=='employee') ?  'employee': 'employer';

        $uri = "/$control/index?cid=$cid";
        $this->request->redirect($uri);
    }

    public function action_fundnames()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        $coms = array();
        $sql = "SELECT client_id, fund_id, company, fund, displayname
                  FROM funds_names ORDER BY company";
        $res = DB::query(Database::SELECT, $sql, TRUE)->execute();
        foreach($res as $i)
        {
            if (isset($coms[$i['client_id']])) {
                $info = array('name'=>$i['fund'], 'display'=>$i['displayname']);
                @$coms[$i['client_id']]['funds'][$i['fund_id']] = $info;
            } else {
                $coms[$i['client_id']] =
                    array('name'=>$i['company'],
                        'funds'=>array($i['fund_id']=>array(
                                'name'=>$i['fund'],
                                'display'=>$i['displayname'])));
            }
        }


        $this->template->title = 'Admin :: Manage Fund names';
        $this->template->content = View::factory('admin/fundnames')
                        ->set('ptitle', 'Admin :: Manage Fund Names')
                        ->set('coms', $coms)
                        ->set('user', $user)
                        ->set('logged_in', true);
    }

    public function action_updatefundname()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        $cid = $_REQUEST['cid'];
        $fid = $_REQUEST['fid'];
        $name = addslashes($_REQUEST['name']);
        $sql = "UPDATE funds_names SET displayname='$name'
                 WHERE client_id = $cid AND fund_id=$fid";
        $res = DB::query(Database::UPDATE, $sql, TRUE)->execute();
        $success = ($res) ? 'true' : "Could not update name. Please try again";
        echo json_encode($success);
        exit;
    }

    public function action_employers()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        // Get template variables
        $employers=Jelly::select('client')->companies()->execute();

        $this->template->title = 'Admin :: Manage Employers';
        $this->template->content = View::factory('admin/employers')
                        ->set('ptitle', 'Admin :: Employers')
                        ->set('employers', $employers)
                        ->set('user', $user)
                        ->set('logged_in', true);
    }

    public function action_employees()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        $this->template->title = 'Admin :: Manage Employees';
        $this->template->content = View::factory('admin/employees')
                        ->set('ptitle', 'Admin :: Employees')
                        ->set('user', $user)
                        ->set('logged_in', true);
    }

    public function action_loademployees()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        // Get template variables
        $employees=Jelly::select('client')->individuals()->execute();

        $coms = array();
        $sql = "SELECT IFNULL(com.name,'Unknown') as company, emp.id as id,
                       emp.name, emp.entitykey, IFNULL(u.id,0) as uid
                  FROM `clients` emp
                  LEFT JOIN clients com on com.id = emp.companyid
                  LEFT JOIN users u on u.client_id = emp.id
                 WHERE emp.type='employee'
                   AND emp.name not like '%Unassigned Contributions%'
                 ORDER BY com.name, emp.name";
        $res = DB::query(Database::SELECT, $sql, TRUE)->execute();

        $list = array();

        foreach($res as $i)
        {
            $line = array();
            $name = preg_replace('/^(d|m)(r|s|iss)\.?\s+/i','',$i['name']);
            array_push($line, $i['company']);
            array_push($line, ucwords(strtolower($name)));
            array_push($line, $i['entitykey']);
            $l = '<a href="view?type=employee&eid='.$i['id'].'">View account</a>';
            array_push($line, $l);

            if ($i['uid']>0)
            {
               $l ='<a href="/members/api/employee_resetpasslink?type=employees&id='.$i['uid'].'" class="btn btn-small btn-danger">Reset user account</a>';
            }
            else
            {
               $l = '<span>Online account has not been setup</span>';
            }
            array_push($line, $l);

            array_push($list,$line);
        }

        echo json_encode(array('aaData'=>$list));
        exit;
    }

    public function action_reports_generate()
    {
        if (@sizeof($_REQUEST['schemes']))
        {
            if (@sizeof($_REQUEST['holders']))
            {
                $employer_id = $_REQUEST['adm_employer_id'];
                $schemes = $_REQUEST['schemes'];
                $holders = $_REQUEST['holders'];
                $sd = $_REQUEST['sd'];
                $ed = $_REQUEST['ed'];
                $info = $this->PDFStatements($employer_id,$schemes, $holders, $sd, $ed);
                $mail = (isset($_REQUEST['mail'])) ? $_REQUEST['mail']:'';
                $this->PrintPDF($info,$mail);
                $response = true;
            } else {
                $response = 'Please select at least one employee';
            }
        } else {
              $response = 'Please select at least one scheme';
        }
        echo json_encode($response);
        exit;
    }

    public function action_reports()
    {
        // Redirect to login page if user is not logged in
        $user = parent::checklogin('admin');

        if (isset($_REQUEST['adm_employer_id']))
        {
            if (@sizeof($_REQUEST['schemes']))
            {
                if (@sizeof($_REQUEST['holders']))
                {
                    $employer_id = $_REQUEST['adm_employer_id'];
                    $schemes = $_REQUEST['schemes'];
                    $holders = $_REQUEST['holders'];
                    $sd = $_REQUEST['sd'];
                    $ed = $_REQUEST['ed'];
                    # only generate for first 3 clients
                    $holders = array_slice($holders,0,3);
                    echo $this->PDFStatements($employer_id,$schemes, $holders, $sd, $ed);
                    exit;
                } else {
                    echo 'Please select at least one employee';
                    exit;
                }
            } else {
                    echo 'Please select at least one scheme';
                    exit;
            }
        } else {

            // Get template variables
            $employers=Jelly::select('client')->companies()->execute();
            $employees=Jelly::select('client')->individuals()->execute();

            $emps = array();
            foreach($employers as $e)
            {
                $emps[$e->id] = $e->name .' ('.$e->entitykey.')';
            }

            $sd = date('Y-m-d',
                      mktime(0, 0, 0, date("m")-1, 1, date("Y")));
            $ed = date('Y-m-d',
                       mktime(0, 0, 0, date("m"), -1, date("Y")));

            $this->template->title = 'Admin Home';
            $this->template->content = View::factory('admin/reports')
                        ->set('ptitle', 'Admin Reporting')
                        ->set('employers', $emps)
                        ->set('sd', $sd)
                        ->set('ed', $ed)
                        ->set('user', $user)
                        ->set('logged_in', true);
        }
    }
 

    private function emailpdf($toname, $to, $period, $attachment='')
    {
        $toname = preg_replace('/^(d|m)(r|s|iss)\.?\s+/i','',$toname);

        $subj = "Petra Trust Statement as of $period";

        $msg="Dear $toname,\r\n\r\nPlease find attached your pension ".
             "statement covering the period $period.\r\n\r\nFor any enquiries or clarifications regarding this statement, contact us on: ".
            "\r\n\r\nEmail: clientservices@petratrust.com\r\n".
            "Telepone: 0302.740.963/4 or 0302.763.908".
            "\r\n\r\nRegards,\r\nPetra Trust Team";
        $res = $this->SendEmail($msg,$subj, $to,$attachment);
        return $res;
    }

    private function PDFStatements($employer_id,$schemes,$holders,$sd,$ed)
    {
        $info = array('data'=>array());

        # set dates
        $sdn = strtotime($sd);
        $edn = strtotime($ed);

        # Get employers scheme and holder information
        $client = Jelly::select('client')->load($employer_id);
        $employees = $client->employees();
        $ci = $client->usercontributions($schemes);

        # Get addresses and email
        list($phones, $emails, $addresses) = $this->ContactInfo();

        $sids = '';
        foreach($schemes as $s) { $sids .= $s.','; }
        $sids = '('.preg_replace('/,$/','',$sids).')';

        # get funds
        $sql = "SELECT f.id, f.name, f.tier, f.email, 
                       IFNULL(fn.displayname,f.name) as displayname
                  FROM funds f
                  LEFT JOIN funds_names fn ON fn.fund_id = f.id
                 WHERE f.id IN ".DB::Expr($sids);
        $f = DB::query(Database::SELECT, $sql, TRUE)->execute();

        $funds = array();
        $fundnames = array();
        $admin_emails = array();
        $prices = $client->price_index();

        foreach($f as $i)
        {
            $id = $i['id'];
            @$funds[$id] = $i['name'];
            $fundnames[$id] = $i['displayname'];
            if ($i['email']<>'') { array_push($admin_emails, $i['email']); }
        }

        # set admin email
        $info['admin_name'] = $client->name;
        if (sizeof($admin_emails)) { $info['admin_email'] = $admin_emails[0]; }

        $seen = array();
        $have_address = array_keys($addresses);
        foreach($employees as $m)
        {
            if (in_array($m->id,$holders)) # employee data has been requested
            {
                # get address
                $add = array('','','','','','');
                if (in_array($m->id, $have_address)) { $add = $addresses[$m->id]; }

                # email
                $email = (in_array($m->id, array_keys($emails))) ? $emails[$m->id]:'';

                # set info
                $info['data'][$m->id] = array('name'=>ucwords(strtolower($m->name)),
                                              'email'=>$email,
                                              'start'=> $sd,
                                              'company'=>$client->name,
                                              'end'=>$ed,
                                              'account'=>$m->entitykey,
                                              'contributions'=>array(),
                                              'fundwithdate'=> 0,
                                              'address'=>$add);

                if (!in_array($m->id, $seen)) # if employee has not been processed already move on
                {
                    if(in_array($m->id, array_keys($ci))) # if contribution info has been found for user
                    {
                        $scheck = $echeck = 0;

                        foreach($ci[$m->id] as $date => $sdetails)
                        {
                            foreach($sdetails as $tier => $tinfo)
                            {
                                foreach($tinfo as $fundid => $con)
                                {
                                        $r = $this->matchTiername($funds,$tier);
                                        if ($r !== false)
                                        {
                                            if (strtotime($date) <= $edn)
                                            {
                                                # get period start date
                                                $sinitcheck = ($scheck) ? $info['data'][$m->id]['start'] : $ed; 
                                                $scheck = 1;

                                                if (strtotime($date) < strtotime($sinitcheck)) {
                                                    $info['data'][$m->id]['start'] = $date;
                                                    $info['data'][$m->id]['fundwithdate'] = $fundid;
                                                    if (strtotime($info['data'][$m->id]['start']) <= $sdn) {
                                                        $info['data'][$m->id]['start'] = $sd;
                                                    }
                                                }

                                                # get period end date
                                                $einitcheck = ($scheck) ? $info['data'][$m->id]['end'] : $sd; 
                                                $echeck = 1;

                                                if (strtotime($date) > strtotime($einitcheck)) {
                                                    $info['data'][$m->id]['end'] = $date;
                                                    if (strtotime($info['data'][$m->id]['end']) >= $edn) {
                                                        $info['data'][$m->id]['end'] = $ed;
                                                    }
                                                }

                                                # indicate whether this is a previous balance or contributions in the given period
                                                $dt = (strtotime($date)>=$sdn && strtotime($date)<=$edn) ? $date : 'previous';

                                                if ($dt !='previous') { $dt = date('d/m/Y',strtotime($dt)); }

                                                if ($tier == 'Tier 3' || $tier == 'Tier 4')
                                                {
                                                    $who = $this->EmpOrEmp($fundid, $funds);

                                                    if ($who=='Employee' && ($con['employee']>0 || $con['redemption']<0))
                                                    {
                                                        if ($dt == 'previous')
                                                        {
                                                            @$info['data'][$m->id]['contributions'][$fundid][$dt] += $con['units'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total_units'] += $con['units'];
                                                        } else {
                                                            @$info['data'][$m->id]['contributions'][$fundid]['dep'][$dt] += $con['employee'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['red'][$dt] += $con['redemption'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total'] += $con['employee'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total'] += $con['redemption'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total_units'] += $con['units'];
                                                        }
                                                    }
                                                    if ($who=='Employer' && ($con['employer']>0 || $con['redemption']<0))
                                                    {
                                                        if ($dt == 'previous')
                                                        {
                                                            @$info['data'][$m->id]['contributions'][$fundid][$dt] += $con['units'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total_units'] += $con['units'];
                                                        } else {
                                                            @$info['data'][$m->id]['contributions'][$fundid]['dep'][$dt] += $con['employer'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['red'][$dt] += $con['redemption'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total'] += $con['employer'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total'] += $con['redemption'];
                                                            @$info['data'][$m->id]['contributions'][$fundid]['total_units'] += $con['units'];
                                                        }
                                                    }
                                                }
                                                elseif ($tier == 'Tier 2')
                                                {
                                                    if ($dt == 'previous')
                                                    {
                                                        @$info['data'][$m->id]['contributions'][$fundid][$dt] += $con['units'];
                                                        @$info['data'][$m->id]['contributions'][$fundid]['total_units'] += $con['units'];
                                                    }else{
                                                        @$info['data'][$m->id]['contributions'][$fundid]['dep'][$dt] += $con['employee'];
                                                        @$info['data'][$m->id]['contributions'][$fundid]['red'][$dt] += $con['redemption'];
                                                        @$info['data'][$m->id]['contributions'][$fundid]['total'] += $con['employee'];
                                                        @$info['data'][$m->id]['contributions'][$fundid]['total'] += $con['redemption'];
                                                        @$info['data'][$m->id]['contributions'][$fundid]['total_units'] += $con['units'];
                                                    }
                                                }
                                                else { }
                                            }
                                        }
                                }
                            }
                        }
                    } # has contributions check
                    @array_push($seen, $m->id);
                } # Processed already check

               //$this->d($info['data'],1); 

                @$info['data'][$m->id]['netincomeper'] = 0;
                @$info['data'][$m->id]['sumprevbal'] = 0.00;
                @$info['data'][$m->id]['sumcontribs'] = 0.00;
                @$info['data'][$m->id]['sumcurrbal'] = 0.00;
                @$info['data'][$m->id]['sumnetinc'] = 0.00;

                foreach($funds as $fid => $fu)
                {
                        $fu = $fid;

                        if (in_array($fid, array_keys($info['data'][$m->id]['contributions'])))
                        {
                            @$info['data'][$m->id]['contributions'][$fid]['displayname'] = $fundnames[$fid];

                            # get prices for calculations
                            #$prevsdate = $this->yesterday($info['data'][$m->id]['start']);
                            $prevsdate = $info['data'][$m->id]['start']; # using start date now instead of previous day date
                            $prevedate = $ed;
                            $prevprice = $client->find_recent(@$prices[$fid],$prevsdate);
                            $currprice = $client->find_recent(@$prices[$fid],$prevedate);

                             if (strtotime($currprice['date']) > strtotime($info['data'][$m->id]['end'])) {
				 $info['data'][$m->id]['end'] = $currprice['date'];

				 # if end date is beyond user requested, set end date to user requested end date    
                                 if (strtotime($info['data'][$m->id]['end']) >= $edn) { 
                                     $info['data'][$m->id]['end'] = $ed;
                                 }
			     } elseif (strtotime($currprice['date']) < strtotime($info['data'][$m->id]['end'])) {
				 $info['data'][$m->id]['end'] = $currprice['date'];

				 # if end date is before user requested start date, set end date to start date    
                                 #if (strtotime($info['data'][$m->id]['end']) >= $edn) { 
                                 #    $info['data'][$m->id]['end'] = $info['data'][$m->id]['start'];
                                 #}
                             }

                            @$info['data'][$m->id]['contributions'][$fid]['prevprice'] = $prevprice['price'];
                            @$info['data'][$m->id]['contributions'][$fid]['currprice'] = $currprice['price'];
                            @$info['data'][$m->id]['contributions'][$fid]['prevdate'] = $prevprice['date'];
                            @$info['data'][$m->id]['contributions'][$fid]['currdate'] = $currprice['date'];
                            @$info['data'][$m->id]['contributions'][$fid]['currdate'] = $currprice['date'];


                            if (@sizeof($info['data'][$m->id]['contributions']) == 0)
                            {
                                # Handle no contributions ever
                                @$info['data'][$m->id]['contributions'][$fid]['total'] = 0;
                                @$info['data'][$m->id]['contributions'][$fid]['pb'] = 0;
                                @$info['data'][$m->id]['contributions'][$fid]['total_units'] = 0;
                                @$info['data'][$m->id]['contributions'][$fid]['previous'] = 0;
                                @$info['data'][$m->id]['contributions'][$fid]['netincome'] = 0;
                                @$info['data'][$m->id]['contributions'][$fid]['netincomeper'] = 0.00;
                            } else {
                                # calculate value of previous balance
                                @$info['data'][$m->id]['contributions'][$fid]['previous'] =
                                        @$info['data'][$m->id]['contributions'][$fid]['previous'] * $prevprice['price'];

                                # calculate the sum of previous balances and sum of overall contributions
                                @$info['data'][$m->id]['sumprevbal'] +=  @$info['data'][$m->id]['contributions'][$fid]['previous'];
                                @$info['data'][$m->id]['sumcontribs'] +=  @$info['data'][$m->id]['contributions'][$fid]['total'];

                                $prev =  @$info['data'][$m->id]['contributions'][$fid]['previous'];
                                $oldprice = $prevprice['price'];
                                $newprice = $currprice['price'];

                                # calculate the period balance (total current units * current price of fund)
                                @$info['data'][$m->id]['contributions'][$fid]['pb']=@$info['data'][$m->id]['contributions'][$fid]['total_units']*$newprice;

                                # calculate the net income (total period balance - period contributions - previous balance)
                                $net =  @$info['data'][$m->id]['contributions'][$fid]['pb'] -
                                        @$info['data'][$m->id]['contributions'][$fid]['total'] - $prev;
                                @$info['data'][$m->id]['contributions'][$fid]['netincome'] = $net;

                                # calculate the net price
                                $netper = ($oldprice==0) ? 0 : (($newprice / $oldprice) - 1) * 100;
                                @$info['data'][$m->id]['contributions'][$fid]['netincomeper'] = $netper;

                                # track overall period balance and net income
                                @$info['data'][$m->id]['sumcurrbal'] +=  @$info['data'][$m->id]['contributions'][$fid]['pb'];
                                @$info['data'][$m->id]['netincomeper'] = $netper;
                                @$info['data'][$m->id]['sumnetinc'] += $net;
                            }
                        }
                }

                @$info['data'][$m->id]['netincomeper'] =  @$info['data'][$m->id]['contributions'][@$info['data'][$m->id]['fundwithdate']]['netincomeper'];
                @$info['data'][$m->id]['period'] = date('d/m/Y',strtotime($info['data'][$m->id]['start'])).' - '.
                                                   date('d/m/Y',strtotime($info['data'][$m->id]['end']));

            } # Holders check
        }

        # statement period
        $info['start'] = date('d/m/Y',strtotime($sd));
        $info['end'] = date('d/m/Y',strtotime($ed));
        $info['period'] = date('d/m/Y',strtotime($sd)).' - '.
                          date('d/m/Y',strtotime($ed));

        return json_encode($info);
    }

    private function PrintPDF($info, $mail='')
    {
        require_once 'fpdf17/fpdf.php';

        $info = json_decode($info);
        //$this->d($info,1);
        $time = date('d/m/Y h:m:s', mktime(0, 0, 0, date("m"), date('d'), date("Y")));


        $pdf = new FPDF();
        $pdf->SetAutoPageBreak(false);

        // for tracking emails to clients
        $emlist = new FPDF();
        $emlist->AddPage();
        $emlist->SetFont('Arial','B',12);
        $emlist->Cell(20,10,'List of clients that could not be emailed.',0,1);
        $emlist->Cell(20,10,'Name',0,0); $emlist->SetX(70);
        $emlist->Cell(20,10,'Entity Key',0,0); $emlist->SetX(120);
        $emlist->Cell(20,10,'Reason',0,1);

        foreach($info->data as $emp)
        {
            $pdf->AddPage();
            $pdf = $this->PDFTop($pdf,$emp,$time);
            $pdf = $this->PDFBottom($pdf,$time);

            $row = 110;
            foreach($emp->contributions as $k =>$toys)
            {
                $sum = 0.00;
                $prevbal = $toys->previous;
                $pni = $toys->netincome;
                $endbal = $toys->pb;
                $disp = $toys->displayname;

                list($pdf, $row) = $this->checkline($pdf,$time,$row+10);
                $pdf->SetFont('Arial','B',11);
                $pdf->SetXY(10,$row); $pdf->Cell(180,10,$disp);

                list($pdf, $row) = $this->checkline($pdf,$time,$row+7);
                $pdf->SetLineWidth(0.5);
                $pdf->Line(10, $row, 200, $row);

                $pdf->SetFont('Arial','B',9);
                $pdf->SetXY(10,$row); $pdf->Cell(180,10,'Contribution Date');
                $pdf->SetXY(157,$row); $pdf->Cell(180,10,'Contribution Amount');

                list($pdf, $row) = $this->checkline($pdf,$time,$row+8);
                $pdf->SetLineWidth(0.1);
                $pdf->Line(10, $row, 200, $row);

                // Contributions
                $pdf->SetFont('Arial','',9);
                $pdf->SetXY(50, $row); $pdf->Cell(10,10,'Balance brought forward');
                $pdf->SetXY(160, $row); $pdf->Cell(10,10,'GHS');
                $pdf->SetXY(170, $row); $pdf->Cell(20,10,number_format($prevbal,2),0,0,'R');

                list($pdf, $row) = $this->checkline($pdf,$time,$row+6);
                if (!isset($toys->dep) && !isset($toys->red))
                {
                    $pdf->SetFont('Arial','I',8);
                    $pdf->SetXY(10,$row); $pdf->Cell(180,10,'No contributions in period');
                    list($pdf, $row) = $this->checkline($pdf,$time,$row+6);
                }
                else
                {
                    foreach($toys->dep as $date => $v)
                    {
                        if ($v==0 && isset($toys->red)) { }
                        else {
                            $sum += $v;
                            $pdf->SetFont('Arial','',9);
                            $pdf->SetXY(10,$row); $pdf->Cell(10,10,$date);
                            $pdf->SetXY(160,$row); $pdf->Cell(10,10,'GHS');
                            $pdf->SetXY(170,$row); $pdf->Cell(20,10,number_format($v,2),0,0,'R');
                            list($pdf, $row) = $this->checkline($pdf,$time,$row+6);
                        }
                    }
                    if (isset($toys->red))
                    {
                        foreach($toys->red as $date => $v)
                        {
                            if ($v != 0)
                            {
                                $sum += $v;
                                $pdf->SetFont('Arial','',9);
                                $pdf->SetXY(10,$row); $pdf->Cell(180,10,$date);
                                $pdf->SetXY(50,$row); $pdf->Cell(10,10,'Redemption');
                                $pdf->SetXY(160,$row); $pdf->Cell(10,10,'GHS');
                                $pdf->SetXY(170,$row); $pdf->Cell(30,10,number_format($v,2),0,0,'R');
                                list($pdf, $row) = $this->checkline($pdf,$time,$row+6);
                            }
                        }
                    }
                }

                list($pdf, $row) = $this->checkline($pdf,$time,$row+4);
                $pdf->SetLineWidth(0.1);
                $pdf->Line(10, $row, 200, $row);

                list($pdf, $row) = $this->checkline($pdf,$time,$row+5);
                $pdf->SetFont('Arial','',9);
                $pdf->SetXY(10,$row); $pdf->Cell(10,10,'Net Contributions');
                $pdf->SetXY(160,$row); $pdf->Cell(10,10,'GHS');
                $pdf->SetXY(170,$row); $pdf->Cell(20,10,number_format($sum,2),0,0,'R');
                list($pdf, $row) = $this->checkline($pdf,$time,$row+5);
                $pdf->SetXY(10,$row); $pdf->Cell(10,10,'Period Net Income');
                $pdf->SetXY(160,$row); $pdf->Cell(10,10,'GHS');
                $pdf->SetXY(170,$row); $pdf->Cell(20,10,number_format($pni,2),0,0,'R');

                list($pdf, $row) = $this->checkline($pdf,$time,$row+7);
                $pdf->SetFont('Arial','B',9);
                $pdf->SetXY(10,$row); $pdf->Cell(10,10,'End of Period Balance As At '.$emp->end);
                $pdf->SetXY(160,$row); $pdf->Cell(10,10,'GHS');
                $pdf->SetXY(170,$row); $pdf->Cell(20,10,number_format($endbal,2),0,0,'R');

                list($pdf, $row) = $this->checkline($pdf,$time,$row+8);
                $pdf->SetLineWidth(0.4);
                $pdf->Line(10, $row, 200, $row);
            }

            $pdf->SetFont('Arial','',8);
            $pdf->SetXY(10,169); $pdf->Cell(10,180,'For any enquiries or clarifications regarding this statment, contact us on:');
            $pdf->SetXY(10,175); $pdf->Cell(10,180,'E: clientservices@petratrust.com');
            $pdf->SetXY(10,180); $pdf->Cell(10,180,'T1: 0302 740 963');
             $pdf->SetXY(10,185); $pdf->Cell(10,180,'T1: 0302 740 964');
             $pdf->SetXY(10,190); $pdf->Cell(10,180,'T1: 0302 763 908');
             $pdf->SetXY(10,195); $pdf->Cell(10,180,'P.O. Box CT 3194, Cantoments');
            $pdf->SetXY(10,200); $pdf->Cell(10,180,'F304/5 Dade Close, Labone');

            if ($mail == 'client')
            {
                $name = preg_replace('/^(d|m)(r|s|iss)\.?\s+/i','',$emp->name);

                if ($emp->email <> '')
                {
                    // safe file and email it
                    $file = preg_replace('/ /','_',$name);
                    $end = preg_replace('/\//','-',$info->end);
                    $filename = $file.'-'.$end.'.pdf';
                    $pdf->Output($filename,'F');

                    $loop = 1;
                    while ($loop) { $loop = file_exists($filename) ? 0 : 1; }

                    $res = $this->emailpdf($emp->name,
                                           $emp->email,
                                           $emp->period, $filename);
                    if ($res!==true) {
                        $emlist->SetFont('Arial','',11);
                        $emlist->SetTextColor(0,0,0);
                        $emlist->Cell(20,10,$name);
                        $emlist->SetX(70);
                        $emlist->Cell(20,10,$emp->account);
                        $emlist->SetX(120);
                        $emlist->Cell(20,10,$res,0,1);
                    }
                } else {
                    $emlist->SetFont('Arial','',11);
                    $emlist->SetTextColor(0,0,0);
                    $emlist->Cell(20,10,$name);
                    $emlist->SetX(70);
                    $emlist->Cell(20,10,$emp->account);
                    $emlist->SetX(120);
                    $emlist->Cell(20,10,'No valid email on file.',0,1);
                }
                // start a new PDF.
                $pdf = null;
                $pdf = new FPDF();
                $pdf->SetAutoPageBreak(false);
            }
        }

        $name = preg_replace('/^(d|m)(r|s|iss)\.?\s+/i','',$info->admin_name);
        if ($mail=='admin')
        {
            // safe file and email it
            $file = preg_replace('/ /','_',$name);
            $end = preg_replace('/\//','-',$info->end);
            $filename = $file.'-'.$end.'.pdf';
            $pdf->Output($filename,'F');
            $loop = 1;

            while ($loop) { $loop = file_exists($filename) ? 0 : 1; }

            $res = $this->emailpdf($info->admin_name,
                            $info->admin_email,
                            $info->period, $filename);
            echo json_encode($res);
        } else {
            if ($mail=='client')
            {
                // send list of clients who failed
                $emlist->Output('failed-emails.pdf','D');
            }
            else
            {
                // send it for download
                $file = preg_replace('/ /','_',$name);
                $end = preg_replace('/\//','-',$info->end);
                $filename = $file.'-'.$end.'.pdf';
                $pdf->Output($filename,'D');
            }
        }
        exit;
    }

    private function checkline($pdf, $time, $linenum)
    {
        if ($linenum >= 260)
        {
            $pdf->AddPage();
            $pdf = $this->PDFBottom($pdf,$time);
            $linenum = 20;
        }
        return array($pdf, $linenum);
    }

    private function PDFTop($pdf,$emp, $time)
    {
            // Header with Image
            $pdf->SetFont('Arial','B',11);

            if (preg_match('/Anglo/',$emp->company,$g)) {
                $pdf->Image('http://www.petratrust.com/members/assets/images/anglo.jpg',null,null,35,10);

            } elseif (preg_match('/ecg/',$emp->company,$g)) {
                $pdf->Image('http://www.petratrust.com/members/assets/images/ecg.jpg',null,null,35,10);

            } elseif (preg_match('/Eco/',$emp->company,$g)) {
                $pdf->Image('http://www.petratrust.com/members/assets/images/ecobank-logo.jpg',null,null,35,10);

            } elseif (preg_match('/Grid/',$emp->company,$g)) {
                $pdf->Image('http://www.petratrust.com/members/assets/images/Gridco.jpg',null,null,35,10);

            } elseif (preg_match('/Shell/',$emp->company,$g)) {
                $pdf->Image('http://www.petratrust.com/members/assets/images/shell_logo.jpg',null,null,35,10);
            }

            $pdf->SetXY(80,10);
            $pdf->Cell(180,10,'BENEFIT STATEMENT');
            $pdf->SetXY(160,10);
            $pdf->Image('http://www.petratrust.com/members/assets/images/petra.jpg',null,null,35,10);

            // Header columns - left side
            $pdf->SetFont('Arial','',9);
            $name = preg_replace('/^(d|m)(r|s|iss)\.?\s+/i','',$emp->name);
            $name = strtoupper($name);
            $pdf->SetXY(10,30); $pdf->Cell(180,10,$name);
            $pdf->SetXY(10,35); $pdf->Cell(180,10,$emp->address[0]);
            $pdf->SetXY(10,40); $pdf->Cell(180,10,$emp->address[1]);
            $pdf->SetXY(10,45); $pdf->Cell(180,10,$emp->address[2]);
            $pdf->SetXY(10,50); $pdf->Cell(180,10,$emp->address[3]);
            $pdf->SetXY(10,55); $pdf->Cell(180,10,$emp->address[4]);

            // Header columns - right side
            $pdf->SetFont('Arial','B',9);
            $pdf->SetXY(160,30); $pdf->Cell(180,10,'Statement Period');
            $pdf->SetFont('Arial','',9);
            $pdf->SetXY(160,35); $pdf->Cell(180,10,$emp->period);
            $pdf->SetFont('Arial','B',9);
            $pdf->SetXY(160,40); $pdf->Cell(180,10,'Account Number');
            $pdf->SetFont('Arial','',9);
            $pdf->SetXY(160,45); $pdf->Cell(180,10,$emp->account);
            $pdf->SetFont('Arial','B',9);
            $pdf->SetXY(160,50); $pdf->Cell(180,10,'Scheme Return');
            $pdf->SetFont('Arial','',9);
            $pdf->SetXY(160,55); $pdf->Cell(180,10,number_format($emp->netincomeper,2).'%');

            // Summary of contributions
            $pdf->SetFont('Arial','B',11);
            $pdf->SetXY(78.5,70); $pdf->Cell(180,10,'SUMMARY STATEMENT');
            $pdf->Line(10, 78, 200, 78);

            $pdf->SetFont('Arial','',9);
            $pdf->SetXY(10, 78); $pdf->Cell(90,10,'Beginning balance');
            $pdf->SetXY(160, 78); $pdf->Cell(10,10,'GHS');
            $pdf->SetXY(170, 78); $pdf->Cell(20,10,number_format($emp->sumprevbal,2),0,0,'R');

            $pdf->SetXY(10, 84); $pdf->Cell(90,10,'Contributions');
            $pdf->SetXY(160, 84); $pdf->Cell(10,10,'GHS');
            $pdf->SetXY(170, 84); $pdf->Cell(20,10,number_format($emp->sumcontribs,2),0,0,'R');

            $pdf->SetXY(10, 90); $pdf->Cell(180,10,'Total Period Net Income');
            $pdf->SetXY(160, 90); $pdf->Cell(10,10,'GHS');
            $pdf->SetXY(170, 90); $pdf->Cell(20,10,number_format($emp->sumnetinc,2),0,0,'R');

            $pdf->SetFillColor(29,55,100);
            $pdf->Rect(10,98,190,5,'F');
            $pdf->SetFont('Arial','B',9);
            $pdf->SetTextColor(255,255,255);
            $pdf->SetXY(10,95.7); $pdf->Cell(180,10,'End of Period Balance');
            $pdf->SetXY(160, 95.7); $pdf->Cell(10,10,'GHS');
            $pdf->SetXY(170, 95.7); $pdf->Cell(20,10,number_format($emp->sumcurrbal,2),0,0,'R');

            // Detailed statement
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('Arial','B',11);
            $pdf->SetXY(78.5,110); $pdf->Cell(180,10,'DETAILED STATEMENT');

            return $pdf;
    }

    private function PDFBottom($pdf,$time)
    {
       $pdf->SetFont('Arial','',8);
       $pdf->SetXY(100,200); $pdf->Cell(10,180,'PRINT TIME: '.$time,0,0,'C');
       $pdf->SetXY(170,200); $pdf->Cell(10,180,'Page '.$pdf->PageNo(),0,0,'C');
       return $pdf;
    }


    private function ContactInfo()
    {
        $sql = "SELECT client_id, attribute, value FROM attributes_clients";
        $add = DB::query(Database::SELECT, $sql, TRUE)->execute();

        $phones = array();
        $emails = array();
        $addresses = array();
        foreach($add as $a) {
                if ($a['attribute']=='address')
                {
                    $addresses[$a['client_id']] = preg_split("/\<br\>/",$a['value']);
                    for($i=0; $i < 5; $i++)
                    {
                        if (@$addresses[$a['client_id']][$i]=='') {
                            @$addresses[$a['client_id']][$i] = ' ';
                        }
                    }
                } elseif ($a['attribute']=='email') {
                    $emails[$a['client_id']] = $a['value'];
                } else {
                    $phones[$a['client_id']] = $a['value'];
                }
        }

        return array($phones, $emails, $addresses);
    }

    private function yesterday($date)
    {
        $cur_date = mktime(1,0,0,substr($date,5,2),
                                 substr($date,8,2),
                                 substr($date,0,4));
        return date('Y-m-d',($cur_date - 86400));
    }

    private function EmpOrEmp($id, $funds)
    {
        if (in_array($id, array_keys($funds))) {
            if (preg_match("/Employer/",$funds[$id])) { return 'Employer'; } 
            if (preg_match("/Employee/",$funds[$id])) { return 'Employee'; } 
        }
        return false;
    }

    private function matchTiername($funds, $name)
    {
        $name = ($name == 'Tier 3') ? 'Pre' : $name;
        $name = ($name == 'Tier 4') ? 'Post' : $name;
        $regex = "(.*?)$name(.*?)";
        $rarray = array('employee'=>0, 'employer'=>0);
        $found = false;

        foreach($funds as $fund)
        {
                if (preg_match("/$regex/",$fund)) {
                    if (preg_match("/Employer/",$fund)) { $rarray['employer']=1; }
                    if (preg_match("/Employee/",$fund)) { $rarray['employee']=1; }
                    $found = true;
                }
        }
        return ($found) ? $rarray : false;
    }

} // End Controller_Admin
