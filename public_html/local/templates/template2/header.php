<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$CurDir = $APPLICATION->GetCurDir();
$CurUri = $APPLICATION->GetCurUri();
?>
<!doctype html>
<html lang="ru">
<head>
	<?

	use Bitrix\Main\Page\Asset;

	// Пример подключения JS
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/jquery-3.6.0.js');
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/fancybox/jquery.fancybox.js');
	Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/myscripts.js');
	// Пример подключения CSS
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/css/vars.css');
	Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/js/fancybox/jquery.fancybox.css');
	$APPLICATION->ShowHead();
	?>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<title><? $APPLICATION->ShowTitle() ?></title>
</head>
<body>

<?
$APPLICATION->ShowPanel();
?>


<div class="container">

	<div class="header">
		<div class="header_content">
			<?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"grey_tabs",
	array(
		"ALLOW_MULTI_SELECT" => "N",
		"CHILD_MENU_TYPE" => "left",
		"DELAY" => "N",
		"MAX_LEVEL" => "1",
		"MENU_CACHE_GET_VARS" => array(
		),
		"MENU_CACHE_TIME" => "3600",
		"MENU_CACHE_TYPE" => "N",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_THEME" => "blue",
		"ROOT_MENU_TYPE" => "top",
		"USE_EXT" => "N",
		"COMPONENT_TEMPLATE" => "grey_tabs"
	),
	false
);?>
		</div>
	</div>
	<h1><?$APPLICATION->ShowTitle(false);?></h1>
	<p><?$APPLICATION->ShowProperty('description');?></p>