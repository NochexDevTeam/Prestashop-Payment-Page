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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Nochex extends PaymentModule
{
    private $html = '';
    private $postErrors = array();

    public $details;
    public $owner;
    public $address;

    public function __construct()
    {
        $this->name = 'nochex';
        $this->tab = 'payments_gateways';
        $this->controllers = array('payment', 'validation');
        $this->author = 'Nochex';
        $this->version = '3.0.1';
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $config = Configuration::getMultiple(
            array(
            'NOCHEX_APC_EMAIL',
            'NOCHEX_APC_TESTMODE',
            'NOCHEX_APC_HIDEDETAILS',
            'NOCHEX_APC_DEBUG',
            'NOCHEX_APC_XMLCOLLECTION',
            'NOCHEX_APC_POSTAGE'
            )
        );
        if (isset($config['NOCHEX_APC_EMAIL'])) {
            $this->email = $config['NOCHEX_APC_EMAIL'];
        }
        if (isset($config['NOCHEX_APC_TESTMODE'])) {
            $this->test_mode = $config['NOCHEX_APC_TESTMODE'];
        }
        if (isset($config['NOCHEX_APC_HIDEDETAILS'])) {
            $this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
        }
        if (isset($config['NOCHEX_APC_DEBUG'])) {
            $this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
        }
        if (isset($config['NOCHEX_APC_XMLCOLLECTION'])) {
            $this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
        }
        if (isset($config['NOCHEX_APC_POSTAGE'])) {
            $this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
        }
        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Nochex APC Module');
        $this->description = $this->l('Accept payments by Nochex');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
        if ($this->email == "") {
            $this->warning = $this->l('Account APC Id and Email must 
            be configured in order to use this module correctly');
        }
    }

    public function install()
    {
        if (!parent::install() or !$this->registerHook('paymentOptions') or !$this->registerHook('paymentReturn')) {
            return false;
        } else {
            return true;
        }
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('NOCHEX_APC_EMAIL')
                or !Configuration::deleteByName('NOCHEX_APC_TESTMODE')
                or !Configuration::deleteByName('NOCHEX_APC_HIDEDETAILS')
                or !Configuration::deleteByName('NOCHEX_APC_DEBUG')
                or !Configuration::deleteByName('NOCHEX_APC_XMLCOLLECTION')
                or !Configuration::deleteByName('NOCHEX_APC_POSTAGE')
                or !Configuration::deleteByName('NOCHEX_ACTIVE')
                or !parent::uninstall()) {
            return false;
        } else {
            return true;
        }
    }

    private function postValidation()
    {
        if (Tools::getValue('btnSubmit')) {
            if (empty(Tools::getValue('email'))) {
                $this->postErrors[] = $this->l('Account Email Id is required.');
            }
        }
    }

    private function postProcess()
    {
        if (Tools::getValue('btnSubmit')) {
            Configuration::updateValue('NOCHEX_APC_EMAIL', Tools::getValue('email'));
            Configuration::updateValue('NOCHEX_APC_TESTMODE', Tools::getValue('test_mode'));
            Configuration::updateValue('NOCHEX_APC_HIDEDETAILS', Tools::getValue('hide_details'));
            Configuration::updateValue('NOCHEX_APC_DEBUG', Tools::getValue('nochex_debug'));
            Configuration::updateValue('NOCHEX_APC_XMLCOLLECTION', Tools::getValue('nochex_xmlcollection'));
            Configuration::updateValue('NOCHEX_APC_POSTAGE', Tools::getValue('nochex_postage'));
            Configuration::updateValue('NOCHEX_ACTIVE', '1');
            $this->html .= '<div class="conf confirm">
            <span style="color:#fff;padding: 5px;background:green;font-weight:bold;">
            '.$this->l('Settings updated').'</span></div>';
        }
    }

    private function displayNoChex()
    {
        $this->html .= '<img src="https://www.nochex.com/logobase-secure-images/logobase-banners/clear-mp.png" 
        height="100px" style="float:left; margin-right:15px;"><br style="clear:both;"/><br style="clear:both;"/><b>'.
        $this->l('This module allows you to accept payments by Nochex (APC Method).').'</b><br /><br />
        '.$this->l('If the client chooses this payment mode, the order will change 
        its status once a positive confirmation is recieved from nochex server').'<br /><br /><br />';
    }

    private function validateTestCheckbox()
    {
        $config = Configuration::getMultiple(
            array(
            'NOCHEX_APC_EMAIL',
            'NOCHEX_APC_TESTMODE',
            'NOCHEX_APC_HIDEDETAILS',
            'NOCHEX_APC_DEBUG',
            'NOCHEX_APC_XMLCOLLECTION',
            'NOCHEX_APC_POSTAGE'
            )
        );
        $this->test_mode = $config['NOCHEX_APC_TESTMODE'];
        return $this->test_mode;
    }

    private function validateBillCheckbox()
    {
        $config = Configuration::getMultiple(
            array(
            'NOCHEX_APC_EMAIL',
            'NOCHEX_APC_TESTMODE',
            'NOCHEX_APC_HIDEDETAILS',
            'NOCHEX_APC_DEBUG',
            'NOCHEX_APC_XMLCOLLECTION',
            'NOCHEX_APC_POSTAGE'
            )
        );
        $this->hide_details = $config['NOCHEX_APC_HIDEDETAILS'];
        return $this->hide_details;
    }

    private function validateDebugCheckbox()
    {
        $config = Configuration::getMultiple(
            array(
            'NOCHEX_APC_EMAIL',
            'NOCHEX_APC_TESTMODE',
            'NOCHEX_APC_HIDEDETAILS',
            'NOCHEX_APC_DEBUG',
            'NOCHEX_APC_XMLCOLLECTION',
            'NOCHEX_APC_POSTAGE'
            )
        );
        $this->nochex_debug = $config['NOCHEX_APC_DEBUG'];
        return $this->nochex_debug;
    }

    private function validateXmlcollectionCheckbox()
    {
        $config = Configuration::getMultiple(
            array(
            'NOCHEX_APC_EMAIL',
            'NOCHEX_APC_TESTMODE',
            'NOCHEX_APC_HIDEDETAILS',
            'NOCHEX_APC_DEBUG',
            'NOCHEX_APC_XMLCOLLECTION',
            'NOCHEX_APC_POSTAGE'
            )
        );
        $this->nochex_xmlcollection = $config['NOCHEX_APC_XMLCOLLECTION'];
        return $this->nochex_xmlcollection;
    }

    private function validatePostageCheckbox()
    {
        $config = Configuration::getMultiple(
            array(
            'NOCHEX_APC_EMAIL',
            'NOCHEX_APC_TESTMODE',
            'NOCHEX_APC_HIDEDETAILS',
            'NOCHEX_APC_DEBUG',
            'NOCHEX_APC_XMLCOLLECTION',
            'NOCHEX_APC_POSTAGE'
            )
        );
        $this->nochex_postage = $config['NOCHEX_APC_POSTAGE'];
        return $this->nochex_postage;
    }

    public function writeDebug($DebugData)
    {
        $nochex_debug = Configuration::get('NOCHEX_APC_DEBUG');
        if ($nochex_debug == "checked") {
            $debug_TimeDate = date("m/d/Y h:i:s a", time());
            $stringData = "\n Time and Date: " . $debug_TimeDate . "... " . $DebugData ."... ";
            $debugging = "../modules/nochex/nochex_debug.txt";
            $f = fopen($debugging, 'a') or die("File can't open");
            $ret = fwrite($f, $stringData);
            if ($ret === false) {
                die("Fwrite failed");
            }
            fclose($f)or die("File not close");
        }
    }

    private function displayForm()
    {
        $validateTestCheck = $this->validateTestCheckbox();
        $validateBillCheck = $this->validateBillCheckbox();
        $validateDebugCheck = $this->validateDebugCheckbox();
        $validateXmlcollectionCheck = $this->validateXmlcollectionCheckbox();
        $validatePostageCheck = $this->validatePostageCheckbox();
        $this->html .=
        '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />'.$this->l('Account details').'</legend>
                <table border="0" width="1250" cellpadding="0" cellspacing="0" id="form">
                    <tr><td colspan="2">'.$this->l('Please specify your Nochex account details').
                    '.<br /><br /></td></tr>
                    <tr>
                    <td width="300" style="height: 35px;">'.$this->l('Nochex Merchant ID / Email Address').'</td>
                    <td><input type="text" name="email" value="'.
                    htmlentities(
                        Tools::getValue('email', $this->email),
                        ENT_COMPAT,
                        'UTF-8'
                    ).'" style="width: 250px;" />
                    </td>
                    <td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> 
                    Nochex Merchant ID / Email Address, 
                    This is your Nochex Merchant ID, e.g. test@test.com or 
                    one that has been created: e.g. test</p></td></tr>
                    <tr><td width="300" style="height: 35px;">'.$this->l('Test Mode').'</td>
                    <td><input type="checkbox" name="test_mode" value="checked" '. $validateTestCheck .' /></td>
                    <td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> Test Mode, 
                    If the Test mode option has been selected, the system will be in test mode. 
                    Note (leave unchecked for Live transactions.) </p></td></tr>
                    <tr><td width="300" style="height: 35px;">'.$this->l('Hide Billing Details').'</td>
                    <td><input type="checkbox" name="hide_details" value="checked" '. $validateBillCheck .' /></td>
                    <td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;"> 
                    Hide Billing Details, If the Hide Billing Details option 
                    has been checked then billing details will be hidden, 
                    Leave unchecked if you want customers to see billing details.</p></td></tr>
                    <tr><td width="300" style="height: 35px;">'.$this->l('Debug').'</td>
                    <td><input type="checkbox" name="nochex_debug" value="checked" '. $validateDebugCheck .' /></td>
                    <td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">
                    Debug, If the Debug option has been selected, 
                    details of the module will be saved to a file. nochex_debug.txt 
                    which can be found in the nochex module which can be found 
                    somewhere like: www.test.com/prestashop/modules/nochex/nochex_debug.txt, 
                    leave unchecked if you dont want to record data about the system.</p></td></tr>
                    <tr><td width="300" style="height: 35px;">'.$this->l('Detailed Product Information').'</td>
                    <td><input type="checkbox" name="nochex_xmlcollection" value="checked" '.
                    $validateXmlcollectionCheck .' /></td>
                    <td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">
                    Detailed Product Information, display order details in a 
                    table structured format on your Nochex Payment Page</p></td></tr>
                    <tr><td width="300" style="height: 35px;">'.$this->l('Postage').'</td>
                    <td><input type="checkbox" name="nochex_postage" value="checked" '. $validatePostageCheck .' /></td>
                    <td width="950"><p style="font-style:italic; text-size:7px; padding-left:10px;">
                    Postage, display the postage amount separately to the Total Amount on your Nochex Payment Page
                    </p></td></tr>
                    <tr><td></td><td>
                    <input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" />
                    </td></tr>
                </table>
            </fieldset>
        </form>';
    }

    public function getContent()
    {
        $this->html = '<h2>'.$this->displayName.'</h2>';
        if (!empty($_POST)) {
            $this->postValidation();
            if (!sizeof($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= '<div class="alert error">'. $err .'</div>';
                }
            }
        } else {
            $this->html .= '<br />';
        }
        $this->displayNoChex();
        $this->displayForm();
        return $this->html;
    }

    public function hookPaymentOptions($params)
    {
        $newOption = new PaymentOption();
        $newOption->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/clear-mp.png'))
                  ->setAction($this->context->link->getModuleLink($this->name, 'postprocess', array(), true));
        return [$newOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
    }
}
