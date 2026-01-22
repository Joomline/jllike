<?php
/**
 * Sortable field for JL Like social buttons order
 *
 * @version 5.3.0
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2025 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class JFormFieldSortable extends FormField
{
    protected $type = 'Sortable';

    /**
     * Список всех доступных социальных сетей с их параметрами
     */
    protected $allNetworks = [
        'fb' => ['param' => 'addfacebook', 'order_param' => 'facebook_order', 'default_order' => 1, 'label' => 'PLG_JLLIKEPRO_TITLE_FC', 'color' => '#3a5795'],
        'vk' => ['param' => 'addvk', 'order_param' => 'vk_order', 'default_order' => 2, 'label' => 'PLG_JLLIKEPRO_TITLE_VK', 'color' => '#4e7299'],
        'tw' => ['param' => 'addtw', 'order_param' => 'tw_order', 'default_order' => 3, 'label' => 'PLG_JLLIKEPRO_TITLE_TW', 'color' => '#000000'],
        'ok' => ['param' => 'addod', 'order_param' => 'od_order', 'default_order' => 4, 'label' => 'PLG_JLLIKEPRO_TITLE_OD', 'color' => '#f7931e'],
        'ml' => ['param' => 'addmail', 'order_param' => 'mail_order', 'default_order' => 5, 'label' => 'PLG_JLLIKEPRO_TITLE_MM', 'color' => '#005ff9'],
        'ln' => ['param' => 'addlin', 'order_param' => 'lin_order', 'default_order' => 6, 'label' => 'PLG_JLLIKEPRO_TITLE_LI', 'color' => '#0e76a8'],
        'pinteres' => ['param' => 'addpi', 'order_param' => 'pi_order', 'default_order' => 7, 'label' => 'PLG_JLLIKEPRO_TITLE_PI', 'color' => '#cb2027'],
        'lj' => ['param' => 'addlj', 'order_param' => 'lj_order', 'default_order' => 8, 'label' => 'PLG_JLLIKEPRO_TITLE_LJ', 'color' => '#00618a'],
        'bl' => ['param' => 'addbl', 'order_param' => 'bl_order', 'default_order' => 9, 'label' => 'PLG_JLLIKEPRO_TITLE_BL', 'color' => '#f57d00'],
        'wb' => ['param' => 'addwb', 'order_param' => 'wb_order', 'default_order' => 10, 'label' => 'PLG_JLLIKEPRO_TITLE_WB', 'color' => '#e6162d'],
        'tl' => ['param' => 'addtl', 'order_param' => 'tl_order', 'default_order' => 11, 'label' => 'PLG_JLLIKEPRO_TITLE_TL', 'color' => '#0088cc'],
        'wa' => ['param' => 'addwa', 'order_param' => 'wa_order', 'default_order' => 12, 'label' => 'PLG_JLLIKEPRO_TITLE_WA', 'color' => '#25d366'],
        'vi' => ['param' => 'addvi', 'order_param' => 'vi_order', 'default_order' => 13, 'label' => 'PLG_JLLIKEPRO_TITLE_VI', 'color' => '#665CAC'],
        'th' => ['param' => 'addth', 'order_param' => 'th_order', 'default_order' => 16, 'label' => 'PLG_JLLIKEPRO_TITLE_TH', 'color' => '#000000'],
        'rd' => ['param' => 'addrd', 'order_param' => 'rd_order', 'default_order' => 17, 'label' => 'PLG_JLLIKEPRO_TITLE_RD', 'color' => '#FF4500'],
    ];

    protected function getInput()
    {
        // Загружаем ассеты
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Регистрируем и подключаем CSS и JS для sortable
        $wa->registerAndUseStyle('plg_jllike.sortable', 'plugins/content/jllike/elements/css/sortable.css');
        $wa->registerAndUseScript(
            'plg_jllike.sortable',
            'plugins/content/jllike/elements/js/sortable.js',
            [],
            ['defer' => true],
            []
        );

        // Получаем текущие настройки плагина
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $params = new Registry($plugin->params);

        // Собираем сети с их текущими порядками и статусами
        $networks = $this->getNetworksWithOrder($params);

        // Передаем языковые константы в JavaScript
        $doc = Factory::getDocument();
        $translations = json_encode([
            'dragHint' => Text::_('PLG_JLLIKEPRO_SORTABLE_DRAG_HINT'),
            'enabled' => Text::_('PLG_JLLIKEPRO_YES'),
            'disabled' => Text::_('PLG_JLLIKEPRO_NO'),
        ]);
        $doc->addScriptDeclaration('window.JLLikeSortableTranslations = ' . $translations . ';');

        return $this->renderSortableList($networks);
    }

    /**
     * Получает список сетей с их текущими порядками
     */
    protected function getNetworksWithOrder($params)
    {
        $networks = [];

        foreach ($this->allNetworks as $key => $network) {
            $enabled = (int) $params->get($network['param'], 1);
            $order = (int) $params->get($network['order_param'], $network['default_order']);

            $networks[] = [
                'key' => $key,
                'enabled' => $enabled,
                'order' => $order,
                'param' => $network['param'],
                'order_param' => $network['order_param'],
                'label' => Text::_($network['label']),
                'color' => $network['color'],
            ];
        }

        // Сортируем по текущему порядку
        usort($networks, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $networks;
    }

    /**
     * Рендерит HTML sortable списка
     */
    protected function renderSortableList($networks)
    {
        // Получаем текущее значение addall (показывать общий счетчик)
        $plugin = PluginHelper::getPlugin('content', 'jllike');
        $params = new Registry($plugin->params);
        $addallEnabled = (int) $params->get('addall', 1);
        $addallChecked = $addallEnabled ? 'checked' : '';
        $addallClass = $addallEnabled ? 'enabled' : 'disabled';

        $html = '
        <div class="jllike-sortable-container" id="jllike-sortable-container">
            <div class="jllike-sortable-header">
                <span class="sortable-col-handle"></span>
                <span class="sortable-col-icon">' . Text::_('PLG_JLLIKEPRO_SORTABLE_ICON') . '</span>
                <span class="sortable-col-name">' . Text::_('PLG_JLLIKEPRO_SORTABLE_NETWORK') . '</span>
                <span class="sortable-col-status">' . Text::_('PLG_JLLIKEPRO_SORTABLE_STATUS') . '</span>
                <span class="sortable-col-order">' . Text::_('PLG_JLLIKEPRO_ORDER') . '</span>
            </div>
            <ul class="jllike-sortable-list" id="jllike-sortable-list">';

        foreach ($networks as $index => $network) {
            $enabledClass = $network['enabled'] ? 'enabled' : 'disabled';
            $checkedAttr = $network['enabled'] ? 'checked' : '';

            $html .= '
                <li class="jllike-sortable-item ' . $enabledClass . '"
                    data-network="' . $network['key'] . '"
                    data-param="' . $network['param'] . '"
                    data-order-param="' . $network['order_param'] . '"
                    draggable="true">
                    <span class="drag-handle">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M2 4h12v1H2V4zm0 3h12v1H2V7zm0 3h12v1H2v-1zm0 3h12v1H2v-1z"/>
                        </svg>
                    </span>
                    <span class="network-icon" style="background-color: ' . $network['color'] . '">
                        <i class="l-ico l-' . $network['key'] . '-icon"></i>
                    </span>
                    <span class="network-name">' . $network['label'] . '</span>
                    <span class="network-toggle">
                        <input type="checkbox"
                               class="network-enabled-toggle"
                               data-network="' . $network['key'] . '"
                               data-param="' . $network['param'] . '"
                               ' . $checkedAttr . '>
                    </span>
                    <span class="network-order">' . ($index + 1) . '</span>
                </li>';
        }

        // Добавляем кнопку "Все" (общий счетчик) - не перетаскиваемую
        $html .= '
                <li class="jllike-sortable-item jllike-all-counter ' . $addallClass . '" data-network="all" data-param="addall" draggable="false">
                    <span class="drag-handle drag-handle-disabled">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" opacity="0.3">
                            <path d="M8 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                            <path d="M11 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm0 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                        </svg>
                    </span>
                    <span class="network-icon" style="background-color: #888888">
                        <i class="l-ico l-all-icon"></i>
                    </span>
                    <span class="network-name">' . Text::_('PLG_JLLIKEPRO_ENABLE_ALL') . '</span>
                    <span class="network-toggle">
                        <input type="checkbox"
                               class="network-enabled-toggle"
                               data-network="all"
                               data-param="addall"
                               ' . $addallChecked . '>
                    </span>
                    <span class="network-order">—</span>
                </li>';

        $html .= '
            </ul>
            <div class="jllike-sortable-footer">
                <small class="text-muted">
                    <span class="icon-info" aria-hidden="true"></span>
                    ' . Text::_('PLG_JLLIKEPRO_SORTABLE_INFO') . '
                </small>
            </div>
        </div>';

        return $html;
    }
}
