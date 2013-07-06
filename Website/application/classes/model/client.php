<?php defined('SYSPATH') or die ('No direct script access.');

class Model_Client extends Model_Base
{
    private $t2 = 0;
    private $t3 = 0;
    private $t4 = 0;

    // flags to reduce number of db calls
    private $have_tierinfo = 0;

	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('clients')
			->fields(array(
			'id' => new Field_Primary,
			'name' => new Field_String,
			'entitykey' => new Field_String,
			'type' => new Field_Enum(array(
				'choices'=> array('employee','employer')
			 )),
			'description' => new Field_String,
			'status' => new Field_String,
            'companyid' => new Field_String,
			'user' => new Field_HasOne(
					array( 'model' => 'user')
				)
        ));
	}


    /**** User Methods **/
    public function employees()
    {
        if ($this->type=='employee') return array();

        $employees = array();
        
        $p = Jelly::select('client')
                 ->where('clients.companyid','=',$this->id)
                 ->execute();

        $seen = array();
        foreach($p as $employee) { 
              if (!in_array($employee->id, $seen))
              { 
                  $employees[]= $employee; 
                  array_push($seen, $employee->id);
              }
        } 
        return $employees;
    }

    public function employers()
    {
        if ($this->type=='employer') return array();

        $employers = array();

        $p = Jelly::select('client')
                    ->where('clients.id','=',$this->companyid)
                    ->where('clients.type','=','employer')
                    ->execute();

        $seen = array();
        foreach($p as $employer) { 
                if (!in_array($employer->id, $seen))
                { 
                    $employers[]= $employer; 
                    array_push($seen, $employer->id);
                }
        } 

        return $employers;
    }

    public function usersWithAccounts()
    {
        $sql = "SELECT client_id FROM users";
        $acc = DB::query(Database::SELECT, $sql, TRUE)->execute();
        $r = array();
        foreach($acc as $a) { 
                if ($a['client_id'] <> '') {
                    array_push($r, $a['client_id']);
                } 
        }
        return $r;
    }

    
    public function userTiers()
    {
        $sql = "SELECT fc.client_id, f.tier 
                  FROM funds_clients fc
                  JOIN funds f ON f.id = fc.fund_id
                  WHERE fc.client_id = {$this->id} 
                  GROUP BY fc.client_id, f.tier";
        $acc = DB::query(Database::SELECT, $sql, TRUE)->execute();
        $r = array();
        foreach($acc as $a) { 
                if (!in_array($a['client_id'], array_keys($r))) {
                    $r[$a['client_id']] = array();
                }
                array_push($r[$a['client_id']], $a['tier']);
        }
        return $r;
    }


    /**** Balance Methods **/
    public function tier3_balance()
    {
        $t = 0;
        $b = $this->balance();
        if (isset($b['Tier 3'])) {
            foreach($b as $tier =>  $info) { 
                if ($tier=='Tier 3') {
                    $t += $info['balance']; 
                }
            }
        }
        return $t;
    }

    public function balance()
    {
        $data = $this->client_balance();

        $cb = array('Tier 2'=>array(),'Tier 3'=>array(),
                    'Tier 4'=>array()); 

        foreach($data as $tier => $info) 
        {
             foreach($info as $date => $bi)
             {
                @$cb[$tier]['balance'] = $bi['balance']; 
                @$cb[$tier]['date'] = $bi['date'];
             }
        }

        return $cb;
    }

    public function client_balance($client_id='')
    {
        $client_id = ($client_id=='') ? $this->id : $client_id;

        $key = 'client-balance-'.$client_id;
        $data = $this->getSessionVar($key); 
        if($data != null) { return $data; }

        $cids = '';
        if($this->type=='employer')
        { 
            $sql =  "select distinct(id) from clients where companyid = $client_id";
            $ids = DB::query(Database::SELECT, $sql, TRUE)->execute();
            foreach($ids as $c) { $cids .= $c['id'].','; }
            $cids = '('.preg_replace('/,$/','',$cids).')';
        } else {
            $cids = "($client_id)";
        }

        # get issues
        $issues = $this->baltypes(DB::Expr($cids));

        $t2_fid = $t3_fid = $t4_fid = null;
        $bal2['Tier 2'] = $bal2['Tier 3'] = $bal2['Tier 4'] = array();

        if ($issues != null)
        {
            foreach($issues as $date=> $info)
            {
                foreach($info as $tier => $i)
                {
                    if ($tier=='Tier 2') { $t2_fid = $i['fundid']; }
                    if ($tier=='Tier 3') { $t3_fid = $i['fundid']; }
                    if ($tier=='Tier 4') { $t4_fid = $i['fundid']; }
                    @$bal2[$tier][$date]['units'] += $i['units'];
                    @$bal2[$tier][$date]['price'] = $i['price'];
                }
            }
        }

        # get prices
        $prices = $this->price_index();

        # get dates starting from a year ago
        $sd = (date('Y')-1).'-'.date('m').'-'.date('d');
        $alldates = $this->create_date_range($sd, date('Y-m-d'));

        $bal['Tier 2'] = $bal['Tier 3'] = $bal['Tier 4'] = array();
        @$prevbal['Tier 2'] = array('units'=>0, 'price'=>0,'date'=>''); 
        @$prevbal['Tier 3'] = array('units'=>0, 'price'=>0,'date'=>''); 
        @$prevbal['Tier 4'] = array('units'=>0, 'price'=>0,'date'=>''); 
        foreach($alldates as $date) { 
                $datef = date_format(date_create($date),'jS F Y');
                $bal['Tier 2'][$datef] = array('balance'=>0.00, 'date'=>''); 
                $bal['Tier 3'][$datef] = array('balance'=>0.00, 'date'=>''); 
                $bal['Tier 4'][$datef] = array('balance'=>0.00, 'date'=>'');

                for($j = 2; $j < 5; $j++)
                {
                    $tier = 'Tier '.$j;
                    $tid = ($tier=='Tier 2') ? $t2_fid 
                                             : (($tier=='Tier 3') ?  $t3_fid : $t4_fid); 

                    if (@isset($bal2[$tier][$date])) {
                        $bal[$tier][$datef]['balance'] =  $bal2[$tier][$date]['units'] *
                                                          $bal2[$tier][$date]['price']; 

                        $bal[$tier][$datef]['date'] =  $date;

                        $prevbal[$tier]['units'] = $bal2[$tier][$date]['units']; 
                        $prevbal[$tier]['price'] = $bal2[$tier][$date]['price']; 
                        $prevbal[$tier]['date'] = $date; 

                    } else {
                        if ($prevbal[$tier]['units'] != 0) {
                            if ($tid != null && isset($prices[$tid][$date]))
                            {
                                # use price for day
                                $bal[$tier][$datef]['balance'] = $prevbal[$tier]['units']*
                                                                 $prices[$tid][$date]; 
                                $bal[$tier][$datef]['date'] = $date; 

                                $prevbal[$tier]['price'] = $prices[$tid][$date]; 
                                $prevbal[$tier]['date'] = $date; 
                            }
                            else 
                            {
                                # use last known price
                                $bal[$tier][$datef]['balance']= $prevbal[$tier]['units'] *
                                                                $prevbal[$tier]['price']; 
                                $bal[$tier][$datef]['date'] = $prevbal[$tier]['date']; 
                            }
                        }            
                    }
                }
        }

        $this->setSessionVar($key, $bal);

        return $bal;
    }

    protected function baltypes($id)
    {
        if ($id=='()') return null;

        $sql = "SELECT IF(d2.tier!='Unknown',SUBSTRING(d2.tier,6),'Unknown') as tier, 
                       DATE(d2.deal_date) as date, 
                       d2.fund_id,
                       SUM(IF(d2.type='Issue',d2.units,0)) as issues,
                       SUM(IF(d2.type='Redemption',d2.units,0)) as redemp,
                       d2.price
                 FROM deals d2
                WHERE d2.cancelid=0
                  AND d2.tier != ''
                  AND d2.type != 'Cancellation'
                  AND d2.client_id IN $id
                GROUP BY d2.tier, DATE(d2.deal_date)";
        $bals = DB::query(Database::SELECT, $sql, TRUE)->execute();

        $resp = array();
        foreach($bals as $b)
        {
            @$resp[$b['date']][$b['tier']]['units'] += ($b['issues'] - $b['redemp']);
            @$resp[$b['date']][$b['tier']]['fundid'] += $b['fund_id'];
            @$resp[$b['date']][$b['tier']]['price'] += $b['price'];
        }
        ksort($resp);

        $tier2 = $tier3 = $tier4 = 0;
        foreach($resp as $date => $tiers)
        {
            foreach($tiers as $tier => $i)
            {
                if ($tier=='Tier 2') { 
                    @$resp[$date][$tier]['units'] += $tier2; 
                    $tier2 = @$resp[$date][$tier]['units']; 
                }
                if ($tier=='Tier 3') { 
                    @$resp[$date][$tier]['units'] += $tier3; 
                    $tier3 = @$resp[$date][$tier]['units']; 
                }
                if ($tier=='Tier 4') { 
                    @$resp[$date][$tier]['units'] += $tier4; 
                    $tier4 = @$resp[$date][$tier]['units']; 
                }
            }
        }
        return $resp;
    }

 

    /**** Contribution Methods **/
    public function contributions()
    {
        $r = array();
        $info = $this->userdealinfo();

        foreach($info as $cid => $data)
        {
            foreach($data as $key => $d)
            {
               @$r[$d['date']][$d['tier']][$d['fundid']]['redemption'] += $d['redemption'];
               @$r[$d['date']][$d['tier']][$d['fundid']]['employee'] += $d['employee'];
               @$r[$d['date']][$d['tier']][$d['fundid']]['employer'] += $d['employer'];
               @$r[$d['date']][$d['tier']][$d['fundid']]['units'] += $d['units'];
               @$r[$d['date']][$d['tier']][$d['fundid']]['total'] += $d['total'];
               @$r[$d['date']][$d['tier']][$d['fundid']]['name'] = $d['scheme'];
            }
         }
         krsort($r);
         return $r;
    } 

    public function usercontributions($funds=array())
    {
        $r = array();
        $info = $this->userdealinfo();

        foreach($info as $cid => $in)
        {
            foreach($in as $key => $d)
            {
                if (sizeof($funds)==0 or in_array($d['fundid'],$funds)) 
                {
                    @$r[$cid][$d['date']][$d['tier']][$d['fundid']]['redemption'] += $d['redemption'];
                    @$r[$cid][$d['date']][$d['tier']][$d['fundid']]['employee'] += $d['employee'];
                    @$r[$cid][$d['date']][$d['tier']][$d['fundid']]['employer'] += $d['employer'];
                    @$r[$cid][$d['date']][$d['tier']][$d['fundid']]['units'] += $d['units'];
                    @$r[$cid][$d['date']][$d['tier']][$d['fundid']]['total'] += $d['total'];
                    @$r[$cid][$d['date']][$d['tier']][$d['fundid']]['name'] = $d['scheme'];
                }
            }
         }
         return $r;
    }


    /*** Deals Methods **/
    public function dealinfo()
	{
		$info = array();

        $deals = $this->deals();

		foreach($deals as $deal)
		{
				$scheme = $deal->scheme();
				$date = $deal->dealdate();
				$ftype = $deal->fund_type();
				$idx = $date.'-'.$scheme.'-'.$deal->tier();

                if ($deal->type=='Issue') 
                {
				    if(in_array($idx,array_keys($info)))
				    {
					    if ($ftype=='Employee')
					    {
						    $info[$idx]['employee']+=$deal->payment;
					    } else {
						    $info[$idx]['employer']+=$deal->payment;
					    }
					    $info[$idx]['total']+=$deal->payment;
				    }
				    else
				    {
					    $info[$idx] = array('date'=>$date,
										    'employee'=>0,
										    'employer'=>0,
										    'tier'=>$deal->tier(),
										    'scheme'=>$deal->scheme(),
										    'total'=>0);
					    if ($ftype=='Employee')
					    {
						    $info[$idx]['employee']+=$deal->payment;
					    } else {
						    $info[$idx]['employer']+=$deal->payment;
					    }
					    $info[$idx]['total']+=$deal->payment;
                    }
				}
        }

        krsort($info);
		return $info;
    }

    public function userdealinfo()
    {
        $r = array();

        $deals = $this->deals();

        foreach($deals as $deal)
		{
                $cid = $deal->client_id;
				$scheme = $deal->scheme();
				$date = $deal->dealdate();
				$ftype = $deal->fund_type();
                $units = $deal->units;
				$idx = $date.'-'.$deal->fund_id.'-'.$deal->tier();
                
                if (!in_array($cid, array_keys($r))) {
                    $r[$cid] = array();
                }

				if(in_array($idx,array_keys($r[$cid])))
				{
					    $r[$cid][$idx]['fundid'] = $deal->fund_id;
					    $r[$cid][$idx]['scheme'] = $deal->scheme();

                        if ($deal->type=='Redemption')
                        {
					        $r[$cid][$idx]['units'] -= $deal->units;
						    $r[$cid][$idx]['redemption'] -= $deal->payment;
					        $r[$cid][$idx]['total'] -= $deal->payment;
                        } else {
					        if ($ftype=='Employee')
					        {
						        $r[$cid][$idx]['employee']+=$deal->payment;
						        $r[$cid][$idx]['units']+=$deal->units;
					        } else {
						        $r[$cid][$idx]['employer']+=$deal->payment;
						        $r[$cid][$idx]['units']+=$deal->units;
					        }
					        $r[$cid][$idx]['total']+=$deal->payment;
                        }
				}
				else
				{
					    $r[$cid][$idx] = array('date'=>$date,
										'employee'=>0,
										'units'=>0,
										'redemption'=>0,
										'employer'=>0,
										'tier'=>$deal->tier(),
										'scheme'=>$deal->scheme(),
										'fundid'=>$deal->fund_id,
										'total'=>0);
                        if ($deal->type=='Redemption')
                        {
						    $r[$cid][$idx]['redemption'] -= $deal->payment;
					        $r[$cid][$idx]['total'] -= $deal->payment;
					        $r[$cid][$idx]['units'] -= $deal->units;
                        } else {
                            if ($ftype=='Employee') {
						        $r[$cid][$idx]['employee']+=$deal->payment;
					            $r[$cid][$idx]['units'] += $deal->units;
					        } else {
						        $r[$cid][$idx]['employer']+=$deal->payment;
					            $r[$cid][$idx]['units'] += $deal->units;
					        }
					        $r[$cid][$idx]['total']+=$deal->payment;
                        }
                 }
        }
        return $r;
    }    
    
    protected function deals()
    {
        // Get the session instance
       $key = 'deals-'.$this->id;
       $data = $this->getSessionVar($key);

       $deals = array();

       if ($data != null) { 
           foreach($data as $deal) 
            { 
                if ($this->type=='employer')
                {
                    $deals[] = new Deal($deal);
                } 
                elseif ($deal['client_id'] == $this->id)
                {
                    $deals[] = new Deal($deal);
                } 
            } 
            return $deals;
       }

       $holdings = $this->holdings();

       if (sizeof($holdings) > 0)
       {
            $fund_ids = '';
            foreach($holdings as $h) { $fund_ids .= $h['fund_id'].','; }
            $fund_ids = '('.preg_replace('/,$/','',$fund_ids).')';

            $sql = "SELECT d.id, d.type, d.client_id, d.fund_id,
                       f.name, c.name as cname, f.tier, d.deal_date, 
                       d.units, 
                       d.price, d.payment, d.cancelid
                  FROM deals d
                  JOIN funds f ON f.id = d.fund_id
                   AND f.id IN ".DB::Expr($fund_ids).
                " JOIN funds_clients fc ON fc.fund_id  = f.id
                  JOIN clients c ON c.id = fc.client_id AND c.type='employer'
                 WHERE d.type != 'Cancellation' AND d.cancelid = 0
                 GROUP BY d.id ORDER BY  d.deal_date";
            $d = DB::query(Database::SELECT, $sql, TRUE)->execute();

            $this->setSessionVar($key, $d);

            foreach($d as $deal) 
            { 
                if ($this->type=='employer')
                {
                    $deals[] = new Deal($deal);
                } 
                elseif ($deal['client_id'] == $this->id)
                {
                    $deals[] = new Deal($deal);
                } 
            } 
        }

        return $deals;
    }

    public function holdings()
    {
        // Get the session instance
       $key = 'holdings-'.$this->id;
       $data = $this->getSessionVar($key);

       if ($data != null) { return $data; }
       
        $sql = "SELECT fc.fund_id, fc.client_id
                  FROM funds_clients fc
                 WHERE fc.client_id = {$this->id}";
        $holdings_info = DB::query(Database::SELECT, $sql, TRUE)->execute();
        $this->setSessionVar($key, $holdings_info);
        return $holdings_info;
    }

    /**** Price methods **/
    public function price_index()
    {
        // Get the session instance
       $key = 'price-index';
       $data = $this->getSessionVar($key);

       if ($data != null) { return $data; }

       $pricing_info = array();

       $sql = "SELECT f.tier as t, f.name as n, f.id, 
                      DATE(p.date) as d, MAX(p.price) as pr
                 FROM prices p 
                 JOIN funds f ON f.id = p.fund_id
                GROUP BY f.tier, f.name, p.date";
       $prices = DB::query(Database::SELECT, $sql, TRUE)
                ->execute();

        foreach($prices as $p) 
        { 
            @$pricing_info[$p['id']][$p['d']] = $p['pr']; 
        } 

        $this->setSessionVar($key, $pricing_info);

        return $pricing_info;
    }



    /* Tier Methods */
    public function has_tier($tier)
    {
        if (!$this->have_tierinfo) { $this->set_tiers();  }
        if ($tier == 'Tier 2') return $this->t2;
        if ($tier == 'Tier 3') return $this->t3;
        if ($tier == 'Tier 4') return $this->t4;
		return false;
    }

    private function set_tiers()
    {
        $deals = $this->deals();

		foreach($deals as $deal)
		{
			if ($deal->tier() == 'Tier 2') { $this->t2 = 1; }
			if ($deal->tier() == 'Tier 3') { $this->t3 = 1; }
			if ($deal->tier() == 'Tier 4') { $this->t4 = 1; }
		}
        $this->have_tierinfo=1;
    }


    /**** Helper methods **/
    public function getSessionVar($key,$default=null)
    {
        return null;
        // Get the session instance
        //$session = Session::instance();
        //$data = $session->get($key,$default);
        //return $data;
    }

    public function setSessionVar($key, $val)
    {
        //$session = Session::instance();
        //$session->set($key, $val);
    }

    public function find_recent($list, $date, $count=0)
    {
        $mindate = min(array_keys($list)); 

        if (strtotime($date) < strtotime($mindate)) {
            return array(
                'date'=>date_format(date_create($mindate),'jS F Y'),
                'price'=>$list[$mindate]); 
        }

        if (isset($list[$date])) { 
            return 
              array(
                'date'=>date_format(date_create($date),'jS F Y'),
                'price'=>$list[$date]
              ); 
        }

        $s =  sizeof($list);
        if ($count > $s or $s==0) { 
            return array(
                'date'=>date_format(date_create(date('Y-m-d')),'jS F Y'),
                'price'=>0.00); 
        }
        
        $cur_date = mktime(1,0,0,substr($date,5,2),     
                                 substr($date,8,2),
                                 substr($date,0,4));

        $prev_date = date('Y-m-d',($cur_date - 86400)); //substract 24hrs

        return $this->find_recent($list, $prev_date, $count++);
    }
}



class Deal
{
    public $id;
    public $type;
    public $client_id;
    public $fund_id;
    public $fund_name;
    public $scheme;
    public $fund_tier;
    public $deal_date;
    public $units;
    public $price;
    public $payment;
    public $cancelid;

	public function __construct($deal)
	{
	    $this->id = $deal['id']; 
		$this->type = $deal['type']; 
        $this->client_id = $deal['client_id'];
        $this->fund_id = $deal['fund_id'];
        $this->fund_name = $deal['name'];
        $this->scheme = $deal['cname'];
        $this->fund_tier = $deal['tier'];
		$this->deal_date = $deal['deal_date'];
		$this->units = $deal['units'];
		$this->price = $deal['price'];
		$this->payment = $deal['payment'];
		$this->cancelid = $deal['cancelid'];
	}

	public function dealdate()
	{
		return preg_replace('/\s\d+:\d+:\d+$/','',$this->deal_date);	
	}

	public function scheme()
	{
        return $this->scheme;
	}

	public function tier()
	{
		return $this->fund_tier;
	}

	public function fund_type()
	{
			return preg_match('/Employer/',$this->fund_name)
						? 'Employer' : 'Employee';	
	}
}

