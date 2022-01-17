<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("Кастомный компонент"),
	"DESCRIPTION" => GetMessage("Выводим список элементов"),
	"PATH" => array(
		"ID" => "main_components",
		"CHILD" => array(
			"ID" => "mainelements",
			"NAME" => "Список элементов"
		)
	),
	"ICON" => "/images/icon.gif",
);
?>