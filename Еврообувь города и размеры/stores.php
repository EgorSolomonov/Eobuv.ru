<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

use Bitrix\Main\Loader;


function getFlowersOffers($productId)
{
	$result = array();
	if (Loader::includeModule("iblock")) {
		$IBLOCK_ID = 11; // ID Инфоблока
		$ID = $productId; // ID элемента инфоблока
		$arInfo = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);

		if (is_array($arInfo)) {
			$rsOffers = CIBlockElement::GetList(
				false,
				array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_' . $arInfo['SKU_PROPERTY_ID'] => $ID, "ACTIVE" => "Y"), // Фильтрация
				false,
				false,
				array("ID", "IBLOCK_ID", "PROPERTY_RAZMERY", 'CATALOG_GROUP_7') // Свойства, которые нужно получить.
			);
			while ($arOffer = $rsOffers->GetNext()) {
				array_push($result, $arOffer);
			}
		}
	}
	return $result;
}
/*echo "<pre>";
print_r(getFlowersOffers(22179)); 
echo "</pre>";*/
//$_POST['id']=8244;
//$_POST['store_id']=3;
$offers = getFlowersOffers($_POST['id']);

$resStores = \CCatalogStore::GetList(
	[],
	['ID' => $_POST['store_id']],
	false,
	false,
	['*']
);

$arResult['STORES'] = [];

while ($arStore = $resStores->Fetch()) {
	$arResult['STORES'][] = $arStore;
}
//print_r($arResult['STORES']);
?>
<div class="shop__item  is-selected">
	<div class="shop__image"> <img class=" lazyloaded" data-src="<?= $arResult['STORES'][0]['IMAGE_ID'] ?>" alt="" src="<?= $arResult['STORES'][0]['IMAGE_ID'] ?>"> </div>
	<div class="shop__desc">
		<div class="shop__title"><?= $arResult['STORES'][0]['TITLE'] ?></div>


		<div class="product__size">
			<div class="size__wrapper">
				<!-- <div class="size__label">в наличии размеры</div>-->
				<ul class="size__values product__size">
					<? foreach ($offers as $key => $offer) : ?>
						<? $products = $offer['ID'];

						$amount = \Bitrix\Catalog\StoreProductTable::getList([
							'filter' => [
								//"!AMOUNT"=>0,
								"PRODUCT_ID" => $offer['ID'],
								'STORE_ID' => $arResult['STORES'][0]['ID']
							],
							'select' => array('AMOUNT', 'STORE_ID', 'STORE_TITLE' => 'STORE.TITLE'),
						])->fetchAll();

						?>
						<? if ($amount[0]['AMOUNT'] == 0) { ?>
							<li class="size__item is-disabled " data-id="<?= $offer['ID'] ?>" data-price="<?= $offer['PRICE'] ?>">
								<input type="radio" name="value" disabled="disabled" value="<?= $offer['ID'] ?>" id="popup-size-value-box-<?= $offer['ID'] ?>" class="form-control size__input">
								<label for="popup-size-value-box-<?= $offer['ID'] ?>" class="size__label"><?= $offer['PROPERTY_RAZMERY_VALUE'] ?></label>
							</li>
						<? } else { ?>
							<li class="size__item <? if ($_POST['size'] === $offer['PROPERTY_RAZMERY_VALUE']) { ?>selected<? } ?>" data-id="<?= $offer['ID'] ?>" data-price="<?= $offer['PRICE'] ?>">
								<input type="radio" name="value" <? if ($_POST['size'] === $offer['PROPERTY_RAZMERY_VALUE']) { ?>checked<? } ?> value="<?= $offer['ID'] ?>" id="popup-size-value-box-<?= $offer['ID'] ?>" class="form-control size__input">
								<label for="popup-size-value-box-<?= $offer['ID'] ?>" class="size__label"><?= $offer['PROPERTY_RAZMERY_VALUE'] ?></label>
							</li>
						<? } ?>
					<? endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="shop__contacts">
		<? if ($arResult['STORES'][0]['ADDRESS']) { ?>
			<div class="item item--address">
				<div class="item__value">Адрес: <?= $arResult['STORES'][0]['ADDRESS'] ?></div>
			</div>
		<? } ?>
		<? if ($arResult['STORES'][0]['PHONE']) { ?>
			<div class="item item--phone">
				<div class="item__value">Телефон: <?= $arResult['STORES'][0]['PHONE'] ?></div>
			</div>
		<? } ?>
		<div class="item item--worktime">

			<!--<div class="item__label">Режим работы:</div>
		<div class="item__value"> ПН-СБ 10:00-20:00<br>
		  ВС 10:00-20:00</div>
	  </div>-->
		</div>
	</div>