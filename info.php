<?php
/**
 *
 * @category        module
 * @package         wbs_admin
 * @author          Konstantin Polyakov
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.0
 *
 */

/*

27.04.2017 - добавлена возможность загрузки иконки 
02.05.2017 - добавлена возможность редактирования стилей шаблонов

*/

/* -------------------------------------------------------- */
// Must include code to stop this file being accessed directly
if(defined('WB_PATH') == false) { die('Illegale file access /'.basename(__DIR__).'/'.basename(__FILE__).''); }
/* -------------------------------------------------------- */

$module_directory   = 'wbs_admin';
$module_name        = 'WBS Admin v0.1.0';
$module_type        = 'addon';
$module_function    = 'tool';
$module_version     = '0.1.0';
$module_platform    = '2.10.0';
$module_author      = 'Konstantin Polyakov';
$module_license     = 'GNU General Public License';
$module_description = 'Settings of website';

$links = ['windows', 'media_efects']; // зависимости