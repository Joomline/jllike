<?php
/**
 * jllike
 *
 * @version 5.0.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2010-2025 by Joomline (http://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/plugins/content/jllike/helper.php';

class plgAdsmanagercontentJlLikeAds extends JPlugin
{
    public function ADSonContentAfterDisplay($content)
    {
        JPlugin::loadLanguage('plg_content_jllike');
        $plugin = & JPluginHelper::getPlugin('content', 'jllike');
        $plgParams = new JRegistry;
        $plgParams->loadString($plugin->params);
        $view = JFactory::getApplication()->input->get('view');
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
		$prefix = (JFactory::getConfig()->get('force_ssl') == 2) ? 'https://' : 'http://';
		$root = JURI::getInstance()->toString(array('host'));
		$url = $prefix . $plgParams->get('pathbase', '') . str_replace('www.', '', $root);
		if($plgParams->get('punycode_convert',0))
		{
			$file = JPATH_ROOT.'/libraries/idna_convert/idna_convert.class.php';
			if(!JFile::exists($file))
			{
				return JText::_('PLG_JLLIKEPRO_PUNYCODDE_CONVERTOR_NOT_INSTALLED');
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
		$uri = StringHelper::str_ireplace(JURI::root(), '', JURI::current());
        $link = $url.'/'.$uri;

        if(!defined('JURI_IMAGES_FOLDER')){
            define('JURI_IMAGES_FOLDER',JURI::root()."images/com_adsmanager/contents");
        }

        $image = (!empty($content->images[0]->thumbnail)) ? JURI_IMAGES_FOLDER . '/' . $content->images[0]->thumbnail : '';

        $text = $helper->getShareText($content->metadata_description, $content->ad_text, $content->ad_text);
        $shares = $helper->ShowIN($content->id, $link, $content->ad_headline, $image, $text, $plgParams->get('enable_opengraph', 1));

        return $shares;
    } //end function
}//end class
