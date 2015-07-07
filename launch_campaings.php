<?php
// Set true for debug $days = 0 
define('DEBUG_SAC',true);

include(dirname(__FILE__).'/../../config/config.inc.php');
include(_PS_ROOT_DIR_.'/init.php');
include_once(dirname(__FILE__).'/classes/Campaign.php');
include_once(dirname(__FILE__).'/classes/CampaignHistory.php');

// Check security key
if ( Tools::getValue('secure_key') != Configuration::get('SUPER_AC_SECURE_KEY') )
	die('NOSECUREKEY');

class LaunchCampaign 
{


	public  function sendCampaign() 
	{		
	
		// get abandoned cart :
		$sql = "SELECT * FROM (
		SELECT
		CONCAT(LEFT(c.`firstname`, 1), '. ', c.`lastname`) `customer`, a.id_cart total, ca.name carrier, c.id_customer, a.id_cart, a.date_upd,a.date_add,
				IF (IFNULL(o.id_order, 'Non ordered') = 'Non ordered', IF(TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', a.`date_add`)) > 86000, 'Abandoned cart', 'Non ordered'), o.id_order) id_order, IF(o.id_order, 1, 0) badge_success, IF(o.id_order, 0, 1) badge_danger, IF(co.id_guest, 1, 0) id_guest
		FROM `"._DB_PREFIX_."cart` a  
				JOIN `"._DB_PREFIX_."customer` c ON (c.id_customer = a.id_customer)
				LEFT JOIN `"._DB_PREFIX_."currency` cu ON (cu.id_currency = a.id_currency)
				LEFT JOIN `"._DB_PREFIX_."carrier` ca ON (ca.id_carrier = a.id_carrier)
				LEFT JOIN `"._DB_PREFIX_."orders` o ON (o.id_cart = a.id_cart)
				LEFT JOIN `"._DB_PREFIX_."connections` co ON (a.id_guest = co.id_guest AND TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', co.`date_add`)) < 1800)
				WHERE a.date_add > (NOW() - INTERVAL 60 DAY) ORDER BY a.id_cart DESC 
		) AS toto WHERE id_order='Abandoned cart'";

		$currency = Context::getContext()->currency->sign;
		$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
				
				
		$abandoned_carts = Db::getInstance()->ExecuteS($sql);
		// get all available campaigns  

		$sqlCampaigns = 'SELECT * FROM `'._DB_PREFIX_.'campaign` WHERE active=1 AND is_abn_campaign=1';

		$allCampaigns = Db::getInstance()->ExecuteS($sqlCampaigns);

		if( !$allCampaigns || empty($allCampaigns) )
			die('NO CAMPAIGN');
		
		// loop on all abandoned carts
		foreach( $abandoned_carts as $abncart ) {



			if( Cart::getNbProducts((int)$abncart['id_cart']) > 0  ) {

			$emailsSent = 0;
			// loop on all available campaigns 
			foreach( $allCampaigns as $camp ) {
				
				if( DEBUG_SAC )
				echo 'IdCustomer : '.$abncart['id_customer'].' - IdCart : '.$abncart['id_cart'].'<br/>';
			
				$cartIsOnCampaign = $this->checkIfCartIsOnCampaign( $abncart['date_upd'] , $camp['execution_time_day'] , $camp['execution_time_hour']);
			
				if( $cartIsOnCampaign ){
					
					if( DEBUG_SAC )
					echo 'Cart on campaign</br>';
			
					$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
					$customer = new Customer($abncart['id_customer']);
					$cart = new Cart($abncart['id_cart']);			
					$products = $cart->getProducts();
				
				
					$tpl_vars = array(
						'{firstname}' => $customer->firstname,
						'{lastname}' => $customer->lastname,
						'{campaign_name}' => $camp['name'],
						'{track_url}' => $this->getBaseURL().'?id_cart='.(int)$abncart['id_cart'].'&id_customer='.(int)$abncart['id_customer'],
						'{track_request}' => '?id_cart='.(int)$abncart['id_cart'].'&id_customer='.(int)$abncart['id_customer'],
						'{order_link}' => Context::getContext()->link->getPageLink('order', false, (int)$cart->id_lang, 'step=3&recover_cart='.(int)$cart->id.'&token_cart='.md5(_COOKIE_KEY_.'recover_cart_'.(int)$cart->id) . '&id_cart='.(int)$abncart['id_cart'].'&id_customer='.(int)$abncart['id_customer']  )
					);
				
					$campM = new Campaign($camp['id_campaign']);			
					
					if( $campM->voucher_amount && $campM->voucher_day && $campM->voucher_amount_type ) {
						
						$campM->clean_old_reduction($campM->voucher_prefix);
						$customerVoucher = $campM->registerDiscount($customer->id,$campM->voucher_amount ,$campM->voucher_day,$campM->voucher_amount_type,$campM->voucher_prefix);
					
						$tpl_vars['{coupon_name}'] = $customerVoucher->name;
						$tpl_vars['{coupon_code}'] = $customerVoucher->code;
						$tpl_vars['{coupon_value}'] = ( $camp['voucher_amount_type'] == 'percent' ? $customerVoucher->reduction_percent.'%' :  Tools::displayprice($customerVoucher->reduction_amount) );
						$tpl_vars['{coupon_valid_to}'] = date('d/m/Y',strtotime( $customerVoucher->date_to ));
					}	
						
					if (! empty( $products ) ) {	
						$cart_content = $campM->getCartContentHeader();
					}
					else { 
						$cart_content = '';
					}
					
					foreach( $products as $prod ) {						
				
						$p = new Product($prod['id_product'],true,$id_lang);
						$price_no_tax = (Product::getPriceStatic($p->id,false,null,2,null,false,true,1,false,null,$abncart['id_cart'],null,$null,true,true,null,false,false));
						$total_no_tax = $prod['cart_quantity'] * $price_no_tax;
						$images = Image::getImages((int)$id_lang, (int)$p->id);
					
						$link = new Link();
						$cart_content .= '<tr>
											<td align="center" >'. ( isset($images[0]) ? '<img src="'.Tools::getShopProtocol().$link->getImageLink($p->link_rewrite,$images[0]['id_image']).'" width="80"/>' : '' ).'</td>
											<td align="center" ><a href="'.$link->getProductLink($p).'?id_cart='.(int)$abncart['id_cart'].'&id_customer='.(int)$abncart['id_customer'].'"/>'.$p->name.'</a></td>
											<td align="center" >'.Tools::displayprice($price_no_tax).'</td>
											<td align="center" >'.$prod['cart_quantity'].'</td>
											<td align="center" >'.Tools::displayprice($total_no_tax).'</td>
										</tr>';
					
					}		
					
					$cart_content .= '</table>';
					
					$tpl_vars['{cart_content}'] = $cart_content;

				
					// send email to customer : 
					
					$mailUser = Mail::Send(
								$id_lang ,
								$campM->getFileName() ,
								$camp['name'] , 
								$tpl_vars ,
								$customer->email ,
								null,
								null,
								null,
								null,
								null,
								$campM->mailPath,
								false, Context::getContext()->shop->id);

					// if mail user is successfully sent : 
					
					if( $mailUser ) {
						
						$history = new CampaignHistory();
						$history->id_campaign= (int)$camp['id_campaign'];
						$history->id_customer = $abncart['id_customer'];
						$history->id_cart = $abncart['id_cart'];
						$history->id_cart_rule = ( isset($customerVoucher->id) ? $customerVoucher->id : 0);
						$history->click = 0;
						$history->converted = 0;
						$history->date_update = date('Y-m-d H:i:s', time());
						$history->save();
					
						// Email to admin :
					
						Mail::Send(
							$id_lang ,
							$campM->getFileName() ,
							Mail::l( sprintf('Email sent to %s %s for campaign %s' , $customer->lastname , $customer->firstname , $camp['name'] )) , 
							$tpl_vars ,
							Configuration::get('PS_SHOP_EMAIL') ,
							null,
							null,
							null,
							null,
							null,
							$campM->mailPath,
							false, Context::getContext()->shop->id
						); 	
						
						++$emailsSent;
						
			
					}
					else {
						PrestaShopLogger::addLog( 'Error when sending user email (tpl:'.$campM->getFileName().',customer:'.$customer->email.', campagne : '.$camp['name'] , 3 );
					}
				}
			}
			// log emailing results : 
			if( $emailsSent > 0 ) {
				PrestaShopLogger::addLog( $emailsSent . ' emails sent for '.$camp['name'] . ' campaign' , 1 );
			}
		}
		}	
	}
	
	public function getBaseURL()
    {
		
		$this->context = Context::getContext(); 
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$this->context->shop->domain.$this->context->shop->getBaseURI();
    }
	
	public function checkIfCartIsOnCampaign( $abndate , $days , $hours )
	{	
		
		
		// Abandoned cart date + Campaign Days and Hours
		
		$time = strtotime( $abndate . ' + '.$days.' Days + '.$hours.' hours' );
		if( !isset ( $time ) ) { return false; }
	 	$gAbnDate = date('Y-m-d H:i:00',$time);
		
		// Now time ( cron should be fired every 30minutes (e.g : at 2 or 2:30 or 3 or 3:30...) so
		// for 0 min check last 29mins (from 0 to 31mins) and for 30 check last 29mins (from 30 to 01);
		$now =  date('Y-m-d H:i:00');
		$oldnow = date('Y-m-d H:i:00', time() - 60 * 29);
	
		// Debug 
		/*
		echo ' ADD ' . $days . ' JOUR and '  . $hours . ' HEURE '. '<br>';
		echo ' NOW :  ' .date('Y-m-d H:i:s') . '<br>';
		echo ' CART ABONDONNE AT ' . $abndate. '<br>';;
		echo ' plus campaign : ' . $gAbnDate . '<br>';
		echo 'MIN TIME ' . $oldnow . '<br>';
		*/
		
		if( DEBUG_SAC )
			echo $oldnow.' < '.$gAbnDate.' ---- '.$gAbnDate.' <= '.$now.'<br/><br/>';
		
		if( strtotime($oldnow) < strtotime($gAbnDate) AND strtotime($gAbnDate) <= strtotime($now) ) {
			
			return true;
		
		}

		return false;
	}	
}


$la = new LaunchCampaign();
$la->sendCampaign();
