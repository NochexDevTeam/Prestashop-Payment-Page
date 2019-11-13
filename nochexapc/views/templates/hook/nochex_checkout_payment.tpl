<!--*
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
-->
<style>
a.nochex:after {
display: block;
content: "\f054";
position: absolute;
right: 15px;
margin-top: -11px;
top: 50%;
font-family: "FontAwesome";
font-size: 25px;
height: 22px;
width: 14px;
color: #777777;
}
</style>
<script>
window.onload = function(){
  subForm();
}
function subForm(){  
  document.forms['nochex_form'].submit();
}
</script>

<form action="https://secure.nochex.com/default.aspx" method="post" id="nochex_form" name="nochex_form" class="hidden">
	<input type='hidden' name='amount' value="{$amount|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='description' value="{$description|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='xml_item_collection' value="{$xml_item_collection|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='postage' value="{$postage|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='billing_fullname' value="{$billing_fullname|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='billing_address' value="{$billing_address|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='billing_city' value="{$billing_city|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='billing_postcode' value="{$billing_postcode|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='delivery_fullname' value="{$delivery_fullname|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='delivery_address' value="{$delivery_address|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='delivery_city' value="{$delivery_city|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='delivery_postcode' value="{$delivery_postcode|escape:'htmlall':'UTF-8'}" />
    <input type='hidden' name='customer_phone_number' value="{$customer_phone_number|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='email_address' value="{$email_address|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='order_id' value="{$order_id|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='hide_billing_details' value="{$hide_billing_details|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='optional_1' value="{$optional_1|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='optional_2' value="{$optional_2|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='merchant_id' value="{$merchant_id|escape:'htmlall':'UTF-8'}" />
    <input type='hidden' name='success_url' value="{$successurl|escape:'htmlall':'UTF-8'}"/>
	<input type='hidden' name='test_success_url' value="{$successurl|escape:'htmlall':'UTF-8'}"/>
	<input type='hidden' name='cancel_url' value="{$cancelurl|escape:'htmlall':'UTF-8'}"/>
	<input type='hidden' name='declined_url' value="{$cancelurl|escape:'htmlall':'UTF-8'}"/>
	<input type='hidden' name='callback_url' value="{$responderurl|escape:'htmlall':'UTF-8'}" />
	<input type='hidden' name='test_transaction' value="{$test_transaction|escape:'htmlall':'UTF-8'}" />
</form>