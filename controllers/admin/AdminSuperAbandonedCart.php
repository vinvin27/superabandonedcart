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
        
        $cron_url = $this->getBaseURL().'/modules/superabandonedcart/launch_campaings.php?secure_key='.Configuration::get('SUPER_AC_SECURE_KEY');;
        
        $header  = '<div class="alert alert-info">
        				<p>  '. $this->l('Don\'t forget to set cron task :') .' </p>
        				<p><b> */30 * * * * wget -O /dev/null '.$cron_url.'</b></p>	
        				<br/>
        				<p><b>'. $this->l('All email send is also send to : ') . Configuration::get('PS_SHOP_EMAIL') .'</b></p>
        				
        				
        			</div>';
        return $header . parent::renderList();
    }
	
	public function initToolbar() {
        parent::initToolbar();
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
    	
    	$voucher_type[] = array( 'voucher_type' => 'fixe', 'name'=> 'Fixed');
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
                    'class' => 't',
                    'desc' => 'Also mail subject'
                    
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
					'desc' => $this->l('Available variables : {firstname} , {lastname} , {coupon_name} , {coupon_code} , {coupon_value} , {coupon_valid_to} , {campaign_name}, {cart_content} {track_url} {track_request}')
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Include voucher'),
                    'name' => 'include_voucher',
                    'class' => 't include_voucher',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'include_voucher_enable',
                            'value' => 1,
                            'checked' => 'checked',
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'include_voucher_disable',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher prefix : '),
                    'name' => 'voucher_prefix',
                    'size' => 60,
                    'class' => 'voucher_mode',
                    'desc' => $this->l('Prefix for the voucher create')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Voucher percent or fixed  : '),
                    'name' => 'voucher_amount_type',
                    'desc' => $this->l('Is an percentage or fixe voucher?'),
                    'class' => 'voucher_mode',
                    'options' => array(
                    	'query' => $voucher_type,   
                    	'id' => 'voucher_type',
                    	'name' => 'name'     
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher value : '),
                    'name' => 'voucher_amount',
                    'class' => 'voucher_mode',
                    'size' => 60,
                    'desc' => $this->l('Voucher value ?')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Voucher valid in day '),
                    'name' => 'voucher_day',
                    'size' => 60,
                    'class' => 'voucher_mode',
                    'desc' => $this->l('If empty, code will automatically generated.')
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Status'),
                    'name' => 'active',
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
        
        //update way :
        $extra = '';
        if ($id_campaign = Tools::getValue('id_campaign')){
        	$campaign = new Campaign($id_campaign);
        	// If no voucher disable voucher display only 
        	if( $campaign->voucher_prefix ==  '' ){
        		$extra = '<script> $( function()
        							 { 
        								$("#include_voucher_disable").attr("checked","checked");
        								$(".voucher_mode").parent().parent(".form-group").fadeOut();
        							 } ); </script>';
        	}
        	
        	
        }
        
        return $extra . parent::renderForm();
    }
    
    
    
 	public function postProcess() {
 	
 		//ADD
        if (Tools::isSubmit('submitAddcampaign')) {
        
        
            parent::validateRules();
            
            if (count($this->errors))
                return false;
           
            // ADD WAY
            if ( ( !$id_campaign = (int) Tools::getValue('id_campaign') ) && empty($this->errors) ) 
			{
            					
            	$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
					
            	// Include voucher : 
            	
			
				
				
				// Create campaign : 
				$campaign = new Campaign();
				$campaign->name = Tools::getValue('name');
				$campaign->email_tpl = Tools::getValue('email_tpl');
				$campaign->execution_time_day = Tools::getValue('execution_time_day');
				$campaign->execution_time_hour = Tools::getValue('execution_time_hour');
				$campaign->voucher_prefix = Tools::getValue('voucher_prefix');
				$campaign->voucher_amount = Tools::getValue('voucher_amount');
				$campaign->voucher_amount_type = Tools::getValue('voucher_amount_type');
				$campaign->voucher_day = Tools::getValue('voucher_day');
				
				
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
            } 
			elseif ($id_campaign = Tools::getValue('id_campaign')) 
			{
             
             	$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));	
				
				// Create campaign : 
				$campaign = new Campaign($id_campaign);
				
				$campaign->name = Tools::getValue('name');
				$campaign->email_tpl = Tools::getValue('email_tpl');
				$campaign->execution_time_day = Tools::getValue('execution_time_day');
				$campaign->execution_time_hour = Tools::getValue('execution_time_hour');
				$campaign->voucher_prefix = Tools::getValue('voucher_prefix');
				$campaign->voucher_amount = Tools::getValue('voucher_amount');
				$campaign->voucher_amount_type = Tools::getValue('voucher_amount_type');
				$campaign->voucher_day = Tools::getValue('voucher_day');
				
				
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
        elseif( Tools::getIsset('deletecampaign') &&  Tools::getValue($this->identifier) ) {
        
        	$id_campaign = (int)Tools::getValue($this->identifier);   	
        	
			$b = new Campaign($id_campaign);
			$b->delete();
			unset($b);
        		
        	
        
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
	
	// Récupère aussi le dossier si le Shop est dedans
	public function getBaseURL()
    	{
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
			.$this->context->shop->domain.$this->context->shop->getBaseURI();
    	}
	
	
}
