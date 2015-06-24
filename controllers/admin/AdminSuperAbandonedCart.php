<?php
require_once (dirname(__FILE__) . '/../../classes/Campaign.php');

class AdminSuperAbandonedCartController extends AdminController {


  public function __construct() {
      
      	$this->table = 'campaign';
        $this->className = 'Campaign';
        $this->module = 'superabandonedcart';
        $this->lang = false;
        //$this->image_dir = '../modules/categoriesbanner/images';
        $this->context = Context::getContext();
        $this->_defaultOrderBy = 'created';
        $this->_defaultorderWay = 'DESC';
        $this->bootstrap = true;
        $this->bulk_actions = array(
			'delete' => array(
				'text' => $this->l('Delete selected'),
				'icon' => 'icon-trash',
				'confirm' => $this->l('Delete selected items?')
			)
		);
        if (Shop::isFeatureActive())
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        parent::__construct();
        $this->fields_list = array(
            'id_campaign' => array(
                'title' => $this->l('Campaign #'),
                'width' => 10,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false
            ),
             'name' => array(
                'title' => $this->l('Name'),
                'width' => 100,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false,
            ),
            'execution_time_day' => array(
                'title' => $this->l('Execution schedule day'),
                'width' => 10,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'callback' => 'execution_time_day'
            ),
            'execution_time_hour' => array(
                'title' => $this->l('Execution schedule hour'),
                'width' => 10,
                'type' => 'text',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'callback' => 'execution_time_hour'
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => '70',
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter' => false,
                'search' => false
            )
        );
        $this->_defaultOrderBy = 'a.id_campaign';
        $this->_defaultOrderWay = 'DESC';
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->_group = 'GROUP BY a.id_campaign';
        }
        parent::__construct();
    }
    public function renderList() {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        
        $cron_url = Tools::getHttpHost(true).'/modules/superabandonedcart/launch_campaings.php';
        
        $header  = '<div class="alert alert-info">
        				<p>Don\'t forget to set cron task : </p>
        				<p><b> 30 * * * * '.$cron_url.'</b></p>	
        			</div>';
        return $header . parent::renderList();
    }
	public function initToolbar() {
        parent::initToolbar();
    }
    
	
	
    public function DisplayCategorieById($cats){
		if(empty($cats)){ return '-'; };
    	$ids =  explode(',',$cats);
    	$return = '';
    	foreach( $ids as $id ){
    	
			$id_lang = (int) Context::getContext()->language->id;
			$cat = new Category($id,$id_lang);
			$return .= $cat->name . ', ';    	
    	}
    	return $return;
    	
    	
    
	}
	
	public function execution_time_day($day){
		return (empty($day) ? 0 : $day ). ' Day(s)';
		
	}
	
    public function execution_time_hour($hour){
		return (empty($hour)? 0 :  $hour ).' Hour(s)';
	}
	
    public function renderForm() {
    
    	$id_lang = (int) Context::getContext()->language->id;
    	$categories = Category::getSimpleCategories($id_lang);
    	$array_day = $voucher_type = $array_hour = array();
    	for($i=0 ; $i<32 ; $i++){
    		$array_day[] = array( 'id_day' => $i , 'name' => $i.' Days' );
    	
    	}
    	
        for($i=0 ; $i<25 ; $i++){
    		$array_hour[] = array( 'id_hour' => $i , 'name' => $i.' Hours' );
    	
    	}
    	
    	$voucher_type[] = array( 'voucher_type' => 'fixe', 'name'=> 'Fixe');
    	$voucher_type[] = array( 'voucher_type' => 'percent', 'name' => 'Percent');
    	
    	
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Create new campaign'),
            ),
            'input' => array(
            	array(
                    'type' => 'text',
                    'label' => $this->l('Campaign name : '),
                    'name' => 'name',
                    'required' => true,
                    'class' => 't'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('After how many day(s) send campaign ? : '),
                    'name' => 'execution_time_day',
                    'required' => true,
                    'class' => 't',
                    'options' => array(
                    	'query' => $array_day,   
                    	'id' => 'id_day',
                    	'name' => 'name'     
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('After how many hour(s) send campaign ? : '),
                    'name' => 'execution_time_hour',
                    'required' => true,
                    'class' => 't',
                   	'options' => array(
                    	'query' => $array_hour,   
                    	'id' => 'id_hour',
                    	'name' => 'name'     
                    )
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Mail Template : '),
                    'name' => 'email_tpl',
                    'size' => 60,
                    'rows' => 10,
                    'cols' => 62,
                    'class' => 'rte',
                    'autoload_rte' => true,
                    'required' => true,
                    'desc' => $this->l('Available variables : {firstname} , {lastname} , {coupon_name} , {coupon_code} , {coupon_value} , {coupon_valid} , {campaign_name}, {cart_content}')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher name : '),
                    'name' => 'voucher_name',
                    'size' => 60,
                    'required' => true,
                    'desc' => $this->l('Name of the voucher')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher code : '),
                    'name' => 'voucher_code',
                    'size' => 60,
                    'required' => true,
                    'desc' => $this->l('If empty, code will automatically generated.')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Voucher percent or fixed  : '),
                    'name' => 'voucher_amount_type',
                    'required' => true,
                    'desc' => $this->l('Is an percentage or fixe voucher?'),
                    'options' => array(
                    	'query' => $voucher_type,   
                    	'id' => 'voucher_type',
                    	'name' => 'name'     
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher value : '),
                    'name' => 'voucher_amount_value',
                    'size' => 60,
                    'required' => true,
                    'desc' => $this->l('Voucher value ?')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher valid to : '),
                    'name' => 'voucher_date_to',
                    'size' => 60,
                    'class' => 'datepicker input-medium',
                    'required' => true,
                    'desc' => $this->l('If empty, code will automatically generated.')
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
            );
        }
        if (!($BlogCategory = $this->loadObject(true)))
            return;
        $this->fields_form['submit'] = array(
            'title' => $this->l('Save   '),
            'class' => 'button'
        );
        
        
        
        
        return parent::renderForm();
    }
    
    
    
 	public function postProcess() {
 	
 		//ADD
        if (Tools::isSubmit('submitAddcampaign')) {
        
        
            parent::validateRules();
            
            if (count($this->errors))
                return false;
                
                
        	//if( !Tools::getIsset('voucher_name') OR Tools::isEmpty(Tools::getValue('voucher_name')) ){ $this->errors[] = Tools::displayError('Voucher name can\'t be empty'); }
           
            // ADD WAY
            if ( ( !$id_campaign = (int) Tools::getValue('id_campaign') ) && empty($this->errors) ) {
            
            	// Check values for voucher : 
            	$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
				$voucher_name = Tools::getValue('voucher_name');
				$voucher_code = Tools::getValue('voucher_code');
				$voucher_amount_type = Tools::getValue('voucher_amount_type');
				$voucher_amount_value = Tools::getValue('voucher_amount_value');
				$voucher_date = Tools::getValue('voucher_date_to');
				
				$new_voucher = new CartRule(null,$defaultLanguage->id);
				
				$new_voucher->name = $voucher_name;
				
				$new_voucher->date_from = date('Y-m-d');
				$new_voucher->date_to = $voucher_date;
				$new_voucher->description = 'Campaign : '.$voucher_name;
				$new_voucher->code = $voucher_code;
				$new_voucher->quantity = 1000; // Todo : Update when campaign is lunch to number of concerned people
				// Si percent : 
				if( $voucher_amount_type == 'percent' ){
					$new_voucher->reduction_percent = $voucher_amount_value;
				} // if fixed amount
				else{
					$new_voucher->reduction_amount = $voucher_amount_value;
				}
				
				$new_voucher->save();
				
				// Create campaign : 
				$campaign = new Campaign();
				$campaign->name = Tools::getValue('name');
				$campaign->email_tpl = Tools::getValue('email_tpl');
				$campaign->execution_time_day = Tools::getValue('execution_time_day');
				$campaign->execution_time_hour = Tools::getValue('execution_time_hour');
				$campaign->voucher_amount_type = Tools::getValue('voucher_amount_type');
				$campaign->id_voucher = $new_voucher->id;
				$campaign->active = Tools::getValue('active');
				
				// Create email files :
				$path = _PS_ROOT_DIR_.'/modules/superabandonedcart/mails/'.$defaultLanguage->iso_code.'/';
				if( !file_exists( $path ) ){
					if( !mkdir( $path , 0777 , true ) ){ 
						 $this->errors[] = Tools::displayError('Mails directory could not be created. Please check system permissions');
					}
				}
				
				
				$tpl_file_name = $campaign->getFileName('html');
    			
				// create html files
				$f = fopen($path.$tpl_file_name, 'w');

				fwrite($f, $campaign->email_tpl);
				fwrite($f, PHP_EOL);
				fclose($f);
				
				$tpl_file_name = $campaign->getFileName('txt');
				// create txt files
				$f = fopen($path.$tpl_file_name, 'w');

				fwrite($f, strip_tags($campaign->email_tpl) );
				fwrite($f, PHP_EOL);
				fclose($f);
				
                if (!$campaign->save()){
                    $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
                }
                
                
                
            // UPDATE WAY
            } elseif ($id_campaign = Tools::getValue('id_campaign')) {
             
             	$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
				$voucher_name = Tools::getValue('voucher_name');
				$voucher_code = Tools::getValue('voucher_code');
				$voucher_amount_type = Tools::getValue('voucher_amount_type');
				$voucher_amount_value = Tools::getValue('voucher_amount_value');
				$voucher_date = Tools::getValue('voucher_date_to');
				
				
				$campaign = new Campaign($id_campaign);
				
				$new_voucher = new CartRule($campaign->id_voucher,$defaultLanguage->id);
				
				$new_voucher->name = $voucher_name;
				
				
				$new_voucher->date_from = date('Y-m-d');
				$new_voucher->date_to = $voucher_date;
				$new_voucher->description = 'Campaign : '.$voucher_name;
				$new_voucher->code = $voucher_code;
				$new_voucher->quantity = 1000; // Todo : Update when campaign is lunch to number of concerned people
				
				// Si percent : 
				if( $voucher_amount_type == 'percent' ){
					$new_voucher->reduction_percent = $voucher_amount_value;
				} // if fixed amount
				else{
					$new_voucher->reduction_amount = $voucher_amount_value;
				}
			//	d($new_voucher);
				if( ! $new_voucher->save() ){
					$this->errors[] = Tools::displayError('An error has occured : when saved voucher');
				}
				
				// Create campaign : 
				
				$campaign->name = Tools::getValue('name');
				$campaign->email_tpl = Tools::getValue('email_tpl');
				$campaign->execution_time_day = Tools::getValue('execution_time_day');
				$campaign->execution_time_hour = Tools::getValue('execution_time_hour');
				$campaign->voucher_amount_type = $voucher_amount_type;
				$campaign->id_voucher = $new_voucher->id;
				$campaign->active = Tools::getValue('active');
				
				$path = _PS_ROOT_DIR_.'/modules/superabandonedcart/mails/'.$defaultLanguage->iso_code.'/';
				if( !file_exists( $path ) ){
					if( !mkdir( $path , 0777 , true ) ){ 
						 $this->errors[] = Tools::displayError('Mails directory could not be created. Please check system permissions');
					}
				}
				$tpl_file_name = $campaign->getFileName('html');
    			
				// create html files
				$f = fopen($path.$tpl_file_name, 'w');

				fwrite($f, $campaign->email_tpl);
				fwrite($f, PHP_EOL);
				fclose($f);
				
				$tpl_file_name = $campaign->getFileName('txt');
				// create txt files
				$f = fopen($path.$tpl_file_name, 'w');

				fwrite($f, strip_tags($campaign->email_tpl) );
				fwrite($f, PHP_EOL);
				fclose($f);
				
				
				
				if (!$campaign->save()){
                    $this->errors[] = Tools::displayError('An error has occurred: Can\'t save the current object');
                }
                
            }
            
            
       }
       elseif (Tools::isSubmit('statuscampaign') && Tools::getValue($this->identifier)) {
            if ($this->tabAccess['edit'] === '1') {
                if (Validate::isLoadedObject($object = $this->loadObject())) {
                    if ($object->toggleStatus()) {
                        $identifier = ((int) $object->id_parent ? '&id_campaign=' . (int) $object->id_parent : '');
                        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperAbandonedCart'));
                    } else
                        $this->errors[] = Tools::displayError('An error occurred while updating the status.');
                } else
                    $this->errors[] = Tools::displayError('An error occurred while updating the status for an object.')
                            . ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
            } else
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
        }
        elseif( Tools::getIsset('submitBulkdeletecampaign') && (Tools::getValue('campaignBox')) ){
        
        	$ids_banner_deleted = Tools::getValue('campaignBox');
        	
        	// remove each banner
        	foreach( $ids_banner_deleted as $id ){
        	
        		$b = new Campaign($id);
        		$b->delete();
        		unset($b);
        		
        	}
        
        }
        // Enable selection 
        elseif( Tools::getIsset('submitBulkenableSelectioncampaign') && (Tools::getValue('campaignBox')) ){
        
        	$ids_banner_deleted = Tools::getValue('campaignBox');
        	
        	// remove each banner
        	foreach( $ids_banner_deleted as $id ){
        	
        		$b = new Campaign($id);
        		$b->toggleStatus();
        		unset($b);
        		
        	}
        
        }
        // Disable selection
         elseif( Tools::getIsset('submitBulkdisableSelectioncampaign') && (Tools::getValue('campaignBox')) ){
        
        	$ids_banner_deleted = Tools::getValue('campaignBox');
        	
        	// remove each banner
        	foreach( $ids_banner_deleted as $id ){
        	
        		$b = new Campaign($id);
        		$b->toggleStatus();
        		unset($b);
        		
        	}
        
        }
	}
	
	
	
	/* public function processImageCategory($FILES, $id) {
        if (isset($FILES['path_img']) && isset($FILES['path_img']['tmp_name']) && !empty($FILES['path_img']['tmp_name'])) {
            if ($error = ImageManager::validateUpload($FILES['path_img'], 4000000))
                return Tools::displayError($this->l('Invalid image'));
            else {
                $ext = substr($FILES['path_img']['name'], strrpos($FILES['path_img']['name'], '.') + 1);
                $file_name = 'ban_'.$id . '.' . $ext;
                $path = _PS_MODULE_DIR_ . 'categoriesbanner/images/' . $file_name;
                
                // if file exist (update case)
                if (file_exists($path)){
                	unlink($path);
                }
                
                $banner = new Banner($id);
				$banner->path_img = '/modules/categoriesbanner/images/' . $file_name;
				$banner->save();
				if (!move_uploaded_file($FILES['path_img']['tmp_name'], $path))
                    return Tools::displayError($this->l('An error occurred while attempting to upload the file.'));
                
            }
        }
    } */
}
