<?php
require_once (dirname(__FILE__) . '/../../classes/Campaign.php');
require_once (dirname(__FILE__) . '/../../classes/CampaignHistory.php');

class AdminSuperAbandonedCartStatsController extends AdminController {

	public  $messageHeader;

  public function __construct() {
      
      	$this->table = 'campaign';
        $this->className = 'Campaign';
        $this->module = 'superabandonedcart';
        $this->lang = false;
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
        
       return  $this->renderHistoryList();
    }
	
	public function initToolbar() {
        parent::initToolbar();
    }
    
	
	
	
    public function renderForm() {
    
    	$id_lang = (int) Context::getContext()->language->id;
    	$categories = Category::getSimpleCategories($id_lang);
    	$array_day = $voucher_type = $array_hour = array();
    	for($i=1 ; $i<32 ; $i++){
    		$array_day[] = array( 'id_day' => $i , 'name' => $i.' Days' );
    	
    	}
    	
        for($i=1 ; $i<25 ; $i++){
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
					'desc' => $this->l('Available variables : {firstname} , {lastname} , {coupon_name} , {coupon_code} , {coupon_value} , {coupon_valid_to} , {campaign_name}, {cart_content} , {track_url} , {track_request}')
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
                    'label' => $this->l('Voucher valid in day :'),
                    'name' => 'voucher_day',
                    'size' => 60,
                    'class' => 'voucher_mode',
                    'desc' => $this->l('How many days voucher will be valid? ')
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
 	
        
	}
	
	
	public function writeFile($path,$content){

		
	
	}
	public function renderHistoryList()
	{
		
		$module = new superabandonedcart();

		$historyList = CampaignHistory::getHistory();
		$fields_list = $this->getStandardFieldList();

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = true;
		//$helper->actions = array('delete');
		$helper->show_toolbar = false;
		//$helper->module = $module;
		$helper->listTotal = count($historyList);
		$helper->identifier = 'id_campaign_history';
		$helper->title = $this->l('Cart Stats History');
		$helper->table = $module->name;
		$helper->token = Tools::getAdminTokenLite('AdminSuperAbandonedCart');
		$helper->currentIndex = AdminController::$currentIndex;


		return $helper->generateList($historyList, $fields_list);
	}
	
	public function getStandardFieldList()
	{
		return array(
			'id_campaign_history' => array(
				'title' => $this->l('ID'),
				'type' => 'text',
			),
			'id_customer' => array(
				'title' => $this->l('Customer ID'),
				'type' => 'text',
			),
			'id_cart' => array(
				'title' => $this->l('Cart ID'),
				'type' => 'text',
			),
			'id_campaign' => array(
				'title' => $this->l('Campaign ID'),
				'type' => 'text',
			),
			'click' => array(
				'title' => $this->l('Cliked'),
				'active' => 'click',
                'type' => 'bool',
			),
			'converted' => array(
				'title' => $this->l('Converted'),
				'active' => 'converted',
                'type' => 'bool',
			),
		);
	}

	
	// Récupère aussi le dossier si le Shop est dedans
	public function getBaseURL()
    	{
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
			.$this->context->shop->domain.$this->context->shop->getBaseURI();
    	}
	
	
}
