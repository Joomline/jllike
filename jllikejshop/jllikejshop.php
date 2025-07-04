<?php
/**
 * jllike
 *
 * @version 5.0.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2025 by Joomline (https://joomline.ru)
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

require_once JPATH_ROOT . '/plugins/content/jllike/helper.php';

class PlgJshoppingproductsJlLikeJShop extends CMSPlugin
{
    // Новый приватный метод для получения базового Uri
    private function getBaseUri($plgParams)
    {
        $uri = new Uri(Uri::root());
        $uri->setScheme((Factory::getConfig()->get('force_ssl') == 2) ? 'https' : 'http');
        $host = $uri->getHost();
        $pathbase = $plgParams->get('pathbase', '');
        if ($pathbase && strpos($host, 'www.') === false && $pathbase === 'www.') {
            $host = 'www.' . $host;
        } elseif ($pathbase === '' && strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }
        $uri->setHost($host);
        $uri->setPath('');
        $uri->setQuery([]);
        return $uri;
    }

    public function onBeforeDisplayProductView(&$content)
    {
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
        // Новый способ формирования базового URL
        $baseUri = $this->getBaseUri($plgParams);
        $url = $baseUri->toString();
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
            // После конвертации обновляем host в Uri
            $baseUri->setHost(parse_url($url, PHP_URL_HOST));
        }
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
