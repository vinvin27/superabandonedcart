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
		$this->version = '1.1.3';
		$this->author = 'Vince4digitalife';
		$this->need_instance = 0;
		$this->bootstrap = true;
		parent::__construct();
		$this->displayName = $this->l('Super Abandoned Cart');
		$this->description = $this->l('Increase your sales thanks to abandoned cart');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}
	public function install()
	{
		Configuration::updateValue('SUPER_AC_SECURE_KEY', md5( _COOKIE_KEY_.time()));
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'campaign` (
				  `id_campaign` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `voucher_amount_type` varchar(50) NOT NULL,
				  `email_tpl` text NOT NULL,
				  `execution_time_day` int(11) NOT NULL,
  				  `execution_time_hour` int(11) NOT NULL,
				  `id_carts` varchar(255) NOT NULL,
				  `id_voucher` int(11) NOT NULL,
				  `active` tinyint(1) NOT NULL,
				  PRIMARY KEY (`id_campaign`)
				) ENGINE=InnoDB ;
				
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'campaign_shop` (
				  `id_campaign` int(11) NOT NULL,
				  `id_shop` int(11) NOT NULL
				) ENGINE=InnoDB;
				';
		
		Db::getInstance()->Execute($sql);
		
		$this->CreateTabs();
		
		if (!parent::install() OR !$this->registerHook('displayBackOfficeHeader'))
			return false;
		return true;
	}
	
	public function hookDisplayBackOfficeHeader(){
		 $this->context->controller->addCss($this->_path.'views/css/tab.css');
		 $this->context->controller->addJs($this->_path.'views/js/js.js');
	}
	
	public function uninstall()
	{
		Configuration::deleteByName('SUPER_AC_SECURE_KEY');
		$idtabs = array();
		$idtabs[] = Tab::getIdFromClassName("AdminSuperAbandonedCart");
		foreach ($idtabs as $tabid):
			if ($tabid) {
				$tab = new Tab($tabid);
				$tab->delete();
			}
        endforeach;
		return parent::uninstall() AND $this->unregisterHook('displayBackOfficeHeader');
	}
	
	
	
	 private function CreateTabs() {
        $langs = Language::getLanguages();
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $smarttab = new Tab();
        $smarttab->class_name = "AdminSuperAbandonedCart";
        $smarttab->module = "superabandonedcart";
        $smarttab->id_parent = 0;
        foreach ($langs as $l) {
            $smarttab->name[$l['id_lang']] = $this->l('Super Abandoned Cart');
        }
        $smarttab->save();
        $tab_id = $smarttab->id;
      
        return true;
    }

	public function getCartContentHeader(){
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
