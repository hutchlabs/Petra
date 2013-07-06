<?php defined('SYSPATH') or die ('No direct script access.');


class Model_Base extends Jelly_Model
{
    protected function d($var, $exit=false)
    {
        print '<pre>'; print_r($var); print '</pre>';
        if($exit) { exit; }
    }

    protected function create_date_range($strDateFrom, $strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.
        $aryRange = array();

        $iDateFrom = mktime(1,0,0,substr($strDateFrom,5,2),     
                                  substr($strDateFrom,8,2),
                                  substr($strDateFrom,0,4));

        $iDateTo = mktime(1,0,0,substr($strDateTo,5,2),     
                                substr($strDateTo,8,2),
                                substr($strDateTo,0,4));

        if ($iDateTo >= $iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
          
            while ($iDateFrom < $iDateTo)
            {   
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }

        return $aryRange;
    }

    protected function connect()
    {
        $l = mysql_connect('mysql.petratrust.com','pmmember','sh0wm3th3m0n3y');
        mysql_select_db('petramembersdb',$l);
        return $l;
    }
}
