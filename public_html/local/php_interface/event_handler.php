<?php
use Bitrix\Main;
$eventManager = Main\EventManager::getInstance();
//������ ���������� �� ������� �������� ������ ���������������� ������� OnUserTypeBuildList
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', ['lib\usertype\CUserTypeProp', 'GetUserTypeDescription']);
