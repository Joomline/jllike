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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class PlgJLLikeHelper
{
    protected $params = null;

    protected static $instance = null;

    /**
     * Пример вывода лайков в любом месте макетов, шаблонов и т.п.
     * require_once JPATH_ROOT .'plugins/content/jllike/helper.php';
     * $helper = PlgJLLikeHelper::getInstance();
     * $helper->loadScriptAndStyle(0); //1-если в категории, 0-если в контенте
     * echo $helper->ShowIN($id, $link, $title, $image, $desc, $enable_opengraph);
     */


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
    function ShowIn($id, $link = '', $title = '', $image = '', $desc = '', $enable_opengraph = 1)
    {
        PluginHelper::importPlugin('content', 'jllike');

        $position_content = $this->params->get('position_content', 0);
        $enableCounters = (int) $this->params->get('enableCounters', 1);

        if ($position_content == 1) {
            $position_buttons = '_right';
        } else if ($position_content == 0) {
            $position_buttons = '_left';
        } else if ($position_content == 2) {
            $position_buttons = '_center';
        } else {
            $position_buttons = '';
        }

        if (empty($image)) {
            $image = trim($this->params->get('default_image', ''));
            $image = !empty($image) ? Uri::root() . $image : '';
        }

        $desc = $this->cleanText($desc);
        $desc = $this->limittext($desc, 200);
        $title = $this->cleanText($title);

        if ($enable_opengraph) {
            $this->addOpenGraphTags($title, $desc, $image, $link);
        }
        
        $titlefc = Text::_('PLG_JLLIKEPRO_TITLE_FC');
        $titlevk = Text::_('PLG_JLLIKEPRO_TITLE_VK');
        $titletw = Text::_('PLG_JLLIKEPRO_TITLE_TW');
        $titleod = Text::_('PLG_JLLIKEPRO_TITLE_OD');
        $titlemm = Text::_('PLG_JLLIKEPRO_TITLE_MM');
        $titleli = Text::_('PLG_JLLIKEPRO_TITLE_LI');
        $titlepi = Text::_('PLG_JLLIKEPRO_TITLE_PI');
        $titlelj = Text::_('PLG_JLLIKEPRO_TITLE_LJ');
        $titlebl = Text::_('PLG_JLLIKEPRO_TITLE_BL');
        $titlewb = Text::_('PLG_JLLIKEPRO_TITLE_WB');
        $titletl = Text::_('PLG_JLLIKEPRO_TITLE_TL');
        $titlewa = Text::_('PLG_JLLIKEPRO_TITLE_WA');
        $titlevi = Text::_('PLG_JLLIKEPRO_TITLE_VI');
        $titleth = Text::_('PLG_JLLIKEPRO_TITLE_TH');
        $titlerd = Text::_('PLG_JLLIKEPRO_TITLE_RD');
        $titleAll = Text::_('PLG_JLLIKEPRO_TITLE_ALL');

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
        if ($this->params->get('addmail', 1)) {
            $order = $this->params->get('mail_order', 5);
            $providers[$order] = array('title' => $titlemm, 'class' => 'ml');
        }
        if ($this->params->get('addlin', 1)) {
            $order = $this->params->get('lin_order', 6);
            $providers[$order] = array('title' => $titleli, 'class' => 'ln');
        }
        if ($this->params->get('addpi', 1)) {
            $order = $this->params->get('pi_order', 7);
            $providers[$order] = array('title' => $titlepi, 'class' => 'pinteres');
        }
        if ($this->params->get('addlj', 1)) {
            $order = $this->params->get('lj_order', 8);
            $providers[$order] = array('title' => $titlelj, 'class' => 'lj');
        }
        if ($this->params->get('addbl', 1)) {
            $order = $this->params->get('bl_order', 9);
            $providers[$order] = array('title' => $titlebl, 'class' => 'bl');
        }
        if ($this->params->get('addwb', 1)) {
            $order = $this->params->get('wb_order', 10);
            $providers[$order] = array('title' => $titlewb, 'class' => 'wb');
        }
        if ($this->params->get('addtl', 1)) {
            $order = $this->params->get('tl_order', 11);
            $providers[$order] = array('title' => $titletl, 'class' => 'tl');
        }
        if ($this->params->get('addwa', 1)) {
            $order = $this->params->get('wa_order', 12);
            $providers[$order] = array('title' => $titlewa, 'class' => 'wa');
        }
        if ($this->params->get('addvi', 1)) {
            $order = $this->params->get('vi_order', 13);
            $providers[$order] = array('title' => $titlevi, 'class' => 'vi');
        }
        if ($this->params->get('addth', 1)) {
            $order = $this->params->get('th_order', 16);
            $providers[$order] = array('title' => $titleth, 'class' => 'th');
        }
        if ($this->params->get('addrd', 1)) {
            $order = $this->params->get('rd_order', 17);
            $providers[$order] = array('title' => $titlerd, 'class' => 'rd');
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

        if ($this->params->get('disable_more_likes', 0) && Factory::getApplication()->input->cookie->get('jllikepro_article_' . $id)) {
            $scriptPage .= '<div class="disable_more_likes"></div>';
        }

        $buttonText = trim($this->params->get('button_text', ''));

        if (!empty($buttonText)) {
            $scriptPage .= '<div class="button_text likes-block' . $position_buttons . '">' . $buttonText . '</div>';
        }

        $scriptPage .= <<<HTML

				<div class="event-container" >
				<div class="likes-block$position_buttons">
HTML;

        // Collapse buttons logic
        $collapseButtons = (int) $this->params->get('collapse_buttons', 0);
        $visibleCount = (int) $this->params->get('visible_buttons_count', 5);
        $moreButtonText = $this->params->get('more_button_text', '...');

        $providerIndex = 0;
        $totalProviders = count($providers);

        foreach ($providers as $v) {
            $providerIndex++;
            $hiddenClass = '';

            if ($collapseButtons && $providerIndex > $visibleCount && $totalProviders > $visibleCount) {
                $hiddenClass = ' jllike-hidden';
            }

            $scriptPage .= <<<HTML
					<a title="{$v['title']}" class="like l-{$v['class']}$hiddenClass" id="l-{$v['class']}-$id">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
        }

        // Add "More" button if collapse is enabled and there are hidden buttons
        if ($collapseButtons && $totalProviders > $visibleCount) {
            $scriptPage .= <<<HTML
					<a class="jllike-more-button" id="jllike-more-$id">
					<span>$moreButtonText</span>
					</a>
HTML;
        }

        if ($this->params->get('addall', 1) && $enableCounters) {
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
HTML;

        return $scriptPage;
    }



    /**
     * Загрузка скриптов и стилей
     * @param $articleText
     */
    function loadScriptAndStyle($isCategory = 1)
    {
        if (defined('JLLIKEPRO_SCRIPT_LOADED'))
            return;

        define('JLLIKEPRO_SCRIPT_LOADED', 1);

        $doc = Factory::getDocument();

        $isCategory = (int) $isCategory;

        $baseUri = $this->getBaseUri();
        $url = $baseUri->toString();

        $enableCounters = (int) $this->params->get('enableCounters', 1);

        $script = '
            window.jllickeproSettings = window.jllickeproSettings || {};
            jllickeproSettings.url = "' . $url . '";
            jllickeproSettings.typeGet = "' . $this->params->get('typesget', 0) . '";
            jllickeproSettings.enableCounters = ' . ($enableCounters ? 'true' : 'false') . ';
            jllickeproSettings.disableMoreLikes = ' . $this->params->get('disable_more_likes', 0) . ';
            jllickeproSettings.isCategory = ' . $isCategory . ';
            jllickeproSettings.buttonsContayner = "' . $this->params->get('buttons_contayner', '') . '";
            jllickeproSettings.parentContayner = "' . $this->params->get('parent_contayner', 'div.jllikeproSharesContayner') . '";
        ';

        $doc->addScriptDeclaration($script);

        
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript('plg_jllike.buttons.script', 'plugins/content/jllike/js/buttons.js', [], ['defer' => true]);
        $wa->registerAndUseStyle('plg_jllike.buttons', 'plugins/content/jllike/js/buttons.css');

        $btn_border_radius = (int) $this->params->get('btn_border_radius', 15);
        $btn_dimensions = (int) $this->params->get('btn_dimensions', 30);
        $btn_margin = (int) $this->params->get('btn_margin', 6);
        $font_size = (float) $this->params->get('font_size', 1);
        $doc->addStyleDeclaration('
            .jllikeproSharesContayner a {border-radius: ' . $btn_border_radius . 'px; margin-left: ' . $btn_margin . 'px;}
            .jllikeproSharesContayner i {width: ' . $btn_dimensions . 'px;height: ' . $btn_dimensions . 'px;}
            .jllikeproSharesContayner span {height: ' . $btn_dimensions . 'px;line-height: ' . $btn_dimensions . 'px;font-size: ' . $font_size . 'rem;}
        ');

        if (!$isCategory && $this->params->get('enable_fix_buttons', 1) == 1) {
            $doc->addStyleDeclaration('
                .jllikeproSharesContayner {position: fixed; left: 0; top: auto;}
                .jllikeproSharesContayner .event-container>div {display: flex; flex-direction: column;}
            ');
        }

        if (!$isCategory && $this->params->get('enable_mobile_css', 1) == 1) {
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

        // Передаем настройки в JS
        $params = [
            'enableCounters' => (bool)$this->params->get('enableCounters', 1),
            'random_likes' => (bool)$this->params->get('random_likes', 1),
            // ... возможно, другие параметры ...
        ];
        $js = 'window.jllickeproSettings = Object.assign(window.jllickeproSettings || {}, ' . json_encode($params) . ');';
        Factory::getApplication()->getDocument()->addScriptDeclaration($js);
    }

    function getShareText($metadesc, $introtext, $text)
    {
        $desc_source_one = $this->params->get('desc_source_one', 'desc');
        $desc_source_two = $this->params->get('desc_source_two', 'full');
        $desc_source_three = $this->params->get('desc_source_three', 'meta');

        switch ($desc_source_one) {
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

        switch ($desc_source_two) {
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

        switch ($desc_source_three) {
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

        if (!empty($source_one)) {
            $desc = $source_one;
        } else if (!empty($source_two)) {
            $desc = $source_two;
        } else if (!empty($source_three)) {
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

        if ($clear_plugin_tags) {
            $text = preg_replace('/\[.+?\]/', '', $text);
            $text = preg_replace('/{.+?}/', '', $text);
        }

        $text = htmlspecialchars($text, ENT_QUOTES);
        $text = preg_replace('/&amp;amp;/', '&amp;', $text);

        return $text;
    }

    private static function getPluginParams($folder = 'content', $name = 'jllike')
    {
        $plugin = PluginHelper::getPlugin($folder, $name);
        if (!$plugin) {
            throw new \RuntimeException(Text::_('JLLIKEPRO_PLUGIN_NOT_FOUND'));
        }
        $params = new Registry($plugin->params);
        return $params;
    }

    public static function extractImageFromText($introtext, $fulltext = '')
    {
        $regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';

        preg_match($regex, $introtext, $matches);

        if (!count($matches)) {
            preg_match($regex, $fulltext, $matches);
        }

        $images = (count($matches)) ? $matches : array();

        $image = '';

        if (count($images)) {
            $image = $images[2];
        }

        if (!empty($image)) {
            if (!preg_match("#^http|^https|^ftp#i", $image)) {
                if (!File::exists(JPATH_SITE . '/' . $image)) {
                    $image = '';
                }

                if (strpos($image, '/') === 0) {
                    $image = substr($image, 1);
                }

                $image = Uri::root() . $image;
            }
        } else {
            $image = '';
        }

        return $image;
    }

    private function limittext($wordtext, $maxchar)
    {
        $text = '';
        $textLength = mb_strlen($wordtext);

        if ($textLength <= $maxchar) {
            return $wordtext;
        }

        $words = explode(' ', $wordtext);

        foreach ($words as $word) {
            if (mb_strlen($text . ' ' . $word) > $maxchar - 1) {
                break;
            }
            $text .= ' ' . $word;
        }

        return $text;
    }

    private function addOpenGraphTags($title = '', $text = '', $image = '', $url = '')
    {
        $doc = Factory::getDocument();

        $doc->setMetaData('og:type', 'article');

        if ($image) {
            $doc->setMetaData('og:image', $image);
            Factory::getApplication()->setUserState('jllike.image', $image);
            // Twitter large card
            $doc->setMetaData('twitter:card', 'summary_large_image');
            $doc->setMetaData('twitter:image', $image);
        } else {
            // Если нет картинки, пусть будет обычная карточка
            $doc->setMetaData('twitter:card', 'summary');
        }

        if ($title) {
            $doc->setMetaData('og:title', $title);
            $doc->setMetaData('twitter:title', $title);
        }
        if ($text) {
            $desc = preg_replace('/\s+/u', ' ', $text);
            $doc->setMetaData('og:description', $desc);
            $doc->setMetaData('twitter:description', $desc);
        }
        if ($url) {
            $doc->setMetaData('og:url', $url);
        }
    }

    public function getVMImage($id)
    {
        $db = Factory::getDbo();
        $image = '';
        $query = $db->getQuery(true);
        $query->select('`file_url`')
            ->from('#__virtuemart_medias as m')
            ->from('#__virtuemart_product_medias as pm')
            ->where('pm.virtuemart_product_id = ' . (int) $id)
            ->where('pm.virtuemart_media_id = m.virtuemart_media_id')
            ->order('pm.ordering ASC')
            ->setLimit(1, 0);
        $db->setQuery($query);
        $res = $db->loadResult();

        if ($res) {
            $baseUri = $this->getBaseUri();
            $baseUri->setPath(ltrim($res, '/'));
            $image = $baseUri->toString();
        }
        return $image;
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
