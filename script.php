<?php
/**
 * jllike — скрипт установки пакета
 *
 * @version 5.3.1
 * @author Vadim Kunicin (vadim@joomline.ru), Arkadiy (a.sedelnikov@gmail.com)
 * @copyright (C) 2012-2026 by Joomline (https://joomline.ru)
 * @license GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 **/

defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerScript;

/**
 * Проверка минимальных версий до установки плагинов пакета.
 * Тег <minimum-joomla-version> Joomla не читает, поэтому раньше пакет
 * без предупреждения ставился и на неподдерживаемые версии.
 */
class Pkg_JllikeInstallerScript extends InstallerScript
{
    protected $minimumJoomla = '4.0.0';

    protected $minimumPhp = '8.1.0';
}
