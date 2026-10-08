<?php
/**
 * jllike
 *
 * @version 5.3.1
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2026 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die;

class PlgContentJllikeInstallerScript
{
    /**
     * Файлы старых версий, которые установщик Joomla не удаляет при обновлении.
     *
     * Самый опасный — js/buttons.min.js: при выключенной отладке Joomla подключает .min.js
     * вместо buttons.js, а старая версия требует jQuery. В шаблонах без jQuery (Cassiopeia,
     * Astroid и др.) это даёт "jQuery is not defined" и неработающие кнопки, а при наличии
     * jQuery — JSONP-запросы к сторонним API счётчиков.
     *
     * @var string[]
     */
    protected $obsoleteFiles = [
        '/plugins/content/jllike/js/buttons.min.js',
        '/plugins/content/jllike/js/buttons.min.css',
        '/plugins/content/jllike/js/twit.js',
        '/plugins/content/jllike/js/twit.min.js',
        '/plugins/content/jllike/js/pioneers-scroll.js',
    ];

    public function postflight($type, $parent)
    {
        if ($type !== 'install' && $type !== 'update' && $type !== 'discover_install') {
            return true;
        }

        foreach ($this->obsoleteFiles as $file) {
            $path = JPATH_ROOT . $file;

            if (is_file($path) && !@unlink($path)) {
                \Joomla\CMS\Factory::getApplication()->enqueueMessage(
                    'JL Like: удалите вручную устаревший файл ' . $file,
                    'warning'
                );
            }
        }

        return true;
    }
}
