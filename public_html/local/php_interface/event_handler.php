<?php
use Bitrix\Main;
$eventManager = Main\EventManager::getInstance();
//¬ешаем обработчик на событие создани€ списка пользовательских свойств OnUserTypeBuildList
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', ['lib\usertype\CUserTypeProp', 'GetUserTypeDescription']);
