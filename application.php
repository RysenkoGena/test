<?include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); 

use Bitrix\Main\Application;

$application =  Application::getInstance()->getContext()->getRequest();
d($application);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>