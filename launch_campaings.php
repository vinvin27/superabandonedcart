<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(_PS_ROOT_DIR_.'/init.php');
include_once(dirname(__FILE__).'/classes/Campaign.php');


//Campaign::sendCampaign();


class LaunchCampaign {


public  function sendCampaign(){
	
	
		
		// get abandoned cart :

		$sql = "SELECT * FROM (
		SELECT
		CONCAT(LEFT(c.`firstname`, 1), '. ', c.`lastname`) `customer`, a.id_cart total, ca.name carrier, c.id_customer, a.id_cart, a.date_upd,a.date_add,
				IF (IFNULL(o.id_order, 'Non ordered') = 'Non ordered', IF(TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', a.`date_add`)) > 86400, 'Abandoned cart', 'Non ordered'), o.id_order) id_order, IF(o.id_order, 1, 0) badge_success, IF(o.id_order, 0, 1) badge_danger, IF(co.id_guest, 1, 0) id_guest
		FROM `"._DB_PREFIX_."cart` a  
				JOIN `"._DB_PREFIX_."customer` c ON (c.id_customer = a.id_customer)
				LEFT JOIN `"._DB_PREFIX_."currency` cu ON (cu.id_currency = a.id_currency)
				LEFT JOIN `"._DB_PREFIX_."carrier` ca ON (ca.id_carrier = a.id_carrier)
				LEFT JOIN `"._DB_PREFIX_."orders` o ON (o.id_cart = a.id_cart)
				LEFT JOIN `"._DB_PREFIX_."connections` co ON (a.id_guest = co.id_guest AND TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', co.`date_add`)) < 1800)
		) AS toto WHERE id_order='Abandoned cart'";

		$currency = Context::getContext()->currency->sign;
		$defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
				
				
		$abandoned_carts = Db::getInstance()->ExecuteS($sql);
		// get all available campaigns  

		$sqlCampaigns = 'SELECT * FROM `'._DB_PREFIX_.'campaign` WHERE active=1';

		$allCampaigns = Db::getInstance()->ExecuteS($sqlCampaigns);

		// loop on all abandoned carts
		foreach( $abandoned_carts as $abncart ) {

		// loop on all available campaigns 
		foreach( $allCampaigns as $camp ){
		
			$cartIsOnCampaign = $this->checkIfCartIsOnCampaign( $abncart['date_add'] , $camp['execution_time_day'] , $camp['execution_time_hour']);
		
		
		
			if( $cartIsOnCampaign ){
		
				$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
				$customer = new Customer($abncart['id_customer']);
				$cR = new CartRule($camp['id_voucher'], $id_lang);
				$cart = new Cart($abncart['id_cart']);
			
				$products = $cart->getProducts();
			
				$campM = new Campaign($camp['id_campaign']);
					
					
				if (! empty( $products ) ) {	
					$cart_content = $campM->getCartContentHeader();
				}
				else { 
				 	$cart_content = '';
				}
				
				foreach( $products as $prod ){
			
					$p = new Product($prod['id_product'],true,$id_lang);
					$price_no_tax = (Product::getPriceStatic($p->id,false,null,2,null,false,true,1,false,null,$abncart['id_cart'],null,$null,true,true,null,false,false));
					$total_no_tax = $prod['cart_quantity'] * $price_no_tax;
					$images = Image::getImages((int)$id_lang, (int)$p->id);
				
					$link = new Link();
					$cart_content .= '<tr >
										<td align="center" ><img src="'.$link->getImageLink($p->link_rewrite,$images[0]['id_image']).'" width="80"/></td>
										<td align="center" ><a href="'.$link->getProductLink($p).'"/>'.$p->name.'</a></td>
										<td align="center" >'.Tools::displayprice($price_no_tax).'</td>
										<td align="center" >'.$prod['cart_quantity'].'</td>
										<td align="center" >'.Tools::displayprice($total_no_tax).'</td>
									</tr>';
				
				}		
				$tpl_vars = array(
					'{firstname}' => $customer->firstname,
					'{lastname}' => $customer->lastname,
					'{coupon_name}' => $cR->name,
					'{coupon_code}' => $cR->code,
					'{cart_content}' => $cart_content,
					'{coupon_value}' => ( $camp['voucher_amount_type'] == 'percent' ? $cR->reduction_percent.'%' :  $currency.$cR->reduction_amount ),
					'{coupon_valid_to}' => date('d/m/Y',strtotime( $cR->date_to )),
					'{campaign_name}' => $camp['name']
				);
			
			
				$path = _PS_ROOT_DIR_.'/modules/superabandonedcart/mails/';
				// send email to customer : 
				Mail::Send(
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
							$path,
							false, Context::getContext()->shop->id
							); 	
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
						$path,
						false, Context::getContext()->shop->id
					); 	
					
					echo 'ID ' . $abncart['id_cart'];
			}
		}

	}

	
	
	}
	public function checkIfCartIsOnCampaign( $abndate , $days , $hours ){


		
		// Abandoned cart date + Campaign Days and Hours
		
		$time = strtotime( $abndate . ' + '.$days.' Days + '.$hours.' hours' );
		$gAbnDate = date('Y-m-d H:i:s',$time);
		
		// Now time ( cron should be fired every 30minutes (e.g : at 2 or 2:30 or 3 or 3:30...) so
		// for 0 min check last 29mins (from 0 to 31mins) and for 30 check last 29mins (from 30 to 01);
		$now =  date('Y-m-d H:i:s');
		$oldnow = date('Y-m-d H:i:s', time() - 60 * 29);
	
		// Debug 
		/*
		echo ' ADD ' . $days . ' JOUR and '  . $hours . ' HEURE '. '<br>';
		echo ' NOW :  ' .date('Y-m-d H:i:s') . '<br>';
		echo ' CART ABONDONNE AT ' . $abndate. '<br>';;
		echo ' plus campaign : ' . $gAbnDate . '<br>';
		echo 'MIN TIME ' . $oldnow . '<br>';
		*/
	
	
		if( strtotime($oldnow) < strtotime($gAbnDate) AND strtotime($gAbnDate) <= strtotime($now) ){
			return true;
		}

		return false;
	} 	

	
}


$la = new LaunchCampaign();
$la->sendCampaign();
