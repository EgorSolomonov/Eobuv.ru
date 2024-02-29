<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);

CModule::IncludeModule("sale"); 
CModule::IncludeModule("catalog");

$IBLOCK_ID = ID_инфоблока_товаров; 
$ID = $ID_товара; 
$arInfo = GetInfoByProductIBlock($IBLOCK_ID); 
if (is_array($arInfo)) 
{ 
     $rsOffers = CIBlockElement::GetList(array(),array('IBLOCK_ID' => $arInfo['IBLOCK_ID'], 'PROPERTY_'.$arInfo['SKU_PROPERTY_ID'] => $ID)); 
     while ($arOffer = $rsOffers->GetNext()) 
    {  ... // тут ведем обработку } 
}