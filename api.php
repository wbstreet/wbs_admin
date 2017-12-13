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

$action = $_POST['action'];

require('../../config.php');
require_once(WB_PATH.'/framework/functions.php');

require_once(WB_PATH."/framework/class.admin.php");
$admin = new Admin("Start", "start", false, false);

include(WB_PATH.'/modules/wbs_admin/lib.class.admin.php');
$clsSettings = new WbsAdmin();

function check_authed() {
	global $admin;
	if (!$admin->is_authenticated()) {
		print_error("Доступ в админ-панель разрешён только зарегистрированным пользователям. Пожалуйста, войдите или зарегистрируйтесь.");
	} 
}

function get_template_data($template_name, $title) {
    global $database;
    
    // получаем имя директории по имени шаблона
    $sql = "SELECT `directory` FROM `".TABLE_PREFIX."addons` WHERE `name`='".$database->escapeString($template_name)."' AND `type`='template' AND `function`='template'";
    $r = $database->query($sql);
    if ($r->numRows() == 0) print_error("Шаблон с таким именем не найден!", ['title'=>$title]);
    return $r->fetchRow(MYSQLI_ASSOC);
}

function get_template_config($template_dir, $title) {
    // проверка конфига    
    $path_config_template = WB_PATH."/templates/".$template_dir."/config_template.php";
    if (!file_exists($path_config_template)) print_error("Не найден конфигурационный файл для выбранного шаблона", ['title'=>$title]);
    include($path_config_template);
    if (!isset($config_template['edit_css']) || count($config_template['edit_css']) == 0) print_error("Для выбранного шаблона отсутствует список доступных CSS-файлов", ['title'=>$path_config_template]);

    return $config_template;
}

if (substr($action, 0, strlen('window')) == 'window') {
    $loader = new Twig_Loader_Filesystem($clsSettings->pathTemplates);
    $twig = new Twig_Environment($loader);
}

if ($action=='window_icon') {

    print_success(
   	  $twig->render('icon.twig', [
	//	'FTAN'=>$admin->getFTAN(),
		'WB_URL'=>WB_URL,
      ]),
   	  ['title'=>'Иконка сайта']
   );

} else if ($action=='load_icon') {

    check_authed();

    include(WB_PATH.'/include/modules/wbs_core/chrisbliss18/php-ico/class-php-ico.php');

    $file_size = 500 * 1024; // в байтах

    if (!isset($_FILES['icon'])) print_error('Не выбрано изображение!');
    if ($_FILES['icon']['size'] >= $file_size) print_error('Размер файла не должен превышать '.($file_size/1024).' Кб !');

    $source = WB_PATH.'/temp/new_favicon';

    if (move_uploaded_file($_FILES['icon']['tmp_name'], $source)) {

        $ico_lib = new PHP_ICO( $source , [32, 32] );
        $ico_lib->save_ico( WB_PATH.'/favicon.ico' );
    
        unlink($source);
        
        print_success('Сделано!');
    } else print_error('Ошибка загрузки изображения');

} else if ($action=='window_css') {

    check_authed();

    $template_name = $clsFilter->f('template', [['1', "Не выбран шаблон!"]], 'fatal');
    $title = "Редактирование CSS ({$template_name})";

    $template_data = get_template_data($template_name, $title);
    $template_config = get_template_config($template_data['directory'], $title);

    $files = [];
    foreach($template_config['edit_css'] as $index => $name) {
        $files[] = ['index'=>$index, 'name'=>$name];
    }

    print_success(
    $twig->render('css.twig', [
	//	'FTAN'=>$admin->getFTAN(),
		'WB_URL'=>WB_URL,
		'files'=>$files,
		'template'=>$template_name,
        ]),
   	    ['title'=>$title]
   );


} else if ($action=='window_css_selected') {

    check_authed();

    $template_name = $clsFilter->f('template', [['1', "Не выбран шаблон!"]], 'fatal');

    $css_index = $clsFilter->f('index', [['integer', "Не выбран css-файл!"]], 'fatal');
    
    $template_data = get_template_data($template_name, "Ошибка");
    $template_config = get_template_config($template_data['directory'], "Ошибка");

    // проверка CSS-файла
    $css_file = WB_PATH."/templates/".$template_data['directory']."/".$template_config['edit_css'][$css_index];
    if (!file_exists($css_file)) {
        $onclick = "sendform(this, 'create_css', {
            data: {template: '{$template_name}', index: '{$css_index}'},
            url:mod_settings.url_api,
            arg_func_success: this,
            func_success: function(res, arg) {
                W.close(arg);
                W.open_by_api('window_css_selected', {data:{template: '{$template_name}', index: '{$css_index}' }, url:mod_settings.url_api});
            }})";
        print_error("Не найден CSS-файл для выбранного шаблона <br> <input type='button' value='Создать его сейчас?' onclick=\"{$onclick}\">", ['title'=>"Ошибка"]);
    }

    $title = "Редактирование CSS ({$template_name} - {$template_config['edit_css'][$css_index]})";

    // Javascript-скрипты редактора
    require_once(WB_PATH.'/include/editarea/wb_wrapper_edit_area.php');
    if (function_exists('registerEditArea')) {
        ob_start();
        registerEditArea('code_area', 'css');
        $EditArea = ob_get_contents();
        ob_end_clean();
    } else $EditArea = 'none';

    print_success($twig->render('css_selected.twig', [
	//	'FTAN'=>$admin->getFTAN(),
		'WB_URL'=>WB_URL,
		'template'=>$template_name,
		'index'=>$css_index,
		'content'=>file_get_contents($css_file),
		
		'registerEditArea' => $EditArea,
        ]),
   	    ['title'=>$title]
    );

} else if ($action=='create_css') {

    check_authed();

    $template_name = $clsFilter->f('template', [['1', "Не выбран шаблон!"]], 'fatal');

    $template_data = get_template_data($template_name, "Ошибка");
    $template_config = get_template_config($template_data['directory'], "Ошибка");

    $css_index = $clsFilter->f('index', [['integer', "Не выбран css-файл!"]], 'fatal');

    // проверка CSS-файла
    $css_file = WB_PATH."/templates/".$template_data['directory']."/".$template_config['edit_css'][$css_index];
    if (file_exists($css_file)) print_error("CSS-файл уже существует!", ['title'=>"Ошибка"]);
    
    file_put_contents($css_file, "// Ваш код CSS");
    
    print_success("CSS-файл успешно создан!");

} else if ($action=='save_css') {

    check_authed();
    
    $template_name = $clsFilter->f('template', [['1', "Не выбран шаблон!"]], 'fatal');

    $css_index = $clsFilter->f('index', [['integer', "Не выбран шаблон!"]], 'fatal');

    $content = $clsFilter->f('content', [['1', "Файл не может быть пустым!"]], 'fatal');

    // проверка конфига
    $template_data = get_template_data($template_name, "");
    $template_config = get_template_config($template_data['directory'], "");

    // проверка CSS-файла
    $css_file = WB_PATH."/templates/".$template_data['directory']."/".$template_config['edit_css'][$css_index];
    if (!file_exists($css_file)) print_error("Не найден CSS-файл для выбранного шаблона");

    file_put_contents($css_file, $content);

    print_success("Сохранено!");

} else if ($action=='window_variables') {

    check_authed();

    $prefix = "customsettings_";    
    $variables = [
        $prefix."feedback_email"=>"Email формы обратной связи",
        ];
        
    $variables_keys = array_keys($variables);
    foreach($variables_keys as $i => $v) $variables_keys[$i] = "'".$v."'";

    // ----> добавить отсутствующие поля
    $r = $database->query("SELECT * FROM `".TABLE_PREFIX."settings` WHERE `name` IN (".implode(",", $variables_keys).")");
    if ($database->is_error()) print_error($database->get_error());
    $existed_keys = [];
    while ($row = $r->fetchRow(MYSQLI_ASSOC)) $existed_keys[] = $row['name'];

    if (count($variables) !== count($existed_keys)) {
        $inserts = [];
        foreach($variables as $name => $name_translate) {
            if (!in_array($name, $existed_keys)) $inserts[] = "('{$name}', '')";
        }
        if (count($inserts) > 0) $database->query("INSERT INTO `".TABLE_PREFIX."settings` (`name`, `value`) VALUES ".implode(",", $inserts));
    }
    // <<<------

    $_variables = [];
    $r = $database->query("SELECT * FROM `".TABLE_PREFIX."settings` WHERE `name` IN (".implode(",", $variables_keys).")");
    if ($database->is_error()) print_error($database->get_error());
    while ($row = $r->fetchRow(MYSQLI_ASSOC)) {
        $row['name_translate'] = $variables[$row['name']];
        $_variables[] = $row;
    }
    
    print_success($twig->render('variables.twig', [
		'WB_URL'=>WB_URL,
		'variables'=>$_variables,
        ]),
   	    ['title'=>"Настройки переменных"]
    );    

} else if ($action=='save_variables') {

    check_authed();

    $name = $clsFilter->f('name', [['1', "Не указано имя!"]], 'fatal');
    $value = $clsFilter->f('value', [['1', "Не указано имя!"]], 'fatal');
    
    $r = $database->query("UPDATE `".TABLE_PREFIX."settings` SET `value`='$value' WHERE `name` = '$name' ");
    if ($database->is_error()) print_error($database->get_error());

    print_success("Сохранено!");

} else if ($action=='mod_minishop_1to2_update_photo') {

    check_authed();

    $pathMinishop = WB_PATH."/modules/minishop/lib.class.minishop.php";
    if (!file_exists($pathMinishop)) print_error("Отсутствует файл \"minishop/lib.class.minishop.php\". Возможно, модуль не установлен");

    include($pathMinishop);

    $clsMinishop = new Minishop(0, 0);
    
    $count = $clsMinishop->photo_old2new();

    print_success("Перемещено: {$count}");

} else if ($action=='window_update') {

    check_authed();

    /*function getGithub() {
        //getcwd();
        chdir(WB_PATH.'/modules/wbs_core/include/php-github-api-master/lib/');
        include_once(WB_PATH.'/modules/wbs_core/include/php-github-api-master/lib/Github/Client.php');
        $client = new \Github\Client();
        return $client;
    }
    $client = getGithub();*/

    /*include_once(WB_PATH.'/modules/wbs_core/include/github-php-client-master/client/GitHubClient.php');
    $client = new GitHubClient();
    $client->setPage();
    $client->setPageSize(20);
    $repos = $client->repos->listUserRepositories('shyzik93');
    foreach($repos as $repo) {
        print_success(json_encode(get_object_vars($repos)));
    }*/
    
    /*
    $modules = [$module1, .., $moduleN]
    $module = ['name'=>'', 'version'=>'', 'is_installed'=>'', 'is_updated'=>'']
    */
    $modules = [];
    
    // подключаемся к гитхабу
    
    // получаем список модулей
    
    // ---- сверяем версии модулей и определяем кнопки: (установить) | (удалить, (обновить|установленаСвежаяВерсия))

    print_success($twig->render('update.twig', [
                'WB_URL'=>WB_URL,
                'modules'=>$modules,
        ]),
            ['title'=>"Проверка обновлений"]
    );    

} else if ($action=='update_delete') {

    check_authed();

} else if ($action=='update_update') {

    check_authed();

} else if ($action=='update_install') {    

    check_authed();

} else {
    check_authed();
    print_error('неврный API');
}

?>