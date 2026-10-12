<?php
/**
 * jllike
 *
 * @version 5.3.1
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2026 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

require_once JPATH_ROOT . '/plugins/content/jllike/helper.php';

class PlgJshoppingproductsJlLikeJShop extends CMSPlugin
{
    public function onBeforeDisplayProductView(&$content)
    {
        // Без включённого контент-плагина JL Like (там настройки) ничего не выводим: иначе PHP-предупреждения
        if (!PluginHelper::isEnabled('content', 'jllike')) {
            return '';
        }
        Factory::getLanguage()->load('plg_content_jllike');
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $plgParams = new Registry;
        $plgParams->loadString($plugin->params);
        $input = Factory::getApplication()->input;
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
        $uri = str_ireplace(Uri::root(), '', Uri::current());
        $route = $uri;
        $link = rtrim(Uri::root(), '/') . '/' . ltrim($route, '/');

        $image = '';
        if (!empty($content->product->image)) {
            $image = $content->product->image;
        }

        if (!empty($image))
        {
            $jshopConfig = JSFactory::getConfig();
            $image = $jshopConfig->image_product_live_path . '/' . $image;
        }

        $lang = Factory::getLanguage()->getTag();
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
