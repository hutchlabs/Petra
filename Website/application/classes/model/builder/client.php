<?php defined('SYSPATH') or die ('No direct script access.');


class Model_Builder_Client extends Jelly_Builder
{
	public function active()
	{
		return $this->where('status', '=', 'Active');	
	}

	public function individuals()
	{
		return $this->where('type', '=', 'employee')->order_by('name','asc');	
	}

	public function companies()
	{
		return $this->where('type', '=', 'employer')->order_by('name','asc');	
	}
}
