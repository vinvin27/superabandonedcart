<?php


require_once (dirname(__FILE__) . '/../superabandonedcart.php');

class Campaign extends ObjectModel {
	
	public $id_campaign;
	public $name;
	public $email_tpl;
	public $execution_time_day;
	public $execution_time_hour;
	public $active;
	//Extra Field for Create Voucher
	public $voucher_prefix;
	public $voucher_day;
	public $voucher_amount_type;
	public $voucher_amount;	
	public $is_abn_campaign;
	
	
	public $mailPath;
	
	
						
	public static $definition = array(
	
		'table' => 'campaign',
		'primary' => 'id_campaign',
		'multilang' => false,
		'fields' => array(
			'id_campaign' => array(
				'type' => ObjectModel::TYPE_INT				
			),
			'is_abn_campaign' => array(
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
	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		$this->mailPath = _PS_ROOT_DIR_.'/modules/superabandonedcart/mails/';
		parent::__construct($id,$id_lang,$id_shop);	
	
	}
	
	
	public function getFileName($ext = ''){
		// Avoid name with speciaux characters 
		$tpl_file_name = strtolower(preg_replace("/[^A-Za-z0-9]/","",$this->name));
		$except = array('\\', '/', ':', '*', '?', '"', '<', '>', '|',' '); 
		$tpl_file_name = strtolower(str_replace($except, '', $tpl_file_name)); 
		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$tpl_file_name = strtr( $tpl_file_name, $unwanted_array );
		if( !empty($ext) ) { $ext = '.'.$ext; }
		return $tpl_file_name.$ext;
	
	}
	
	public function getCartContentHeader(){
		
		$module = new superabandonedcart();
		return $module->getCartContentHeader();
		
	}
	
	public function registerDiscount($id_customer,$amount,$day,$type,$name)
	{	
		
		$languages = Language::getLanguages(false);
		
		$cartRule = new CartRule();
		
		if( $type == 'percent'  )
			$cartRule->reduction_percent = $amount;
		else
			$cartRule->reduction_amount= $amount;
		
		$cartRule->quantity = 1;
		$cartRule->quantity_per_user = 1;
		$cartRule->date_from = date('Y-m-d H:i:s', time());
		$cartRule->date_to = date('Y-m-d H:i:s', time() + 86000*$day );
		//$cartRule->minimum_amount = ''; // Utile ?
		$cartRule->minimum_amount_tax = true;
		$cartRule->code = $name.'_'.strtoupper(Tools::passwdGen(6));
		//$cartRule->code = $name;
		// QUESTION ? 
		// It does not work if I do not use languages but it works with the referalprogam module (Prestashop Module)
		foreach ($languages as $lang) {
			
			$cartRule->name[$lang['id_lang']] = $name.' Customer ID :'.$id_customer;
		
		}
		$cartRule->id_customer = (int)$id_customer;
		$cartRule->reduction_tax = true;
		$cartRule->highlight = 1;


		if ( $cartRule->add() )
			return $cartRule;

		return false;
	}
	
	public function clean_old_reduction($prefix) 
	{		
	
		$sql = "DELETE FROM `"._DB_PREFIX_."cart_rule` WHERE code LIKE  '".$prefix."%' AND  date_to < '".date('Y-m-d H:i:s')."' AND quantity = 1";
	
		if( Db::getInstance()->Execute( $sql ) )
			
			return true;
		
		else
		
			return false;	 
	
	}	
	
	
	public function getBaseURL()
    {
		
		$this->context = Context::getContext(); 
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$this->context->shop->domain.$this->context->shop->getBaseURI();
    }
	
	
	
}
