<?php
/*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class nochexpostprocessModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
		
			$customer = new Customer(intval($this->context->cart->id_customer));
		$currency = new Currency((int)$cart->id_currency);
				
		$c_rate = (is_array($currency) ? $currency['conversion_rate'] : $currency->conversion_rate);
		$amo = number_format(round($cart->getOrderTotal(true, 3) / $c_rate, 2), 2, '.', '');

			$billing_address = new Address($this->context->cart->id_address_invoice);
			$delivery_address = new Address($this->context->cart->id_address_delivery);
			$billing_address->country = new Country($billing_address->id_country);
			$delivery_address->country = new Country($delivery_address->id_country);
			$billing_address->state	= new State($billing_address->id_state);
			$delivery_address->state = new State($delivery_address->id_state);
			
			$bill_add_fields = $billing_address->getFields();
			$del_add_fields = $delivery_address->getFields();
			
			if($bill_add_fields['phone_mobile'] == ""){
			
			$customer_phone = $bill_add_fields['phone'];
						
			}else{
			
			$customer_phone = $bill_add_fields['phone_mobile'];
			
			}
			
			/*--- Gets the configuration details, which have been stored from the nochex module config form  ---*/
			$apc_email = Configuration::get('NOCHEX_APC_EMAIL');
			$test_mode = Configuration::get('NOCHEX_APC_TESTMODE');
			$hide_details = Configuration::get('NOCHEX_APC_HIDEDETAILS');
			$nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
			$nochex_xmlcollection = Configuration::get('NOCHEX_APC_XMLCOLLECTION');
			$nochex_postage = Configuration::get('NOCHEX_APC_POSTAGE');
			$nochex_callback = Configuration::get('NOCHEX_CALLBACK');
					
		if($test_mode=="checked"){
			$testMode = "100";		
		}else{		
			$testMode = "0";
		}

	if ($hide_details == "checked"){
		
		$hide_billing_details = 1;
		
		}else{
	
		$hide_billing_details = 0;
		}
		
		
	
		if($nochex_postage == "checked"){
		
		$disPostage = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING)/ $c_rate;
		$postAmo = $cart->getOrderTotal(true, 3) - $cart->getOrderTotal(true, Cart::ONLY_SHIPPING)/ $c_rate;
		$disAmount =  number_format(round($postAmo, 2), 2, '.', '') ;
		
		}else{
		
		$disPostage = "";
		$disAmount =  number_format(round($amo, 2), 2, '.', '');
		
		}
		
		if($nochex_callback == "checked"){
		
		$optional2 = 'callback';
		
		}else{
		
		$optional2 = '';
		
		}
		


if($nochex_xmlcollection == "checked"){
		
		//--- get the product details  
		$productDetails = $cart->getProducts();
		$item_collection = "<items>";
		
		//--- Loops through and stores each product that has been ordered in the $prodDet variable.
		foreach($productDetails as $details_product)
		{
		
		$filterDesc = filter_var($details_product['description_short'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
		$filterName = filter_var($details_product['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
		
		$item_collection .= "<item><id>". $details_product['id_product'] . "</id><name>" . $filterName . "</name><description>".$filterDesc."</description><quantity>" . $details_product['quantity']  . "</quantity><price>" .  number_format(round($details_product['price']/ $c_rate, 2), 2, '.', '')  . "</price></item>";
		
		}
		$item_collection .= "</items>";
		
		$prodDet = "Order created for: " . intval($cart->id);
		}else{
		$item_collection = "";
		//--- get the product details  
		$productDetails = $cart->getProducts();
		$prodDet = "";
		
		//--- Loops through and stores each product that has been ordered in the $prodDet variable.
		foreach($productDetails as $details_product)
		{
		
		$prodDet .= "Product ID: ". $details_product['id_product'] . ", Product Name: " . $filterName . ", Quantity: " . $details_product['quantity']  . ", Amount: &#163 " .  number_format(round($details_product['price']/ $c_rate, 2), 2, '.', '')  . ". ";
		
		}
		$prodDet .= " ";
		
		}
			
$this->context->smarty->assign(array(
			'amount' => $disAmount,
			'order_id' => intval($cart->id),
			'description' => $prodDet,
			'xml_item_collection' => $item_collection,
			'hide_billing_details' => $hide_billing_details,
			'billing_fullname' => $bill_add_fields['firstname'].', '.$bill_add_fields['lastname'],
			'billing_address' => $bill_add_fields['address1'],
			'billing_city' => $bill_add_fields['city'],
			'billing_postcode' => $bill_add_fields['postcode'],
            'delivery_fullname' => $del_add_fields['firstname'] . ', '. $del_add_fields['lastname'],
			'delivery_address' => $del_add_fields['address1'],
			'delivery_city' => $del_add_fields['city'],
			'delivery_postcode' => $del_add_fields['postcode'],
			'customer_phone_number' => $customer_phone,
			'email_address' => $customer->email,
			'optional_1' => $cart->secure_key,
			'optional_2' => $optional2,
			'merchant_id' => $apc_email,
			'successurl' => 'https://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.intval($cart->id).'&id_module='.$this->module->id.'&key='.$cart->secure_key,
			'cancelurl' => 'https://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?controller=order',
			'postage' => $disPostage,
			'responderurl' => 'https://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?fc=module&module=nochex&controller=validation',
			'test_transaction' => $testMode,
));


/**/
$this->setTemplate('module:nochex/views/templates/hook/nochex_checkout_payment.tpl');



    }
}
