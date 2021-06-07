<?php

/**
 * 2007-2021 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(dirname(__FILE__) . '/classes/ReassuranceClass.php');

class Reassurance extends Module
{
    protected $config_form = false;
    protected $_html = '';
    public $img_path;
    protected $templateFile;
    public function __construct()
    {
        $this->name = 'reassurance';
        $this->tab = 'administration';
        $this->version = '1.7.0';
        $this->author = 'Ali ederar';
        $this->bootstrap = true;
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('reassurance Test module');
        $this->description = $this->l('reassurance Test module');
        $this->templateFile = 'module:reassurance/views/templates/hook/reassurance.tpl';
        $this->img_path = $this->_path . 'views/img/';
        $this->confirmUninstall = $this->l('Are you Sure ?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function createTabs()
    {
        $idParent = (int) Tab::getIdFromClassName('AdminReass');
        if (empty($idParent)) {
            $parent_tab = new Tab();
            $parent_tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $parent_tab->name[$lang['id_lang']] = $this->trans('Reassurance Module');
            }
            $parent_tab->class_name = 'AdminReass';
            $parent_tab->id_parent = 0;
            $parent_tab->module = $this->name;
            $parent_tab->icon = 'library_books';
            $parent_tab->add();
        }

        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Reassurance');
        }
        $tab->class_name = 'AdminReassurance';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminReass');
        $tab->module = $this->name;
        $tab->icon = 'library_books';
        $tab->add();

 
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Configuration');
        }
        $tab->class_name = 'AdminConfigurationReassurance';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminReassurance');
        $tab->module = $this->name;
        $tab->add();

       
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Reassurance');
        }
        $tab->class_name = 'AdminReassurance';
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminReassurance');
        $tab->module = $this->name;
        $tab->add();

        return true;
    }

    public function removeTabs($class_name)
    {
        if ($tab_id = (int)Tab::getIdFromClassName($class_name)) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateGlobalValue('REASSURANCE_LIMIT', 10);
        Configuration::updateGlobalValue('REASSURANCE_ICON_WIDTH', 40);
        Configuration::updateGlobalValue('REASSURANCE_ICON_HEIGHT', 40);
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->createTabs() &&
            $this->registerHook('header') &&
            $this->registerHook('displayFooterBefore') &&
            $this->registerHook('displayReassurance');
    }

    public function uninstall()
    {

        include(dirname(__FILE__) . '/sql/uninstall.php');
        $this->removeTabs('AdminReassurance');
        $this->removeTabs('AdminConfigReassurance');
        $this->removeTabs('AdminReassurance');
        return parent::uninstall();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitReassuranceModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitReassuranceModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'REASSURANCE_ICON_WIDTH',
                        'label' => $this->trans('Icon Width'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'REASSURANCE_ICON_HEIGHT',
                        'label' => $this->trans('Icon Height'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'REASSURANCE_LIMIT',
                        'label' => $this->trans('Limit'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'REASSURANCE_ICON_WIDTH' => Configuration::get('REASSURANCE_ICON_WIDTH', 40),
            'REASSURANCE_ICON_HEIGHT' => Configuration::get('REASSURANCE_ICON_HEIGHT', 40),
            'REASSURANCE_LIMIT' => Configuration::get('REASSURANCE_LIMIT', 10),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }


    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookdisplayFooterBefore()
    {
        $reassurances = ReassuranceClass::getReassurance();
        $icon_width = Configuration::get('REASSURANCE_ICON_WIDTH');
        $icon_height = Configuration::get('REASSURANCE_ICON_HEIGHT');
        $this->context->smarty->assign(array(
            'reassurances' => $reassurances,
            'uri' => $this->img_path,
            'iconWidth' => $icon_width,
            'iconHeight' => $icon_height,
        ));


        return $this->fetch('module:reassurance/views/templates/hook/footer_before.tpl');
    }

    public function hookdisplayReassurance()
    {
        $icon_width = Configuration::get('REASSURANCE_ICON_WIDTH');
        $icon_height = Configuration::get('REASSURANCE_ICON_HEIGHT');
        $reassurances = ReassuranceClass::getReassurance();
        $this->context->smarty->assign(array(
            'reassurances' => $reassurances,
            'uri' => $this->img_path,
            'iconWidth' => $icon_width,
            'iconHeight' => $icon_height,
        ));

        return $this->fetch('module:reassurance/views/templates/hook/reassurance.tpl');
    }
}
