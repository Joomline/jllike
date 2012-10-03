<?php
/**
 * jllike
 *
 * @version 1.2
 * @author Vadim Kunicin (vadim@joomline.ru)
 * @copyright (C) 2012 by Vadim Kunicin (http://www.joomline.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentjllike extends JPlugin
{
/*
    private $_networks;
    private $_url;
    private $_result = null;

    function __construct ($url,$networks = null) {
        $this->_networks = ($networks!=null)?$networks:$this->_getAllSocials();
        $this->_url = urlencode($url);
    }*/
	

	


	public function onContentPrepare($context, &$article, &$params, $page = 0){
		if($context == 'com_content.article'){
		JPlugin::loadLanguage( 'plg_content_jllike' );
		if (strpos($article->text, '{jllike-off}') !== false) {
			$article->text = str_replace("{jllike-off}","",$article->text);
			return true;
		}

		if (strpos($article->text, '{jllike}') === false && !$this->params->def('autoAdd')) {
			return true;
		}
		if (!isset($article->catid)) {
			$article->catid='';	
		}
		$exceptcat = is_array($this->params->def('categories')) ? $this->params->def('categories') : array($this->params->def('categories'));
		
		if (!in_array($article->catid,$exceptcat)) {
			$view = JRequest::getCmd('view');
			if ($view == 'article') {

				$doc = &JFactory::getDocument();
				$uri = JURI::getInstance();
				$base = $uri->toString(array('scheme', 'host', 'port'));
				$article_url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->catslug));
				$pathbase = 'var pathbs = "http://'.$this->params->def('pathbase').'";';
				$doc->addScriptDeclaration($pathbase);
				
				$doc->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js");
				$doc->addScript("plugins/content/jllike/js/buttons.js?5");
				$doc->addCustomTag('<script type="text/javascript">var jqlike = jQuery.noConflict();</script>');
				//$doc->addScript("plugins/content/jllike/js/pioneers-scroll.js?5");
				
				
				//$doc->addScript("jQuery(document).ready(function($) {$('.likes-block .like').socialButton();$.scrollToButton('hash', 1000);}); ");
				//$doc->addCustomTag("<script >jq = jQuery.noConflict();</script>");
				$doc->addStyleSheet("plugins/content/jllike/js/buttons.css");
	
				$titlefc = JText::_( 'PLG_JLLIKE_TITLE_FC' );
				$titlevk = JText::_( 'PLG_JLLIKE_TITLE_VK' );
				$titletw = JText::_( 'PLG_JLLIKE_TITLE_TW' );
				$titleod = JText::_( 'PLG_JLLIKE_TITLE_OD' );
				$titlegg = JText::_( 'PLG_JLLIKE_TITLE_GG' );
				
				$pagehash = $article->id;
				$scriptPage = <<<HTML
				<script>
					jqlike(document).ready(function($) {
						$('.like').socialButton();
					});
				</script>
				
				 <div class="event-container" >
				<div class="likes-block">
HTML;
				if ($this->params->def('addfacebook')) {
		 		$scriptPage .= <<<HTML
					<a title="$titlefc" href="$article_url" class="like l-fb">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
}			
			if ($this->params->def('addvk')) {
				$scriptPage .= <<<HTML
					<a title="$titlevk" href="$article_url" class="like l-vk">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
}			
			if ($this->params->def('addtw')) {
				$scriptPage .= <<<HTML
					<a title="$titletw" href="$article_url" class="like l-tw">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
}	
			if ($this->params->def('addod')) {		
				$scriptPage .= <<<HTML
					<a title="$titleod" href="$article_url" class="like l-ok">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
}
			if ($this->params->def('addgp')) {
				$scriptPage .= <<<HTML
					<a title="$titlegg" href="$article_url" class="like l-gp">
					<i class="l-ico"></i>
					<span class="l-count"></span>
					</a>
HTML;
}
				$scriptPage .= <<<HTML
				</div>
				</div>
				<div style="text-align: left;">
					<a style="text-decoration:none; color: #c0c0c0; font-family: arial,helvetica,sans-serif; font-size: 5pt; " target="_blank" href="http://joomline.ru/rasshirenija/plugin/jllike.html">Social Like</a>
				</div>
					
HTML;

				
				if ($this->params->def('autoAdd') == 1) {
					$article->text .= $scriptPage;
				} else {
					$article->text = str_replace("{jllike}",$scriptPage,$article->text);
				}

			}
		} else {
			$article->text = str_replace("{jllike}","",$article->text);
		}

	

	}
}

	
	
}