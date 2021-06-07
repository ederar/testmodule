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

class ReassuranceClass extends ObjectModel
{
    public $id;
    public $icon;
    public $title;
    public $description;
    public $active;
    public $position;
    public $alt;
    public $link;
    public $date_add;
    public $date_upd;

       /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'reassurance',
        'primary' => 'id_reassurance',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'icon' => ['type' => self::TYPE_STRING, 'required' => false , 'validate' => 'isGenericName'],
            'title' => ['type' => self::TYPE_STRING, 'required' => true, 'lang' => true, 'validate' => 'isGenericName', 'size' => 60],
            'description' => ['type' => self::TYPE_HTML, 'lang' => true, 'required' => true ,'validate' => 'isCleanHtml'],
            'alt' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isGenericName', 'size' => 100],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'link' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl', 'required' => false, 'size' => 255],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];


    public static function getMaxPosition()
    {
        $query = new DbQuery();
        $query->select('MAX(position)');
        $query->from('reassurance', 'rs');

        $response = Db::getInstance()->getRow($query);

        if ($response['MAX(position)'] == null){
            return -1;
        }
        return $response['MAX(position)'];
    }

    public function add($autoDate = true, $nullValues = false)
    {
        $this->position = (int) $this->getMaxPosition() + 1;
        return parent::add($autoDate, $nullValues);
    }

    public function updatePosition($way, $position)
    {
        $query = new DbQuery();
        $query->select('rs.`id_reassurance`, rs.`position`');
        $query->from('reassurance', 'rs');
        $query->orderBy('rs.`position` ASC');
        $tabs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!$tabs ) {
            return false;
        }

        foreach ($tabs as $tab) {
            if ((int) $tab['id_reassurance'] == (int) $this->id) {
                $moved_tab = $tab;
            }
        }

        if (!isset($moved_tab) || !isset($position)) {
            return false;
        }

        return (Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'reassurance`
            SET `position`= `position` '.($way ? '- 1' : '+ 1').'
            WHERE `position`
            '.($way
                    ? '> '.(int)$moved_tab['position'].' AND `position` <= '.(int)$position
                    : '< '.(int)$moved_tab['position'].' AND `position` >= '.(int)$position
                ))
            && Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'reassurance`
            SET `position` = '.(int)$position.'
            WHERE `id_reassurance` = '.(int)$moved_tab['id_reassurance']));
    }


    public static function showIcon($value)
    {
        $src = __PS_BASE_URI__. 'modules/reassurance/views/img/'.$value;
        return $value ? '<img src="'.$src.'" width="40px" height="40px" class="img img-thumbnail"/>' : '-';
    }


    public static function getReassurance()
    {
        $idLang = Context::getContext()->language->id;

        $query = new DbQuery();
        $query->select('rs.*, rs_lang.*');
        $query->from('reassurance', 'rs');
        $query->leftJoin('reassurance_lang', 'rs_lang', 'rs.`id_reassurance` = rs_lang.`id_reassurance`'.Shop::addSqlRestrictionOnLang('rs_lang'));
        $query->where('rs.`active` =  1 AND rs_lang.`id_lang` =  '.(int) $idLang);
        $query->orderBy('rs.`position` ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public static function getReassuranceCount()
    {
        $query = new DbQuery();
        $query->select('rs.*');
        $query->from('reassurance','rs');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
}