<?php
/**
 * jllike
 *
 * @version 2.7.1
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2010-2017 by Joomline (http://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

if (version_compare(JVERSION, '3.5.0', 'ge'))
{
    if(!class_exists('StringHelper1')){
        class StringHelper1 extends \Joomla\String\StringHelper{}
    }
    if(!class_exists('JRegistry')){
        class JRegistry extends Joomla\Registry\Registry{}
    }
}
else
{
    if(!class_exists('StringHelper1')){
        jimport('joomla.string.string');
        class StringHelper1 extends JString{}
    }
}

class PlgJLLikeHelper
{
    var $params = null;

    protected static $instance = null;


    function __construct($params = null)
    {
        $this->params = $params;
    }

    public static function getInstance($params = null, $folder = 'content', $plugin = 'jllike')
    {
        if (self::$instance === null) {
            if (!$params) {
                $params = self::getPluginParams($folder, $plugin);
            }
            self::$instance = new PlgJLLikeHelper($params);
        }

        return self::$instance;
    }

    /**
     * Кнопки шары
     * @param $id не нужный параметр, на будущее
     * @return string
     */
    function ShowIn($id, $link='', $title='', $image='', $desc='', $enable_opengraph=1)
    {
        JPluginHelper::importPlugin('content', 'jllike');

        $position_content = $this->params->get('position_content', 0);

        if ($position_content == 1)
        {
            $position_buttons = '_right';
        }
        else if
        ($position_content == 0)
        {
            $position_buttons = '_left';
        }
        else if
        ($position_content == 2)
        {
            $position_buttons = '_center';
        }
        else
        {
            $position_buttons = '';
        }

        if(empty($image))
        {
            $image = trim($this->params->get('default_image', ''));
            $image = !empty($image) ? JUri::root() . $image : '';
        }

        $desc = $this->cleanText($desc);
        $desc = $this->limittext($desc, 200);
        $title = $this->cleanText($title);

        if($enable_opengraph)
        {
            $this->addOpenGraphTags($title, $desc, $image, $link);
        }
        $donatelink = JText::_('PLG_JLLIKEPRO_DONATE_LINK');
        $titlefc = JText::_('PLG_JLLIKEPRO_TITLE_FC');
        $titlevk = JText::_('PLG_JLLIKEPRO_TITLE_VK');
        $titletw = JText::_('PLG_JLLIKEPRO_TITLE_TW');
        $titleod = JText::_('PLG_JLLIKEPRO_TITLE_OD');
        $titlegg = JText::_('PLG_JLLIKEPRO_TITLE_GG');
        $titlemm = JText::_('PLG_JLLIKEPRO_TITLE_MM');
        $titleli = JText::_('PLG_JLLIKEPRO_TITLE_LI');
        $titlepi = JText::_('PLG_JLLIKEPRO_TITLE_PI');
        $titlelj = JText::_('PLG_JLLIKEPRO_TITLE_LJ');
		$titlebl = JText::_('PLG_JLLIKEPRO_TITLE_BL');
		$titlewb = JText::_('PLG_JLLIKEPRO_TITLE_WB');
        $titleAll = JText::_('PLG_JLLIKEPRO_TITLE_ALL');

        $providers = array();
        if ($this->params->get('addfacebook', 1)) {
            $order = $this->params->get('facebook_order', 1);
            $providers[$order] = array('title' => $titlefc, 'class' => 'fb');
        }
        if ($this->params->get('addvk', 1)) {
            $order = $this->params->get('vk_order', 2);
            $providers[$order] = array('title' => $titlevk, 'class' => 'vk');
        }
        if ($this->params->get('addtw', 1)) {
            $order = $this->params->get('tw_order', 3);
            $providers[$order] = array('title' => $titletw, 'class' => 'tw');
        }
        if ($this->params->get('addod', 1)) {
            $order = $this->params->get('od_order', 4);
            $providers[$order] = array('title' => $titleod, 'class' => 'ok');
        }
        if ($this->params->get('addgp', 1)) {
            $order = $this->params->get('gp_order', 5);
            $providers[$order] = array('title' => $titlegg, 'class' => 'gp');
        }
        if ($this->params->get('addmail', 1)) {
            $order = $this->params->get('mail_order', 6);
            $providers[$order] = array('title' => $titlemm, 'class' => 'ml');
        }
        if ($this->params->get('addlin', 1)) {
            $order = $this->params->get('lin_order', 7);
            $providers[$order] = array('title' => $titleli, 'class' => 'ln');
        }
        if ($this->params->get('addpi', 1)) {
            $order = $this->params->get('pi_order', 8);
            $providers[$order] = array('title' => $titlepi, 'class' => 'pinteres');
        }
        if ($this->params->get('addlj', 1)) {
            $order = $this->params->get('lj_order', 9);
            $providers[$order] = array('title' => $titlelj, 'class' => 'lj');
        }
		if ($this->params->get('addbl', 1)) {
            $order = $this->params->get('bl_order', 10);
            $providers[$order] = array('title' => $titlebl, 'class' => 'bl');
        }		
				if ($this->params->get('addwb', 1)) {
            $order = $this->params->get('wb_order', 11);
            $providers[$order] = array('title' => $titlewb, 'class' => 'wb');
        }		

        ksort($providers);
        reset($providers);

        $scriptPage = '';
        $scriptPage .= <<<HTML
				<div class="jllikeproSharesContayner jllikepro_{$id}">
				<input type="hidden" class="link-to-share" id="link-to-share-$id" value="$link"/>
				<input type="hidden" class="share-title" id="share-title-$id" value="$title"/>
				<input type="hidden" class="share-image" id="share-image-$id" value="$image"/>
				<input type="hidden" class="share-desc" id="share-desc-$id" value="$desc"/>
				<input type="hidden" class="share-id" value="{$id}"/>
HTML;

        if($this->params->get('disable_more_likes', 0) && !empty($_COOKIE['jllikepro_article_'.$id])){
            $scriptPage .= '<div class="disable_more_likes"></div>';
        }

        $buttonText = StringHelper1::trim($this->params->get('button_text', ''));

        if(!empty($buttonText)){
            $scriptPage .= '<div class="button_text likes-block'.$position_buttons.'">'.$buttonText.'</div>';
        }

        $scriptPage .= <<<HTML

				<div class="event-container" >
				<div class="likes-block$position_buttons">
HTML;

        foreach($providers as $v)
        {
            $scriptPage .= <<<HTML
					<a title="{$v['title']}" class="like l-{$v['class']}" id="l-{$v['class']}-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }

		if ($this->params->get('addall', 1)) {
        $scriptPage .= <<<HTML
					<a title="$titleAll" class="l-all" id="l-all-$id">
					<i class="l-ico"></i>
					<span class="l-count l-all-count" id="l-all-count-$id">0</span>
					</a>
HTML;
		}
        $scriptPage .= <<<HTML
					</div>
				</div>
			</div>
            <div class="likes-block$position_buttons">
			$donatelink
			</div>
HTML;

        return $scriptPage;
    }



    /**
     * Загрузка скриптов и стилей
     * @param $articleText
     */
    function loadScriptAndStyle($isCategory=1)
    {
        if(defined('JLLIKEPRO_SCRIPT_LOADED'))
            return;

        define('JLLIKEPRO_SCRIPT_LOADED', 1);

        $doc = JFactory::getDocument();

        $isCategory = (int)$isCategory;

        $prefix = (JFactory::getConfig()->get('force_ssl') == 2) ? 'https://' : 'http://';
        $url = $prefix . $this->params->get('pathbase', '') . str_replace('www.', '', $_SERVER['HTTP_HOST']);

        $script = <<<SCRIPT
            var jllickeproSettings = {
                url : "$url",
                typeGet : "{$this->params->get('typesget', 0)}",
                disableMoreLikes : {$this->params->get('disable_more_likes', 0)},
                isCategory : $isCategory,
                buttonsContayner : "{$this->params->get('buttons_contayner', '')}",
                parentContayner : "{$this->params->get('parent_contayner', 'div.jllikeproSharesContayner')}",
            };
SCRIPT;

        $doc->addScriptDeclaration($script);

			JHtml::_('jquery.framework');		
			 
			$doc->addScript(JURI::base() . "plugins/content/jllike/js/buttons.min.js?8");
	
            if($this->params->get('enable_twit',0))
            {
                $doc->addScript(JURI::base() . "plugins/content/jllike/js/twit.js");
            }

       
        $doc->addStyleSheet(JURI::base() . "plugins/content/jllike/js/buttons.min.css?4");

        $btn_border_radius = (int)$this->params->get('btn_border_radius',15);
        $btn_dimensions = (int)$this->params->get('btn_dimensions',30);
        $btn_margin = (int)$this->params->get('btn_margin',6);
        $font_size = (float)$this->params->get('font_size',1);
        $doc->addStyleDeclaration('
            .jllikeproSharesContayner a {border-radius: '.$btn_border_radius.'px; margin-left: '.$btn_margin.'px;}
            .jllikeproSharesContayner i {width: '.$btn_dimensions.'px;height: '.$btn_dimensions.'px;}
            .jllikeproSharesContayner span {height: '.$btn_dimensions.'px;line-height: '.$btn_dimensions.'px;font-size: '.$font_size.'rem;}
        ');

        if(!$isCategory && $this->params->get('enable_mobile_css',1) == 1){
            $doc->addStyleDeclaration('
            @media screen and (max-width:800px) {
                .jllikeproSharesContayner {position: fixed;right: 0;bottom: 0; z-index: 999999; background-color: #fff!important;width: 100%;}
                .jllikeproSharesContayner .event-container > div {border-radius: 0; padding: 0; display: block;}
                .like .l-count {display:none}
                .jllikeproSharesContayner a {border-radius: 0!important;margin: 0!important;}
                .l-all-count {margin-left: 10px; margin-right: 10px;}
                .jllikeproSharesContayner i {width: 44px!important; border-radius: 0!important;}
                .l-ico {background-position: 50%!important}
                .likes-block_left {text-align:left;}
                .likes-block_right {text-align:right;}
                .likes-block_center {text-align:center;}
                .button_text {display: none;}
            }
            ');
        }
    }

    function getShareText($metadesc, $introtext, $text)
    {
        $desc_source_one = $this->params->get('desc_source_one', 'desc');
        $desc_source_two = $this->params->get('desc_source_two', 'full');
        $desc_source_three = $this->params->get('desc_source_three', 'meta');

        switch($desc_source_one)
        {
            case 'full':
                $source_one = $text;
                break;
            case 'meta':
                $source_one = $metadesc;
                break;
            default:
                $source_one = $introtext;
                break;
        }

        switch($desc_source_two)
        {
            case 'desc':
                $source_two = $introtext;
                break;
            case 'meta':
                $source_two = $metadesc;
                break;
            default:
                $source_two = $text;
                break;
        }

        switch($desc_source_three)
        {
            case 'desc':
                $source_three = $introtext;
                break;
            case 'full':
                $source_three = $text;
                break;
            default:
                $source_three = $metadesc;
                break;
        }

        $source_one = trim($source_one);
        $source_two = trim($source_two);
        $source_three = trim($source_three);

        $desc = '';

        if(!empty($source_one))
        {
            $desc = $source_one;
        }
        else if(!empty($source_two))
        {
            $desc = $source_two;
        }
        else if(!empty($source_three))
        {
            $desc = $source_three;
        }

        return $desc;
    }

    private function cleanText($text)
    {
        $clear_plugin_tags = $this->params->get('clear_plugin_tags', 1);
        $text = strip_tags($text);
        $text = preg_replace('/&nbsp;/', ' ', $text);
        $text = str_replace("\n", ' ', $text);

        if($clear_plugin_tags)
        {
            $text = preg_replace('/{.+?}/', '', $text);
        }

        $text = htmlspecialchars($text, ENT_QUOTES);
        $text = preg_replace('/&amp;amp;/', '&amp;', $text);

        return $text;
    }

    private static function getPluginParams($folder = 'content', $name = 'jllike')
    {
        $plugin = JPluginHelper::getPlugin($folder, $name);
        if (!$plugin) {
            throw new RuntimeException(JText::_('JLLIKEPRO_PLUGIN_NOT_FOUND'));
        }
        $params = new JRegistry($plugin->params);
        return $params;
    }

    public static function extractImageFromText( $introtext, $fulltext = '' )
    {
        jimport('joomla.filesystem.file');

        $regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';

        preg_match ($regex, $introtext, $matches);

        if(!count($matches))
        {
            preg_match ($regex, $fulltext, $matches);
        }

        $images = (count($matches)) ? $matches : array();

        $image = '';

        if (count($images))
        {
            $image = $images[2];
        }

        if(!empty($image))
        {
        if (!preg_match("#^http|^https|^ftp#i", $image))
        {
            $image = JFile::exists( JPATH_SITE . '/' . $image ) ? $image : '';

            if(strpos($image, '/') === 0)
            {
                $image = substr($image, 1);
            }

            $image = JURI::root().$image;

            }
        }
        else
        {
            $image = '';
        }

        return $image;
    }

    private function limittext($wordtext, $maxchar)
    {
        $text = '';
        $textLength = StringHelper1::strlen($wordtext);

        if($textLength <= $maxchar)
        {
            return $wordtext;
        }

        $words = explode(' ', $wordtext);

        foreach ($words as $word)
        {
            if(StringHelper1::strlen($text . ' ' . $word) > $maxchar - 1)
            {
                break;
            }
            $text .= ' ' . $word;
        }

        return $text;
    }

    private function addOpenGraphTags($title='', $text='', $image='', $url='')
    {
        $doc = JFactory::getDocument();

        $doc->setMetaData('og:type', 'article');

        if($image){
            $doc->setMetaData('og:image', $image);
            JFactory::getApplication()->setUserState('jllike.image', $image);
        }

        if($title)
            $doc->setMetaData('og:title', $title);
        if($text)
            $doc->setMetaData('og:description', $text);
        if($url)
            $doc->setMetaData('og:url', $url);
    }
    
}
