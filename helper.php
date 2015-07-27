<?php
/**
 * jllikepro
 *
 * @version 2.1.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2010-2015 by Vadim Kunicin (http://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class PlgJLLikeHelper
{
    var $params = null;

    protected static $instance = null;

    function ShowIn($id, $link='', $title='', $image='')
    {
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
        else
        {
            $position_buttons = '';
        }

        $titlefc = JText::_('PLG_JLLIKEPRO_TITLE_FC');
        $titlevk = JText::_('PLG_JLLIKEPRO_TITLE_VK');
        $titletw = JText::_('PLG_JLLIKEPRO_TITLE_TW');
        $titleod = JText::_('PLG_JLLIKEPRO_TITLE_OD');
        $titlegg = JText::_('PLG_JLLIKEPRO_TITLE_GG');
        $titlemm = JText::_('PLG_JLLIKEPRO_TITLE_MM');
        $titleli = JText::_('PLG_JLLIKEPRO_TITLE_LI');
        $titleya = JText::_('PLG_JLLIKEPRO_TITLE_YA');
        $titlepi = JText::_('PLG_JLLIKEPRO_TITLE_PI');
        $scriptPage = '';
        $scriptPage .= <<<HTML
				<div class="jllikeproSharesContayner jllikepro_{$id}">
				<input type="hidden" class="link-to-share" id="link-to-share-$id" value="$link"/>
				<input type="hidden" class="share-title" id="share-title-$id" value="$title"/>
				<input type="hidden" class="share-image" id="share-image-$id" value="$image"/>
				<input type="hidden" class="share-id" value="{$id}"/>
HTML;

        if($this->params->get('disable_more_likes', 0) && !empty($_COOKIE['jllikepro_article_'.$id])){
            $scriptPage .= '<div class="disable_more_likes"></div>';
        }

        $buttonText = JString::trim($this->params->get('button_text', ''));

        if(!empty($buttonText)){
            $scriptPage .= '<div class="button_text">'.$buttonText.'</div>';
        }

        $scriptPage .= <<<HTML

				<div class="event-container" >
				<div class="likes-block$position_buttons">
HTML;
        if ($this->params->get('addfacebook', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titlefc" class="like l-fb" id="l-fb-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }
        if ($this->params->get('addvk', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titlevk" class="like l-vk" id="l-vk-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }
        if ($this->params->get('addtw', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titletw" class="like l-tw" id="l-tw-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }
        if ($this->params->get('addod', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titleod" class="like l-ok" id="l-ok-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }
        if ($this->params->get('addgp', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titlegg" class="like l-gp" id="l-gp-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }
        if ($this->params->get('addmail', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titlemm" class="like l-ml" id="l-ml-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }

        if ($this->params->get('addlin', 0)) {
            $scriptPage .= <<<HTML
					<a title="$titleli" class="like l-ln" id="l-ln-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }

        if ($this->params->get('addpi', 1)) {
            $scriptPage .= <<<HTML
					<a title="$titlepi" class="like l-pinteres" id="l-pinteres-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }

        $scriptPage .= <<<HTML
						<div>
							<a style="text-decoration:none; color: #c0c0c0; font-family: arial,helvetica,sans-serif; font-size: 5pt; " target="_blank" href="http://joomline.ru/rasshirenija/plugin/jllike.html">Social Like</a>
						</div>
					</div>
				</div>
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

        if(version_compare(JVERSION, '3.0', 'ge'))
        {
            JHtml::_('jquery.framework');
			$doc->addScript(JURI::base() . "plugins/content/jllike/js/buttons.js?5");

        }
        else if ($this->params->get('load_libs',0) == 0)
        {
            if ($this->params->get('jqload',0) == 1)
            {
                $doc->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js");
            }
            else if ($this->params->get('jqloadcont',0) == 1)
            {
                $doc->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js");
            }
            $doc->addScript(JURI::base() . "plugins/content/jllike/js/buttons.js?5");
      
        }
        else
        {
            if ($this->params->get('jqload',0) == 1)
            {
                $doc->addCustomTag('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>');
            }
            else if ($this->params->get('jqloadcont',0) == 1)
            {
                $doc->addCustomTag('<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>');
            }
            $doc->addCustomTag('<script src="' . JURI::base() . 'plugins/content/jllike/js/buttons.js?5"></script>');
            
        }

        $doc->addStyleSheet(JURI::base() . "plugins/content/jllike/js/buttons.css");
    }


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

        if (!preg_match("#^http|^https|^ftp#i", $image))
        {
            $image = JFile::exists( JPATH_SITE . DS . $image ) ? $image : '';

            if(strpos($image, '/') === 0)
            {
                $image = substr($image, 1);
            }

            $image = JURI::root().$image;
        }

        return $image;
    }

    public static function addOpenGraphTags($title='', $text='', $image='')
    {
        $doc = JFactory::getDocument();

        $desc = JString::substr(strip_tags($text),0,200);
        $desc = str_replace(array('"', "'"), '', $desc);

        $doc->setMetaData('og:type', 'article');

        if($image)
            $doc->setMetaData('og:image', $image);
        if($title)
            $doc->setMetaData('og:title', $title);
        if($desc)
            $doc->setMetaData('og:description', $desc);
    }

    
}