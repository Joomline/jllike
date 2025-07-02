<?php
/**
 * jllike
 *
 * @version 5.0.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2010-2025 by Joomline (http://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

if (!class_exists('StringHelper1')) {
    if (class_exists('Joomla\\String\\StringHelper')) {
        class StringHelper1 extends \Joomla\String\StringHelper {}
    } else {
        class StringHelper1 {
            public static function str_ireplace($search, $replace, $subject, $count = null) {
                return str_ireplace($search, $replace, $subject, $count);
            }
            public static function strlen($string) {
                return mb_strlen($string);
            }
            public static function trim($string) {
                return trim($string);
            }
        }
    }
}

require_once JPATH_ROOT . '/plugins/content/jllike/helper.php';

class PlgJshoppingproductsJlLikeJShop extends CMSPlugin
{


    public function onBeforeDisplayProductView(&$content)
    {
        JPlugin::loadLanguage('plg_content_jllike');
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
		$prefix = (Factory::getConfig()->get('force_ssl') == 2) ? 'https://' : 'http://';
		$root = Uri::getInstance()->toString(['host']);
        $url = $prefix . $plgParams->get('pathbase', '') . str_replace('www.', '', $root);
        if ($plgParams->get('punycode_convert', 0)) {
            $file = JPATH_ROOT . '/libraries/idna_convert/idna_convert.class.php';
            if (!File::exists($file)) {
                return Text::_('PLG_JLLIKEPRO_PUNYCODDE_CONVERTOR_NOT_INSTALLED');
            }

            include_once $file;

            if ($url) {
                if (class_exists('idna_convert')) {
                    $idn = new idna_convert;
                    $url = $idn->encode($url);
                }
            }
        }
        $uri = StringHelper1::str_ireplace(Uri::root(), '', Uri::current());
        $link = $url . '/' . $uri;

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
