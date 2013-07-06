<?php defined('SYSPATH') or die ('No direct script access.');


class Model_Fund extends Jelly_Model
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('funds')
			->fields(array(
			'id' => new Field_Primary,
			'name' => new Field_String,
			'type' => new Field_String,
			'tier' => new Field_String,
			'phone' => new Field_String,
			'email' => new Field_String,
			'description' => new Field_String,
			'status' => new Field_String,
		));
	}

 
	public function tier()
	{
		return $this->tier;
	}

	public function holder()
	{
			return (preg_match('/Employee/',$this->name,$m))
					? 'employee' : 'employer';
	}
}
