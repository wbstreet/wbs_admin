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





$path_twig = WB_PATH.'/include/Sensio/Twig/lib/Twig/Autoloader.php';

if (file_exists($path_twig)) {

    if (!class_exists('Twig_Environment')) include($path_twig);

} else echo "<script>console.log('Модуль минимаркета требует шаблонизатор Twig')</script>";



$path_core = WB_PATH.'/modules/wbs_core/include_all.php';

if (file_exists($path_core)) include($path_core);

else echo "<script>console.log('Модуль минимаркета требует модуль \"wbs_core\"')</script>";



class WbsAdmin extends Addon {

    function __construct() {

        parent::__construct('wbs_admin', null, null);
    }

    

}



?>