<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
if (!defined('_PS_VERSION_'))
	exit;
	
	
require_once (dirname(__FILE__) . '/classes/Campaign.php');
class superabandonedcart extends Module
{

	public function __construct()
	{
		$this->name = 'superabandonedcart';
		$this->tab = 'checkout';
		$this->version = '2.0.1';
		$this->author = 'Vince';
		$this->need_instance = 0;
		$this->bootstrap = true;
		parent::__construct();
		$this->displayName = $this->l('Super Abandoned Cart');
		$this->description = $this->l('Increase your sales thanks to abandoned cart');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}
	
	public function install()
	{
		// If SECURE KEY doesn't exist  
		if( !Configuration::get('SUPER_AC_SECURE_KEY') ) {
			Configuration::updateValue('SUPER_AC_SECURE_KEY', md5( _COOKIE_KEY_.time()));
		}
		
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'campaign` (
				  `id_campaign` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `voucher_prefix` varchar(50) NOT NULL,
				  `voucher_amount` varchar(50) NOT NULL,
				  `voucher_amount_type` varchar(50) NOT NULL,
				  `voucher_day` varchar(50) NOT NULL,
				  `email_tpl` text NOT NULL,
				  `execution_time_day` int(11) NOT NULL,
  				  `execution_time_hour` int(11) NOT NULL,
				  `active` tinyint(1) NOT NULL,
				  PRIMARY KEY (`id_campaign`)
				) ENGINE=InnoDB;
				
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'campaign_history` (
				  `id_campaign_history` int(11) NOT NULL AUTO_INCREMENT,
				  `id_campaign` int(11) NOT NULL ,
				  `id_customer` int(11) NOT NULL ,
				  `id_cart` int(11) NOT NULL,
				  `id_cart_rule` int(11) NOT NULL,
				  `click` int(1) NOT NULL,
				  `converted` int(1) NOT NULL,
				  `date_update` datetime, 	
				  PRIMARY KEY (`id_campaign_history`)
				) ENGINE=InnoDB ;
				
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'campaign_shop` (
				  `id_campaign` int(11) NOT NULL,
				  `id_shop` int(11) NOT NULL
				) ENGINE=InnoDB;
				';
		
		Db::getInstance()->Execute($sql);
		
		
		// Alert table : V.2.0.1 (Is abn campaing ?)
		Db::getInstance()->Execute('ALTER TABLE   `'._DB_PREFIX_.'campaign` ADD  `is_abn_campaign` BOOLEAN NOT NULL AFTER  `execution_time_hour`');
		
		
		$this->CreateTabs();
		
		if (!parent::install() || !$this->registerHook('displayBackOfficeHeader') || !$this->registerHook('displayHeader') || !$this->registerHook('displayAdminOrder') )
			return false;
		return true;
	}
	
	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCss($this->_path.'views/css/tab.css');
		$this->context->controller->addJs($this->_path.'views/js/js.js');
	}
	
	public function uninstall()
	{
		$idtabs = array();
		$idtabs[] = Tab::getIdFromClassName("AdminSuperAbandonedCart");
		$idtabs[] = Tab::getIdFromClassName("AdminSuperAbandonedCartStats");
		foreach ($idtabs as $tabid):
			if ($tabid) {
				$tab = new Tab($tabid);
				$tab->delete();
			}
        endforeach;
        
        $sql = array('DROP table '._DB_PREFIX_.'campaign','DROP table '._DB_PREFIX_.'campaign_history','DROP table '._DB_PREFIX_.'campaign_shop');
        
        foreach( $sql as $remove )
        Db::getInstance()->Execute($remove);
        
		return parent::uninstall() AND $this->unregisterHook('displayBackOfficeHeader') && $this->unregisterHook('displayHeader') && $this->unregisterHook('displayAdminOrder') ;
	}	
	
	private function CreateTabs() 
	{
        $langs = Language::getLanguages();
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        
        $smarttab = new Tab();
        $smarttab->class_name = "AdminSuperAbandonedCart";
        $smarttab->module = "";
        $smarttab->id_parent = 0;
        foreach ($langs as $l) {
            $smarttab->name[$l['id_lang']] = $this->l('Super Abandoned Cart');
        }
        $smarttab->save();
        $tab_id = $smarttab->id;
      
      	// create child tab :
      	$tabvalue = array(
      	
      		array( 
      			'class_name' => 'AdminSuperAbandonedCart',
      			'name' =>  $this->l('Campaign'),
      			'module' => 'superabandonedcart'	
      		),
      		// tab stats :
      		array( 
      			'class_name' => 'AdminSuperAbandonedCartStats',
      			'name' =>  $this->l('Stats'),
      			'module' => 'superabandonedcart'	
      		)
      	
      	);
      	foreach ($tabvalue as $tab) {
            $newtab = new Tab();
            $newtab->class_name = $tab['class_name'];
            $newtab->id_parent = $tab_id;
            $newtab->module = $tab['module'];
            foreach ($langs as $l) {
                $newtab->name[$l['id_lang']] = $this->l($tab['name']);
            }
            $newtab->save();
        }
      
        return true;
    }
	
	public function hookDisplayHeader()
	{
		
		if( Tools::getValue('id_customer') && Tools::getValue('id_cart') )
		{

			$sql = 'UPDATE `'._DB_PREFIX_.'campaign_history` SET click = 1 WHERE id_customer = '.(int)Tools::getValue('id_customer').' AND id_cart = '.(int)Tools::getValue('id_cart').' ';
			Db::getInstance()->Execute($sql);			
			
		}		
		
	}
	
	
	public function hookdisplayAdminOrder()
	{
  
		$id_order = Tools::getValue('id_order');
		$token = Tools::getAdminTokenLite('AdminOrders');
		$commande = new Order($id_order);
		$order_cart_rule = $commande->getCartRules();
		
		if( count($order_cart_rule) > 0) {
		
			// Est ce on veut exatement le même numéro de panier ?
			//AND `id_cart` = '.(int)$commande->id_cart.' 
			
			$sql = 'SELECT * FROM `'._DB_PREFIX_.'campaign_history` WHERE 
			`id_customer` = '.(int)$commande->id_customer.' 			
			AND `id_cart_rule` = '.(int)$order_cart_rule[0]['id_cart_rule'].' ';
			$voucher = Db::getInstance()->getRow($sql);						
			
			if( $voucher  ) {
				
				echo '
				<!-- Commande en cours. -->
				<div class="panel">
				<div class="panel-heading"><i class="icon-money"></i>'.$this->l('Relance panier').'</div>
				<div class="table-responsive">';
					
				echo '<table class="table" >			
				'.$this->l('Bravo la commande vient d\'une relance panier').' <br/>
				Numéro de campagne : <b>'.$voucher['id_campaign'].'</b/>
				</table>
				</div></div>';
				
			}
		}
	}
	

	public function getCartContentHeader()
	{
		$module = new superabandonedcart();
		return '<table width="100%">
									<thead>
										<tr style="background:#ddd">
											<th>'.$module->l('Image').'</th>
											<th>'.$module->l('Product').'</th>
											<th>'.$module->l('Unit price').'</th>
											<th>'.$module->l('Quantity').'</th>
											<th>'.$module->l('Total').'</th>
										</tr>
									</thead>
									';
	}
	
}
