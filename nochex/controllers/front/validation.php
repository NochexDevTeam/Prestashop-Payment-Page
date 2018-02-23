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

include_once(_PS_MODULE_DIR_.'/nochex/nochex.php');


class nochexvalidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */

	public function postProcess()
	{

$cart = $this->context->cart;
	 
	if (!isset($_POST)) $_POST = &$HTTP_POST_VARS;
	foreach ($_POST AS $key => $value) {
	$values[] = $key."=".urlencode($value);
	}
	$work_string = @implode("&", $values);
	

if(isset($_REQUEST["optional_2"]) && $_REQUEST["optional_2"] == "callback"){

	$url = "https://secure.nochex.com/callback/callback.aspx";
	$ch = curl_init ();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $work_string);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$output = curl_exec ($ch);
	curl_close ($ch);

	$response = preg_replace ("'Content-type: text/plain'si","",$output);
	//--- The response from APC is stored in this variable. ---//
	$responseMessage= "APC Response.... " . $response;

	$secure = "1";
	
	if(isset($_REQUEST["transaction_status"]) && $_REQUEST["transaction_status"] == "100"){
	$testStatus = "Test";
	}else{
	$testStatus = "Live";
	}

	$nochex = new nochex();
    $currency = new Currency((int)$cart->id_currency);
	
	if(isset($_REQUEST["transaction_id"])){
		$transaction_id = $_REQUEST["transaction_id"];
	}else{
		$transaction_id = 0;	
	}
	
	if(isset($_REQUEST["optional_1"])){
		$custom = $_REQUEST["optional_1"];
	}else{
		$custom = 0;	
	}
	
	$extras = array("transaction_id" => $transaction_id);
	$customer->secure_key = $custom;	 
	 
	if ($response=="AUTHORISED"){
	
	$apc = "AUTHORISED";
	
	}else{
		
	$apc = "DECLINED";
	
	}
	
	$responses = "Payment Accepted - Callback was ". $apc .". Transaction Status - ".testStatus;
	
	$this->module->validateOrder((int)$_POST["order_id"], Configuration::get('PS_OS_PAYMENT'), $_POST["amount"], "nochex", $responses, $extras, (int)$cart->id_currency, false, $customer->secure_key);
	
    Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$_REQUEST["order_id"].'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	 	
	}else{	
	 


	$url = "https://www.nochex.com/apcnet/apc.aspx";
	$ch = curl_init ();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_POST, true);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, $work_string);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$output = curl_exec ($ch);
	curl_close ($ch);

	$response = preg_replace ("'Content-type: text/plain'si","",$output);
	//--- The response from APC is stored in this variable. ---//
	$responseMessage= "APC Response.... " . $response;
	//--- The variable with the APC response stored is sent to the new instance class with a function that writes to nochex_debug.txt ---//

	$secure = "1";

	$nochex = new nochex();
    $currency = new Currency((int)$cart->id_currency);
	
	if(isset($_REQUEST["transaction_id"])){
		$transaction_id = $_REQUEST["transaction_id"];
	}else{
		$transaction_id = 0;	
	}
	
	if(isset($_REQUEST["custom"])){
		$custom = $_REQUEST["custom"];
	}else{
		$custom = 0;	
	}
	
	$extras = array("transaction_id" => $transaction_id);
	$customer->secure_key = $custom;

	/* If statement which checks the apc status of an order */
	if ($response=="AUTHORISED"){
	
	$apc = "AUTHORISED";
	
	}else{
	
	
	$apc = "DECLINED";
	
	}
	
		$responses = "Payment Accepted - APC ". $apc .". Transaction Status - ".$_POST["status"];
	
	    $this->module->validateOrder((int)$_POST["order_id"], Configuration::get('PS_OS_PAYMENT'), $_POST["amount"], "nochex", $responses, $extras, (int)$cart->id_currency, false, $customer->secure_key);
	
        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$_REQUEST["order_id"].'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
	 
	 }
	 
	 
	 	   
   } 
}
