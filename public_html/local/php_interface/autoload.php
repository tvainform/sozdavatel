<?php
use Bitrix\Main\Loader;
//������������ ����� �������
Loader::registerAutoLoadClasses(null, [
	'lib\usertype\CUserTypeProp' => APP_CLASS_FOLDER . 'usertype/CUserTypeProp.php',
]);