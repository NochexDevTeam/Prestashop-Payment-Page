<?php
/**
* 2007-2019 PrestaShop
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
*  @author Nochex
*  @copyright 2007-2019 Nochex
*  @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  Plugin Name: Nochex Payment Gateway for Prestashop 1.7
*  Description: Accept Nochex Payments, orders are updated using APC.
*  Version: 3.0.4
*  License: GPL2
*
*/
 
class NochexApcValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */

    public function postProcess()
    {
	
	
        include_once(_PS_MODULE_DIR_.'/nochexapc/nochexapc.php');
        ini_set("SMTP", "mail.nochex.com");
		
        $nochexapc = new nochexapc();
		  
        if ($nochexapc->module_key == $this->context->controller->module->module_key) {
            if (Tools::getValue('order_id')) {
			
		    $cart2 = new Cart((int)Tools::getValue('order_id'));
		    
		    if (number_format($cart2->getOrderTotal(true, 3), 2, '.', '') <> Tools::getValue('amount')) {
			
			if (Tools::getValue('optional_1')) {
                            $custom = Tools::getValue('optional_1');
                        } else {
                            $custom = 0;
                        }
						
			$extras = array("transaction_id" => $transaction_id);				
			PrestaShopLogger::addLog(
                            'Order total and Paid total, do not match!!',
                            3,
                            null,
                            'nochexapc - validation',
                            0,
                            true
                        ); 						
				$this->module->validateOrder((int)Tools::getValue("order_id"),(int)Configuration::get('PS_OS_ERROR'),Tools::getValue("amount"),$nochexapc->displayName,"Issue - Amounts mismatch",$extras,Tools::getValue('curr'),false,$custom);
			}
		    
			$work_string = http_build_query($_POST); 
					 
			if (Tools::getValue('optional_2') == "callback") {
						
			$url = "https://secure.nochex.com/callback/callback.aspx";
				
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $work_string);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $output = curl_exec($ch);
                        curl_close($ch);
                        $response = preg_replace(
                            "'Content-type: text/plain'si",
                            "",
                            $output
                        );
                        if ($_REQUEST["transaction_status"] == "100") {
                            $testStatus = "Test";
                        } else {
                            $testStatus = "Live";
                        }
                        if (Tools::getValue('transaction_id')) {
                            $transaction_id = Tools::getValue('transaction_id');
                        } else {
                            $transaction_id = 0;
                        }
                        if (Tools::getValue('optional_1')) {
                            $custom = Tools::getValue('optional_1');
                        } else {
                            $custom = 0;
                        }
                        $extras = array("transaction_id" => $transaction_id);
                        if ($response=="AUTHORISED") {
                            $apc = "AUTHORISED";
                        } else {
                            $apc = "DECLINED";
                        }
                        $responses = "Payment Accepted - Callback was ". $apc .
                        ". Transaction Status - ".$testStatus;
                        $nochexapc->validateOrder(
                            (int)Tools::getValue("order_id"),
                            Configuration::get('PS_OS_PAYMENT'),
                            Tools::getValue("amount"),
                            $nochexapc->displayName,
                            $responses,
                            $extras,
                            Tools::getValue('curr'),
                            false,
                            $custom
                        );
						
					} else {
						
                    if (Tools::getValue('custom') == Tools::getValue('key')) {
					
                        $url = "https://www.nochex.com/apcnet/apc.aspx";
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $work_string);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $output = curl_exec($ch);
                        curl_close($ch);
						
                        $response = preg_replace(
                            "'Content-type: text/plain'si",
                            "",
                            $output
                        );
						
                        if (Tools::getValue("transaction_id")) {
                            $transaction_id = Tools::getValue("transaction_id");
                        } else {
                            $transaction_id = 0;
                        }
                        if (Tools::getValue("custom")) {
                            $custom = Tools::getValue("custom");
                        } else {
                            $custom = 0;
                        }
						
                        $extras = ["transaction_id" => $transaction_id];
						
                       if ($response == "AUTHORISED") {
                            $apc = "AUTHORISED";
                        } else {
                            $apc = "DECLINED";
                        }
						
                       $responses = "Payment Accepted - APC " . $apc .". Transaction Status - " . Tools::getValue("status");
						 
                       $this->module->validateOrder((int)Tools::getValue("order_id"),(int)Configuration::get('PS_OS_PAYMENT'),Tools::getValue("amount"),$nochexapc->displayName,$responses,$extras,Tools::getValue('curr'),false,$custom);
					   
					    } else {
                        PrestaShopLogger::addLog(
                            'Secure Keys do not match!!',
                            3,
                            null,
                            'nochexapc - validation',
                            0,
                            true
                        ); 
                    }
                } 
	
	            } else {
                PrestaShopLogger::addLog(
                    'Order not present!!',
                    3,
                    null,
                    'nochexapc - validation',
                    0,
                    true
                ); 
            }
        } else {
            PrestaShopLogger::addLog(
                'Issue with module keys not matching!!',
                3,
                null,
                'nochexapc',
                0,
                true
            ); 
        }
	
	}
}
