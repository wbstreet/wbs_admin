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

include(WB_PATH.'/modules/wbs_admin/lib.class.admin.php');
$clsAdmin = new WbsAdmin();

$path = WB_PATH.'/modules/wbs_core/include.php';
if(!function_exists('wbs_core_include') && file_exists($path)) {
    include_once($path);
}
if(function_exists('wbs_core_include')) wbs_core_include(['functions.js', 'windows.js', 'windows.css']);

?>

<style>
    h1, h2, h3 {
        background: #eee;
    }
</style>

<script>
    "use strict"
    
    let mod_settings = new mod_settings_Main();
</script>

<h2>Настройки сайта</h2>

<br>

<?php
// минимум
//require_once(WB_PATH.'/include/editarea/wb_wrapper_edit_area.php');
//echo (function_exists('registerEditArea')) ? registerEditArea('code_area', 'css') : 'none';
//echo "<textarea id='code_area'></textarea>";
 ?>


<!--<br><h3> Настройка модуля городов </h3><br>

<br><h3> Настройка модуля магазина </h3><br>

<h4> Версия 1 >> версия 2 </h4>

<input type="button" value="Конвертировать фотографии" onclick="sendform(this, 'mod_minishop_1to2_update_photo', {data:{}, url:mod_settings.url_api});">

<br><h3> Настройка модуля тайзеров </h3>

-->



    <input type='button' value='Логотип' onclick="W.open_by_api('window_logo', {data:{}, url:mod_settings.url_api})">
    <input type='button' value='Иконка сайта' onclick="W.open_by_api('window_icon', {data:{}, url:mod_settings.url_api})">
    <br>
    <input type="button" value="Цветовая схема">
    <input type='button' value='Настройки переменных' onclick="W.open_by_api('window_variables', {data:{}, url:mod_settings.url_api})">
    <br>
    <input type='button' value='Произвольные переменные' onclick="W.open_by_api('window_any_variables', {data:{}, url:mod_settings.url_api})">

    <br><br>
    <input type="button" value="Проверить обновления" onclick="W.open_by_api('window_update', {data:{}, url:mod_settings.url_api})">

<br><br>
<h2>Расширенные настройки (требуют определённых знаний)</h2>

    <?php
        $sql = "SELECT * FROM `".TABLE_PREFIX."addons` WHERE `directory`=(SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_template')";
        $r = $database->query($sql);
        if ($database->is_error()) { echo $database->get_error(); die();}
        $default_template = $r->fetchRow(MYSQLI_ASSOC)['name'];
    ?>
    
    Редактирование CSS: <select onchange="$('#btn_window_css')[0].dataset.template = this.value">
        <option value="<?=$default_template?>">Текущий шаблон</option>
        <?php
            $sql = "SELECT * FROM `".TABLE_PREFIX."addons` WHERE `type`='template' AND `function`='template'";
            $r = $database->query($sql);
            while ($r && $row = $r->fetchRow(MYSQLI_ASSOC)) {
                echo "<option value='{$row['name']}'>{$row['name']}</option>\n";
            }
        ?>
    </select>
    <input id='btn_window_css' data-template='<?=$default_template?>' type='button' value='Начать редактирование' onclick="W.open_by_api('window_css', {data:{template:this.dataset.template}, url:mod_settings.url_api})">
