<?php

class CustomersEmailCampaign extends ObjectModel {
	
	public $id_customersemailsender_campaign;
	public $name;
	public $email_tpl;
	public $execution_time_day;
	public $execution_time_hour;
	public $id_carts;
	public $id_voucher;
	public $active;
	
	// extra fields from cart Rule :
	public $voucher_name;
	public $voucher_prefix;
	public $voucher_amount_type;
	public $voucher_amount;
	public $voucher_day;
	
	
	
	public static $definition = array(
	
		'table' => 'customersemailsender_campaign',
		'primary' => 'id_customersemailsender_campaign',
		'multilang' => false,
		'fields' => array(
			'id_customersemailsender_campaign' => array(
				'type' => ObjectModel::TYPE_INT
				
			),
			'name' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'email_tpl' => array(
				'type' => ObjectModel::TYPE_HTML,
				'required' => true
			),
			'execution_time_day' => array(
				'type' => ObjectModel::TYPE_INT,
				'required' => true
			),
			'execution_time_hour' => array(
				'type' => ObjectModel::TYPE_INT,
				'required' => true
			),
			'voucher_name' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'voucher_prefix' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'voucher_day' => array(
				'type' => ObjectModel::TYPE_INT
			),
			'voucher_amount_type' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'voucher_amount' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'active' => array(
				'type' => ObjectModel::TYPE_BOOL,
				'required' => true
			)
			
		)
	);
	
	// Override construct to link object to voucher object fields
	/*public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id,$id_lang,$id_shop);
		
		// language @Todo manage language
		/*$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
		
		$cr = new CartRule($this->id_voucher,$defaultLanguage->id);
		
		$this->voucher_date_to = ( !empty($cr->date_to) ? date('Y-m-d',strtotime($cr->date_to)) : '');
		$this->voucher_name = $cr->name;
		$this->voucher_code = $cr->code;
		
		if( $this->voucher_amount_type == 'percent' ) {
			$this->voucher_amount_value = $cr->reduction_percent;
		}
		else{
			$this->voucher_amount_value = $cr->reduction_amount;
		}
		
	}
	*/
	
	public function getFileName($ext = ''){
	
		$tpl_file_name = strtolower(preg_replace("/[^A-Za-z0-9]/","",$this->name));
		$except = array('\\', '/', ':', '*', '?', '"', '<', '>', '|',' '); 
		$tpl_file_name = strtolower(str_replace($except, '', $tpl_file_name)); 
		$unwanted_array = array(    
							'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$tpl_file_name = strtr( $tpl_file_name, $unwanted_array );
		if( !empty($ext) ) { $ext = '.'.$ext; }
		return $tpl_file_name.$ext;
	
	}
	public function registerDiscount($count_voucher,$amount,$day,$type,$name, $campaign_name)
	{	
		
		$languages = Language::getLanguages(false);
		
		$cartRule = new CartRule();
		
		if( $type == 'percent'  )
			$cartRule->reduction_percent = $amount;
		else
			$cartRule->reduction_amount= $amount;
		
		$cartRule->quantity = $count_voucher;
		$cartRule->quantity_per_user = 1;
		$cartRule->date_from = date('Y-m-d H:i:s', time());
		$cartRule->date_to = date('Y-m-d H:i:s', time() + 86000*$day );

		$cartRule->minimum_amount_tax = true;
		$cartRule->code = preg_replace("/[^A-Za-z0-9]/","",$name).'_'.strtoupper(Tools::passwdGen(6));
		
		foreach ($languages as $lang) {
			
			$cartRule->name[$lang['id_lang']] = $name.' Campagne :'. $campaign_name;
		
		}
		
		$cartRule->reduction_tax = true;
		$cartRule->highlight = 1;
		if ( $cartRule->add() )
			return $cartRule;
		return false;
	}
	public function clean_old_reduction($prefix) 
	{		
	
		$sql = "DELETE FROM `"._DB_PREFIX_."cart_rule` WHERE code LIKE  '".$prefix."%' AND  date_to < '".date('Y-m-d H:i:s')."' AND quantity = 1";
	
		if( Db::getInstance()->Execute( $sql ) ) {

			return true;

		} else {

			return false;	 
		}	
	}	
	
	public function getCartContentHeader() {
		
		$module = new superabandonedcart();
		
		return $module->getCartContentHeader();
		
	}
	
}
