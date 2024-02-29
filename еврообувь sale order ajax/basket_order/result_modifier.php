<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);

// В нашем замечательном $arResult данных по примененному купону к большому сожалению нет. Точней есть, но вытащить его практически невозможно.
// Проще написать самому.
$basket  = \Bitrix\Sale\Basket::loadItemsForFUser(
    \CSaleBasket::GetBasketUserID(),
     "s1" 
 );
  $order  = Bitrix\Sale\Order::create( "s1" , \Bitrix\Sale\Fuser::getId());
  $order ->setPersonTypeId( 1 ); // Физическое лицо
  $order ->setBasket( $basket ); // Прикручиваем корзину к заказу
  $discounts  =  $order ->getDiscount(); // Получаем сущность скидок
  $res  =  $discounts ->getApplyResult(); // Принимает результат
  foreach($res['COUPON_LIST'] as $key=>$coupon){ // Проходимся циклом по купон листу и выводим наш купон в $arResult.
    $arResult['ACTIVE_COUPON'] = $coupon;
  }

//Выборка всех активных складов:
// $rsStore = \Bitrix\Catalog\StoreTable::getList(array(
// 	'filter' => array('ACTIVE'>='Y'),
//     'select'=>array('*','UF_*'),
// ));

// while($arStore=$rsStore->fetch()){
// 	$arIBlockListID['STORES'][$arStore['ID']]=$arStore;
// }
// echo '<pre>';
// var_dump($arResult);
// echo '</pre>';

// Получаем все примененные торговые правила.
$discount_basket = \Bitrix\Sale\Basket::loadItemsForFUser(\CSaleBasket::GetBasketUserID(), \Bitrix\Main\Context::getCurrent()->getSite())->getOrderableItems();
$discount = \Bitrix\Sale\Discount::buildFromBasket($discount_basket, new \Bitrix\Sale\Discount\Context\Fuser($discount_basket->getFUserId(true)));
if($discount){
    $discount->calculate();
    $discounts = $discount->getApplyResult();
    // ID торговых правил работы с корзиной.
    $dk_list = array(25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 37, 38, 39);
    foreach($discounts['DISCOUNT_LIST'] as $key => $value){
        if(in_array($value['REAL_DISCOUNT_ID'], $dk_list)){
        $arResult['DK_APPLY'][] = $value;
        }
    }
    foreach($arResult['DK_APPLY'] as $key => $value){
        $arResult['DK_PRICE'] = $arResult['PRICE_WITHOUT_DISCOUNT_VALUE'] * ($value['ACTIONS_DESCR_DATA']['BASKET'][0]['VALUE']/100);
    }
    // Общая скидка минус скидка по ДК. Общая скидка всегда будет больше, поэтому отрицательных значений не будет.
    $arResult['BASKET_PRICE_DISCOUNT_DIFF_VALUE'] = $arResult['BASKET_PRICE_DISCOUNT_DIFF_VALUE'] - $arResult['DK_PRICE'];
}