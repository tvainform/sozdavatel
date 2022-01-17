<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!isset($arParams["CACHE_TIME"]))	$arParams["CACHE_TIME"] = 3600;
if($this->startResultCache(false, array($arParams["CACHE_TIME"])))
{

	$arSelect = array_merge($arParams["FIELD_CODE"], array(
		"ID",
		"NAME",
		"PREVIEW_TEXT",
	));
	$arFilter = array (
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"ACTIVE" => "Y",
	);

	$res = CIBlockElement::GetList(Array("ID"=>"RAND"), $arFilter, false, Array(), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();
		$arResult["ITEMS"][] = $arFields;
	}

	foreach($arResult["ITEMS"] as $sect){
		$db_old_groups = CIBlockElement::GetElementGroups($sect["ID"], true);
		$ar_group = $db_old_groups->Fetch();
		$arResult["ITEMS"][$key++ -1]["SECTION"] = $ar_group["NAME"];
	}
}

$this->IncludeComponentTemplate();
?>