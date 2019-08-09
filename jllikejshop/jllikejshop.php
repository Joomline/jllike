<?php
/**
 * jllike
 *
 * @version 4.0.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2010-2016 by Vadim Kunicin (https://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined('_JEXEC') or die;
error_reporting(E_ERROR);
jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');
require_once JPATH_ROOT . '/plugins/content/jllike/helper.php';

class plgJshoppingProductsJlLikeJShop extends JPlugin
{


    public function onBeforeDisplayProductView(&$content)
    {
        JPlugin::loadLanguage('plg_content_jllike');
        $plugin = & JPluginHelper::getPlugin('content', 'jllike');
        $plgParams = new JRegistry;
        $plgParams->loadString($plugin->params);
        $input = JFactory::getApplication()->input;
        $view = $input->getCmd('controller', '');
        $JShopShow = $plgParams->get('jshopcontent');

        if (!$JShopShow || $view != 'product') {
            return '';
        }
		$parent_contayner = $this->params->get('parent_contayner', '');
        if(!empty($parent_contayner))
        {
            $plgParams->set('parent_contayner', $parent_contayner);
        }
        $helper = PlgJLLikeHelper::getInstance($plgParams);
        $helper->loadScriptAndStyle(0);
		$prefix = (JFactory::getConfig()->get('force_ssl') == 2) ? 'https://' : 'http://';
		$root = JURI::getInstance()->toString(array('host'));
        $url = $prefix . $plgParams->get('pathbase', '') . str_replace('www.', '', $root);
        if ($plgParams->get('punycode_convert', 0)) {
            $file = JPATH_ROOT . '/libraries/idna_convert/idna_convert.class.php';
            if (!JFile::exists($file)) {
                return JText::_('PLG_JLLIKEPRO_PUNYCODDE_CONVERTOR_NOT_INSTALLED');
            }

            include_once $file;

            if ($url) {
                if (class_exists('idna_convert')) {
                    $idn = new idna_convert;
                    $url = $idn->encode($url);
                }
            }
        }
        $uri = JString::str_ireplace(JURI::root(), '', JURI::current());
        $link = $url . '/' . $uri;

        $image = $content->product->product_name_image;

		if(empty($image))
		{
			$image = $content->product->image;
		}

        if (!empty($image))
        {
            $jshopConfig = JSFactory::getConfig();
            $image = $jshopConfig->image_product_live_path . '/' . $image;
        }

        $lang = JFactory::getLanguage()->getTag();
        $name = 'name_'.$lang;
        $sdesc = 'short_description_'.$lang;
        $desc = 'description_'.$lang;
        $mdesc = 'meta_description_'.$lang;

        $text = $helper->getShareText($content->product->$mdesc, $content->product->$sdesc, $content->product->$desc);
        $shares = $helper->ShowIN($content->product->product_id, $link, $content->product->$name, $image, $text, $plgParams->get('enable_opengraph', 1));

        switch ($plgParams->get('jshopposition', 2)) {
            case 1 :
                $content->_tmp_product_html_start = $shares;
                break;
            case 3 :
                $content->_tmp_product_html_end = $shares;
                break;
            default:
                $content->_tmp_product_html_after_buttons = $shares;
                break;
        }
    } //end function


}//end class
