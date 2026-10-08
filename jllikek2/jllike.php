<?php
/**
 * jllike
 *
 * @version 5.2.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2025 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

// Load the K2 Plugin API (класса Joomla\CMS\Loader\ClassLoader в Joomla 4-6 нет — был фатал)
if (!is_file(JPATH_ADMINISTRATOR . '/components/com_k2/lib/k2plugin.php'))
{
    return;
}
\JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . '/components/com_k2/lib/k2plugin.php');

class PlgK2Jllike extends \K2Plugin
{

    // Some params
    public $pluginName = 'line';
    public $pluginNameHumanReadable = 'Line-Chart';
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
        $helper = PlgJLLikeHelper::getInstance($this->params, 'k2', 'jllike');

        $isCategory = ($input->getString('view', '') == 'itemlist') ? true : false;
        $link = $this->getRoute($article);

        if($this->params->get('k2_images', 'fields') == 'fields' && !empty($article->imageLarge))
        {
            // Абсолютный URL формирует PlgJLLikeHelper::ShowIN
            $image = trim($article->imageLarge);
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

    /**
     * Абсолютная ссылка на материал K2 (метод вызывался, но не был объявлен)
     */
    private function getRoute($article)
    {
        if (!class_exists('K2HelperRoute'))
        {
            require_once JPATH_SITE . '/components/com_k2/helpers/route.php';
        }

        $catAlias = isset($article->category->alias) ? $article->category->alias : '';
        $route = \K2HelperRoute::getItemRoute(
            $article->id . ':' . urlencode($article->alias),
            $article->catid . ':' . urlencode($catAlias)
        );

        return Route::_($route, false, Route::TLS_IGNORE, true);
    }
}
