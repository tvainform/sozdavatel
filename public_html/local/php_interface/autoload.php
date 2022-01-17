<?php
use Bitrix\Main\Loader;
//Автозагрузка наших классов
Loader::registerAutoLoadClasses(null, [
	'lib\usertype\CUserTypeProp' => APP_CLASS_FOLDER . 'usertype/CUserTypeProp.php',
]);