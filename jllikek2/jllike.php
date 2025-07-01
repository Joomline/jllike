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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Example K2 Plugin to render YouTube URLs entered in backend K2 forms to video players in the frontend.
 */

// Load the K2 Plugin API
\JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . '/components/com_k2/lib/k2plugin.php');

class PlgK2Jllike extends \K2Plugin
{

    // Some params
    var $pluginName = 'line';
    var $pluginNameHumanReadable = 'Line-Chart';
    private $enableShow;

    function __construct(&$subject, $params)
    {
        if(!$this->enableShow())
		{
            $this->enableShow = false;
			return;
		}
        parent::__construct($subject, $params);
		$parent_contayner = $this->params->get('parent_contayner', '');
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $this->params = new Registry($plugin->params);
		if(!empty($parent_contayner))
        {
            $this->params->set('parent_contayner', $parent_contayner);
        }
        $this->loadLanguage('plg_content_jllike');
        $this->enableShow = true;
    }

    function onK2BeforeDisplay(&$item, &$params, $limitstart){
        if($this->check('onK2BeforeDisplay')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2AfterDisplayTitle(&$item, &$params, $limitstart){
        if($this->check('onK2AfterDisplayTitle')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2BeforeDisplayContent(&$item, &$params, $limitstart){
        if($this->check('onK2BeforeDisplayContent')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2AfterDisplayContent(&$item, &$params, $limitstart){
        if($this->check('onK2AfterDisplayContent')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }

    function onK2AfterDisplay(&$item, &$params, $limitstart){
        if($this->check('onK2AfterDisplay')){
            return $this->loadLikes($item, $params, $limitstart);
        }
    }


    private function enableShow()
	{
		$app = Factory::getApplication();
		$input = $app->input;
        $view = $input->getString('view','');
        $layout = $input->getString('layout','');
        $task = $input->getString('task','');

        if(!$app->isClient('administrator') && ($view == 'itemlist' || ($view == 'item' && ($layout == 'item' || !$layout))) && $task != 'edit' && $task != 'add')
		{
            return true;
        }
        else
		{
            return false;
        }
    }

    private function check($trigger)
	{
        if($this->enableShow && $trigger == $this->params->get('k2trigger', ''))
		{
            return true;
        }
        return false;
    }

    private function loadLikes(&$article, &$params, $limitstart)
    {

        $k2categories = $this->params->get('k2categories', array());
        $k2categories = (is_array($k2categories)) ? $k2categories : array();
        $input = Factory::getApplication()->input;
        $print = $input->getInt('print', 0);

        if(in_array($article->catid, $k2categories) || $print)
        {
            return '';
        }

        include_once JPATH_ROOT.'/plugins/content/jllike/helper.php';

        $url = $this->getUrl();
        $isCategory = ($input->getString('view', '') == 'itemlist') ? true : false;

        $helper = PlgJLLikeHelper::getInstance($this->params);
        $conf = Factory::getConfig();
        $enableSef = $conf->get('sef', 0);

        if($enableSef)
        {
            $link = $url.Route::_(K2HelperRoute::getItemRoute($article->id.':'.$article->alias, $article->catid.':'.urlencode($article->category->alias)));
        }
        else
        {
            $link = $url.'/'.K2HelperRoute::getItemRoute($article->id.':'.$article->alias, $article->catid.':'.urlencode($article->category->alias));
        }

        if($this->params->get('k2_images', 'fields') == 'fields' && !empty($article->imageLarge))
        {
            $image = trim($article->imageLarge);
            if(!empty($image))
            {
                if(StringHelper::strpos($image, '/') === 0)
                {
                    $image = StringHelper::substr($image, 1);
                }
                $image = Uri::root().$image;
            }
        }
        else
        {
            $image = PlgJLLikeHelper::extractImageFromText($article->introtext, $article->fulltext);
        }

        $text = $helper->getShareText($article->metadesc, $article->introtext, $article->fulltext);
        $enableOG = $isCategory ? 0 : $this->params->get('k2_add_opengraph', 0);
        $shares = $helper->ShowIN($article->id, $link, $article->title, $image, $text, $enableOG);

        if (!$isCategory)
        {
            $helper->loadScriptAndStyle(0);
            return $shares;
        }
        else if($this->params->get('allow_in_category', 0))
        {
            $helper->loadScriptAndStyle(1);
            return $shares;
        }
    }

    private function getUrl()
    {
		$root = Uri::getInstance()->toString(['host']);
		$prefix = (Factory::getConfig()->get('force_ssl') == 2) ? 'https://' : 'http://';
        $url = $prefix . $this->params->get('pathbase', '') . str_replace('www.', '', $root);

        if($this->params->get('punycode_convert',0))
        {
            $file = JPATH_ROOT.'/libraries/idna_convert/idna_convert.class.php';
            if(!File::exists($file))
            {
                return Text::_('PLG_JLLIKEPRO_PUNYCODDE_CONVERTOR_NOT_INSTALLED');
            }

            include_once $file;

            if($url)
            {
                if (class_exists('idna_convert'))
                {
                    $idn = new idna_convert;
                    $url = $idn->encode($url);
                }
            }
        }
        return $url;
    }
}
