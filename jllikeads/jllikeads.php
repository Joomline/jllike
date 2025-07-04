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

class PlgAdsmanagercontentJlLikeAds extends CMSPlugin
{
    public function ADSonContentAfterDisplay($content)
    {
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
        $baseUri = $this->getBaseUri($plgParams);
        $url = $baseUri->toString();
        if($plgParams->get('punycode_convert',0))
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
        $uri = str_ireplace(Uri::root(), '', Uri::current());
        $baseUri->setPath(ltrim($uri, '/'));
        $link = $baseUri->toString();

        if(!defined('JURI_IMAGES_FOLDER')){
            define('JURI_IMAGES_FOLDER',Uri::root()."images/com_adsmanager/contents");
        }

        $image = (!empty($content->images[0]->thumbnail)) ? JURI_IMAGES_FOLDER . '/' . $content->images[0]->thumbnail : '';

        $text = $helper->getShareText($content->metadata_description, $content->ad_text, $content->ad_text);
        $shares = $helper->ShowIN($content->id, $link, $content->ad_headline, $image, $text, $plgParams->get('enable_opengraph', 1));

        return $shares;
    } //end function

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
}//end class
