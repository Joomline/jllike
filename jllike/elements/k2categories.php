<?php
/**
 * @version		$Id: categoriesmultiple.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseDriver;

class JFormFieldk2Categories extends FormField
{

    public $type = 'k2categories';

    protected function getInput()
    {
        if(!is_file(JPATH_ADMINISTRATOR.'/components/com_k2/k2.php'))
        {
            return '';
        }

        $value = empty($this->value) ? array() : $this->value;

        $db = Factory::getDbo();
        $query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                $v->title = $v->name;
                $v->parent_id = $v->parent;
                $pt = $v->parent;
                $list = isset($children[$pt]) ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();

        foreach ($list as $item)
        {
            $item->treename = str_ireplace('&#160;', '- ', $item->treename);
            $mitems[] = HTMLHelper::_('select.option', $item->id, '   '.$item->treename);
        }

        $output = HTMLHelper::_('select.genericlist', $mitems, $this->name, 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $value);
        return $output;
    }
}