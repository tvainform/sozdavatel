<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "Тестовое задание (сайт 1)");
$APPLICATION->SetPageProperty("description", "Этот сайт является основным из двух, созданных по технологии многосайтовости");
$APPLICATION->SetPageProperty("title", "Тестовое задание (сайт 1)");
$APPLICATION->SetTitle("Тестовое задание (сайт 1)");
?>
<section>
	<h2>Задание 1.1: Вывести на главной странице каждого сайта список новостей из одного общего инфоблока</h2>
<?$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	"flat", 
	array(
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"ADD_SECTIONS_CHAIN" => "N",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"DETAIL_URL" => "",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"FILTER_NAME" => "",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => "1",
		"IBLOCK_TYPE" => "news",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"INCLUDE_SUBSECTIONS" => "Y",
		"MEDIA_PROPERTY" => "",
		"MESSAGE_404" => "",
		"NEWS_COUNT" => "20",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Новости",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"PREVIEW_TRUNCATE_LEN" => "",
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"SEARCH_PAGE" => "/search/",
		"SET_BROWSER_TITLE" => "N",
		"SET_LAST_MODIFIED" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "N",
		"SHOW_404" => "N",
		"SLIDER_PROPERTY" => "",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "DESC",
		"SORT_ORDER2" => "ASC",
		"STRICT_SECTION_CHECK" => "N",
		"TEMPLATE_THEME" => "blue",
		"USE_RATING" => "N",
		"USE_SHARE" => "N",
		"COMPONENT_TEMPLATE" => "flat"
	),
	false
);?>
</section>
	<section>
		<h2>Задание 1.2: "Бонусный счет (Интернет магазин).
			При переводе заказа в статус ""Завершен"", если сумма заказа более 5000 рублей добавлять на бонусный счет пользователя 5% от стоимости товаров, не включая стоимость доставки."</h2>
		<p>Данное задание нельзя реализовать стандартными инструментами "Правил корзины" в битрикс, но можно воспользоваться костылём</p>
		<p>Для этого нам нужно событие смены статуса заказа <b>OnSaleStatusOrder</b> или (вариант в новом ядре) <b>OnSaleStatusOrderChange</b> и метод <b>CSaleUserAccount::UpdateAccount</b> изменяющий сумму на счете пользователя
			Добавим следующий код в init.php, пояснения ниже</p>
		<code>
			<pre>
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
AddEventHandler("sale", "OnSaleStatusOrder", "OrderComplete");
function OrderComplete($orderID, &$arFields)
{
	Loader::includeModule("sale");
	Loader::includeModule("highloadblock");
	if ($arFields == 'F') {
		$order = \Bitrix\Sale\Order::load($orderID);
		$orderUser = $order->getUserId();
		$orderSumm = $order->getPrice();
		$orderDeliveryPrice = $order->getDeliveryPrice();
		$orderPayments = \Bitrix\Sale\PaymentCollection::getList([
			'select' => ['SUM'],
			'filter' => [
				'=ORDER_ID' => $orderID,
				'=PAY_SYSTEM_ID' => 110,//Наложенный платеж
				'=PAID' => 'Y'
			]
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
			</pre>
		</code>
		<ul>
			<li>Подключили модуль sale <code>Loader::includeModule("sale")</code>;</li>
			<li>Проверили, что статус заказ перешел в выполнен <code>if ($arFields == 'F')</code>;</li>
			<li>Зная id заказа, из переменной $orderID, получили ID пользователя <code>$orderUser = $order->getUserId()</code> и сумму заказа <code>$orderSumm = $order->getPrice()</code>;</li>
			<li>Высчитали 5% из стоимости заказа <code>$bonusPercent</code>;</li>
			<li>Методом <code>CSaleUserAccount::UpdateAccount</code> начислил пользователю сумму бонуса, основанием сделали ID заказа;</li>
		</ul>
		<span>Создаем и заполняем HL инфоблок (BonusPrice) с пользовательскими полями:</span>
<ul>
	<li>UF_BONUS_FROM - Стоимость заказа от</li>
	<li>UF_BONUS_TO - Стоимость заказа до</li>
	<li>UF_BONUS_PRICE - Величина процента начисления</li>
</ul>
<ul>
	<li>Подключили модуль Hl инфоблоков <code>Loader::includeModule("highloadblock")</code>;</li>
	<li>Минимальное и максимальное значения, из HLблока загоняем в переменные <code>$priceFrom</code> и <code>$priceTo</code></li>
	<li>Получили значения пользовательских полей инфоблока и отсекли диапазаон подпадающий под условия начисления <code>if (($orderPriceBonus > $priceFrom) && ($orderPriceBonus < $priceTo))</code></li>
</ul>

		<p>В моём случае бонус начисляется только при оплате Наложенным платежом <code>'=PAY_SYSTEM_ID' => 110</code></p>
	</section>
<section>
	<h2>Задание 1.3: FAQ. Создать инфоблок "Вопрос-ответ".
		Поле "Описание для анонса" будет содержать текст вопроса
		Поле "Email" будет содержать Email пользователя.
		При заполнении поля "Детальное описание", пользователь должен получить письмо со своим вопросом и ответом на него.
		Текст письма необходимо иметь возможность менять через почтовый шаблон</h2>
	<ul>
		<li>Создаём новый тип инфоблока FAQ, в нем создаём инфоблок Вопрос-ответ</li>
		<li>Создаём новый тип почтового события <b>NEW_QUESTION_ADDED</b></li>
		<li>Создаём новый почтовый шаблон этого типа</li>
		<li>Добавим следующий код в init.php, пояснения внутри</li>
	</ul>
	<pre><code>
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
		</code></pre>
	<p>Теперь при добавлении ответа к уже существующему вопросу автору будет приходить письмо на указанный в вопросе адрес</p>
</section>
<section>
	<h2>Задание 1.4: "Кастомный компонент.
		Написать собственный компонент для выборки и вывода списка элементов инфоблока.
		Требования:
		1. Вывести по элементам: название, описание элемента, название раздела в котором лежит элемент
		2. Запросы на API должны кэшироваться
		3. Нельзя использовать копию стандартного компонента, необходимо написать свой."</h2>
	<p>Создал свой компонент "list.elements" в пространстве имен "main"</p>
	<p>Данный компонент выводит массив вида:</p>
	<pre><code>
			Template arResult: Array
(
    [ITEMS] => Array
        (
            [0] => Array
                (
                    [ID] => 41
                    [~ID] => 41
                    [NAME] => Ремень Грубая Кожа
                    [~NAME] => Ремень Грубая Кожа
                    [PREVIEW_TEXT] =>
                    [~PREVIEW_TEXT] =>
                    [PREVIEW_TEXT_TYPE] => text
                    [~PREVIEW_TEXT_TYPE] => text
                )

            [1] => Array
                (
                    [ID] => 40
                    [~ID] => 40
                    [NAME] => Ремень Плетение
                    [~NAME] => Ремень Плетение
                    [PREVIEW_TEXT] =>
                    [~PREVIEW_TEXT] =>
                    [PREVIEW_TEXT_TYPE] => text
                    [~PREVIEW_TEXT_TYPE] => text
                    [SECTION] => Ремни Женские
                )

            [2] => Array
                (
                    [ID] => 39
                    [~ID] => 39
                    [NAME] => Ремень Строчка
                    [~NAME] => Ремень Строчка
                    [PREVIEW_TEXT] =>
                    [~PREVIEW_TEXT] =>
                    [PREVIEW_TEXT_TYPE] => text
                    [~PREVIEW_TEXT_TYPE] => text
                    [SECTION] => Ремни Мужские
                )
		</code></pre>
	<p>
		Вывод из шаблона выглядит так:
	</p>
	<? $APPLICATION->IncludeComponent(
	"main:list.elements",
	".default",
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "2",
		"FIELD_CODE" => array(
			0 => "",
			1 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "",
			1 => "",
		),
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3"
	),
	false
);?>
</section>
<section>
	<h2>Задание 1.5: Пользовательское свойство.</h2>
	<p>Необходимо создать кастомное множественное свойство, это свойство должно иметь настройки отображения на странице настроек этого свойства. У каждого поля этого свойства должно быть справа поле поменьше, где указывается индекс сортировки этих полей, а также кнопка добавления нового поля. Все должно быть сделано в соответствии с API 1С-Битрикс.
		Какие должны быть настройки у этого поля:</p>
	<ul>
		<li>1. Возможность указать количество новых полей для нового элемента ИБ.</li>
		<li>2. Возможность включить/выключить вывод поля для указания индекса сортировки.</li>
		<li>3. Ширина поля, по аналогии с обычными нативными текстовыми свойствами ИБ.</li>
	</ul>
		<p>Логика работы кастомного свойства:</p>
	<ul>
		<li>1. При создании элемента ИБ, отображается кастомное свойство в соответствии с указанными настройками этого свойства в настройках ИБ (количество пустых полей по-умолчнию, возможность сортировки).</li>
		<li>2. Сортировка полей осуществляется после сохранения элемента ИБ, т.е. при выводе кастомного свойства или при сохранении кастомного свойства в ИБ.</li>
		<li>3. Если на форме редактирования элемента ИБ будет выводиться два и более подобных свойств, они не должны конфликтовать друг с другом.</li>
		<li>4. При запросе элемента ИБ через API-команды, кастомное свойство должно выводить отсортированный по полю сортировки значений.</li>
	</ul>
<br>
	<p>Создал кастомное множественное свойство "Собственный тип": local->php_interface сам класс (CUserTypeProp.php) находится в папке lib->usertype</p>
	<p>Пользовательский тип работает нормально, но не могу отсортировать список по полю сортировка, не хватает времени разобраться</p>
	<p>С работой можно ознакомиться в инфоблоке Custom</p>
</section>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>