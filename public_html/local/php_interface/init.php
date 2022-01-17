<?
/*���������� ��� ���������� ������� �� ������� �� 5000 ���*/
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
AddEventHandler("sale", "OnSaleStatusOrder", "OrderComplete");
function OrderComplete($orderID, &$arFields)
{
	Loader::includeModule("sale");
	Loader::includeModule("highloadblock");
	if ($arFields == 'F') {
		$order = \Bitrix\Sale\Order::load($orderID); // 123 - ID ������
		$orderUser = $order->getUserId();
		$orderSumm = $order->getPrice();
		$orderDeliveryPrice = $order->getDeliveryPrice();
		$orderPayments = \Bitrix\Sale\PaymentCollection::getList([
			'select' => ['SUM'],
			'filter' => [
				'=ORDER_ID' => $orderID,
				'=PAY_SYSTEM_ID' => 110,//���������� ������
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
/*���������� ��������� �������*/
//������������ ���������� ��������� ��������� � ������ ������� ����� IBQuestForm
AddEventHandler('iblock', 'OnBeforeIBlockElementUpdate', 'IBQuestForm');
//��������� �������
function IBQuestForm(&$arFields)
{
	//������� ����������, ������ ������� �����������:
	$SITE_ID = 's1'; //������������� �����
	$IBLOCK_ID = 4; //������������� ��������� � ���������
	$EVEN_TYPE = 'NEW_REQUEST_ADDED'; // ��� ��������� �������
	if ($arFields['IBLOCK_ID'] == $IBLOCK_ID) {
		//�������� � ������ ��� ������, ������� ����� �������� � ������
		//����������� ��� ���� ��� � �������� �������
		$arQuestForm = array(
			//����������� ���� ���������
			"NAME" => $arFields['NAME'], //�������� �������
			"QUEST" => $arFields['PREVIEW_TEXT'], //�����
			"ANSWER" => $arFields['DETAIL_TEXT'],  //��������� ��������
			//�������� ��������� - ������ ����������� ID ���� ������ �������
			"EMAIL" => $arFields['PROPERTY_VALUES']['25'],
		);
		//� ����������, �������� ��� ��� � �����
		//������� ������� ���� �������� �������
		//� ���������, ��� ��������� �������� (�����) ���������
		if($arQuestForm['ANSWER'] != ''){
			CEvent::Send($EVEN_TYPE, $SITE_ID, $arQuestForm );
		}
	}
}
//���������
require dirname(__FILE__) . '/constants.php';
//������������ �������
require dirname(__FILE__) . '/autoload.php';
//��������� �������
require dirname(__FILE__) . '/event_handler.php';
/**
 * ������ ��� print_r() � var_dump()
 * @param $val - ��������
 * @param string $name - ���������
 * @param bool $mode - ������������ var_dump() ��� print_r()
 * @param bool $die - ������������ die() ����� ������
 */
function print_p($val, $name = '���������� ����������', $mode = false, $die = false){
	global $USER;
	if($USER->IsAdmin()){
		echo '<pre>'.(!empty($name) ? $name.': ' : ''); if($mode) { var_dump($val); } else { print_r($val); } echo '</pre>';
		if($die) die;
	}
}
?>
