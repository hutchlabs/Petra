<?php defined('SYSPATH') or die ('No direct script access.');


class Model_Price extends Jelly_Model
{
	public static function initialize(Jelly_Meta $meta)
	{
			$meta->table('prices')
			->fields(array(
			'id' => new Field_Primary,
			'fund' => new Field_BelongsTo(
					array(
						'foreign' => 'funds.id',
						'column' => 'fund_id'
					)
			),
			'price' => new Field_String,
			'date' => new Field_String
		));
	}

	public function pricedate()
	{
		return preg_replace('/\s\d+:\d+:\d+$/','',$this->date);	
	}

	public function scheme()
	{
		return preg_replace('/Tier.*?$/','',$this->fund['name']);	
	}

	public function tier()
	{
		return $this->fund['tier'];
	}

	public function fund_type()
	{
			return preg_match('/Employer/',$this->fund['name'])
						? 'Employer' : 'Employee';	
	}
}
