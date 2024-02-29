<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

use Bitrix\Main\Loader;

$stores = [];

function getFlowersOffers($productId, $size)
{
    $result = array();
    if (Loader::includeModule("iblock")) {
        $IBLOCK_ID = 11; // ID Инфоблока
        $ID = $productId; // ID элемента инфоблока
        $arInfo = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);

        if (is_array($arInfo)) {
            $rsOffers = CIBlockElement::GetList(
                false,
                array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_' . $arInfo['SKU_PROPERTY_ID'] => $ID, 'PROPERTY_RAZMERY_VALUE' => $size, "ACTIVE" => "Y"), // Фильтрация
                false,
                false,
                array("ID", "IBLOCK_ID", "PROPERTY_RAZMERY", 'CATALOG_GROUP_7') // Свойства, которые нужно получить.
            );
            while ($arOffer = $rsOffers->GetNext()) {
                array_push($result, $arOffer['ID']);
            }
        }
    }
    return $result;
}

$offers = getFlowersOffers($_POST['id'], $_POST['size']);

if (!empty($offers)) {
    $amount = \Bitrix\Catalog\StoreProductTable::getList([
        'filter' => [
            ">AMOUNT" => 0,
            "PRODUCT_ID" => $offers
        ],
        'select' => array('STORE_ID'),
    ]);
    while ($arEnum = $amount->fetch()) {
        $stores[] = $arEnum['STORE_ID'];
    }
} else {
    $amount = \Bitrix\Catalog\StoreProductTable::getList([
        'filter' => [
            ">AMOUNT" => 0,
            "PRODUCT_ID" => $_POST['id']
        ],
        'select' => array('STORE_ID'),
    ]);
    while ($arEnum = $amount->fetch()) {
        $stores[] = $arEnum['STORE_ID'];
    }
}

echo json_encode($stores);
