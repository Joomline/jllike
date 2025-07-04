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
use Joomla\CMS\Application\CMSApplication;
use Joomla\Plugin\Content\Jllike\Helper\PlgJLLikeHelper;

require_once JPATH_ROOT . '/plugins/content/jllike/helper.php';

class PlgContentJllike extends CMSPlugin
{
    private $protokol;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
        $this->protokol = (Factory::getConfig()->get('force_ssl') == 2) ? 'https://' : 'http://';
    }

    public function onAfterRender()
    {
        $app = Factory::getApplication();
        $buffer = $app->getBody();
        if($buffer !== null)
        {
            $image = $app->getUserState('jllike.image', '');
            if(!empty($image))
            {
                $app->setUserState('jllike.image', '');
                $html = "  <link rel=\"image_src\" href=\"". $image ."\" />\n</head>";
                $count = 1;
                $buffer = str_ireplace('</head>', $html, $buffer, $count);
            }
            $buffer = str_ireplace('<meta name="og:', '<meta property="og:', $buffer);
            $app->setBody($buffer);
        }
    }

    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        if(Factory::getApplication()->isClient('administrator'))
        {
            return true;
        }

        $input = Factory::getApplication()->input;
        $allowContext = array(
            'com_content.article',
            'easyblog.blog',
            'com_virtuemart.productdetails'
        );
        $allow_in_category = $this->params->get('allow_in_category', 0);
        if($allow_in_category)
        {
            $allowContext[] = 'com_content.category';
            $allowContext[] = 'com_content.featured';
        }
        if(!in_array($context, $allowContext)){
            return true;
        }
        if (strpos($article->text, '{jllike-off}') !== false) {
            $article->text = str_replace("{jllike-off}", "", $article->text);
            return true;
        }
        $autoAdd = $this->params->get('autoAdd',0);
        $sharePos = (int)$this->params->get('shares_position', 1);
        $enableOpenGraph = $this->params->get('enable_opengraph',1);
        $option = $input->get('option');
        $helper = \PlgJLLikeHelper::getInstance($this->params);
        if (strpos($article->text, '{jllike}') === false && !$autoAdd)
        {
            return true;
        }
        if (!isset($article->catid))
        {
            $article->catid = '';
        }
        $print = (int) $input->get('print', 0);
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
                    $idn = new \idna_convert;
                    $url = $idn->encode($url);
                }
            }
            $baseUri->setHost(parse_url($url, PHP_URL_HOST));
        }
        switch ($option) {
            case 'com_content':
                if(empty($article->id))
                {
                    return true;
                }
                if($print)
                {
                    $article->text = str_replace("{jllike}", "", $article->text);
                    return true;
                }
                $cat = $this->params->get('categories', array());
                $exceptcat = is_array($cat) ? $cat : array($cat);
                if (in_array($article->catid, $exceptcat))
                {
                    $article->text = str_replace("{jllike}", "", $article->text);
                    return true;
                }
                // Универсальный вызов маршрутизатора для разных версий Joomla
                if (class_exists('Joomla\\Component\\Content\\Site\\Helper\\RouteHelper')) {
                    $route = Route::_(\Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute($article->slug, $article->catid));
                } else {
                    $route = Route::_(\ContentHelperRoute::getArticleRoute($article->slug, $article->catid));
                }
                $link = rtrim(Uri::root(), '/') . '/' . ltrim($route, '/');
                $image = '';
                if($this->params->get('content_images', 'fields') == 'fields')
                {
                    if(!empty($article->images))
                    {
                        $images = json_decode($article->images);
                        if(!empty($images->image_intro))
                        {
                            $image = $images->image_intro;
                        }
                        else if(!empty($images->image_fulltext))
                        {
                            $image = $images->image_fulltext;
                        }
                        if(!empty($image))
                        {
                            $image = Uri::root().$image;
                        }
                    }
                }
                else
                {
                    $image = \PlgJLLikeHelper::extractImageFromText($article->introtext, $article->fulltext);
                }
                $text = $helper->getShareText($article->metadesc, $article->introtext, $article->text);
                $enableOG = $context == 'com_content.article' ? $enableOpenGraph : 0;
                $shares = $helper->ShowIN($article->id, $link, $article->title, $image, $text, $enableOG);
                if ($context == 'com_content.article')
                {
                    $view = $input->get('view');
                    if ($view == 'article')
                    {
                        if ($autoAdd == 1 || strpos($article->text, '{jllike}') == true)
                        {
                            $helper->loadScriptAndStyle(0);
                            switch($sharePos)
                            {
                                case 0:
                                    $article->text = $shares . str_replace("{jllike}", "", $article->text);
                                    break;
                                default:
                                    $article->text = str_replace("{jllike}", "", $article->text) . $shares;
                                    break;
                            }
                        }
                    }
                }
                else if ($context == 'com_content.category' || 'com_content.featured')
                {
                    if ($autoAdd == 1 || strpos($article->text, '{jllike}') == true)
                    {
                        $helper->loadScriptAndStyle(1);
                        $article->text = str_replace("{jllike}", "", $article->text) . $shares;
                    }
                }
                break;
            case 'com_virtuemart':
                if ($context == 'com_virtuemart.productdetails') {
                    $VirtueShow = $this->params->get('virtcontent', 1);
                    if ($VirtueShow == 1)
                    {
                        $autoAddvm = $this->params->get('autoAddvm', 0);
                        if ($autoAddvm == 1 || strpos($article->text, '{jllike}') !== false)
                        {
                            $helper->loadScriptAndStyle(0);
                            $uri = str_ireplace(Uri::root(), '', Uri::current());
                            $link = rtrim(Uri::root(), '/') . '/' . ltrim($uri, '/');
                            $image = $helper->getVMImage($article->virtuemart_product_id);
                            $text = $helper->getShareText($article->metadesc, $article->product_s_desc, $article->product_desc);
                            $shares = $helper->ShowIN($article->virtuemart_product_id, $link, $article->product_name, $image, $text, $enableOpenGraph);
                            switch($sharePos){
                                case 0:
                                    $article->text = $shares . str_replace("{jllike}", "", $article->text);
                                    break;
                                default:
                                    $article->text = str_replace("{jllike}", "", $article->text) . $shares;
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'com_easyblog':
                if (($context == 'easyblog.blog') && ($this->params->get('easyblogshow', 0) == 1))
                {
                    $allow_in_category = $this->params->get('allow_in_category', 0);
                    $isCategory = ($input->get('view', '') == 'entry') ? false : true;
                    if(!$allow_in_category && $isCategory)
                    {
                        return true;
                    }
                    if ($autoAdd == 1 || strpos($article->text, '{jllike}') == true)
                    {
                        $helper->loadScriptAndStyle(0);
                        $uri = str_ireplace(Uri::root(), '', Uri::current());
                        $baseUri->setPath(ltrim($uri, '/'));
                        $link = $baseUri->toString();
                        $image = '';
                        if($this->params->get('easyblog_images','fields') == 'fields'){
                            $images = json_decode($article->image);
                            if(isset($images->type) && $images->type == 'image')
                            {
                                $image = $images->url;
                            }
                        }
                        else
                        {
                            $image = \PlgJLLikeHelper::extractImageFromText($article->intro, $article->content);
                        }
                        $enableOG = $isCategory ? 0 : $this->params->get('easyblog_add_opengraph', 0);
                        $text = $helper->getShareText($article->metadesc, $article->intro, $article->content);
                        $shares = $helper->ShowIN($article->id, $link, $article->title, $image, $text, $enableOG);
                        switch($sharePos){
                            case 0:
                                $article->text = $shares . str_replace("{jllike}", "", $article->text);
                                break;
                            default:
                                $article->text = str_replace("{jllike}", "", $article->text) . $shares;
                                break;
                        }
                    }
                }
                break;
            default:
                break;
        }
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
