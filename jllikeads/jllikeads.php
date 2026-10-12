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

class PlgAdsmanagercontentJlLikeAds extends CMSPlugin
{
    public function ADSonContentAfterDisplay($content)
    {
        // Без включённого контент-плагина JL Like (там настройки) ничего не выводим: иначе PHP-предупреждения
        if (!PluginHelper::isEnabled('content', 'jllike'))
        {
            return '';
        }
        Factory::getLanguage()->load('plg_content_jllike');
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $plgParams = new Registry;
        $plgParams->loadString($plugin->params);
        $view = Factory::getApplication()->input->get('view');
        $ADSShow = $plgParams->get('adscontent', 0);

        if (!$ADSShow || $view != 'details')
        {
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

        if(!defined('JURI_IMAGES_FOLDER')){
            define('JURI_IMAGES_FOLDER',Uri::root()."images/com_adsmanager/contents");
        }

        $image = (!empty($content->images[0]->thumbnail)) ? JURI_IMAGES_FOLDER . '/' . $content->images[0]->thumbnail : '';

        $text = $helper->getShareText($content->metadata_description, $content->ad_text, $content->ad_text);
        $shares = $helper->ShowIN($content->id, $link, $content->ad_headline, $image, $text, $plgParams->get('enable_opengraph', 1));

        return $shares;
    } //end function

}//end class
