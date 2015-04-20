<?php
/**
 * jllike
 *
 * @version 2.1.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2010-2015 by Vadim Kunicin (http://www.joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

require_once JPATH_ROOT.'/plugins/content/jllike/helper.php';

class plgContentjllike extends JPlugin
{
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        if(JFactory::getApplication()->isAdmin())
        {
            return true;
        }

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
        $option = JRequest::getCmd('option');
        $helper = PlgjllikeHelper::getInstance($this->params);

        if (strpos($article->text, '{jllike}') === false && !$autoAdd)
        {
            return true;
        }

        if (!isset($article->catid))
        {
            $article->catid = '';
        }

        $print = JRequest::getInt('print', 0);

        $url = 'http://' . $this->params->get('pathbase', '') . str_replace('www.', '', $_SERVER['HTTP_HOST']);

        if($this->params->get('punycode_convert',0))
        {
            $file = JPATH_ROOT.'/libraries/idna_convert/idna_convert.class.php';
            if(!JFile::exists($file))
            {
                return JText::_('PLG_jllike_PUNYCODDE_CONVERTOR_NOT_INSTALLED');
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

        switch ($option) {
            case 'com_content':

                if(empty($article->id))
                {
                    //если категория, то завершаем
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


                include_once JPATH_ROOT.'/components/com_content/helpers/route.php';
                $link = $url . JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid));

                $image = '';
                if($this->params->get('content_images', 'fields') == 'fields')
                {
					If(!empty($article->images))
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
							$image = JURI::root().$image;
						}
					}
                    
                }
                else
                {
                    $image = PlgjllikeHelper::extractImageFromText($article->introtext, $article->fulltext);
                }

                $shares = $helper->ShowIN($article->id, $link, $article->title, $image);

                if ($context == 'com_content.article')
                {

                    $view = JRequest::getCmd('view');
                    if ($view == 'article')
                    {
                        if ($autoAdd == 1 || strpos($article->text, '{jllike}') == true)
                        {
                            $helper->loadScriptAndStyle(0);

                            $text = ($this->params->get('desc_source_com_content', 'intro') == 'intro') ? $article->introtext : $article->text;

                            PlgjllikeHelper::addOpenGraphTags($article->title, $text, $image);

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
                else if ($context == 'com_content.category')
                {
                    if ($autoAdd == 1 || strpos($article->text, '{jllike}') == true)
                    {
                        $helper->loadScriptAndStyle(1);
                        $article->text = str_replace("{jllike}", "", $article->text) . $shares;
                    }
                }
                break;
          
            default:
                break;
        }
    }
}