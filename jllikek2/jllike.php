<?php
/**
 * jllike
 *
 * @version 5.1.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2025 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die;

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
        $route = $this->getRoute($article);
        $link = rtrim(Uri::root(), '/') . '/' . ltrim($route, '/');

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
        $baseUri = $this->getBaseUri();
        $url = $baseUri->toString();
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
            $baseUri->setHost(parse_url($url, PHP_URL_HOST));
        }
        return $baseUri->toString();
    }

    private function getBaseUri()
    {
        $uri = new Uri(Uri::root());
        $uri->setScheme((Factory::getConfig()->get('force_ssl') == 2) ? 'https' : 'http');
        $host = $uri->getHost();
        $pathbase = $this->params->get('pathbase', '');
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
}
