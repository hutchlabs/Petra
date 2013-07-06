<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Base extends Controller_Template {
	
	// Base template
	public $template = 'templates/base';
	
	// Auth instance
	public $auth;
	
	public function before()
	{
		parent::before();
		
		// Retrieve auth instance
		$this->auth = Auth::instance();

		
		if ($this->auto_render === TRUE)
		{
			// Default template title
			$this->template->title    = '';
			$this->template->content  = '';
			$this->template->user  = $this->auth->get_user();
			$this->template->logged_in  = $this->auth->logged_in();
		}
	}

    protected function d($array,$exit=false)
    {
        print '<pre>'; print_r($array); print '</pre>';
        if ($exit) { exit; }
    }

    protected function connect()
    {
        $l = mysql_connect('mysql.petratrust.com','pmmember','sh0wm3th3m0n3y');
        mysql_select_db('petramembersdb',$l);
        return $l;
    }

    protected function createDateRangeArray($strDateFrom,$strDateTo)
    {
        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),    
               substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     
               substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }
    
    protected function SendEmail($message, $subject, $to,$attachment='')
    {
            $message = wordwrap(stripslashes($message), 70,"\r\n");

            require_once Kohana::find_file('vendor', 
                                      'phpmailer/class.phpmailer','php');

            $mail = new PHPMailer();
            $mail->SetLanguage("en", 'phpmailer/language/');
            $mail->From = 'no-reply@petratrust.com';
            $mail->FromName = 'Petra Trust';
            $mail->Subject = stripslashes($subject);
            $mail->Body = stripslashes($message);
            if ($attachment<>'') {
                $mail->AddAttachment($attachment,'Statement.pdf');
            }
            $mail->AddAddress($to);
            $res = $mail->Send();

            return ($res===true) ? $res : $mail->ErrorInfo;
    }

    protected function SendSMS($message, $number)
    {
        $text = $this->myUrlEncode($message);
        $number = '+233' . substr($number, 1);
        $url = 'http://app.rancardmobility.com/rmcs/sendMessage.jsp'.
                  '?username=GraMeaNDrm&password=g9m3e3nd6&from=1982&'.
               'to='.$number.'&text='.$text;
        $result = $this->curl_post($url);

        $result = preg_replace("/[\n\r]/",'',$result); 
        return (preg_match('/000|OK/',$result)) ? true : $result;
    }

    protected function myUrlEncode($string) 
    {
        $string = preg_replace("/[\n\r]/",' ',$string); 
        $string = str_replace(" ", "%20", $string);
        return $string;
    }

    /** 
     * Send a POST requst using cURL 
     * @param string $url to request 
     * @param array $post values to send 
     * @param array $options for cURL 
     * @return string 
     */ 
    protected function curl_post($url, $post = NULL, $options = array()) 
    { 
            $defaults = array( 
                  CURLOPT_POST => 1, 
                  CURLOPT_HEADER => 0, 
                  CURLOPT_URL => $url, 
                  CURLOPT_FRESH_CONNECT => 1, 
                  CURLOPT_RETURNTRANSFER => 1, 
                  CURLOPT_FORBID_REUSE => 1, 
                  CURLOPT_TIMEOUT => 4, 
                  CURLOPT_POSTFIELDS => @http_build_query($post) ); 

            $ch = curl_init(); 
            curl_setopt_array($ch, ($options + $defaults)); 
            if( ! $result = curl_exec($ch)) 
            { 
                return curl_error($ch); 
            } 
         curl_close($ch); 
         return $result; 
    } 
	
} // End Controller_Base
