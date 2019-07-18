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
*  Version: 3.0.1
*  License: GPL2
*/

include_once(_PS_MODULE_DIR_.'/nochex/nochex.php');


class NochexValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */

    public function init()
    {
        $cart = $this->context->cart;
        $values = "";
        foreach ($_POST as $key => $value) {
            $values[] = $key."=".urlencode($value);
        }
        $work_string = @implode("&", $values);
        if ($_REQUEST["optional_2"] == "callback") {
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
            $response = preg_replace("'Content-type: text/plain'si", "", $output);
            if ($_REQUEST["transaction_status"] == "100") {
                $testStatus = "Test";
            } else {
                $testStatus = "Live";
            }
            if ($_REQUEST["transaction_id"]) {
                $transaction_id = $_REQUEST["transaction_id"];
            } else {
                $transaction_id = 0;
            }
            if ($_REQUEST["optional_1"]) {
                $custom = $_REQUEST["optional_1"];
            } else {
                $custom = 0;
            }
            $extras = array("transaction_id" => $transaction_id);
            if ($response=="AUTHORISED") {
                $apc = "AUTHORISED";
            } else {
                $apc = "DECLINED";
            }
            $responses = "Payment Accepted - Callback was ". $apc .". Transaction Status - ".$testStatus;
            $this->module->validateOrder(
                (int)Tools::getValue("order_id"),
                Configuration::get('PS_OS_PAYMENT'),
                Tools::getValue("amount"),
                "nochex",
                $responses,
                $extras,
                (int)$cart->id_currency,
                false,
                $custom
            );
        } else {
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
            $response = preg_replace("'Content-type: text/plain'si", "", $output);
            if ($_REQUEST["transaction_id"]) {
                $transaction_id = $_REQUEST["transaction_id"];
            } else {
                $transaction_id = 0;
            }
            if ($_REQUEST["custom"]) {
                $custom = $_REQUEST["custom"];
            } else {
                $custom = 0;
            }
            $extras = array("transaction_id" => $transaction_id);
            if ($response == "AUTHORISED") {
                $apc = "AUTHORISED";
            } else {
                $apc = "DECLINED";
            }
            $responses = "Payment Accepted - APC " . $apc . ". Transaction Status - " . $_REQUEST["status"];
            $this->module->validateOrder(
                (int)Tools::getValue("order_id"),
                Configuration::get('PS_OS_PAYMENT'),
                Tools::getValue("amount"),
                "nochex",
                $responses,
                $extras,
                (int)$cart->id_currency,
                false,
                $custom
            );
        }
    }
}
