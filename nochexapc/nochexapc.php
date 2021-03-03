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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class NochexApc extends PaymentModule
{
    private $html = '';
    private $postErrors = [];

    public $details;
    public $owner;
    public $address;

    public function __construct()
    {
        $this->name = 'nochexapc';
        $this->tab = 'payments_gateways';
        $this->controllers = ['payment', 'validation'];
        $this->author = 'Nochex';
        $this->version = '3.0.4';
        $this->module_key = 'f43b0673015bdd13977381a3ee77bba4';
        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->trans('Nochex APC Module');
        $this->description = $this->trans('Accept payments by Nochex');
        $this->confirmUninstall = $this->trans('Are you sure you want to delete your details?');
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
        if (!Configuration::deleteByName('NOCHEX_APC_VAL_EMAIL')
                or !Configuration::deleteByName('NOCHEX_APC_VAL_TESTMODE')
                or !Configuration::deleteByName('NOCHEX_APC_VAL_HIDEDETAILS')
                or !Configuration::deleteByName('NOCHEX_APC_VAL_DEBUG')
                or !Configuration::deleteByName('NOCHEX_APC_VAL_XMLCOLLECTION')
                or !Configuration::deleteByName('NOCHEX_APC_VAL_POSTAGE')
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
            if (!Tools::getValue('NOCHEX_APC_VAL_EMAIL')) {
                $this->postErrors[] = $this->trans(
                    'The "Merchant Alias ID / Email Address" field is a required.',
                    [],
                    'Modules.Nochexapc.Admin'
                );
            }
        }
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('NOCHEX_APC_VAL_EMAIL', Tools::getValue('NOCHEX_APC_VAL_EMAIL'));
            Configuration::updateValue('NOCHEX_APC_VAL_TESTMODE', Tools::getValue('NOCHEX_APC_VAL_TESTMODE'));
            Configuration::updateValue('NOCHEX_APC_VAL_HIDEDETAILS', Tools::getValue('NOCHEX_APC_VAL_HIDEDETAILS'));
            Configuration::updateValue('NOCHEX_APC_VAL_DEBUG', Tools::getValue('NOCHEX_APC_VAL_DEBUG'));
            Configuration::updateValue('NOCHEX_APC_VAL_XMLCOLLECTION', Tools::getValue('NOCHEX_APC_VAL_XMLCOLLECTION'));
            Configuration::updateValue('NOCHEX_APC_VAL_POSTAGE', Tools::getValue('NOCHEX_APC_VAL_POSTAGE'));
        }
        $this->html .= $this->displayConfirmation(
            $this->trans('Settings updated', [], 'Admin.Notifications.Success')
        );
    }


    public function writeDebug($DebugData)
    {
        $nochex_debug = Configuration::get('NOCHEX_APC_VAL_DEBUG');
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


    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Nochex Module Settings', [], 'Modules.Nochexapc.Admin'),
                    'icon' => 'icon-envelope'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans(
                            'Merchant Alias ID / Email Address',
                            [],
                            'Modules.Nochexapc.Admin'
                        ),
                        'name' => 'NOCHEX_APC_VAL_EMAIL',
                        'required' => true
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Test Mode', [], 'Modules.Nochexapc.Admin'),
                        'name' => 'NOCHEX_APC_VAL_TESTMODE',
                        'required' => false,
                        'values' => [
                            [
                                'id' => 'nochexapc_testmode_on',
                                'value' => 1,
                                'label' => $this->trans('Enabled'),
                            ],
                            [
                                'id' => 'nochexapc_testmode_off',
                                'value' => 0,
                                'label' => $this->trans('Disabled'),
                            ]
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Hide Billing Details', [], 'Modules.Nochexapc.Admin'),
                        'name' => 'NOCHEX_APC_VAL_HIDEDETAILS',
                        'required' => false,
                        'values' => [
                        [
                            'id' => 'nochexapc_hidedetails_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled'),
                        ],
                        [
                            'id' => 'nochexapc_hidedetails_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled'),
                        ]
                    ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Debug Mode', [], 'Modules.Nochexapc.Admin'),
                        'name' => 'NOCHEX_APC_VAL_DEBUG',
                        'required' => false,
                        'values' => [
                        [
                            'id' => 'nochexapc_debug_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled'),
                        ],
                        [
                            'id' => 'nochexapc_debug_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled'),
                        ]
                    ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Detailed Product Information', [], 'Modules.Nochexapc.Admin'),
                        'name' => 'NOCHEX_APC_VAL_XMLCOLLECTION',
                        'required' => false,
                        'values' => [
                        [
                            'id' => 'nochexapc_xmlC_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled'),
                        ],
                        [
                            'id' => 'nochexapc_xmlC_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled'),
                        ]
                    ],
                   ],
                   [
                        'type' => 'switch',
                        'label' => $this->trans('Show Postage Separately', [], 'Modules.Nochexapc.Admin'),
                        'name' => 'NOCHEX_APC_VAL_POSTAGE',
                        'required' => false,
                        'values' => [
                        [
                            'id' => 'nochexapc_postage_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled'),
                        ],
                        [
                            'id' => 'nochexapc_postage_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled'),
                        ]
                   ],
                   ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ]
            ],
        ];
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];
        $this->fields_form = [];
        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        return [
            'NOCHEX_APC_VAL_EMAIL' => Tools::getValue(
                'NOCHEX_APC_VAL_EMAIL',
                Configuration::get('NOCHEX_APC_VAL_EMAIL')
            ),
            'NOCHEX_APC_VAL_TESTMODE' => Tools::getValue(
                'NOCHEX_APC_VAL_TESTMODE',
                Configuration::get('NOCHEX_APC_VAL_TESTMODE')
            ),
            'NOCHEX_APC_VAL_HIDEDETAILS' => Tools::getValue(
                'NOCHEX_APC_VAL_HIDEDETAILS',
                Configuration::get('NOCHEX_APC_VAL_HIDEDETAILS')
            ),
            'NOCHEX_APC_VAL_DEBUG' => Tools::getValue(
                'NOCHEX_APC_VAL_DEBUG',
                Configuration::get('NOCHEX_APC_VAL_DEBUG')
            ),
            'NOCHEX_APC_VAL_XMLCOLLECTION' => Tools::getValue(
                'NOCHEX_APC_VAL_XMLCOLLECTION',
                Configuration::get('NOCHEX_APC_VAL_XMLCOLLECTION')
            ),
            'NOCHEX_APC_VAL_POSTAGE' => Tools::getValue(
                'NOCHEX_APC_VAL_POSTAGE',
                Configuration::get('NOCHEX_APC_VAL_POSTAGE')
            ),
        ];
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
                    $this->html .= $this->displayError($this->trans($err, [], 'Notifications.Error'));
                }
            }
        } else {
            $this->html .= '<br />';
        }
        $this->html .= $this->displayNoChex();
        $this->html .= $this->renderForm();
        return $this->html;
    }

    private function displayNoChex()
    {
        return $this->display(__FILE__, './views/templates/hook/infos.tpl');
    }

    public function hookPaymentOptions($params)
    {
        $newOption = new PaymentOption();
        $newOption->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/clear-mp.png'))
                  ->setAction($this->context->link->getModuleLink($this->name, 'postprocess', [], true));
        return [$newOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
    }
}
