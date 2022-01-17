<?
/*Обработчик для начисления бонусов за покупку от 5000 руб*/
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
AddEventHandler("sale", "OnSaleStatusOrder", "OrderComplete");
function OrderComplete($orderID, &$arFields)
{
	Loader::includeModule("sale");
	Loader::includeModule("highloadblock");
	if ($arFields == 'F') {
		$order = \Bitrix\Sale\Order::load($orderID); // 123 - ID заказа
		$orderUser = $order->getUserId();
		$orderSumm = $order->getPrice();
		$orderDeliveryPrice = $order->getDeliveryPrice();
		$orderPayments = \Bitrix\Sale\PaymentCollection::getList([
			'select' => ['SUM'],
			'filter' => [
				'=ORDER_ID' => $orderID,
				'=PAY_SYSTEM_ID' => 110,//Наложенный платеж
				'=PAID' => 'Y']
		]);
		while ($orderPayment = $orderPayments->fetch()) {
			$orderPayid = $orderPayment['SUM'];
		}
		if (empty($orderPayid)) {
			$orderPriceBonus = $orderSumm - $orderDeliveryPrice;
		} else {
			$orderPriceBonus = $orderSumm - $orderDeliveryPrice - $orderPayid;
		}
		$hlblockDatas = HL\HighloadBlockTable::getById(4)->fetch();
		$entityHlBonus = HL\HighloadBlockTable::compileEntity($hlblockDatas);
		$entityDataClassBonus = $entityHlBonus->getDataClass();
		$bonusData = $entityDataClassBonus::getList(array(
			'select' => array('UF_BONUS_FROM', 'UF_BONUS_TO', 'UF_BONUS_PRICE'),
		));
		while ($arBonusData = $bonusData->Fetch()) {
			$priceFrom = $arBonusData['UF_BONUS_FROM'];
			$priceTo = $arBonusData['UF_BONUS_TO'];
			if (($orderPriceBonus > $priceFrom) && ($orderPriceBonus < $priceTo)) {
				$priceBonus = $arBonusData['UF_BONUS_PRICE'];
				$summToAddBonus = $orderPriceBonus * $priceBonus / 100;
				CSaleUserAccount::UpdateAccount(
					$orderUser,
					$summToAddBonus,
					"RUB",
					false,
					$orderID,
					false
				);
			}
		}
	}
}
/*Обработчик почтового события*/
//Регистрируем обработчик изменения инфоблока с именем функции будет IBQuestForm
AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate', 'IBQuestForm');
//Описываем функцию
function IBQuestForm(&$arFields)
{
	//Создаем переменные, внутри которых прописываем:
	$SITE_ID = 's1'; //Индетификатор сайта
	$IBLOCK_ID = 4; //Индетификатор инфоблока с вопросами
	$EVEN_TYPE = 'NEW_REQUEST_ADDED'; // Тип почтового события
	if ($arFields['IBLOCK_ID'] == $IBLOCK_ID) {
		//Собираем в массив все данные, которые хотим передать в письмо
		//Перечисляем все поля как в почтовом событии
		$arQuestForm = array(
			//Стандартные поля инфоблока
			"NAME" => $arFields['NAME'], //Название вопроса
			"QUEST" => $arFields['PREVIEW_TEXT'], //Анонс
			"ANSWER" => $arFields['DETAIL_TEXT'],  //Детальное описание
			//Свойства инфоблока - просто перечисляем ID всех нужных свойств
			"EMAIL" => $arFields['PROPERTY_VALUES']['25'],
		);
		//И собственно, собираем все это в метод
		//Который создаст наше почтовое событие
		//С проверкой, что детальное описание (ответ) заполнено
		if($arQuestForm['ANSWER'] != ''){
			CEvent::Send($EVEN_TYPE, $SITE_ID, $arQuestForm );
		}
	}
}
//Константы
require dirname(__FILE__) . '/constants.php';
//Автозагрузка классов
require dirname(__FILE__) . '/autoload.php';
//Обработка событий
require dirname(__FILE__) . '/event_handler.php';
/**
 * обёртка для print_r() и var_dump()
 * @param $val - значение
 * @param string $name - заголовок
 * @param bool $mode - использовать var_dump() или print_r()
 * @param bool $die - использовать die() после вывода
 */
function print_p($val, $name = 'Содержимое переменной', $mode = false, $die = false){
	global $USER;
	if($USER->IsAdmin()){
		echo '<pre>'.(!empty($name) ? $name.': ' : ''); if($mode) { var_dump($val); } else { print_r($val); } echo '</pre>';
		if($die) die;
	}
}
?>
