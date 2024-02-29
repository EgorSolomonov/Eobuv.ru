<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);
// добавляем библиотеку Фэнси бокс
$this->addExternalJS("https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/carousel/carousel.umd.js");
$this->addExternalJS("https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/carousel/carousel.thumbs.umd.js");
$this->addExternalJS("https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js");
$this->addExternalCss("https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/carousel/carousel.css");
$this->addExternalCss("https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/carousel/carousel.thumbs.css");
$this->addExternalCss("https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css");
// добавляем библиотеку Фэнси бокс
if (!empty($arResult['OFFERS']) && !empty($arResult['OFFERS_PROP'])) {
	$selectedOffer = $arResult['JS_OFFERS'][$arResult['OFFER_ID_SELECTED']];
	$offersPropCodes = array_keys($arResult['OFFERS_PROP']);
	$discount = (round($selectedOffer['BASE_PRICE']) - round($selectedOffer['PRICE']));
	$procent = (round($selectedOffer['BASE_PRICE']) - round($selectedOffer['PRICE'])) / round($selectedOffer['BASE_PRICE']);
	$procent = $procent * 100;
}
//print_r($arResult['CACHE_TMPL']);
if (in_array($arResult['ID'],  json_decode($_SESSION['FAVORITES']))) {

	$favoriteAdd = 'active';
}
$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = array(
	'ID' => $mainId,
	'DISCOUNT_PERCENT_ID' => $mainId . '_dsc_pict',
	'STICKER_ID' => $mainId . '_sticker',
	'BIG_SLIDER_ID' => $mainId . '_big_slider',
	'BIG_IMG_CONT_ID' => $mainId . '_bigimg_cont',
	'SLIDER_CONT_ID' => $mainId . '_slider_cont',
	'OLD_PRICE_ID' => $mainId . '_old_price',
	'PRICE_ID' => $mainId . '_price',
	'DISCOUNT_PRICE_ID' => $mainId . '_price_discount',
	'PRICE_TOTAL' => $mainId . '_price_total',
	'SLIDER_CONT_OF_ID' => $mainId . '_slider_cont_',
	'QUANTITY_ID' => $mainId . '_quantity',
	'QUANTITY_DOWN_ID' => $mainId . '_quant_down',
	'QUANTITY_UP_ID' => $mainId . '_quant_up',
	'QUANTITY_MEASURE' => $mainId . '_quant_measure',
	'QUANTITY_LIMIT' => $mainId . '_quant_limit',
	'BUY_LINK' => $mainId . '_buy_link',
	'ADD_BASKET_LINK' => $mainId . '_add_basket_link',
	'BASKET_ACTIONS_ID' => $mainId . '_basket_actions',
	'NOT_AVAILABLE_MESS' => $mainId . '_not_avail',
	'COMPARE_LINK' => $mainId . '_compare_link',
	'TREE_ID' => $mainId . '_skudiv',
	'DISPLAY_PROP_DIV' => $mainId . '_sku_prop',
	'DESCRIPTION_ID' => $mainId . '_description',
	'DISPLAY_MAIN_PROP_DIV' => $mainId . '_main_sku_prop',
	'OFFER_GROUP' => $mainId . '_set_group_',
	'BASKET_PROP_DIV' => $mainId . '_basket_prop',
	'SUBSCRIBE_LINK' => $mainId . '_subscribe',
	'TABS_ID' => $mainId . '_tabs',
	'TAB_CONTAINERS_ID' => $mainId . '_tab_containers',
	'SMALL_CARD_PANEL_ID' => $mainId . '_small_card_panel',
	'TABS_PANEL_ID' => $mainId . '_tabs_panel'
);

if ($haveOffers) {

	$actualItem = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] ?? reset($arResult['OFFERS']);
	$showSliderControls = false;

	foreach ($arResult['OFFERS'] as $offer) {
		if ($offer['MORE_PHOTO_COUNT'] > 1) {
			$showSliderControls = true;
			break;
		}
	}
} else {
	$actualItem = $arResult;
	$showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}

$skuProps = array();
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;
$obName = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);

$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;

use Bitrix\Main\Page\Asset;

$asset = Asset::getInstance();
$price5 = getPrice($arResult['ID'], array(5));
// Меняем логику формирования цены. 
// $price7=getPrice($arResult['ID'],array(7));
$price7 = $arResult['PRODUCT']['USE_OFFERS'] ? $arResult['OFFERS'][0]['ITEM_PRICES'][0]['PRICE'] : $arResult['ITEM_PRICES'][0]['PRICE'];
// $price7=$arResult['OFFERS'][0]['ITEM_PRICES'][0]['PRICE'];
ob_start();

// dump($_GET);
// dump($arResult['DETAIL_PAGE_URL']);
// dump($_COOKIE["prev_page"]);
// dump($arResult);

// Получаем гет параметры из запроса для возврата на предыдущую страницу
$PAGE_NUM = (string)htmlspecialchars(strip_tags($_GET["PAGEN_1"]));
$SECTION_NAME = (string)htmlspecialchars(strip_tags($_GET['SECTION_CATALOG']));

function checkURI($prevPage, $pageNum, $id, $section)
{
	if ($prevPage === "/") {
		return '/';
	} elseif ($prevPage === "/hit/") {
		return '/hit/?PAGEN_1=' . $pageNum . '&el=' . $id;
	} elseif ($prevPage === "/new/") {
		return '/new/?PAGEN_1=' . $pageNum . '&el=' . $id;
	} elseif ($prevPage === "/sale/") {
		return '/sale/?PAGEN_1=' . $pageNum . '&el=' . $id;
	} elseif ($prevPage === "/catalog/") {
		return '/catalog/?PAGEN_1=' . $pageNum . '&el=' . $id;
	} elseif ($prevPage === $section) {
		return $section . '?PAGEN_1=' . $pageNum . '&el=' . $id;
	} else {
		return "javascript:history.back()";
	}
}
?>

<div class="section section--np section--product" id="<?= $itemIds['ID'] ?>" data-bids='#BASKET_IDS#'>
	<div class="section__header">
		<div class="section__title">
			<!-- <a href="<? if ($section != "") : ?>
				<?= $arResult["SECTION"]["SECTION_PAGE_URL"] ?><? if ($PAGE_NUM) : ?>?PAGEN_1=<?= $PAGE_NUM ?>#el<?= $arResult['ID'] ?><? else : ?>#el<?= $arResult['ID'] ?><? endif; ?>
					<? else : ?>
						<?= $arResult["SECTION"]['LIST_PAGE_URL'] ?><? if ($PAGE_NUM) : ?>?PAGEN_1=<?= $PAGE_NUM ?>#el<?= $arResult['ID'] ?><? else : ?>#el<?= $arResult['ID'] ?><? endif; ?>
							<? endif; ?>" class="link__back">
			</a> -->
			<a class="link__back" href="<?php echo checkURI($_COOKIE["prev_page"], $PAGE_NUM, $arResult['ID'], $SECTION_NAME); ?>"></a>
			<h1 class="product-card__name">
				<? // $arResult['NAME'] формируется в result_modifier.php
				?>
				<?= $arResult['NAME'] ?></h1>
		</div>
	</div>
	<div class="section__content">
		<div class="product">
			<div class="product__gallery">
				<?/*
              <div class="gallery__thumbs js-gallery-thumbs">
                <div class="swiper">
                  <div class="swiper-wrapper">
                   <div class="swiper-slide">
                      <div class="item">
						  <img class="item__img lazyload" height="138px" src="<?=$arResult['DETAIL_PICTURE']['SRC'];?>" 
						  data-src="<?=$arResult['DETAIL_PICTURE']['SRC'];?>" alt="">
					  </div>
                    </div>
					 <?foreach ($arResult['PROPERTIES']['MORE_PHOTO']['VALUE'] as $key=>$photo):?>
                    <div class="swiper-slide">
                      <div class="item"><img class="item__img lazyload" height="138px" src="<?=CFile::GetPath($photo);?>" data-src="<?=CFile::GetPath($photo);?>" alt=""></div>
                    </div>
                    <?endforeach;?>
                  </div>
					<div class="swiper__button swiper__button--prev swiper-button-disabled"></div>
					<div class="swiper__button swiper__button--next"></div>
                </div>
              </div>
              */ ?>

				<div class="gallery__preview js-gallery-preview">
					<!-- СТИКЕР публичной части -->
					<? if ($arResult['PROPERTIES']['STICKER']['VALUE']) { ?>
						<div class="item__sticker fast_sticker" style="color:<?= $arResult['PROPERTIES']['STICKER_TEXT_COLOR']['VALUE'] ?>;background-color:<?= $arResult['PROPERTIES']['STICKER_COLOR']['VALUE'] ?>">
							<?= $arResult['PROPERTIES']['STICKER_TEXT']['VALUE'] ?>
						</div>
					<? } ?>


					<!--галерея FANCYBOX -->
					<div class="f-carousel" id="myCarousel_catalog">
						<div class="f-carousel__viewport">
							<div class="f-carousel__track">
								<?
								//ресайз detail фото
								$preview_d = CFile::ResizeImageGet($arResult['DETAIL_PICTURE'], array("width" => '200', "height" => '200'), BX_RESIZE_IMAGE_EXACT, false);
								$photos_d = CFile::ResizeImageGet($arResult['DETAIL_PICTURE'], array("width" => '700', "height" => '700'), BX_RESIZE_IMAGE_EXACT, false);
								?>
								<a href="<?= $arResult['DETAIL_PICTURE']['SRC']; ?>" data-fancybox="gallery_catalog" class="f-carousel__slide" data-thumb-src="<?= $preview_d["src"]; ?>">
									<img data-lazy-src="<?= $photos_d["src"]; ?>" alt="<?= $key; ?>" src="<?= $photos_d["src"]; ?>">
								</a>
								<? foreach ($arResult['PROPERTIES']['MORE_PHOTO']['VALUE'] as $key => $photo) {
									//ресайз фото
									$preview = CFile::ResizeImageGet($photo, array("width" => '200', "height" => '200'), BX_RESIZE_IMAGE_EXACT, false);
									$photos = CFile::ResizeImageGet($photo, array("width" => '700', "height" => '700'), BX_RESIZE_IMAGE_EXACT, false);
								?>
									<a href="<?= CFile::GetPath($photo); ?>" data-fancybox="gallery_catalog" class="f-carousel__slide" data-thumb-src="<?= $preview["src"]; ?>">
										<img data-lazy-src="<?= $photos["src"]; ?>" alt="<?= $key; ?>" src="<?= $photos["src"]; ?>">
									</a>
								<? } ?>
							</div>
						</div>
					</div>
					<?/*
                <div class="swiper">
				 <div class="swiper-wrapper">
                   <div class="item swiper-slide"> <a class="item__link" href="<?=$arResult['DETAIL_PICTURE']['SRC'];?>" data-fancybox="gallery"> <img class="item__img  lazyload" src="<?=$arResult['DETAIL_PICTURE']['SRC'];?>" alt=""  data-src="<?=$arResult['DETAIL_PICTURE']['SRC'];?>" alt=""> </a> </div>
				    <?foreach ($arResult['PROPERTIES']['MORE_PHOTO']['VALUE'] as $key=>$photo):?>
						<div class="item swiper-slide"> 
							<a class="item__link" href="<?=CFile::GetPath($photo);?>" data-fancybox="gallery"> 
								<img class="item__img  lazyload" src="<?=CFile::GetPath($photo);?>" alt="<?=$key;?>"  data-src="<?=CFile::GetPath($photo);?>"> 
							</a> 
						</div>
				  <?endforeach;?>
				 </div>
                  <div class="swiper-pagination"></div>
				  <div class="swiper__buttons swiper__buttons--arrows">
					<div class="swiper__button swiper__button--prev"></div>
					<div class="swiper__button swiper__button--next"></div>
				  </div>
                </div>
                    */ ?>
				</div>
			</div>
			<div class="product__info">
				<? if (!empty($arResult['OFFERS']) && !empty($arResult['OFFERS_PROP'])) { ?>
					<div class="prices__wrap">
						<? if ($price7 > 0 && $price7 != $price5) : ?>
							<div class="prices__info">
								<div class="item">
									<div class="item__name">Скидка</div>
									<div class="item__value"><?= round(((round($price5) - round($price7)) / round($price5)) * 100) ?>%</div>
								</div>
								<div class="item">
									<div class="item__name">Ваша экономия</div>
									<div class="item__value">
										<div class="price"><?= (round($price5) - round($price7)) ?></div>
									</div>
								</div>
							</div>
						<? endif; ?>
						<div class="prices__values">
							<? if ($price7 > 0 && $price7 != $price5) : ?>
								<div class="price price--old"><?= round($price5) ?></div>
								<div class="price price--default"><?= round($price7) ?></div>
							<? else : ?>
								<div class="price price--default"><?= round($price5) ?></div>

							<? endif; ?>

						</div>
					</div>
					<div class="price__split">
						<div class="price__split-tab">
							<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="4.125" cy="4.125" r="4.125" fill="white" />
								<circle cx="13.875" cy="4.125" r="3.625" stroke="white" />
								<circle cx="4.125" cy="13.875" r="4.125" fill="white" />
								<circle cx="13.875" cy="13.875" r="4.125" fill="white" />
							</svg>
							Плати частями
						</div>
						<p class="price__split-text">4 платежа по
							<? if (round($price7, 2) > 0 && round($price7, 2) != round($price5, 2)) : ?>
								<?= round($price7, 2) / 4 ?>
							<? else : ?>
								<?= round($price5, 2) / 4 ?>
							<? endif; ?> р.
						</p>
						<button class="price__split-info" id="priceSplitButton">
							<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="14" cy="14" r="14" fill="#D9D9D9" />
								<path d="M14.214 10.92C13.83 10.92 13.494 10.782 13.206 10.506C12.93 10.218 12.792 9.882 12.792 9.498C12.792 9.114 12.93 8.778 13.206 8.49C13.494 8.202 13.83 8.058 14.214 8.058C14.61 8.058 14.946 8.202 15.222 8.49C15.51 8.778 15.654 9.114 15.654 9.498C15.654 9.882 15.51 10.218 15.222 10.506C14.946 10.782 14.61 10.92 14.214 10.92ZM13.062 21V12H15.384V21H13.062Z" fill="#737373" />
							</svg>
						</button>
					</div>
					<? if ($arResult['PROPERTIES']['SIMILAR_ITEMS']['VALUE']) : ?>
						<div class="product__label">Модель в другом цвете</div>
						<div class="product__color">
							<? foreach ($arResult['PROPERTIES']['SIMILAR_ITEMS']['ITEMS'] as $item) : ?>
								<div class="item">
									<a href="<?= $item['URL'] ?>">
										<img src="<?= $item['PREVIEW_PICTURE'] ?>" class="item__img ls-is-cached lazyloaded" alt="">
									</a>
								</div>
							<? endforeach; ?>
						</div>
					<? endif; ?>
					<div id="<?= $itemIds['TREE_ID'] ?>">
						<? foreach ($arResult['SKU_PROPS'] as $skuProperty) {

							if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']])) continue;
							$i++;
							$propertyId = $skuProperty['ID'];
							$skuProps[] = array(
								'ID' => $propertyId,
								'SHOW_MODE' => $skuProperty['SHOW_MODE'],
								'VALUES' => $skuProperty['VALUES'],
								'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
							);
						?>

							<?
							if ($skuProperty["USER_TYPE"] == "directory") :
							?>
								<div class="product__label">Модель в другом цвете</div>
								<div class="product__color" data-entity="sku-line-block">
									<?
									$list_tp = [];
									foreach ($arResult['PROPERTIES']['LIST_TP']['VALUE'] as $item) {
										$list_tp[] = $item;
									}
									if (count($list_tp) > 0) {
									?>
										<div class="colors-wrap">
											<div>Выберите цвет</div>
											<div class="colors">
												<?
												if ($arResult['PROPERTIES']['COLOR_TP']['VALUE']) { ?>
													<? if ($arResult['PROPERTIES']['COLOR_TP']['VALUE'] == '#000' || $arResult['PROPERTIES']['COLOR_TP']['VALUE'] == '#000000') { ?>
														<a class="tp active" style="background-color: <?= $arResult['PROPERTIES']['COLOR_TP']['VALUE'] ?>; border-color: #c00;"></a>
													<? } else { ?>
														<a class="tp active" style="background-color: <?= $arResult['PROPERTIES']['COLOR_TP']['VALUE'] ?>"></a>
													<? } ?>
													<? }

												$arSelect = array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "PROPERTY_COLOR_TP"); //IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
												$arFilter = array("IBLOCK_ID" => 48, "ID" => $list_tp, "ACTIVE" => "Y");
												$res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
												while ($ob = $res->GetNextElement()) {
													$arFields = $ob->GetFields();
													if (!empty($arFields['PROPERTY_COLOR_TP_VALUE'])) {
													?>
														<a href="<?= $arFields['DETAIL_PAGE_URL'] ?>" class="tp" style="background-color: <?= $arFields['PROPERTY_COLOR_TP_VALUE'] ?>"></a>
												<?     }
												} ?>
											</div>
										</div>
									<? } ?>
									<? foreach ($skuProperty['VALUES'] as &$value) { ?>
										<div class="item js-sku-prop-container" data-treevalue="<?= $propertyId ?>_<?= $value['ID'] ?>" data-onevalue="<?= $value['ID'] ?>">
											<!--<a href="javascript.void();" class="item__link">-->
											<input type="radio" class="js-change-offer" name="PROP_<?= $propertyId ?>" value="<?= $value['ID'] ?>" id="product-card-color-<?= $value['ID'] ?>" <?= ($selectedOffer['TREE']['PROP_' . $propertyId] == $value['ID']) ? ' checked' : '' ?>>
											<label for="PROP_<?= $propertyId ?>" class="size__label"><img data-src="<?= $value['PICT']['SRC'] ?>" alt="" class="lazyload item__img"><label>
													<!--</a>-->
										</div>
									<? } ?>
								</div>

							<? endif; ?>

						<? } ?>
						<div class="product__size ">

							<? foreach ($arResult['SKU_PROPS'] as $skuProperty) {

								if (!isset($arResult['OFFERS_PROP'][$skuProperty['CODE']])) continue;
								$i++;
								$propertyId = $skuProperty['ID'];
								$skuProps[] = array(
									'ID' => $propertyId,
									'SHOW_MODE' => $skuProperty['SHOW_MODE'],
									'VALUES' => $skuProperty['VALUES'],
									'VALUES_COUNT' => $skuProperty['VALUES_COUNT']
								);
							?>
								<? if (empty($skuProperty["USER_TYPE"])) { ?>
									<div class="size__wrapper" data-entity="sku-line-block">
										<!-- <div class="size__header">
								<div class="size__label">Выберите размер</div>
								<div class="size__linkWrap"><a href="" class="size__link" data-popup="sizetable">Таблица размеров</a></div>
							</div> -->
										<ul class="size__values size__values--large product__size">
											<? foreach ($skuProperty['VALUES'] as &$value) { ?>

												<li class="size__item js-sku-prop-container" data-treevalue="<?= $propertyId ?>_<?= $value['ID'] ?>" data-onevalue="<?= $value['ID'] ?>">
													<input type="radio" name="value" value="1" id="size-value-box-<?= $value['ID'] ?>" class="form-control size__input" type="radio" name="PROP_<?= $propertyId ?>" value="<?= $value['ID'] ?>" <?= ($selectedOffer['TREE']['PROP_' . $propertyId] == $value['ID']) ? ' checked' : '' ?>>
													<label for="size-value-box-<?= $value['ID'] ?>" class="size__label"><?= $value['NAME'] ?></label>
												</li>

											<? } ?>

									</div>
								<? } ?>
							<? } ?>
							<div class="product__buttons">
								#BASKET_BTN#
								<div class="control__link control__link--favorites js-favorite <?= $favoriteAdd ?>" data-id='<?= $arResult['ID']; ?>'></div>
							</div>
						</div>
					</div>
				<? } else { ?>
					<!-- Если простой товар -->
					<div class="prices__wrap">
						<? if ($price7 > 0 && $price7 < $price5) : ?>
							<div class="prices__info">
								<div class="item">
									<div class="item__name">Скидка</div>
									<div class="item__value"><?= round(((round($price5) - round($price7)) / round($price5)) * 100) ?>%</div>
								</div>
								<div class="item">
									<div class="item__name">Ваша экономия</div>
									<div class="item__value">
										<div class="price"><?= (round($price5) - round($price7)) ?></div>
									</div>
								</div>
							</div>
						<? else : ?>
							<div class="prices__info">
								<div class="item">
									<div class="item__name">Скидка</div>
									<div class="item__value"><?= round(((round($price7) - round($price5)) / round($price7)) * 100) ?>%</div>
								</div>
								<div class="item">
									<div class="item__name">Ваша экономия</div>
									<div class="item__value">
										<div class="price"><?= (round($price7) - round($price5)) ?></div>
									</div>
								</div>
							</div>
						<? endif; ?>
						<div class="prices__values">
							<? if ($price7 > 0 && $price7 < $price5) : ?>
								<div class="price price--old"><?= round($price5) ?></div>
								<div class="price price--default"><?= round($price7) ?></div>
							<? else : ?>
								<div class="price price--old"><?= round($price7) ?></div>
								<div class="price price--default"><?= round($price5) ?></div>
							<? endif; ?>
						</div>
					</div>
					<div class="price__split">
						<div class="price__split-tab">
							<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="4.125" cy="4.125" r="4.125" fill="white" />
								<circle cx="13.875" cy="4.125" r="3.625" stroke="white" />
								<circle cx="4.125" cy="13.875" r="4.125" fill="white" />
								<circle cx="13.875" cy="13.875" r="4.125" fill="white" />
							</svg>
							Плати частями
						</div>
						<p class="price__split-text">4 платежа по
							<? if (round($price7, 2) > 0 && round($price7, 2) != round($price5, 2)) : ?>
								<?= round($price7, 2) / 4 ?>
							<? else : ?>
								<?= round($price5, 2) / 4 ?>
							<? endif; ?> р.
						</p>
						<button class="price__split-info" id="priceSplitButton">
							<svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="14" cy="14" r="14" fill="#D9D9D9" />
								<path d="M14.214 10.92C13.83 10.92 13.494 10.782 13.206 10.506C12.93 10.218 12.792 9.882 12.792 9.498C12.792 9.114 12.93 8.778 13.206 8.49C13.494 8.202 13.83 8.058 14.214 8.058C14.61 8.058 14.946 8.202 15.222 8.49C15.51 8.778 15.654 9.114 15.654 9.498C15.654 9.882 15.51 10.218 15.222 10.506C14.946 10.782 14.61 10.92 14.214 10.92ZM13.062 21V12H15.384V21H13.062Z" fill="#737373" />
							</svg>
						</button>
					</div>
					<? if ($arResult['PROPERTIES']['SIMILAR_ITEMS']['VALUE']) : ?>
						<div class="product__label">Модель в другом цвете</div>
						<div class="product__color">
							<? foreach ($arResult['PROPERTIES']['SIMILAR_ITEMS']['ITEMS'] as $item) : ?>
								<div class="item">
									<a href="<?= $item['URL'] ?>">
										<img src="<?= $item['PREVIEW_PICTURE'] ?>" class="item__img ls-is-cached lazyloaded" alt="">
									</a>
								</div>
							<? endforeach; ?>
						</div>
					<? endif; ?>
					<div class="product__size ">
						<div class="product__buttons">
							<button type="submit" onclick="ym(20757979,'reachGoal','add2basket'); return true;" class="btn btn--outline js-offer-cart-btn js-add-to-cart" data-id='<?= $arResult['ID']; ?>'>В корзину</button>
							<div class="control__link control__link--favorites js-favorite" data-id='<?= $arResult['ID']; ?>'></div>
						</div>
					</div>
				<? } ?>
				<!-- <div class="product__buyCounter"> данный товар купили 1 раз за 2 недели </div>-->
				<div class="product__fastLinks">
					<div class="item">
						<div class="item__link js_onclick <?= CCatalogSKU::IsExistOffers($arResult['ID'], $arResult['IBLOCK_ID']) ? 'js-offer' : ''; ?>" data-price data-store data-id='<?= $arResult['ID']; ?>'>Купить в один клик</div>
					</div>
					<div class="item">
						<div class="item__link js_store" data-id='<?= $arResult['ID']; ?>'>Наличие в магазине</div>
					</div>
				</div>
				<div class="editor product__benefits">
					<ul class="benefits">
						<li class="item">Доставка 5-9 дней</li>
						<li class="item">Примерка перед покупкой, бесплатный отказ</li>
						<li class="item">14 дней на возврат</li>
					</ul>
					<!--<p>Дополнительная скидка 5% при <a href="">регистрации на сайте</a></p>-->
				</div>
				<div class="product__specs">
					<div class="acc">
						<div class="acc__item is-open">
							<div class="acc__title js-toggle">Характеристики модели</div>
							<div class="acc__content">
								<div class="specs__list">
									<? foreach ($arResult["DISPLAY_PROPERTIES"] as $pid => $arProperty) : ?>
										<div class="item">

											<div class="item__title"><?= $arProperty["NAME"] ?></div>
											<div class="item__value">
												<?= $arProperty["DISPLAY_VALUE"]; ?>
											</div>
										</div>
									<? endforeach; ?>

								</div>
								<p><?= $arResult['DETAIL_TEXT'] ?></p>
							</div>
						</div>
					</div>
				</div>

				<div class="product__reviews" data-popup="reviews">
					<div class="item">Отзывы (<?= count(reviews_list($arResult['ID'])) ?>)</div>
					<div class="item__rating">
						<div class="rating">
							<div class="rating__item <?= intval($arResult['PROPERTIES']['RATING']['VALUE']) >= 1 ? 'is-selected' : '' ?>"></div>
							<div class="rating__item <?= intval($arResult['PROPERTIES']['RATING']['VALUE']) >= 2 ? 'is-selected' : '' ?>"></div>
							<div class="rating__item <?= intval($arResult['PROPERTIES']['RATING']['VALUE']) >= 3 ? 'is-selected' : '' ?>"></div>
							<div class="rating__item <?= intval($arResult['PROPERTIES']['RATING']['VALUE']) >= 4 ? 'is-selected' : '' ?>"></div>
							<div class="rating__item <?= intval($arResult['PROPERTIES']['RATING']['VALUE']) >= 5 ? 'is-selected' : '' ?>"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<? $GLOBALS['filter'] = array('!PREVIEW_PICTURE' => false, "!PROPERTY_MORE_PHOTO" => false, "IBLOCK_SECTION_ID" => $arResult['IBLOCK_SECTION_ID']); ?>
	<!-- вам может понравится -->
	<? $APPLICATION->IncludeComponent(
		"bitrix:catalog.section",
		"element",
		array(
			"ACTION_VARIABLE" => "action",
			"ADD_PICT_PROP" => "MORE_PHOTO",
			"ADD_PROPERTIES_TO_BASKET" => "Y",
			"ADD_SECTIONS_CHAIN" => "N",
			"ADD_TO_BASKET_ACTION" => "ADD",
			"AJAX_MODE" => "N",
			"AJAX_OPTION_ADDITIONAL" => "",
			"AJAX_OPTION_HISTORY" => "N",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_STYLE" => "Y",
			"BACKGROUND_IMAGE" => "UF_BACKGROUND_IMAGE",
			"BASKET_URL" => "/personal/basket.php",
			"BRAND_PROPERTY" => "BRAND_REF",
			"BROWSER_TITLE" => "-",
			"CACHE_FILTER" => "N",
			"CACHE_GROUPS" => "Y",
			"CACHE_TIME" => "36000000",
			"CACHE_TYPE" => "A",
			"COMPATIBLE_MODE" => "Y",
			"CONVERT_CURRENCY" => "Y",
			"CURRENCY_ID" => "RUB",
			"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
			"DATA_LAYER_NAME" => "dataLayer",
			"DETAIL_URL" => "",
			"DISABLE_INIT_JS_IN_COMPONENT" => "N",
			"DISCOUNT_PERCENT_POSITION" => "bottom-right",
			"DISPLAY_BOTTOM_PAGER" => "Y",
			"DISPLAY_TOP_PAGER" => "N",
			"ELEMENT_SORT_FIELD" => "sort",
			"ELEMENT_SORT_FIELD2" => "id",
			"ELEMENT_SORT_ORDER" => "asc",
			"ELEMENT_SORT_ORDER2" => "desc",
			"ENLARGE_PRODUCT" => "PROP",
			"ENLARGE_PROP" => "NEWPRODUCT",
			"FILTER_NAME" => "filter",
			"HIDE_NOT_AVAILABLE" => "N",
			"HIDE_NOT_AVAILABLE_OFFERS" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_TYPE" => "catalog",
			"INCLUDE_SUBSECTIONS" => "Y",
			"LABEL_PROP" => array(
				0 => "NEWPRODUCT",
			),
			"LABEL_PROP_MOBILE" => "",
			"LABEL_PROP_POSITION" => "top-left",
			"LAZY_LOAD" => "Y",
			"LINE_ELEMENT_COUNT" => "3",
			"LOAD_ON_SCROLL" => "N",
			"MESSAGE_404" => "",
			"MESS_BTN_ADD_TO_BASKET" => "В корзину",
			"MESS_BTN_BUY" => "Купить",
			"MESS_BTN_DETAIL" => "Подробнее",
			"MESS_BTN_LAZY_LOAD" => "Показать ещё",
			"MESS_BTN_SUBSCRIBE" => "Подписаться",
			"MESS_NOT_AVAILABLE" => "Нет в наличии",
			"META_DESCRIPTION" => "-",
			"META_KEYWORDS" => "-",
			"OFFERS_CART_PROPERTIES" => array(
				0 => "ARTNUMBER",
				1 => "COLOR_REF",
				2 => "SIZES_SHOES",
				3 => "SIZES_CLOTHES",
			),
			"OFFERS_FIELD_CODE" => array(
				0 => "",
				1 => "",
			),
			"OFFERS_LIMIT" => "5",
			"OFFERS_PROPERTY_CODE" => array(
				0 => "COLOR_REF",
				1 => "SIZES_SHOES",
				2 => "SIZES_CLOTHES",
				3 => "",
			),
			"OFFERS_SORT_FIELD" => "sort",
			"OFFERS_SORT_FIELD2" => "id",
			"OFFERS_SORT_ORDER" => "asc",
			"OFFERS_SORT_ORDER2" => "desc",
			"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
			"OFFER_TREE_PROPS" => array(
				0 => "COLOR_REF",
				1 => "SIZES_SHOES",
				2 => "SIZES_CLOTHES",
			),
			"PAGER_BASE_LINK_ENABLE" => "N",
			"PAGER_DESC_NUMBERING" => "N",
			"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
			"PAGER_SHOW_ALL" => "N",
			"PAGER_SHOW_ALWAYS" => "N",
			"PAGER_TEMPLATE" => ".default",
			"PAGER_TITLE" => "Товары",
			"PAGE_ELEMENT_COUNT" => "20",
			"PARTIAL_PRODUCT_PROPERTIES" => "N",
			'PRICE_CODE' => $arParams['~PRICE_CODE'],
			"PRICE_VAT_INCLUDE" => "Y",
			"PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons,compare",
			"PRODUCT_DISPLAY_MODE" => "Y",
			"PRODUCT_ID_VARIABLE" => "id",
			"PRODUCT_PROPERTIES" => array(
				0 => "NEWPRODUCT",
				1 => "MATERIAL",
			),
			"PRODUCT_PROPS_VARIABLE" => "prop",
			"PRODUCT_QUANTITY_VARIABLE" => "",
			"PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':false},{'VARIANT':'2','BIG_DATA':true}]",
			"PRODUCT_SUBSCRIPTION" => "Y",
			"PROPERTY_CODE" => array(
				0 => "NEWPRODUCT",
				1 => "",
			),
			"PROPERTY_CODE_MOBILE" => "",
			"RCM_PROD_ID" => $_REQUEST["PRODUCT_ID"],
			"RCM_TYPE" => "personal",
			"SECTION_CODE" => "",
			"SECTION_ID" => "",
			"SECTION_ID_VARIABLE" => "SECTION_CODE_PATH",
			"SECTION_URL" => "/catalog/#SECTION_CODE_PATH#/",
			"SECTION_USER_FIELDS" => array(
				0 => "",
				1 => "",
			),
			"SEF_MODE" => "Y",
			"SET_BROWSER_TITLE" => "N",
			"SET_LAST_MODIFIED" => "N",
			"SET_META_DESCRIPTION" => "N",
			"SET_META_KEYWORDS" => "N",
			"SET_STATUS_404" => "N",
			"SET_TITLE" => "N",
			"SHOW_404" => "N",
			"SHOW_ALL_WO_SECTION" => "N",
			"SHOW_CLOSE_POPUP" => "N",
			"SHOW_DISCOUNT_PERCENT" => "Y",
			"SHOW_FROM_SECTION" => "N",
			"SHOW_MAX_QUANTITY" => "N",
			"SHOW_OLD_PRICE" => "N",
			"SHOW_PRICE_COUNT" => "1",
			"SHOW_SLIDER" => "Y",
			"SLIDER_INTERVAL" => "3000",
			"SLIDER_PROGRESS" => "N",
			"TEMPLATE_THEME" => "blue",
			"USE_ENHANCED_ECOMMERCE" => "Y",
			"USE_MAIN_ELEMENT_SECTION" => "N",
			"USE_PRICE_COUNT" => "N",
			"USE_PRODUCT_QUANTITY" => "N",
			"COMPONENT_TEMPLATE" => "element",
			"DISPLAY_COMPARE" => "N",
			"SEF_RULE" => "",
			"SECTION_CODE_PATH" => ""
		),
		false
	); ?>
	<!-- вам может понравится -->
</div>
<!-- end section-product -->

<?
$jsParams = array(
	'CONFIG' => array(),
	'PRODUCT_TYPE' => $arResult['PRODUCT']['TYPE'],
	'VISUAL' => $itemIds,
	'OFFERS' => $arResult['JS_OFFERS'],
	'OFFER_SELECTED' => $arResult['OFFERS_SELECTED'],
	'TREE_PROPS' => $skuProps,
	'MAIN_BLOCK_OFFERS_PROPERTY_CODE' => $arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE']
);

?>
<script>
	let offers = <?= CUtil::PhpToJSObject($arResult['JS_OFFERS'], false, true) ?>;
</script>
<script>
	var <?= $obName ?> = new JCCatalogElement(<?= CUtil::PhpToJSObject($jsParams, false, true) ?>);
</script>
<?
unset($itemIds, $jsParams);
$arResult['CACHE_TMPL'] = @ob_get_clean();

?>

<!-- popup split -->
<div class="popup popup--style2 popup--split" id="popup-split">
	<?
	$reviews = reviews_list($arResult['ID']);
	?>
	<div class="popup__content popup__content--window">
		<div class="popup__close js-close"></div>
		<div class="popup__split--scrollable">
			<div class="popup__split-content">
				<h3 class="popup__title-split popup__title">Плати частями<br>
					Оплатите только 25% стоимости покупки
				</h3>
				<p class="popup__title-description">Оставшиеся три платежа спишутся автоматически с шагом в две недели</p>
				<div class="popup__bar">
					<div class="popup__split-prices">
						<div class="popup__prices-item">
							<p class="popup__item-price">
								<? if (round($price7, 2) > 0 && round($price7, 2) != round($price5, 2)) : ?>
									<?= round($price7, 2) / 4 ?>
								<? else : ?>
									<?= round($price5, 2) / 4 ?>
								<? endif; ?> р.</p>
							<p class="popup__item-description">Сегодня</p>
						</div>
						<div class="popup__prices-item">
							<p class="popup__item-price">
								<? if (round($price7, 2) > 0 && round($price7, 2) != round($price5, 2)) : ?>
									<?= round($price7, 2) / 4 ?>
								<? else : ?>
									<?= round($price5, 2) / 4 ?>
								<? endif; ?> р.</p>
							<p class="popup__item-description">Через 2 недели</p>
						</div>
						<div class="popup__prices-item">
							<p class="popup__item-price">
								<? if (round($price7, 2) > 0 && round($price7, 2) != round($price5, 2)) : ?>
									<?= round($price7, 2) / 4 ?>
								<? else : ?>
									<?= round($price5, 2) / 4 ?>
								<? endif; ?> р.</p>

							<p class="popup__item-description">Через 4 недели</p>
						</div>
						<div class="popup__prices-item">
							<p class="popup__item-price">
								<? if (round($price7, 2) > 0 && round($price7, 2) != round($price5, 2)) : ?>
									<?= round($price7, 2) / 4 ?>
								<? else : ?>
									<?= round($price5, 2) / 4 ?>
								<? endif; ?> р.</p>

							<p class="popup__item-description">Через 6 недель</p>
						</div>
					</div>
					<div class="popup__split-bar">
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="20" cy="20" r="20" fill="white" />
							<path d="M5 20C5 28.2843 11.7157 35 20 35C28.2843 35 35 28.2843 35 20C35 11.7157 28.2843 5 20 5C11.7157 5 5 11.7157 5 20ZM32.75 20C32.75 27.0416 27.0416 32.75 20 32.75C12.9584 32.75 7.25 27.0416 7.25 20C7.25 12.9584 12.9584 7.25 20 7.25C27.0416 7.25 32.75 12.9584 32.75 20Z" fill="#D9D9D9" />
							<path d="M20 6.125C20 5.50368 20.5044 4.99561 21.124 5.04217C23.1023 5.19082 25.0355 5.73082 26.8099 6.6349C28.919 7.70957 30.7439 9.26815 32.1353 11.1832C33.5266 13.0983 34.445 15.3155 34.8153 17.6535C35.1268 19.6204 35.043 21.6258 34.5731 23.5533C34.4259 24.1569 33.7868 24.4796 33.1959 24.2876C32.605 24.0956 32.2868 23.4616 32.426 22.8561C32.791 21.2682 32.849 19.6215 32.593 18.0055C32.2783 16.0182 31.4976 14.1335 30.315 12.5057C29.1323 10.8779 27.5812 9.55313 25.7884 8.63967C24.3305 7.89684 22.7465 7.44317 21.1236 7.2996C20.5046 7.24485 20 6.74632 20 6.125Z" fill="#C10017" />
							<circle cx="20" cy="20" r="9" fill="#C10017" />
						</svg>
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="20" cy="20" r="20" fill="white" />
							<path d="M5 20C5 28.2843 11.7157 35 20 35C28.2843 35 35 28.2843 35 20C35 11.7157 28.2843 5 20 5C11.7157 5 5 11.7157 5 20ZM32.75 20C32.75 27.0416 27.0416 32.75 20 32.75C12.9584 32.75 7.25 27.0416 7.25 20C7.25 12.9584 12.9584 7.25 20 7.25C27.0416 7.25 32.75 12.9584 32.75 20Z" fill="#D9D9D9" />
							<path d="M20 6.125C20 5.50368 20.5044 4.99561 21.124 5.04217C22.7089 5.16127 24.2674 5.53173 25.7403 6.14181C27.5601 6.89563 29.2137 8.00052 30.6066 9.3934C31.9995 10.7863 33.1044 12.4399 33.8582 14.2597C34.612 16.0796 35 18.0302 35 20C35 21.9698 34.612 23.9204 33.8582 25.7403C33.1044 27.5601 31.9995 29.2137 30.6066 30.6066C29.2137 31.9995 27.5601 33.1044 25.7403 33.8582C24.2674 34.4683 22.7089 34.8387 21.124 34.9578C20.5044 35.0044 20 34.4963 20 33.875C20 33.2537 20.5046 32.7552 21.1236 32.7004C22.4128 32.5863 23.6794 32.2764 24.8792 31.7795C26.4261 31.1387 27.8317 30.1996 29.0156 29.0156C30.1996 27.8317 31.1387 26.4261 31.7795 24.8792C32.4202 23.3323 32.75 21.6744 32.75 20C32.75 18.3256 32.4202 16.6677 31.7795 15.1208C31.1387 13.5739 30.1996 12.1683 29.0156 10.9844C27.8317 9.80044 26.4261 8.86128 24.8792 8.22054C23.6794 7.72356 22.4128 7.41366 21.1236 7.2996C20.5046 7.24485 20 6.74632 20 6.125Z" fill="#C10017" />
							<circle cx="20" cy="20" r="9" fill="#C10017" />
						</svg>
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="20" cy="20" r="20" fill="white" />
							<path d="M5 20C5 28.2843 11.7157 35 20 35C28.2843 35 35 28.2843 35 20C35 11.7157 28.2843 5 20 5C11.7157 5 5 11.7157 5 20ZM32.75 20C32.75 27.0416 27.0416 32.75 20 32.75C12.9584 32.75 7.25 27.0416 7.25 20C7.25 12.9584 12.9584 7.25 20 7.25C27.0416 7.25 32.75 12.9584 32.75 20Z" fill="#D9D9D9" />
							<path d="M20 6.125C20 5.50368 20.5044 4.99561 21.124 5.04216C23.497 5.22047 25.7993 5.9614 27.8375 7.2104C30.1962 8.6558 32.1092 10.7253 33.3651 13.1901C34.621 15.655 35.1708 18.4191 34.9538 21.1769C34.7367 23.9347 33.7613 26.5788 32.1353 28.8168C30.5092 31.0548 28.296 32.7996 25.7403 33.8582C23.1845 34.9168 20.3858 35.2481 17.6535 34.8153C14.9212 34.3826 12.3618 33.2027 10.2583 31.4061C8.44058 29.8536 7.02445 27.8929 6.12158 25.6912C5.88585 25.1163 6.21318 24.4796 6.80409 24.2876C7.395 24.0956 8.02508 24.4215 8.2684 24.9932C9.03802 26.8015 10.2175 28.4123 11.7195 29.6952C13.5075 31.2223 15.683 32.2252 18.0055 32.593C20.3279 32.9609 22.7068 32.6793 24.8792 31.7795C27.0516 30.8796 28.9329 29.3966 30.315 27.4943C31.6971 25.592 32.5262 23.3445 32.7107 21.0004C32.8952 18.6562 32.4278 16.3067 31.3603 14.2116C30.2928 12.1165 28.6667 10.3574 26.6619 9.12884C24.9776 8.09672 23.0811 7.47277 21.1236 7.2996C20.5047 7.24485 20 6.74632 20 6.125Z" fill="#C10017" />
							<circle cx="20" cy="20" r="9" fill="#C10017" />
						</svg>
						<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="20" cy="20" r="20" fill="white" />
							<path d="M5 20C5 28.2843 11.7157 35 20 35C28.2843 35 35 28.2843 35 20C35 11.7157 28.2843 5 20 5C11.7157 5 5 11.7157 5 20ZM32.75 20C32.75 27.0416 27.0416 32.75 20 32.75C12.9584 32.75 7.25 27.0416 7.25 20C7.25 12.9584 12.9584 7.25 20 7.25C27.0416 7.25 32.75 12.9584 32.75 20Z" fill="#C10017" />
							<circle cx="20" cy="20" r="9" fill="#C10017" />
						</svg>
					</div>
				</div>
				<ul class="split-list">
					<li class="split-list__item">
						<svg width="13" height="9" viewBox="0 0 13 9" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M12.2803 0.21967C12.5732 0.512563 12.5732 0.987437 12.2803 1.28033L5.28033 8.28033C4.98744 8.57322 4.51256 8.57322 4.21967 8.28033L0.21967 4.28033C-0.0732233 3.98744 -0.0732233 3.51256 0.21967 3.21967C0.512563 2.92678 0.987437 2.92678 1.28033 3.21967L4.75 6.68934L11.2197 0.21967C11.5126 -0.0732233 11.9874 -0.0732233 12.2803 0.21967Z" fill="#777777" />
						</svg>
						Оплачивайте покупки частями — по 25% каждые две недели
					</li>
					<li class="split-list__item">
						<svg width="13" height="9" viewBox="0 0 13 9" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M12.2803 0.21967C12.5732 0.512563 12.5732 0.987437 12.2803 1.28033L5.28033 8.28033C4.98744 8.57322 4.51256 8.57322 4.21967 8.28033L0.21967 4.28033C-0.0732233 3.98744 -0.0732233 3.51256 0.21967 3.21967C0.512563 2.92678 0.987437 2.92678 1.28033 3.21967L4.75 6.68934L11.2197 0.21967C11.5126 -0.0732233 11.9874 -0.0732233 12.2803 0.21967Z" fill="#777777" />
						</svg>
						Никаких дополнительных платежей — обычная оплата картой
					</li>
				</ul>
				<p class="split-text">При покупке этим способом, выберите на странице оформления заказа<br>способ оплаты — Плати частями</p>
			</div>
			<div class="popup__buttons split-buttons">
				<button type="button" class="btn btn--outline split-button" id="splitAccept">Понятно</button>
			</div>
		</div>
	</div>
	<div class="popup__layer js-close"></div>
</div>
<!-- popup split -->

<!-- popup reviews -->
<div class="popup popup--style2 popup--md popup--reviews">
	<?
	$reviews = reviews_list($arResult['ID']);
	?>
	<input type="hidden" name="ITEM_REVIEW" value="<?= $arResult['ID'] ?>">
	<div class="popup__content">
		<div class="popup__close js-close"></div>
		<div class="popup__title">Отзывы о товаре</div>
		<div class="product__reviews">
			<? foreach ($reviews as $review) : ?>
				<div class="item">
					<div class="item__header">
						<div class="item__info">
							<div class="item__author"><?= $review['FIO']; ?></div>
							<div class="item__date"><?= $review['ACTIVE_FROM'] ?></div>
						</div>
						<? if (!empty($review['RATE'])) : ?>
							<div class="item__rating">
								<div class="rating">
									<div class="rating__item <?= intval($review['RATE']) >= 1 ? 'is-selected' : '' ?>"></div>
									<div class="rating__item <?= intval($review['RATE']) >= 2 ? 'is-selected' : '' ?>"></div>
									<div class="rating__item <?= intval($review['RATE']) >= 3 ? 'is-selected' : '' ?>"></div>
									<div class="rating__item <?= intval($review['RATE']) >= 4 ? 'is-selected' : '' ?>"></div>
									<div class="rating__item <?= intval($review['RATE']) >= 5 ? 'is-selected' : '' ?>"></div>
								</div>
							</div>
						<? endif; ?>
					</div>
					<div class="item__content"> <?= $review['PREVIEW_TEXT']; ?></div>
					<? if ($review['PHOTOS']) : ?>
						<div class="item__photos">
							<div class="photo__item img__resize img__resize--4by3"><a href="assets/review_photo.jpg" class="photo__link img__resizeItem" data-fancybox="review-1"><img class="lazyload photo__itemSrc" data-src="assets/review_photo.jpg" alt=""></a></div>
							<div class="photo__item img__resize img__resize--4by3"><a href="assets/review_photo.jpg" class="photo__link img__resizeItem" data-fancybox="review-1"><img class="lazyload photo__itemSrc" data-src="assets/review_photo.jpg" alt=""></a></div>
						</div>
					<? endif; ?>
				</div>
			<? endforeach; ?>

		</div>
		<div class="popup__buttons">
			<button type="button" class="btn btn--outline" data-popup="addreview">Оставить отзыв</button>
		</div>
	</div>
	<div class="popup__layer js-close"></div>
</div>
<!-- popup addreview -->
<div class="popup popup--style2 popup--md popup--addreview">
	<div class="popup__content">
		<div class="popup__close js-close"></div>
		<div class="popup__title">Оставить отзыв</div>
		<div class="popup__state popup__state--a">
			<div class="form form--review">
				<form class="form--review__action">
					<input type="hidden" name="ITEM_REVIEW" value="<?= $arResult['ID']; ?>" required>
					<input type="hidden" name="ITEM_ARTICLE" value="<?= $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'] ?>">
					<input type="hidden" name="REVIEW_RATING" value="5" required>
					<div class="form__group form__group--combined">
						<input type="text" name="REVIEW_NAME" value="" class="input input--outline form__control" required>
						<label for="" class="form__label">Ваше имя</label>
					</div>
					<div class="form__heading">Отзыв о товаре</div>
					<div class="form__group">
						<textarea name="REVIEW_TEXT" class="input input--textarea form__control" required></textarea>
					</div>
					<div class="form__group item__rating">
						<div class="item__rating--title">
							<p>Общая оценка</p>
						</div>
						<div class="rating">
							<div class="rating__item rating__item--1 rating--lg is-selected" data-value="1"></div>
							<div class="rating__item rating__item--2 rating--lg is-selected" data-value="2"></div>
							<div class="rating__item rating__item--3 rating--lg is-selected" data-value="3"></div>
							<div class="rating__item rating__item--4 rating--lg is-selected" data-value="4"></div>
							<div class="rating__item rating__item--5 rating--lg is-selected" data-value="5"></div>
						</div>
					</div>
					<div class="form__group form__group--submit">
						<button type="submit" class="btn">Отправить</button>
						<div class="form__agree"><a href="/privacy-policy.php" data-popup="privacy" target="_blank">Нажимая кнопку «Отправить», я даю согласие на обработку персональных данных.</a></div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="popup__layer js-close"></div>
</div>
<!-- popup addreview END -->
<?

foreach ($arResult['ITEM_LAST_SECTION'] as $element) {
	$res = CIBlockSection::GetByID($element);
	if ($ar_res = $res->GetNext()) {

		$APPLICATION->AddChainItem($ar_res['NAME'], $ar_res['SECTION_PAGE_URL']);
	}
}
$APPLICATION->AddChainItem($arResult['NAME'], $ar_res['DETAIL_PAGE_URL']);
?>