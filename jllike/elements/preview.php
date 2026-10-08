<?php
/**
 * Preview field for JL Like social buttons
 *
 * @version 5.1.0
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
        
        // Передаем языковые константы в JavaScript
        $doc = Factory::getDocument();
        $translations = json_encode([
            'mobile' => Text::_('PLG_JLLIKEPRO_PREVIEW_MOBILE'),
            'desktop' => Text::_('PLG_JLLIKEPRO_PREVIEW_DESKTOP')
        ]);
        $doc->addScriptDeclaration('window.JLLikePreviewTranslations = ' . $translations . ';');
        
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
        
        // Добавляем мобильные стили для превью (точно как на фронтенде)
        if ($params->get('enable_mobile_css', 1) == 1) {
            $doc->addStyleDeclaration('
            /* Мобильные стили превью - точная копия фронтенда */
            .preview-content.mobile-preview .jllikeproSharesContayner {
                position: static!important;
                right: auto!important;
                bottom: auto!important; 
                z-index: auto!important; 
                background-color: #fff!important;
                width: 100%!important;
                border: 1px solid #e0e0e0;
                border-radius: 0;
            }
            .preview-content.mobile-preview .jllikeproSharesContayner .event-container > div {
                border-radius: 0; 
                padding: 0; 
                display: block;
            }
            .preview-content.mobile-preview .like .l-count {
                display: none!important;
            }
            .preview-content.mobile-preview .jllikeproSharesContayner a {
                border-radius: 0!important;
                margin: 0!important;
                display: inline-block;
                width: auto;
            }
            .preview-content.mobile-preview .l-all-count {
                margin-left: 10px; 
                margin-right: 10px;
                display: inline-block!important;
            }
            .preview-content.mobile-preview .jllikeproSharesContayner i {
                width: 44px!important; 
                height: 44px!important;
                border-radius: 0!important;
            }
            .preview-content.mobile-preview .jllikeproSharesContayner span {
                height: 44px!important;
                line-height: 44px!important;
            }
            .preview-content.mobile-preview .l-ico {
                background-position: 50%!important;
            }
            .preview-content.mobile-preview .likes-block_left {
                text-align: left;
            }
            .preview-content.mobile-preview .likes-block_right {
                text-align: right;
            }
            .preview-content.mobile-preview .likes-block_center {
                text-align: center;
            }
            .preview-content.mobile-preview .button_text {
                display: none;
            }
            ');
        }
    }

    private function getPreviewHTML()
    {
        // Получаем стиль кнопок
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $params = new Registry($plugin->params);
        $buttonStyle = $params->get('button_style', 'default');
        $styleClass = $buttonStyle !== 'default' ? ' jllike-style-' . $buttonStyle : '';

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
                <div class="jllikeproSharesContayner preview-sample' . $styleClass . '" id="preview-sample">
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
        
        // Выводим все сети, выключенные скрываем: иначе после повторного включения
        // в настройках кнопка не появлялась в превью до сохранения
        $networks = [
            ['addfacebook', 'facebook_order', 1, 'fb', 'PLG_JLLIKEPRO_TITLE_FC'],
            ['addvk', 'vk_order', 2, 'vk', 'PLG_JLLIKEPRO_TITLE_VK'],
            ['addtw', 'tw_order', 3, 'tw', 'PLG_JLLIKEPRO_TITLE_TW'],
            ['addod', 'od_order', 4, 'ok', 'PLG_JLLIKEPRO_TITLE_OD'],
            ['addmail', 'mail_order', 5, 'ml', 'PLG_JLLIKEPRO_TITLE_MM'],
            ['addlin', 'lin_order', 6, 'ln', 'PLG_JLLIKEPRO_TITLE_LI'],
            ['addpi', 'pi_order', 7, 'pinteres', 'PLG_JLLIKEPRO_TITLE_PI'],
            ['addlj', 'lj_order', 8, 'lj', 'PLG_JLLIKEPRO_TITLE_LJ'],
            ['addbl', 'bl_order', 9, 'bl', 'PLG_JLLIKEPRO_TITLE_BL'],
            ['addwb', 'wb_order', 10, 'wb', 'PLG_JLLIKEPRO_TITLE_WB'],
            ['addtl', 'tl_order', 11, 'tl', 'PLG_JLLIKEPRO_TITLE_TL'],
            ['addwa', 'wa_order', 12, 'wa', 'PLG_JLLIKEPRO_TITLE_WA'],
            ['addvi', 'vi_order', 13, 'vi', 'PLG_JLLIKEPRO_TITLE_VI'],
            ['addth', 'th_order', 16, 'th', 'PLG_JLLIKEPRO_TITLE_TH'],
            ['addrd', 'rd_order', 17, 'rd', 'PLG_JLLIKEPRO_TITLE_RD'],
        ];

        $providers = array();
        foreach ($networks as [$enableParam, $orderParam, $defaultOrder, $class, $title]) {
            $providers[] = array(
                'order' => (int) $params->get($orderParam, $defaultOrder),
                'class' => $class,
                'title' => Text::_($title),
                'enabled' => (bool) $params->get($enableParam, 1),
            );
        }

        // Сортировка без потери кнопок с одинаковым порядковым номером
        usort($providers, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        $buttonsHtml = '';
        foreach ($providers as $provider) {
            $buttonsHtml .= '
                <a title="' . $provider['title'] . '" class="like like-not-empty l-' . $provider['class'] . '" id="l-' . $provider['class'] . '-preview"' . ($provider['enabled'] ? '' : ' style="display: none"') . '>
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