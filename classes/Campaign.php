<?php


<<<<<<< HEAD
require_once (dirname(__FILE__) . '/../superabandonedcart.php');
=======
>>>>>>> 3bc118cb15124b2e0c83671f7268f2be768bd469

class Campaign extends ObjectModel {
	
	public $id_campaign;
	public $name;
	public $email_tpl;
	public $execution_time_day;
	public $execution_time_hour;
	public $id_carts;
	public $id_voucher;
	public $active;
	
	// extra fields from cart Rule :
	public $voucher_name;
	public $voucher_code;
	public $voucher_amount_type;
	public $voucher_amount_value;
	public $voucher_date_to;
	
	
	
	public static $definition = array(
	
		'table' => 'campaign',
		'primary' => 'id_campaign',
		'multilang' => false,
		'fields' => array(
			'id_campaign' => array(
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
			'id_carts' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'id_voucher' => array(
				'type' => ObjectModel::TYPE_INT
			),
			'active' => array(
				'type' => ObjectModel::TYPE_BOOL,
				'required' => true
			),
			'voucher_amount_type' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			)
<<<<<<< HEAD
=======
			/*,
			'voucher_name' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'voucher_code' => array(
				'type' => ObjectModel::TYPE_STRING
			),
			'voucher_amount_type' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'voucher_amount_value' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			),
			'voucher_date_to' => array(
				'type' => ObjectModel::TYPE_STRING,
				'required' => true
			)*/
>>>>>>> 3bc118cb15124b2e0c83671f7268f2be768bd469
		)
	);
	
	// Override construct to link object to voucher object fields
	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id,$id_lang,$id_shop);
		
		// language @Todo manage language
		$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
		
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
	
	
	public function getFileName($ext = ''){
		$tpl_file_name = $this->name;
		$except = array('\\', '/', ':', '*', '?', '"', '<', '>', '|',' '); 
		$tpl_file_name = strtolower(str_replace($except, '', $tpl_file_name)); 
<<<<<<< HEAD
		$unwanted_array = array(    
							'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
=======
		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
>>>>>>> 3bc118cb15124b2e0c83671f7268f2be768bd469
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$tpl_file_name = strtr( $tpl_file_name, $unwanted_array );
		if( !empty($ext) ) { $ext = '.'.$ext; }
<<<<<<< HEAD
	
=======
>>>>>>> 3bc118cb15124b2e0c83671f7268f2be768bd469
		return $tpl_file_name.$ext;
	
	}
	
<<<<<<< HEAD
	public function getCartContentHeader(){
		$module = new superabandonedcart();
		return $module->getCartContentHeader();
	}
	
=======
>>>>>>> 3bc118cb15124b2e0c83671f7268f2be768bd469
}