<?php

namespace lib\usertype;

use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

/**
 * Реализация свойство «Расписание врача»
 * Class CUserTypeProp
 * @package lib\usertype
 */
class CUserTypeProp
{
	/**
	 * Метод возвращает массив описания собственного типа свойств
	 * @return array
	 */
	public function GetUserTypeDescription()
	{
		return array(
			'USER_TYPE_ID' => 'user_property', //Уникальный идентификатор типа свойств
			'USER_TYPE' => 'USERPROPERTRY',
			'CLASS_NAME' => __CLASS__,
			'DESCRIPTION' => 'Собственный тип',
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
			'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
			'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
			'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
		);
	}

	/**
	 * Конвертация данных перед сохранением в БД
	 * @param $arProperty
	 * @param $value
	 * @return mixed
	 */
	public static function ConvertToDB($arProperty, $value)
	{
		if ($value['VALUE']['TEXT'] != '' && $value['VALUE']['SORT'] != '')
		{
			try {
				$value['VALUE'] = base64_encode(serialize($value['VALUE']));
			} catch(Bitrix\Main\ObjectException $exception) {
				echo $exception->getMessage();
			}
		} else {
			$value['VALUE'] = '';
		}

		return $value;
	}

	/**
	 * Конвертируем данные при извлечении из БД
	 * @param $arProperty
	 * @param $value
	 * @param string $format
	 * @return mixed
	 */
	public static function ConvertFromDB($arProperty, $value, $format = '')
	{
		if ($value['VALUE'] != '')
		{
			try {
				$value['VALUE'] = base64_decode($value['VALUE']);
				$arProperty['VALUE'] = base64_decode($value['SORT']);
			} catch(Bitrix\Main\ObjectException $exception) {
				echo $exception->getMessage();
			}
		}

		return $value;
	}

	/**
	 * Представление формы редактирования значения
	 * @param $arUserField
	 * @param $arHtmlControl
	 */
	public static function GetPropertyFieldHtml($arProperty, $value, $arHtmlControl)
	{
		//usort($value, "usortTest");
		//echo "<pre>Template arResult: "; print_r($arProperty); echo "</pre>";

		$itemId = 'row_' . substr(md5($arHtmlControl['VALUE']), 0, 10); //ID для js
		$fieldName =  htmlspecialcharsbx($arHtmlControl['VALUE']);

		//htmlspecialcharsback нужен для того, чтобы избавиться от многобайтовых символов из-за которых не работает unserialize()
		$arValue = unserialize(htmlspecialcharsback($value['VALUE']), [stdClass::class]);


		$textvalue = ($arValue['TEXT']) ? $arValue['TEXT'] : '';
		$text = '<input type="text" value="'.$textvalue.'" name="'.$fieldName.'[TEXT]">';

		$html = '<div class="property_row" id="'. $itemId .'">';
		//$html .= $arProperty["SORT"];
		$html .= $text;

		$sort = ($arValue['SORT']) ? $arValue['SORT'] : '';

		$html .='&nbsp;сортировка: &nbsp;<input type="text" size="1" name="'. $fieldName .'[SORT]" value="'. $sort . '">';
		if($text!='' && $sort!=''){
			$html .= '&nbsp;&nbsp;<input type="button" style="height: auto;" value="x" title="Удалить" onclick="document.getElementById(\''. $itemId .'\').parentNode.parentNode.remove()" />';
		}

		$html .= '</div>';

		return $html;
	}
}