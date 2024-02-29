<?

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
//define("BX_CATALOG_IMPORT_1C_PRESERVE", true);
define("SITE_STYLE_PATH", "/local/styles");
define("SITE_INCLUDE_PATH", "/local/include");
define("SITE_USER_CLASS_PATH", "/local/php_interface/user_class");
define("SITE_AJAX_PATH", "/local/ajax");

define("IBLOCK_SLIDER", 4); //Слайдер
define("IBLOCK_CATALOG", 11); //Каталог
define("IBLOCK_ID_OFFERS", 12); //Каталог
define("IBLOCK_BRANDS", 5); //Бренды
define("IBLOCK_REVIEWS_ITEMS", 6); //Отзывы о товарах
define("SOC", 7); //Отзывы о товарах 
COption::SetOptionString("catalog", "DEFAULT_SKIP_SOURCE_CHECK", "Y");
COption::SetOptionString("sale", "secure_1c_exchange", "N");
Loader::includeModule("intervolga.custom");

Loader::registerAutoLoadClasses(null, [
    '\Helpers\BasketHelper' => '/local/php_interface/helpers/BasketHelper.php',
]);

Loader::registerAutoLoadClasses(null, [
    '\Security\ajax' => SITE_USER_CLASS_PATH . '/Security.php',
    '\SmsHelper\Sms' => SITE_USER_CLASS_PATH . '/SmsHelper.php',
]);

EventManager::getInstance()->addEventHandler("main", "OnBeforeUserUpdate", [
    '\Dev\Utilities',
    'OnBeforeUserAddHandler',
]); // Пример подключения статического метода класса в обработчике

require_once "include/admin_pages/sale_order.php";

function CheckPhoneNumber($phone)
{
    $result = "да";
    //if(!preg_match("/^\+?\d{11,15}$/", $phone)) {

    if (!preg_match("/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/", $phone)) {

        if (isset($this)) $this->error = GetMessage("SMSC_SMS_WRONG_PHONE") . ": $phone";
        $result = "нет";
    }
    return $result;
}

function reviews_list($id)
{
    $reviews = array();
    $key = 0;
    $arFilter = array(
        "IBLOCK_ID" => IBLOCK_REVIEWS_ITEMS,
        "ACTIVE" => "Y",
        "PROPERTY_ITEM" => $id,
    );
    $res = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter, array("NAME", "PROPERTY_FIO", "PREVIEW_TEXT", "ACTIVE_FROM", "PROPERTY_RATE"));
    while ($ar_fields = $res->GetNext()) {
        $reviews[$key]['NAME'] = $ar_fields['NAME'];
        $reviews[$key]['FIO'] = $ar_fields['PROPERTY_FIO_VALUE'];
        $reviews[$key]['PREVIEW_TEXT'] = $ar_fields['PREVIEW_TEXT'];
        $reviews[$key]['ACTIVE_FROM'] = ConvertDateTime($ar_fields['ACTIVE_FROM'], "DD-MM-YYYY");
        $reviews[$key]['RATE'] = $ar_fields['PROPERTY_RATE_VALUE'];
        $key++;
    }
    return $reviews;
}

function soc_list()
{
    $socs = array();
    $key = 0;
    $arFilter = array(
        "IBLOCK_ID" => SOC,
        "ACTIVE" => "Y",
    );
    $res = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter, array("NAME", "PROPERTY_URL", "PROPERTY_SOC_CLASS"));
    while ($ar_fields = $res->GetNext()) {
        $socs[$key]['NAME'] = $ar_fields['NAME'];
        $socs[$key]['URL'] = $ar_fields['PROPERTY_URL_VALUE'];
        $socs[$key]['CLASS'] = $ar_fields['PROPERTY_SOC_CLASS_VALUE'];
        $key++;
    }
    return $socs;
}
function getPrice($productID, $price)
{
    $db_res = CPrice::GetList(
        array(),
        array(
            "PRODUCT_ID" => $productID,  // Получаем ID Товара
            "CATALOG_GROUP_ID" => $price // Получаем ID типа цен из переменной
        )
    );
    if ($ar_res = $db_res->Fetch()) {
        return $ar_res["PRICE"]; // Выводим цену
    }
}

function optimalPrice($productID, $price)
{

    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('catalog');
    global $USER;
    $arrayPrice = array('ID ' => $price);
    $arPrice = [];
    $iblock_id = IBLOCK_ID_CATALOG;
    $quantity = 1;
    $renewal = 'N';
    $arIfSku = CCatalogSKU::getExistOffers($productID, $iblock_id);
    if (is_array($arIfSku) && $arIfSku[$productID] === true) {
        $iblock_info = CCatalogSKU::GetInfoByProductIBlock($iblock_id);

        if (is_array($iblock_info)) {
            $rsOffers = CIBlockElement::GetList(array("PRICE" => "ASC"), array("IBLOCK_ID" => $iblock_info["IBLOCK_ID"], "PROPERTY_" . $iblock_info["SKU_PROPERTY_ID"] => $productID));
            $arOffer = $rsOffers->GetNext();

            $arPrice = CCatalogProduct::GetOptimalPrice($arOffer['ID'], $quantity, $USER->GetUserGroupArray(), $renewal, $arrayPrice);
            if (!$arPrice || count($arPrice) <= 0) {
                if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($arOffer['ID'], $quantity, $USER->GetUserGroupArray())) {
                    $quantity = $nearestQuantity;
                    $arPrice = CCatalogProduct::GetOptimalPrice($arOffer['ID'], $quantity, $USER->GetUserGroupArray(), $renewal, $arrayPrice);
                }
            }
        }
    } else {
        $arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $USER->GetUserGroupArray(), $renewal, $arrayPrice);
        if (!$arPrice || count($arPrice) <= 0) {
            if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, $quantity, $USER->GetUserGroupArray())) {
                $quantity = $nearestQuantity;
                $arPrice = CCatalogProduct::GetOptimalPrice($productID, $quantity, $USER->GetUserGroupArray(), $renewal, $arrayPrice);
            }
        }
    }
    return $arPrice;
}

/*Раздел скидки*/


class AllProductDiscount
{
    /**
     * @return XML_ID|array
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getFull($arrFilter = array(), $arSelect = array())
    {
        if (!Loader::includeModule('sale')) throw new SystemException('Не подключен модуль Sale');

        //Все товары со скидкой!!!
        // Группы пользователей
        global $USER;
        $arUserGroups = $USER->GetUserGroupArray();
        if (!is_array($arUserGroups)) $arUserGroups = array($arUserGroups);
        // Достаем старым методом только ID скидок привязанных к группам пользователей по ограничениям
        $actionsNotTemp = \CSaleDiscount::GetList(array("ID" => "ASC"), array("USER_GROUPS" => $arUserGroups), false, false, array("ID"));
        while ($actionNot = $actionsNotTemp->fetch()) {
            $actionIds[] = $actionNot['ID'];
        }
        $actionIds = array_unique($actionIds);
        sort($actionIds);
        // Подготавливаем необходимые переменные для разборчивости кода
        global $DB;
        $conditionLogic = array('Equal' => '=', 'Not' => '!', 'Great' => '>', 'Less' => '<', 'EqGr' => '>=', 'EqLs' => '<=');
        $arSelect = array_merge(array("ID", "IBLOCK_ID", "XML_ID"), $arSelect);
        $city = 'MSK';
        // Теперь достаем новым методом скидки с условиями. P.S. Старым методом этого делать не нужно из-за очень высокой нагрузки (уже тестировал)
        $actions = \Bitrix\Sale\Internals\DiscountTable::getList(array(
            'select' => array("ID", "ACTIONS_LIST"),
            'filter' => array(
                "ACTIVE" => "Y", "USE_COUPONS" => "N", "DISCOUNT_TYPE" => "P", "LID" => SITE_ID,
                // ВВОДИМ ID НУЖНОГО МАРКЕТИНГОВОГО ПРАВИЛА. Если нужны все остальные валидные, то переменная - $actionIds.
                "ID" => 5,
                array(
                    "LOGIC" => "OR",
                    array(
                        "<=ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL")),
                        ">=ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL"))
                    ),
                    array(
                        "=ACTIVE_FROM" => false,
                        ">=ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL"))
                    ),
                    array(
                        "<=ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL")),
                        "=ACTIVE_TO" => false
                    ),
                    array(
                        "=ACTIVE_FROM" => false,
                        "=ACTIVE_TO" => false
                    ),
                )
            )
        ));
        // Перебираем каждую скидку и подготавливаем условия фильтрации для CIBlockElement::GetList
        while ($arrAction = $actions->fetch()) {
            $arrActions[$arrAction['ID']] = $arrAction;
        }
        foreach ($arrActions as $actionId => $action) {
            $arPredFilter = array_merge(array("ACTIVE_DATE" => "Y", "CAN_BUY" => "Y"), $arrFilter); //Набор предустановленных параметров
            $arFilter = $arPredFilter; //Основной фильтр
            $dopArFilter = $arPredFilter; //Фильтр для доп. запроса
            $dopArFilter["=XML_ID"] = array(); //Пустое значения для первой отработки array_merge
            //Магия генерации фильтра
            foreach ($action['ACTIONS_LIST']['CHILDREN'] as $condition) {
                foreach ($condition['CHILDREN'] as $keyConditionSub => $conditionSub) {
                    $cs = $conditionSub['DATA']['value']; //Значение условия
                    $cls = $conditionLogic[$conditionSub['DATA']['logic']]; //Оператор условия
                    //$arFilter["LOGIC"]=$conditionSub['DATA']['All']?:'AND';
                    $CLASS_ID = explode(':', $conditionSub['CLASS_ID']);

                    if ($CLASS_ID[0] == 'ActSaleSubGrp') {
                        foreach ($conditionSub['CHILDREN'] as $keyConditionSubElem => $conditionSubElem) {
                            $cse = $conditionSubElem['DATA']['value']; //Значение условия
                            $clse = $conditionLogic[$conditionSubElem['DATA']['logic']]; //Оператор условия
                            //$arFilter["LOGIC"]=$conditionSubElem['DATA']['All']?:'AND';
                            $CLASS_ID_EL = explode(':', $conditionSubElem['CLASS_ID']);

                            if ($CLASS_ID_EL[0] == 'CondIBProp') {
                                $arFilter["IBLOCK_ID"] = $CLASS_ID_EL[1];
                                $arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]] = array_merge((array)$arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]], (array)$cse);
                                $arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]] = array_unique($arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBName') {
                                $arFilter[$clse . "NAME"] = array_merge((array)$arFilter[$clse . "NAME"], (array)$cse);
                                $arFilter[$clse . "NAME"] = array_unique($arFilter[$clse . "NAME"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBElement') {
                                $arFilter[$clse . "ID"] = array_merge((array)$arFilter[$clse . "ID"], (array)$cse);
                                $arFilter[$clse . "ID"] = array_unique($arFilter[$clse . "ID"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBTags') {
                                $arFilter[$clse . "TAGS"] = array_merge((array)$arFilter[$clse . "TAGS"], (array)$cse);
                                $arFilter[$clse . "TAGS"] = array_unique($arFilter[$clse . "TAGS"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBSection') {
                                $arFilter[$clse . "SECTION_ID"] = array_merge((array)$arFilter[$clse . "SECTION_ID"], (array)$cse);
                                $arFilter[$clse . "SECTION_ID"] = array_unique($arFilter[$clse . "SECTION_ID"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBXmlID') {
                                $arFilter[$clse . "XML_ID"] = array_merge((array)$arFilter[$clse . "XML_ID"], (array)$cse);
                                $arFilter[$clse . "XML_ID"] = array_unique($arFilter[$clse . "XML_ID"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondBsktAppliedDiscount') { //Условие: Были применены скидки (Y/N)
                                foreach ($arrActions as $tempAction) {
                                    if (($tempAction['SORT'] < $action['SORT'] && $tempAction['PRIORITY'] > $action['PRIORITY'] && $cse == 'N') || ($tempAction['SORT'] > $action['SORT'] && $tempAction['PRIORITY'] < $action['PRIORITY'] && $cse == 'Y')) {
                                        $arFilter = false;
                                        break 4;
                                    }
                                }
                            }
                        }
                    } elseif ($CLASS_ID[0] == 'CondIBProp') {
                        $arFilter["IBLOCK_ID"] = $CLASS_ID[1];
                        $arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]] = array_merge((array)$arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]], (array)$cs);
                        $arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]] = array_unique($arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]]);
                    } elseif ($CLASS_ID[0] == 'CondIBName') {
                        $arFilter[$cls . "NAME"] = array_merge((array)$arFilter[$cls . "NAME"], (array)$cs);
                        $arFilter[$cls . "NAME"] = array_unique($arFilter[$cls . "NAME"]);
                    } elseif ($CLASS_ID[0] == 'CondIBElement') {
                        $arFilter[$cls . "ID"] = array_merge((array)$arFilter[$cls . "ID"], (array)$cs);
                        $arFilter[$cls . "ID"] = array_unique($arFilter[$cls . "ID"]);
                    } elseif ($CLASS_ID[0] == 'CondIBTags') {
                        $arFilter[$cls . "TAGS"] = array_merge((array)$arFilter[$cls . "TAGS"], (array)$cs);
                        $arFilter[$cls . "TAGS"] = array_unique($arFilter[$cls . "TAGS"]);
                    } elseif ($CLASS_ID[0] == 'CondIBSection') {
                        $arFilter[$cls . "SECTION_ID"] = array_merge((array)$arFilter[$cls . "SECTION_ID"], (array)$cs);
                        $arFilter[$cls . "SECTION_ID"] = array_unique($arFilter[$cls . "SECTION_ID"]);
                    } elseif ($CLASS_ID[0] == 'CondIBXmlID') {
                        $arFilter[$cls . "XML_ID"] = array_merge((array)$arFilter[$cls . "XML_ID"], (array)$cs);
                        $arFilter[$cls . "XML_ID"] = array_unique($arFilter[$cls . "XML_ID"]);
                    } elseif ($CLASS_ID[0] == 'CondBsktAppliedDiscount') { //Условие: Были применены скидки (Y/N)
                        foreach ($arrActions as $tempAction) {
                            if (($tempAction['SORT'] < $action['SORT'] && $tempAction['PRIORITY'] > $action['PRIORITY'] && $cs == 'N') || ($tempAction['SORT'] > $action['SORT'] && $tempAction['PRIORITY'] < $action['PRIORITY'] && $cs == 'Y')) {
                                $arFilter = false;
                                break 3;
                            }
                        }
                    }
                }
            }
            if ($arFilter !== false && $arFilter != $arPredFilter) {
                if (!isset($arFilter['=XML_ID'])) {
                    //Делаем запрос по каждому из фильтров, т.к. один фильтр не получится сделать из-за противоречий условий каждой скидки
                    $res = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
                    while ($ob = $res->GetNextElement()) {
                        $arFields = $ob->GetFields();
                        $poductsArray['IDS'][] = $arFields["ID"];
                    }
                } elseif (!empty($arFilter['=XML_ID'])) {
                    //Подготавливаем массив для отдельного запроса
                    $dopArFilter['=XML_ID'] = array_unique(array_merge($arFilter['=XML_ID'], $dopArFilter['=XML_ID']));
                }
            }
        }

        if (isset($dopArFilter) && !empty($dopArFilter['=XML_ID'])) {
            //Делаем отдельный запрос по конкретным XML_ID
            $res = \CIBlockElement::GetList(array(), $dopArFilter, false, array("nTopCount" => count($dopArFilter['=XML_ID'])), $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $poductsArray['IDS'][] = $arFields["ID"];
            }
        }
        $poductsArray['ids'] = array_unique($poductsArray['ids']);

        return $poductsArray;
    }
}

/*Раздел stok*/

class AllStokProductDiscount
{
    /**
     * @return XML_ID|array
     * @throws SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getFull($arrFilter = array(), $arSelect = array())
    {
        if (!Loader::includeModule('sale')) throw new SystemException('Не подключен модуль Sale');

        //Все товары со скидкой!!!
        // Группы пользователей
        global $USER;
        $arUserGroups = $USER->GetUserGroupArray();
        if (!is_array($arUserGroups)) $arUserGroups = array($arUserGroups);
        // Достаем старым методом только ID скидок привязанных к группам пользователей по ограничениям
        $actionsNotTemp = \CSaleDiscount::GetList(array("ID" => "ASC"), array("USER_GROUPS" => $arUserGroups), false, false, array("ID"));
        while ($actionNot = $actionsNotTemp->fetch()) {
            $actionIds[] = $actionNot['ID'];
        }
        $actionIds = array_unique($actionIds);
        sort($actionIds);
        // Подготавливаем необходимые переменные для разборчивости кода
        global $DB;
        $conditionLogic = array('Equal' => '=', 'Not' => '!', 'Great' => '>', 'Less' => '<', 'EqGr' => '>=', 'EqLs' => '<=');
        $arSelect = array_merge(array("ID", "IBLOCK_ID", "XML_ID"), $arSelect);
        $city = 'MSK';
        // Теперь достаем новым методом скидки с условиями. P.S. Старым методом этого делать не нужно из-за очень высокой нагрузки (уже тестировал)
        $actions = \Bitrix\Sale\Internals\DiscountTable::getList(array(
            'select' => array("ID", "ACTIONS_LIST"),
            'filter' => array(
                "ACTIVE" => "Y", "USE_COUPONS" => "N", "DISCOUNT_TYPE" => "P", "LID" => SITE_ID,
                // ВВОДИМ ID НУЖНОГО МАРКЕТИНГОВОГО ПРАВИЛА. Если нужны все остальные валидные, то переменная - $actionIds.
                "ID" => 41,
                array(
                    "LOGIC" => "OR",
                    array(
                        "<=ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL")),
                        ">=ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL"))
                    ),
                    array(
                        "=ACTIVE_FROM" => false,
                        ">=ACTIVE_TO" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL"))
                    ),
                    array(
                        "<=ACTIVE_FROM" => $DB->FormatDate(date("Y-m-d H:i:s"), "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat("FULL")),
                        "=ACTIVE_TO" => false
                    ),
                    array(
                        "=ACTIVE_FROM" => false,
                        "=ACTIVE_TO" => false
                    ),
                )
            )
        ));
        // Перебираем каждую скидку и подготавливаем условия фильтрации для CIBlockElement::GetList
        while ($arrAction = $actions->fetch()) {
            $arrActions[$arrAction['ID']] = $arrAction;
        }
        foreach ($arrActions as $actionId => $action) {
            $arPredFilter = array_merge(array("ACTIVE_DATE" => "Y", "CAN_BUY" => "Y"), $arrFilter); //Набор предустановленных параметров
            $arFilter = $arPredFilter; //Основной фильтр
            $dopArFilter = $arPredFilter; //Фильтр для доп. запроса
            $dopArFilter["=XML_ID"] = array(); //Пустое значения для первой отработки array_merge
            //Магия генерации фильтра
            foreach ($action['ACTIONS_LIST']['CHILDREN'] as $condition) {
                foreach ($condition['CHILDREN'] as $keyConditionSub => $conditionSub) {
                    $cs = $conditionSub['DATA']['value']; //Значение условия
                    $cls = $conditionLogic[$conditionSub['DATA']['logic']]; //Оператор условия
                    //$arFilter["LOGIC"]=$conditionSub['DATA']['All']?:'AND';
                    $CLASS_ID = explode(':', $conditionSub['CLASS_ID']);

                    if ($CLASS_ID[0] == 'ActSaleSubGrp') {
                        foreach ($conditionSub['CHILDREN'] as $keyConditionSubElem => $conditionSubElem) {
                            $cse = $conditionSubElem['DATA']['value']; //Значение условия
                            $clse = $conditionLogic[$conditionSubElem['DATA']['logic']]; //Оператор условия
                            //$arFilter["LOGIC"]=$conditionSubElem['DATA']['All']?:'AND';
                            $CLASS_ID_EL = explode(':', $conditionSubElem['CLASS_ID']);

                            if ($CLASS_ID_EL[0] == 'CondIBProp') {
                                $arFilter["IBLOCK_ID"] = $CLASS_ID_EL[1];
                                $arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]] = array_merge((array)$arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]], (array)$cse);
                                $arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]] = array_unique($arFilter[$clse . "PROPERTY_" . $CLASS_ID_EL[2]]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBName') {
                                $arFilter[$clse . "NAME"] = array_merge((array)$arFilter[$clse . "NAME"], (array)$cse);
                                $arFilter[$clse . "NAME"] = array_unique($arFilter[$clse . "NAME"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBElement') {
                                $arFilter[$clse . "ID"] = array_merge((array)$arFilter[$clse . "ID"], (array)$cse);
                                $arFilter[$clse . "ID"] = array_unique($arFilter[$clse . "ID"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBTags') {
                                $arFilter[$clse . "TAGS"] = array_merge((array)$arFilter[$clse . "TAGS"], (array)$cse);
                                $arFilter[$clse . "TAGS"] = array_unique($arFilter[$clse . "TAGS"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBSection') {
                                $arFilter[$clse . "SECTION_ID"] = array_merge((array)$arFilter[$clse . "SECTION_ID"], (array)$cse);
                                $arFilter[$clse . "SECTION_ID"] = array_unique($arFilter[$clse . "SECTION_ID"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondIBXmlID') {
                                $arFilter[$clse . "XML_ID"] = array_merge((array)$arFilter[$clse . "XML_ID"], (array)$cse);
                                $arFilter[$clse . "XML_ID"] = array_unique($arFilter[$clse . "XML_ID"]);
                            } elseif ($CLASS_ID_EL[0] == 'CondBsktAppliedDiscount') { //Условие: Были применены скидки (Y/N)
                                foreach ($arrActions as $tempAction) {
                                    if (($tempAction['SORT'] < $action['SORT'] && $tempAction['PRIORITY'] > $action['PRIORITY'] && $cse == 'N') || ($tempAction['SORT'] > $action['SORT'] && $tempAction['PRIORITY'] < $action['PRIORITY'] && $cse == 'Y')) {
                                        $arFilter = false;
                                        break 4;
                                    }
                                }
                            }
                        }
                    } elseif ($CLASS_ID[0] == 'CondIBProp') {
                        $arFilter["IBLOCK_ID"] = $CLASS_ID[1];
                        $arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]] = array_merge((array)$arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]], (array)$cs);
                        $arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]] = array_unique($arFilter[$cls . "PROPERTY_" . $CLASS_ID[2]]);
                    } elseif ($CLASS_ID[0] == 'CondIBName') {
                        $arFilter[$cls . "NAME"] = array_merge((array)$arFilter[$cls . "NAME"], (array)$cs);
                        $arFilter[$cls . "NAME"] = array_unique($arFilter[$cls . "NAME"]);
                    } elseif ($CLASS_ID[0] == 'CondIBElement') {
                        $arFilter[$cls . "ID"] = array_merge((array)$arFilter[$cls . "ID"], (array)$cs);
                        $arFilter[$cls . "ID"] = array_unique($arFilter[$cls . "ID"]);
                    } elseif ($CLASS_ID[0] == 'CondIBTags') {
                        $arFilter[$cls . "TAGS"] = array_merge((array)$arFilter[$cls . "TAGS"], (array)$cs);
                        $arFilter[$cls . "TAGS"] = array_unique($arFilter[$cls . "TAGS"]);
                    } elseif ($CLASS_ID[0] == 'CondIBSection') {
                        $arFilter[$cls . "SECTION_ID"] = array_merge((array)$arFilter[$cls . "SECTION_ID"], (array)$cs);
                        $arFilter[$cls . "SECTION_ID"] = array_unique($arFilter[$cls . "SECTION_ID"]);
                    } elseif ($CLASS_ID[0] == 'CondIBXmlID') {
                        $arFilter[$cls . "XML_ID"] = array_merge((array)$arFilter[$cls . "XML_ID"], (array)$cs);
                        $arFilter[$cls . "XML_ID"] = array_unique($arFilter[$cls . "XML_ID"]);
                    } elseif ($CLASS_ID[0] == 'CondBsktAppliedDiscount') { //Условие: Были применены скидки (Y/N)
                        foreach ($arrActions as $tempAction) {
                            if (($tempAction['SORT'] < $action['SORT'] && $tempAction['PRIORITY'] > $action['PRIORITY'] && $cs == 'N') || ($tempAction['SORT'] > $action['SORT'] && $tempAction['PRIORITY'] < $action['PRIORITY'] && $cs == 'Y')) {
                                $arFilter = false;
                                break 3;
                            }
                        }
                    }
                }
            }
            if ($arFilter !== false && $arFilter != $arPredFilter) {
                if (!isset($arFilter['=XML_ID'])) {
                    //Делаем запрос по каждому из фильтров, т.к. один фильтр не получится сделать из-за противоречий условий каждой скидки
                    $res = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
                    while ($ob = $res->GetNextElement()) {
                        $arFields = $ob->GetFields();
                        $poductsArray['IDS'][] = $arFields["ID"];
                    }
                } elseif (!empty($arFilter['=XML_ID'])) {
                    //Подготавливаем массив для отдельного запроса
                    $dopArFilter['=XML_ID'] = array_unique(array_merge($arFilter['=XML_ID'], $dopArFilter['=XML_ID']));
                }
            }
        }

        if (isset($dopArFilter) && !empty($dopArFilter['=XML_ID'])) {
            //Делаем отдельный запрос по конкретным XML_ID
            $res = \CIBlockElement::GetList(array(), $dopArFilter, false, array("nTopCount" => count($dopArFilter['=XML_ID'])), $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $poductsArray['IDS'][] = $arFields["ID"];
            }
        }
        $poductsArray['ids'] = array_unique($poductsArray['ids']);

        return $poductsArray;
    }
}
if (!defined('BX_AGENTS_LOG_FUNCTION')) {
    define('BX_AGENTS_LOG_FUNCTION', 'OlegproAgentsLogFunction');

    function OlegproAgentsLogFunction($arAgent, $point)
    {
        @file_put_contents(
            $_SERVER["DOCUMENT_ROOT"] . '/agents_executions_points.log',
            (PHP_EOL . date('d-m-Y H:i:s') . PHP_EOL .
                print_r($point, 1) . PHP_EOL .
                print_r($arAgent, 1) . PHP_EOL
            ),
            FILE_APPEND
        );
    }
}

AddEventHandler("catalog", "OnCompleteCatalogImport1C", "filter_nophoto", 1);
function filter_nophoto()
{
    CModule::IncludeModule('iblock');
    $id_elements = array();
    $arSelect = array("ID", "IBLOCK_ID");
    $arFilter = array("IBLOCK_ID" => 11, "ACTIVE" => "Y", "PREVIEW_PICTURE" => false, "PROPERTY_MORE_PHOTO" => false);
    $res = CIBlockElement::GetList(array("id"), $arFilter, false, false, $arSelect);
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $id_elements[] = $arFields['ID'];
    }
    if (!empty($id_elements) && count($id_elements) > 0) {
        $el = new CIBlockElement;
        $arLoadProductArray = array(
            'ACTIVE' => 'N'
        );
        foreach ($id_elements as $elId) {
            $inactive_res = $el->Update($elId, $arLoadProductArray);
        }
        if ($inactive_res) {
            // file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/NOACTIVE_PICTURE.txt', date("d.m.Y H:i:s -- ") . 'СРАБОТАЛО!!!'.  "\n");
        }
    }
}
// Генерируем свойство скидки для сортировки по скидке.
AddEventHandler("catalog", "OnCompleteCatalogImport1C", "property_sale", 2);
function property_sale()
{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule("catalog");
    // Получаем все активные элементы каталога.
    $elements = \Bitrix\Iblock\Elements\ElementCatalogTable::getList([
        'select' => ['ID', 'IBLOCK_ID'],
        'filter' => ['=ACTIVE' => 'Y'],
    ])->fetchAll();
    // Проходимся циклом по всем.
    foreach ($elements as $key => $element) {
        // Получаем две цены с ID 5 и 7.
        $prices = \Bitrix\Catalog\PriceTable::getList([
            'select' => ['CATALOG_GROUP_ID', 'PRICE'],
            "filter" => [
                "=PRODUCT_ID" => $element['ID'],
                "CATALOG_GROUP_ID" => array(5, 7)
            ]
        ])->fetchAll();
        foreach ($prices as $price) {
            // Записываем price5
            if ($price['CATALOG_GROUP_ID'] === '5') {
                $price5 = $price['PRICE'];
            }
            // Записываем price7
            elseif ($price['CATALOG_GROUP_ID'] === '7') {
                $price7 = $price['PRICE'];
            }
            // Вычисляем разность скидки между price5 и price7 в процентах
            $element['PERCENT'] = intval(round(((round($price5) - round($price7)) / round($price5)) * 100));
        }
        // Записываем значение процента в свойство.
        CIBlockElement::SetPropertyValuesEx($element['ID'], $element['IBLOCK_ID'], array('SALE' => $element['PERCENT']));
    }
}

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("rating_calculation", "calc_update"));
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", array("rating_calculation", "calc_delete"));

class rating_calculation
{   // Функция расчета циферки в свойство.
    function find_max_rating($array)
    {
        // Ищем количетсво вхождений рейтинга
        $counts = array_count_values($array);
        // Если все рейтинги одинаковые, то просто берем первый ключ это и будет наш рейтинг.
        if (current($counts) === count($array)) {
            $counts = array_key_first($counts); // Просто первый ключ.
        }

        // Если все рейтинги разные (ПОКА ЛОГИКУ ОТСТАВЛЯЮ)
        // elseif(){
        //     $counts = 'ВСЕ РАЗНЫЕ';
        // }

        // Если есть и одинаковые и разные.
        else {
            // Если элементов меньше или равно двум, то сортируем по КЛЮЧУ 
            if (count($array) <= 2) {
                krsort($counts);
                $counts = array_key_first($counts);
            }
            // Если элементов больше двух, то сортируем по ЗНАЧЕНИЮ
            else {
                arsort($counts);
                $counts = array_key_first($counts);
            }
        }
        return $counts;
    }
    function average_rating($array)
    {
        // Логика подсчета всех отзывов c 5 звездами.
        // $counts = array_count_values($array);
        // $counts = $counts[5];


        // Логика подсчета суммы/количество отзывов.
        $sum = array_sum($array);
        $count = count($array);
        $counts = round($sum / $count + $count / 10, 3);
        return $counts;
        // file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/zzz.txt', date("d.m.Y H:i:s -- ") . var_export($counts, true) . "\n", FILE_APPEND);
    }
    // Событие на обновление
    function calc_update(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] === '6') {
            // Получаем свойство из CODE
            $item = CIBlockElement::GetProperty($arFields['IBLOCK_ID'], $arFields['ID'], array("sort" => "asc"), array("CODE" => "ITEM"))->fetch();
            // Найдем все активные элементы с таким же свойством ID товара.
            $res = CIBlockElement::GetList(array("id"), array("IBLOCK_ID" => $arFields['IBLOCK_ID'], "ACTIVE" => "Y", "PROPERTY_ITEM" => $item["VALUE"]), false, false, array("ID", "IBLOCK_ID", "PROPERTY_RATE", "PROPERTY_ITEM"));
            while ($ob = $res->GetNextElement()) {
                // Получим свойства.
                $prop = $ob->GetProperties();
                // Запишем в переменную все рейтинги.
                $elements_rate[] = $prop['RATE']['VALUE'];
            }
            // Пропущу через функцию, чтобы было легче потом менять логику. Пока что логика основывается на количестве вхождений.
            $rating = new rating_calculation();
            if (!empty($elements_rate)) {
                $average_rate = $rating->average_rating($elements_rate);
                $rate = $rating->find_max_rating($elements_rate);
            } else {
                $average_rate = $rating->average_rating(array(0 => 0));
                $rate = $rating->find_max_rating(array(0 => '0'));
            }
            //Ну тут просто стандартно запилю значение в свойство.
            CIBlockElement::SetPropertyValuesEx($item['VALUE'], false, array('RATING' => $rate, 'AVERAGE_RATING' => $average_rate));
        }
        // file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/zzz.txt', date("d.m.Y H:i:s -- ") . 'ID товара - '. var_export($item['VALUE'], true) . ' Рейтинг - ' . var_export($average_rate, true) . "\n", FILE_APPEND);
    }
    // Событие на удаление.
    function calc_delete($arFields)
    {
        $item = CIBlockElement::GetProperty('6', $arFields, array("sort" => "asc"), array("CODE" => "ITEM"))->fetch();
        // т.к. тут у нас событие before, то уберем с выборки ID рейтинга ID которое удаляем, чтобы его значение не учитывалось.
        $res = CIBlockElement::GetList(array("id"), array("IBLOCK_ID" => $arFields['IBLOCK_ID'], "ACTIVE" => "Y", "!ID" => $arFields, "PROPERTY_ITEM" => $item["VALUE"]), false, false, array("ID", "IBLOCK_ID", "PROPERTY_RATE", "PROPERTY_ITEM"));
        while ($ob = $res->GetNextElement()) {
            // Получим свойства.
            $prop = $ob->GetProperties();
            // Запишем в переменную все рейтинги.
            $elements_rate[] = $prop['RATE']['VALUE'];
        }
        // Пропущу через функцию, чтобы было легче потом менять логику. Пока что логика основывается на количестве вхождений.
        $rating = new rating_calculation();
        if (!empty($elements_rate)) {
            $average_rate = $rating->average_rating($elements_rate);
            $rate = $rating->find_max_rating($elements_rate);
        } else {
            $average_rate = $rating->average_rating(array(0 => 0));
            $rating = $rating->find_max_rating(array(0 => '0'));
        }
        //Ну тут просто стандартно запилю значение в свойство. По документации самый оптимизированный метод.
        CIBlockElement::SetPropertyValuesEx($item['VALUE'], false, array('RATING' => $rate, 'AVERAGE_RATING' => $average_rate));
    }
}
// Убираем из поискового индекса товары, которых нет на складах.
AddEventHandler("search", "BeforeIndex", array("BeforeSearch", "BeforeIndexHandler"));
class BeforeSearch
{
    function BeforeIndexHandler($arFields)
    {
        if ($arFields['MODULE_ID'] === 'iblock' && $arFields['PARAM2'] == '11') {
            Loader::includeModule("iblock");
            // ID Элемента
            $item_id = $arFields['ITEM_ID'];
            // Если у элемента есть торговые предложения:
            if (CCatalogSKU::isExistOffers($item_id)) {
                // Получаем ТП по ID товара.
                $arOffers = CCatalogSKU::getOffersList(
                    $item_id,
                    $iblockID = $arFields['IBLOCK_ID'],
                    $skuFilter = array(),
                    $fields = array(),
                    $propertyFilter = array()
                );
                // Проходимся циклом по всем ТП товара.
                foreach ($arOffers as $key => $offers) {
                    // Проверяем наличие каждого ТП на складах. Цикл внутри цикла, но а что поделать.
                    foreach ($offers as $key2 => $offer) {
                        $result = \Bitrix\Catalog\ProductTable::getList(array(
                            'filter' => array('=ID' => $offer['ID']),
                            'select' => array('ID', 'QUANTITY')
                        ));
                        if ($product = $result->fetch()) {
                            $store[$key2] = intval($product['QUANTITY']);
                        }
                    }
                }
            } else {
                $result = \Bitrix\Catalog\ProductTable::getList(array(
                    'filter' => array('=ID' => $item_id),
                    'select' => array('QUANTITY')
                ));
                if ($product = $result->fetch()) {
                    $store = intval($product['QUANTITY']);
                }
            }
            if (is_array($store)) {
                $store = array_sum($store);
            }
            if ($store <= 0) {
                $arFields["TITLE"] = '';
                $arFields["BODY"] = '';
                $arFields["TAGS"] = '';
                // file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/zzz.txt', date("d.m.Y H:i:s -- "). 'ID - ' . var_export($item_id, true) . 'Наличие на складе - "' . var_export($store, true) .'"'. "\n", FILE_APPEND);
            }
            return $arFields;
        }
    }
}

// КАСТОМ тип свойства для инфоблока с маской номера телефона

EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnIBlockPropertyBuildList',
    array('CustomPhoneProperty', 'GetUserTypeDescription')
);

class CustomPhoneProperty
{
    public static function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'CustomPhone',
            'DESCRIPTION' => 'Маска номера телефона',
            'GetPropertyFieldHtml' => array('CustomPhoneProperty', 'GetPropertyFieldHtml'),
            'GetAdminListViewHTML' => array('CustomPhoneProperty', 'GetAdminListViewHTML'),
        );
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        // Значение свойства
        $propertyValue = !empty($value['VALUE']) ? $value['VALUE'] : '';

        // Применяем маску к значению номера телефона
        $maskedValue = self::phone_format($propertyValue);

        // Выводим поле ввода с маской для номера телефона
        $html = '<input type="text" name="' . $strHTMLControlName['VALUE'] . '" value="' . $maskedValue . '" />';

        return $html;
    }

    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        // Здесь можно отформатировать и вывести значение свойства в административном интерфейсе
        return $value['VALUE'];
    }

    private static function phone_format($phone)
    {
        $phone = trim($phone);
        $res = preg_replace(
            array(
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{3})[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{3})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{3})[-|\s]?(\d{3})/',
            ),
            array(
                '+7($2)$3-$4-$5',
                '+7($2)$3-$4-$5',
                '+7($2)$3-$4-$5',
                '+7($2)$3-$4-$5',
                '+7($2)$3-$4',
                '+7($2)$3-$4',
            ),
            $phone
        );
        return $res;
    }
}

function testAgent()
{
    return "testAgent();";
}

function DkUsers()
{
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");
    $dkGroup = [
        10 => '5',
        11 => '6',
        12 => '7',
        13 => '8',
        14 => '9',
        15 => '10',
        16 => '11',
        18 => '12',
        19 => '13',
        20 => '14',
        21 => '15'
    ];
    $csvFile = new CCSVData('R', true);
    $csvFile->LoadFile(\Bitrix\Main\Application::getDocumentRoot() . '/local/users_discount/discount.csv');
    $csvFile->SetDelimiter(';');

    $CUser = new CUser;

    while ($arRes = $csvFile->Fetch()) {
        $PhoneAuth = \Bitrix\Main\UserPhoneAuthTable::getList($parameters = array(
            'filter' => array('PHONE_NUMBER' => \Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arRes[3]))
        ))->fetch();
        $CSVgroup = array_search($arRes[5], $dkGroup);
        if ($PhoneAuth) {
            // Ищем юзеров, чтобы не было ошибок дублирования в БД (избегаем ошибок).

            // Активные группы по ДК.
            $allGroups_dk = \Bitrix\Main\UserGroupTable::getList(array(
                'filter' => ['USER_ID' => $PhoneAuth['USER_ID'], 'GROUP.ACTIVE' => 'Y', 'GROUP_ID' => array_flip($dkGroup)],
                'select' => ['GROUP_ID', 'USER_ID'],
                'order' =>     ['GROUP.C_SORT' => 'ASC'],
            ))->fetchAll();

            // Если есть привязка к группе.
            if ($allGroups_dk) {
                // Проходимся по всем группам ДК.
                foreach ($allGroups_dk as $activeGroup) {
                    // Если привязка по ДК в файле и привязка по ДК у юзера не совпадают.
                    if (intval($activeGroup) !== intval($CSVgroup)) {
                        // Сначала удаляем старую привязку.
                        \Bitrix\Main\UserGroupTable::delete(array('GROUP_ID' => $activeGroup['GROUP_ID'], 'USER_ID' => $activeGroup['USER_ID']));
                        // Потом добавляем новую.
                        \Bitrix\Main\UserGroupTable::add(array('GROUP_ID' => $CSVgroup, 'USER_ID' => $PhoneAuth['USER_ID']));
                    }
                }
            }
            // Иначе просто добавляем группу.
            else {
                \Bitrix\Main\UserGroupTable::add(array('GROUP_ID' => $CSVgroup, 'USER_ID' => $PhoneAuth['USER_ID']));
            }
            // Обновляем пользовательские поля user.
            $update = $CUser->Update($PhoneAuth['USER_ID'], array(
                'UF_DK_WRITEOFF' => new \Bitrix\Main\Type\DateTime($arRes[7]),
                'UF_DK_SUM' => $arRes[6],
                'UF_DK_NUM' => $arRes[0]
            ));
            if (!$update) {
                file_put_contents(\Bitrix\Main\Application::getDocumentRoot() . '/local/users_discount/users_log.txt', date("d.m.Y H:i:s -- ") . 'Ошибка при обновлении полей юзера - ' . var_export($CUser->LAST_ERROR, true) . "\n", FILE_APPEND);
            }
        } else {
            $arRes[4] = trim(str_replace(" ", "", $arRes[4]));
            $randStr = randString(6);
            $newUserID = $CUser->Add(array(
                'ACTIVE' => 'Y', // Активный.
                'EMAIL' => check_email($arRes[4]) ? $arRes[4] : '', // Email. 
                'PASSWORD' => $randStr, // Рандомный пароль из 6 символов.
                'CONFIRM_PASSWORD' => $randStr, // Подтверждение пароля.
                'LOGIN' => preg_replace('/[^0-9]/', '', \Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arRes[3])), // Логин - телефон.
                'PHONE_NUMBER' => \Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arRes[3]), // Телефон для регистрации.
                'PERSONAL_MOBILE' => \Bitrix\Main\UserPhoneAuthTable::normalizePhoneNumber($arRes[3]), // Телефон в данные о профиле.
                'UF_DK_WRITEOFF' => new \Bitrix\Main\Type\DateTime($arRes[7]), // Дата списания.
                'UF_DK_SUM' => $arRes[6], // Остаток кешбека.
                'UF_DK_NUM' => $arRes[0] // Номер ДК.
            ));
            if ($newUserID) {
                \Bitrix\Main\UserGroupTable::add(array('GROUP_ID' => $CSVgroup, 'USER_ID' => $newUserID));
                file_put_contents(\Bitrix\Main\Application::getDocumentRoot() . '/local/users_discount/users_log.txt', date("d.m.Y H:i:s -- ") . 'Юзер успешно создан - ' . var_export($newUserID, true) . "\n", FILE_APPEND);
            } else {
                file_put_contents(\Bitrix\Main\Application::getDocumentRoot() . '/local/users_discount/users_log.txt', date("d.m.Y H:i:s -- ") . 'Ошибка при создании юзера - ' . var_export($CUser->LAST_ERROR, true) . 'Юзер - ' . var_export($arRes, true) . "\n", FILE_APPEND);
            }
        }
    }
    return "DkUsers();";
}
//
//
//
//добавление пункта меню "Вывод стикеров каталога" в админку в раздел "Сервисы"
//
//
//
AddEventHandler("main", "OnBuildGlobalMenu", "ModifiAdminMenu");
function ModifiAdminMenu(&$adminMenu, &$moduleMenu)
{
    $moduleMenu[] = array(
        "parent_menu" => "global_menu_services", // в раздел "Сервис"
        "section" => "",
        "sort"        => 1,                    // сортировка пункта меню - поднимем повыше
        "url"         => "/bitrix/admin/stickers.php?lang=" . LANG,  // ссылка на пункте меню - тут как раз и пишите адрес вашего файла, созданного в /bitrix/admin/
        "text"        => 'Вывод стикеров каталога',
        "title"       => 'Вывод стикеров в публичной части',
        "icon"        => "fileman_sticker_icon", // малая иконка
        "page_icon"   => "form_page_icon", // большая иконка
        "items_id"    => "onsticker",  // идентификатор ветви
        "items"       => array()          // остальные уровни меню
    );
}
function dump($var, $die = false, $all = false)
{
    global $USER;
    if (($USER->GetID() == 3) || ($all == true)) { ?>
        <div style="text-align: left; font-size: 12px; color:#000; ">
            <pre><? var_dump($var) ?></pre>
        </div><br>
<? }
    if ($die) {
        die;
    }
} ?>
<?
function sectionClose($arSect, $sectionId)
{

    // Получаем раздел по его ID
    $r_sect = CIBlockSection::GetByID($sectionId);
    if ($section = $r_sect->GetNext()) {

        // Добавляем ID раздела в массив
        $arSect[] = $section["ID"];
        if ($section["IBLOCK_SECTION_ID"] > 0) {

            // Повторный запуск функции пока не получим все до корня
            $arSect = sectionClose($arSect, $section["IBLOCK_SECTION_ID"]);
        }
    }
    return $arSect;
}

function checkMinForSection($elementId, $sectionsRemain)
{

    // Получаем список разделов, в которых находится элемент
    $rsSections = CIBlockElement::GetElementGroups($elementId, true);
    $arSections = array();
    while ($arSection = $rsSections->Fetch()) {

        // Получаем основной раздел
        $r_sect = CIBlockSection::GetByID($arSection["ID"]);
        if ($section = $r_sect->GetNext()) {
            $arSections[] = $section["ID"]; //ID раздела
            if ($section["IBLOCK_SECTION_ID"] > 0) {

                // Получаем еще раздел верхнего уровня до корня
                $arSections = sectionClose($arSections, $section["IBLOCK_SECTION_ID"]);
            }
        }
    }
    return array_unique($arSections);
}

EventManager::getInstance()->addEventHandler("sale", "OnSaleOrderCanceled", 'onCancelOrder');
function onCancelOrder(\Bitrix\Main\Event $event)
{
    $order = $event->getParameter("ENTITY");
    $isCanseled = $order->isCanceled();
    $orderId = (int)$order->getBasket()->getOrderId();
    if (intval($orderId)  && $isCanseled) {
        \Bitrix\Sale\Internals\OrderTable::Update($orderId, array('STATUS_ID' => 'c'));
    }
}

?>