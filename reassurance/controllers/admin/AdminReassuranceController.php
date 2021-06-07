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


class AdminReassuranceController extends ModuleAdminController
{

    protected $position_identifier = 'id_reassurance';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'reassurance';
        $this->className = 'ReassuranceClass';
        $this->identifier = 'id_reassurance';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';
        $this->toolbar_btn = null;
        $this->list_no_link = true;
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->trans('Delete selected'),
                'confirm' => $this->trans('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->fields_list = array(
            'id_reassurance' => array(
                'title' => $this->trans('ID')
            ),
            'title' => array(
                'title' => $this->trans('Title'),
                'filter_key' => 'b!title',
            ),
            'icon' => array(
                'title' => $this->trans('Icon'),
                'type' => 'text',
                'callback' => 'showIcon',
                'callback_object' => 'ReassuranceClass',
                'class' => 'fixed-width-xxl',
                'search' => false,
                'orderby' => false,
            ),
            'description' => array(
                'title' => $this->trans('Description'),
                'width' => 'auto',
                'orderby' => false,
                'callback' => 'getDescriptionClean',

            ),
            'alt' => array(
                'title' => $this->trans('alt'),
                'width' => 'auto',
                'orderby' => false,
            ),
            'active' => array(
                'title' => $this->trans('Displayed'),
                'align' => 'center',
                'active' => 'status',
                'class' => 'fixed-width-sm',
                'type' => 'bool',
                'orderby' => false,
            ),
            'position' => array(
                'title' => $this->trans('Position'),
                'filter_key' => 'position',
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
                'orderby' => true,
            ),
        );
    }

    public static function getDescriptionClean($description)
    {
        return Tools::getDescriptionClean($description);
    }

    public function initPageHeaderToolbar()
    {
        $count = count(ReassuranceClass::getReassuranceCount());
        $limit =  Configuration::get('REASSURANCE_LIMIT');
            if (empty($this->display)) {
               if ($count < $limit) {
                $this->page_header_toolbar_btn['new_Reassurance'] = array(
                    'href' => self::$currentIndex.'&addreassurance&token='.$this->token,
                    'desc' => $this->trans('Add new Reassurance'),
                    'icon' => 'process-icon-new'
                );
               }
            }    
        
        parent::initPageHeaderToolbar();
    }

  
    public function postProcess()
    {
        $img = array(
            'error' => array(),
            'image' => '',
        );
        if ($this->action && $this->action == 'save') {
            $allowed_ext = array('jpg','png', 'svg');
            if (isset($_FILES['icon']) && isset($_FILES['icon']['tmp_name']) && !empty($_FILES['icon']['tmp_name'])) {
                $name = str_replace(strrchr($_FILES['icon']['name'], '.'), '', $_FILES['icon']['name']);
    
                $image_size = @getimagesize($_FILES['icon']['tmp_name']);
                if (!empty($image_size) &&
                    ImageManager::isCorrectImageFileExt($_FILES['icon']['name'], $allowed_ext)) {
                    $image_name = explode('.', $_FILES['icon']['name']);
                    $image_extension = $image_name[1];
                    $temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                    $coverimage_name = $name .'-'.rand(0, 1000).'.'.$image_extension;
                    if ($upload_failer = ImageManager::validateUpload($_FILES['icon'])) {
                        $img['error'][] = $upload_failer;
                    } elseif (!$temp_name || !move_uploaded_file($_FILES['icon']['tmp_name'], $temp_name)) {
                        $img['error'][] = $this->trans('An error occurred during move image.',[],'Modules.Reassurance');
                    } else {
                        $destinationFile = _PS_MODULE_DIR_ . $this->module->name.'/views/img/'.$coverimage_name;
                        if (!ImageManager::resize($temp_name, $destinationFile, null, null, $image_extension)){
                            $img['error'][] = $this->trans('An error occurred during the image upload.',[],'Modules.Reassurance');
                        }
                    }
                    if (isset($temp_name)) {
                        @unlink($temp_name);
                    }
    
                    if (!count($img['error'])) {
                        $img['image'] = $coverimage_name;
                        $img['width'] = $image_size[0];
                        $img['height'] = $image_size[1];
                    }
                    if (isset($img['image']) && !empty($img['image'])) {
                        $_POST['icon'] = $img['image'];
                    }
                }
            }
        }
        return parent::postProcess();
    }

    public function renderForm()
    {
      
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->trans('title'),
                'icon' => 'icon-folder-close'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->trans('Title'),
                    'name' => 'title',
                    'lang' => true,
                    'desc' => $this->trans('Please enter a title'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->trans('Description'),
                    'name' => 'description',
                    'lang' => true,
                    'autoload_rte' => 'rte',
                    'desc' => $this->trans('Please enter a Description'),
                ),
                array(
                    'type' => 'file',
                    'label' => $this->trans('Icon'),
                    'name' => 'icon',
                    'desc' => $this->trans('The recommended dimensions are 40 x 40')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Alt'),
                    'name' => 'alt',
                    'lang' => true,
                    'desc' => $this->trans('Please enter an alternate text ')

                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Link'),
                    'name' => 'link',
                    'lang' => true,
                    'desc' => $this->trans('Please enter a link'),
                ),
    
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Display'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled')
                        )
                    )
                ),
            ),
             'submit' => array(
                'title' => $this->trans('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        return parent::renderForm();
    }


   
    public function ajaxProcessUpdatePositions()
    {
        $way = (int) (Tools::getValue('way'));
        $id_reassurance = (int) (Tools::getValue('id'));
        $positions = Tools::getValue($this->table);

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int) $pos[2] === $id_reassurance) {
                if ($posi = new ReassuranceClass((int) $pos[2])) {
                    if (isset($position) && $posi->updatePosition($way, $position)) {
                        echo 'ok position ' . (int) $position . ' for Reassurance ' . (int) $pos[1] . '\r\n';
                    } else {
                        echo '{"hasError" : true, "errors" : "Can not update Reassurance ' . (int) $id_reassurance . ' to position ' . (int) $position . ' "}';
                    }
                } else {
                    echo '{"hasError" : true, "errors" : "This Reassurance (' . (int) $id_reassurance . ') can t be loaded"}';
                }

                break;
            }
        }
    }
}