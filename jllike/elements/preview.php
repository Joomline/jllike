<?php
/**
 * Preview field for JL Like social buttons
 *
 * @version 5.0.1
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2025 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\WebAsset\WebAssetManager;

class JFormFieldPreview extends FormField
{
    protected $type = 'Preview';

    protected function getInput()
    {
        // Используем WebAssetManager для Joomla 4/5
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        // Регистрируем и используем базовые стили социальных кнопок (те же что на фронте)
        $wa->registerAndUseStyle('plg_jllike.buttons', 'plugins/content/jllike/js/buttons.css');
        
        // Регистрируем и используем стили превью виджета (только для админки)
        $wa->registerAndUseStyle('plg_jllike.admin_preview', 'plugins/content/jllike/elements/css/admin-preview.css');
        
        // Регистрируем и используем JS для превью с зависимостями
        $wa->registerAndUseScript(
            'plg_jllike.preview', 
            'plugins/content/jllike/elements/js/preview.js', 
            [],
            ['defer' => true],
            []
        );
        
        // Применяем те же динамические стили, что и на фронтенде
        $this->applyFrontendStyles();
        
        return $this->getPreviewHTML();
    }

    private function applyFrontendStyles()
    {
        // Получаем настройки плагина
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $params = new Registry($plugin->params);
        
        $doc = Factory::getDocument();
        
        // Применяем те же стили, что и в helper.php
        $btn_border_radius = (int) $params->get('btn_border_radius', 15);
        $btn_dimensions = (int) $params->get('btn_dimensions', 30);
        $btn_margin = (int) $params->get('btn_margin', 6);
        $font_size = (float) $params->get('font_size', 1);
        
        $doc->addStyleDeclaration('
            .jllikeproSharesContayner a {border-radius: ' . $btn_border_radius . 'px; margin-left: ' . $btn_margin . 'px;}
            .jllikeproSharesContayner i {width: ' . $btn_dimensions . 'px;height: ' . $btn_dimensions . 'px;}
            .jllikeproSharesContayner span {height: ' . $btn_dimensions . 'px;line-height: ' . $btn_dimensions . 'px;font-size: ' . $font_size . 'rem;}
        ');
        
        // Добавляем мобильные стили (без фиксации позиции для превью)
        if ($params->get('enable_mobile_css', 1) == 1) {
            $doc->addStyleDeclaration('
            @media screen and (max-width:800px) {
                .preview-content.mobile-preview .jllikeproSharesContayner {position: static!important;right: auto!important;bottom: auto!important; z-index: auto!important; background-color: #fff!important;width: 100%!important;}
                .preview-content.mobile-preview .jllikeproSharesContayner .event-container > div {border-radius: 0; padding: 0; display: block;}
                .preview-content.mobile-preview .like .l-count {display:none}
                .preview-content.mobile-preview .jllikeproSharesContayner a {border-radius: 0!important;margin: 0!important;}
                .preview-content.mobile-preview .l-all-count {margin-left: 10px; margin-right: 10px;}
                .preview-content.mobile-preview .jllikeproSharesContayner i {width: 44px!important; border-radius: 0!important;}
                .preview-content.mobile-preview .l-ico {background-position: 50%!important}
                .preview-content.mobile-preview .likes-block_left {text-align:left;}
                .preview-content.mobile-preview .likes-block_right {text-align:right;}
                .preview-content.mobile-preview .likes-block_center {text-align:center;}
                .preview-content.mobile-preview .button_text {display: none;}
            }
            ');
        }
    }

    private function getPreviewHTML()
    {
        $html = '
        <div class="preview-widget-container" id="jllike-preview-widget">
            <div class="preview-header">
                <h4>' . Text::_('PLG_JLLIKEPRO_PREVIEW_WIDGET') . '</h4>
                <div class="preview-controls">
                    <button type="button" class="btn btn-sm" id="toggle-mobile-preview">
                        <span class="icon-mobile" aria-hidden="true"></span>
                        ' . Text::_('PLG_JLLIKEPRO_PREVIEW_MOBILE') . '
                    </button>
                </div>
            </div>
            
            <div class="preview-content" id="preview-content">
                <div class="jllikeproSharesContayner preview-sample" id="preview-sample">
                    <input type="hidden" class="link-to-share" value="https://example.com"/>
                    <input type="hidden" class="share-title" value="' . Text::_('PLG_JLLIKEPRO_PREVIEW_SAMPLE_TITLE') . '"/>
                    <input type="hidden" class="share-image" value=""/>
                    <input type="hidden" class="share-desc" value="' . Text::_('PLG_JLLIKEPRO_PREVIEW_SAMPLE_DESC') . '"/>
                    <input type="hidden" class="share-id" value="preview"/>
                    
                    <div class="button_text likes-block" id="preview-button-text" style="display: none;"></div>
                    
                    <div class="event-container">
                        <div class="likes-block" id="preview-buttons">
                            ' . $this->generateSampleButtons() . '
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="preview-footer">
                <small class="text-muted">
                    <span class="icon-info" aria-hidden="true"></span>
                    ' . Text::_('PLG_JLLIKEPRO_PREVIEW_REALTIME_INFO') . '
                </small>
            </div>
        </div>';

        return $html;
    }

    private function generateSampleButtons()
    {
        // Получаем настройки плагина для определения активных кнопок
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $params = new Registry($plugin->params);
        
        $providers = array();
        
        // Собираем включенные провайдеры с их порядком
        if ($params->get('addfacebook', 1)) {
            $order = $params->get('facebook_order', 1);
            $providers[$order] = array('class' => 'fb', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_FC'));
        }
        if ($params->get('addvk', 1)) {
            $order = $params->get('vk_order', 2);
            $providers[$order] = array('class' => 'vk', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_VK'));
        }
        if ($params->get('addtw', 1)) {
            $order = $params->get('tw_order', 3);
            $providers[$order] = array('class' => 'tw', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_TW'));
        }
        if ($params->get('addod', 1)) {
            $order = $params->get('od_order', 4);
            $providers[$order] = array('class' => 'ok', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_OD'));
        }
        if ($params->get('addmail', 1)) {
            $order = $params->get('mail_order', 5);
            $providers[$order] = array('class' => 'ml', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_MM'));
        }
        if ($params->get('addlin', 1)) {
            $order = $params->get('lin_order', 6);
            $providers[$order] = array('class' => 'ln', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_LI'));
        }
        if ($params->get('addpi', 1)) {
            $order = $params->get('pi_order', 7);
            $providers[$order] = array('class' => 'pinteres', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_PI'));
        }
        if ($params->get('addlj', 1)) {
            $order = $params->get('lj_order', 8);
            $providers[$order] = array('class' => 'lj', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_LJ'));
        }
        if ($params->get('addbl', 1)) {
            $order = $params->get('bl_order', 9);
            $providers[$order] = array('class' => 'bl', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_BL'));
        }
        if ($params->get('addwb', 1)) {
            $order = $params->get('wb_order', 10);
            $providers[$order] = array('class' => 'wb', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_WB'));
        }
        if ($params->get('addtl', 1)) {
            $order = $params->get('tl_order', 11);
            $providers[$order] = array('class' => 'tl', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_TL'));
        }
        if ($params->get('addwa', 1)) {
            $order = $params->get('wa_order', 12);
            $providers[$order] = array('class' => 'wa', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_WA'));
        }
        if ($params->get('addvi', 1)) {
            $order = $params->get('vi_order', 13);
            $providers[$order] = array('class' => 'vi', 'title' => Text::_('PLG_JLLIKEPRO_TITLE_VI'));
        }

        ksort($providers);
        
        $buttonsHtml = '';
        foreach ($providers as $provider) {
            $buttonsHtml .= '
                <a title="' . $provider['title'] . '" class="like like-not-empty l-' . $provider['class'] . '" id="l-' . $provider['class'] . '-preview">
                    <i class="l-ico"></i>
                    <span class="l-count">42</span>
                </a>';
        }

        // Добавляем кнопку "Все"
        if ($params->get('addall', 1)) {
            $buttonsHtml .= '
                <a title="' . Text::_('PLG_JLLIKEPRO_TITLE_ALL') . '" class="l-all" id="l-all-preview">
                    <i class="l-ico"></i>
                    <span class="l-count l-all-count" id="l-all-count-preview">168</span>
                </a>';
        }

        return $buttonsHtml;
    }
} 