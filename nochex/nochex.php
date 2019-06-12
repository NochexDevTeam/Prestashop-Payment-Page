<?php
/*
Plugin Name: Nochex Payment Gateway for Prestashop
Description: Accept Nochex Payments, orders are updated using APC.
Version: 2.0
License: GPL2
*/
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class nochex extends PaymentModule
{	
	private $_html = '';
	private $_postErrors = array();

	public  $details;
	public  $owner;
	public	$address;
	
      
	public function __construct()
	{
		$this->name = 'nochex';
		$this->tab = 'payments_gateways';
		$this->controllers = array('payment', 'validation');
		$this->author = 'Nochex';
		$this->version = 3.0;
		$this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
		 
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		
		/*--- This array gets all of the configuration information from the Configuration file/table in the database. ---*/
		$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE', 'NOCHEX_CALLBACK'));
		if (isset($config['NOCHEX_APC_EMAIL']))
			$this->email = $config['NOCHEX_APC_EMAIL'];
		if (isset($config['NOCHEX_APC_TESTMODE']))
			$this->test_mode = $config['NOCHEX_APC_TESTMODE'];
		if (isset($config['NOCHEX_APC_HIDEDETAILS']))
			$this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
		if (isset($config['NOCHEX_APC_DEBUG']))
			$this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
		if (isset($config['NOCHEX_APC_XMLCOLLECTION']))
			$this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
		if (isset($config['NOCHEX_APC_POSTAGE']))
			$this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
		if (isset($config['NOCHEX_CALLBACK']))
			$this->nochex_postage = $config['NOCHEX_CALLBACK'];
		parent::__construct(); /* The parent construct is required for translations */
		
		
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Nochex APC Module');
		$this->description = $this->l('Accept payments by Nochex');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!isset($this->email))
			$this->warning = $this->l('Account APC Id and Email must be configured in order to use this module correctly');
			
			 
	}

	public function install()
	{
	 
		if (!parent::install() OR !$this->registerHook('paymentOptions') OR !$this->registerHook('paymentReturn'))
			return false;
		return true;
	}
	
	/*--- This function removes the module, and configuration information. ---*/
	public function uninstall()
	{
		if (!Configuration::deleteByName('NOCHEX_APC_EMAIL')
				OR !Configuration::deleteByName('NOCHEX_APC_TESTMODE')
				OR !Configuration::deleteByName('NOCHEX_APC_HIDEDETAILS')
				OR !Configuration::deleteByName('NOCHEX_APC_DEBUG')
				OR !Configuration::deleteByName('NOCHEX_APC_XMLCOLLECTION')
				OR !Configuration::deleteByName('NOCHEX_APC_POSTAGE')
				OR !Configuration::deleteByName('NOCHEX_CALLBACK')
				OR !Configuration::deleteByName('NOCHEX_ACTIVE')
				OR !parent::uninstall())
			return false;
		return true;
		 
        // Uninstall default
        if (!parent::uninstall()) {
            return false;
        }
        return true;
		
	}

	private function _postValidation()
	{
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['email']))
				$this->_postErrors[] = $this->l('Account Email Id is required.');
		}
	}
/*--- Once the update settings button has been pressed on the admin/config file, information is posted and updates the database/configuration details. ---*/
	private function _postProcess()
	{	
	// Funtion and variable which writes to nochex_debug.txt
	//	$btn_Submit = 'Store updated values from admin/module config form details... Nochex Email: ' . $_POST['email'] . '. Test Mode: ' . $_POST['test_mode'] . '. Hide Billing Details: ' . $_POST['hide_details'] . '. nochex_debug : ' .  $_POST['nochex_debug'];
	//	$this->writeDebug($btn_Submit);	

		
		if (isset($_POST['btnSubmit']))
		{
			Configuration::updateValue('NOCHEX_APC_EMAIL', $_POST['email']);
			Configuration::updateValue('NOCHEX_APC_TESTMODE', $_POST['test_mode']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_HIDEDETAILS', $_POST['hide_details']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_DEBUG', $_POST['nochex_debug']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_XMLCOLLECTION', $_POST['nochex_xmlcollection']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_APC_POSTAGE', $_POST['nochex_postage']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_CALLBACK', $_POST['nochex_callback']); /* value is checked or null, stores the state of the checkbox */
			Configuration::updateValue('NOCHEX_ACTIVE', '1');
			// Refreshes the page to show updated controls.
			header('Location: ' . $_SERVER['PHP_SELF'] . '?controller=AdminModules&token='.Tools::getValue('token').$identifier.'&configure=nochex&tab_module='.$this->l('Payments & Gateways').'&module_name=nochex');
		}
		$this->_html .= '<div class="conf confirm"><!--span style="color:#fff;padding: 5px;background:green;font-weight:bold;"> '.$this->l('Settings updated').'</span--></div>';
	}
	private function _displayNoChex()
	{
		$this->_html .= '<img src="https://www.nochex.com/logobase-secure-images/logobase-banners/clear-mp.png" height="100px" style="float:left; margin-right:15px;"><br style="clear:both;"/><br style="clear:both;"/><b>'.$this->l('This module allows you to accept payments by Nochex (APC Method).').'</b><br /><br />
		'.$this->l('If the client chooses this payment mode, the order will change its status once a positive confirmation is recieved from nochex server').'<br />
		<br /><br />';
	}

	/*---  Function returns the value to the form, which shows the state of the checkbox ---*/
	private function _validateTestCheckbox()
	{
	
			$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_CALLBACK'));
			$this->test_mode = $config['NOCHEX_APC_TESTMODE'];

	return $this->test_mode;
	}
	/*---  Function returns the value to the form, which shows the state of the checkbox ---*/
	private function _validateBillCheckbox()
	{
	$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_CALLBACK'));
	$this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
	return $this->hide_details;
	}
	/*---  Function returns the value to the form, which shows the state of the checkbox ---*/
	private function _validateDebugCheckbox()
	{
			$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_CALLBACK'));	
			$this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
		
	return $this->nochex_debug;
	}
	
	private function _validateXmlcollectionCheckbox()
	{	
	$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_CALLBACK'));
	$this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
	return $this->nochex_xmlcollection;
	}
	private function _validatePostageCheckbox()
	{$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_CALLBACK'));
			$this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
	return $this->nochex_postage;
	}
	private function _validateCallbackCheckbox()
	{$config = Configuration::getMultiple(array('NOCHEX_APC_EMAIL','NOCHEX_APC_TESTMODE','NOCHEX_APC_HIDEDETAILS','NOCHEX_APC_DEBUG','NOCHEX_APC_XMLCOLLECTION','NOCHEX_APC_POSTAGE','NOCHEX_CALLBACK'));
			$this->nochex_callback = $config['NOCHEX_CALLBACK'];
	return $this->nochex_callback;
	}
			
			
	/*--- Function, write to a text file ---*/
	// Function that will be called when particular information needs to be written to a nochex_debug file.
	public function writeDebug($DebugData){
	// Calls the configuration information about a control in the module config. 
	$nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
	// If the control nochex_debug has been checked in the module config, then it will use data sent and received in this function which will write to the nochex_debug file
		if ($nochex_debug == "checked"){
		// Receives and stores the Date and Time
		$debug_TimeDate = date("m/d/Y h:i:s a", time());
		// Puts together, Date and Time, as well as information in regards to information that has been received.
		$stringData = "\n Time and Date: " . $debug_TimeDate . "... " . $DebugData ."... ";
		 // Try - Catch in case any errors occur when writing to nochex_debug file.
			try
			{
			// Variable with the name of the debug file.
				$debugging = "../modules/nochex/nochex_debug.txt";
			// variable which will open the nochex_debug file, or if it cannot open then an error message will be made.
				$f = fopen($debugging, 'a') or die("File can't open");
			// Open and write data to the nochex_debug file.
			$ret = fwrite($f, $stringData);
			// Incase there is no data being shown or written then an error will be produced.
			if ($ret === false)
			die("Fwrite failed");
			
				// Closes the open file.
				fclose($f)or die("File not close");
			} 
			//If a problem or something doesn't work, then the catch will produce an email which will send an error message.
			catch(Exception $e)
			{
			mail($this->email, "Debug Check Error Message", $e->getMessage());
			}
		}
	}
	
  ////
////
/*---  Function shows the display form for the admin/config form. ---*/
	private function _displayForm()
	{
	/*--- Calls the function to return the value of the checkbox ---*/
	$validateTestCheck = $this->_validateTestCheckbox();
	$validateBillCheck = $this->_validateBillCheckbox();
	$validateDebugCheck = $this->_validateDebugCheckbox();
	$validateXmlcollectionCheck = $this->_validateXmlcollectionCheckbox();
	$validatePostageCheck = $this->_validatePostageCheckbox();
	$validateCallbackCheck = $this->_validateCallbackCheckbox();
	
	/*--- Form parts that are added in the Configuration file of the nochex module. ---*/
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Account details').'</legend>
				<table border="0" width="1250" cellpadding="0" cellspacing="0" id="form">
					<tr><td colspan="2">'.$this->l('Please specify your Nochex account details').'.<br /><br /></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Nochex Merchant ID / Email Address').'</td><td><input type="text" name="email" value="'.htmlentities(Tools::getValue('email', $this->email), ENT_COMPAT, 'UTF-8').'" style="width: 250px;" /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Nochex Merchant ID / Email Address, This is your Nochex Merchant ID, e.g. test@test.com or one that has been created: e.g. test</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Test Mode').'</td><td><input type="checkbox" name="test_mode" value="checked" '. $validateTestCheck .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Test Mode, If the Test mode option has been selected, the system will be in test mode. Note (leave unchecked for Live transactions.) </p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Hide Billing Details').'</td><td><input type="checkbox" name="hide_details" value="checked" '. $validateBillCheck .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Hide Billing Details, If the Hide Billing Details option has been checked then billing details will be hidden, Leave unchecked if you want customers to see billing details.</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Debug').'</td><td><input type="checkbox" name="nochex_debug" value="checked" '. $validateDebugCheck .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Debug, If the Debug option has been selected, details of the module will be saved to a file. nochex_debug.txt which can be found in the nochex module which can be found somewhere like: www.test.com/prestashop/modules/nochex/nochex_debug.txt, leave unchecked if you dont want to record data about the system.</p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Detailed Product Information').'</td><td><input type="checkbox" name="nochex_xmlcollection" value="checked" '. $validateXmlcollectionCheck .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"></p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Postage').'</td><td><input type="checkbox" name="nochex_postage" value="checked" '. $validatePostageCheck .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"></p></td></tr>
					<tr><td width="300" style="height: 35px;">'.$this->l('Callback').'</td><td><input type="checkbox" name="nochex_callback" value="checked" '. $validateCallbackCheck .' /></td><td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">To use the callback functionality, please contact Nochex Support to enable this functionality on your merchant account otherwise this function wont work.</p></td></tr>
					<tr><td></td><td><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
				</table>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayNoChex();
		$this->_displayForm();

		return $this->_html;
	}

	
	protected function ncxForm()
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
			$apc_email = str_replace(" ", "", Configuration::get('NOCHEX_APC_EMAIL'));
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
		
		$optional2 = 'callback';
		
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

		
		return $this->context->smarty->fetch('module:nochex/views/templates/hook/nochex_checkout_payment.tpl');
	
	
	}
 
  public function hookPaymentOptions($params)
    {
	
	$apc_email = str_replace(" ", "", Configuration::get('NOCHEX_APC_EMAIL'));
	
	if ($apc_email != ""){
        $newOption = new PaymentOption();
				
			$newOption->setCallToActionText($this->l('Nochex'))
                       ->setForm($this->ncxForm())
                       ->setAdditionalInformation($this->context->smarty->fetch('module:nochex/views/templates/front/payment_infos.tpl'));	
				

        return [$newOption];
	}	
    }	
 
}

?>
