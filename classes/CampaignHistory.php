<?php


require_once (dirname(__FILE__) . '/../superabandonedcart.php');

class CampaignHistory extends ObjectModel {
	
	public $id_campaign_history;
	public $id_campaign;
	public $id_customer;
	public $id_cart;
	public $id_cart_rule;
	public $click;
	public $converted;
	public $date_update;
	
	public static $definition = array(
	
		'table' => 'campaign_history',
		'primary' => 'id_campaign_history',
		'multilang' => false,
		'fields' => array(
			'id_campaign_history' => array(
				'type' => ObjectModel::TYPE_INT				
			),
			'id_campaign' => array(
				'type' => ObjectModel::TYPE_INT				
			),
			'id_customer' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'id_cart' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'id_cart_rule' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'click' => array(
				'type' => ObjectModel::TYPE_STRING

			),
			'converted' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'date_update' => array(
				'type' => ObjectModel::TYPE_DATE
			)
		)
	);
	
	// Override construct to link object to voucher object fields
	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id,$id_lang,$id_shop);	
	
	}
	
}
