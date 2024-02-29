<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
// if($GLOBALS['USER']->IsAdmin()) {
//     echo '<pre>'; var_dump($arParams); echo '</pre>';
// }


// Получаю парметры запросов POST
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$request->getPostList()->toArray();
// echo '<pre>';
// var_dump($arResult);
// echo '</pre>';
// dump($arResult['SECTION_PAGE_URL']);

if (!empty($arResult['ITEMS'])) {
?>
	<!-- <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css"> -->

	<div class="row row--gutters-8 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-4 row-cols-1 js-items">
		<?
		foreach ($arResult['ITEMS'] as $key => $arItem) {

			if (in_array($arItem['ID'],  json_decode($_SESSION['FAVORITES']))) {

				$favoriteAdd = 'active';
			} else {
				$favoriteAdd = '';
			}
			$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
			$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
			$strMainID = $this->GetEditAreaId($arItem['ID']);
			$price5 = getPrice($arItem['ID'], array(5));
			$price7 = $arItem['PRODUCT']['USE_OFFERS'] ? $arItem['OFFERS'][0]['ITEM_PRICES'][0]['PRICE'] : $arItem['ITEM_PRICES'][0]['PRICE'];
			//$price7=getPrice($arItem['ID'],array(7));
			if (!empty($arItem['OFFERS'])) {
				$selectedOffer = $arItem['OFFERS'][0];
				$offersPropCodes = array_keys($arItem['OFFERS_PROP']);
				$discount = (round($selectedOffer['BASE_PRICE']) - round($selectedOffer['PRICE']));
			}

			if ($arItem['PROPERTIES']['MORE_PHOTO']['VALUE'][0]) {
				//$arItem['PREVIEW_PICTURE']['SRC']=CFile::GetPath($arItem['PROPERTIES']['MORE_PHOTO']['VALUE'][2]);
				$arItem['DETAIL_PICTURE']['SRC'] = CFile::GetPath($arItem['PROPERTIES']['MORE_PHOTO']['VALUE'][4]);
			}

		?>
			<div class="col js-item" id="<?= $arItem['ID'] ?>">
				<div class="item swiper">
					<? if ($arItem['PROPERTIES']['KOLLEKTSII']['VALUE'] !== 'Осень-Зима 2023-2024' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Сумки' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Косметика' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Сушилки' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Зонты' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Палантины' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Очки' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'ЧНИ' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Ремни' && $arItem['PROPERTIES']['KATEGORIYA_TOVARA']['VALUE'] !== 'Аксессуары' && $arItem['PROPERTIES']['TORGOVAYA_MARKA']['VALUE'] !== 'RIEKER' && $arItem['PROPERTIES']['TORGOVAYA_MARKA']['VALUE'] !== 'REMONTE' && $arItem['PROPERTIES']['TORGOVAYA_MARKA']['VALUE'] !== 'ECCO') : ?>
						<div class="item__sticker">
							<svg width="162" height="30" viewBox="0 0 162 30" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect width="162" height="30" rx="5" fill="#F6F022" />
								<path d="M15.956 19H13.889L10.639 14.736V19H8.845V9.9H10.639V13.917L13.759 9.9H15.826L12.459 14.32L15.956 19ZM16.2426 19V17.336C16.7799 17.414 17.1829 17.3057 17.4516 17.011C17.7203 16.7163 17.8546 16.14 17.8546 15.282V9.9H24.0426V19H22.2616V11.616H19.6356V15.23C19.6356 16.0187 19.5403 16.6817 19.3496 17.219C19.1589 17.7563 18.8946 18.155 18.5566 18.415C18.2273 18.675 17.8719 18.8483 17.4906 18.935C17.1179 19.0217 16.7019 19.0433 16.2426 19ZM30.9948 19V13.449L27.0948 19H25.7298V9.9H27.5238V15.464L31.4238 9.9H32.7888V19H30.9948ZM36.2708 17.284H40.1058V19H34.4768V9.9H40.0408V11.616H36.2708V13.553H39.7158V15.243H36.2708V17.284ZM46.5444 9.9H48.3254V19H46.5444V15.23H43.1644V19H41.3704V9.9H43.1644V13.514H46.5444V9.9ZM56.0349 9.9V11.616H53.5779V19H51.7839V11.616H49.3399V9.9H56.0349ZM60.8319 19.182C59.4626 19.182 58.3316 18.727 57.4389 17.817C56.5462 16.907 56.0999 15.7847 56.0999 14.45C56.0999 13.1067 56.5462 11.9843 57.4389 11.083C58.3316 10.173 59.4626 9.718 60.8319 9.718C61.6552 9.718 62.4136 9.913 63.1069 10.303C63.8089 10.6843 64.3549 11.2043 64.7449 11.863L63.1979 12.76C62.9726 12.3527 62.6519 12.0363 62.2359 11.811C61.8199 11.577 61.3519 11.46 60.8319 11.46C59.9479 11.46 59.2329 11.7373 58.6869 12.292C58.1496 12.8467 57.8809 13.566 57.8809 14.45C57.8809 15.3253 58.1496 16.0403 58.6869 16.595C59.2329 17.1497 59.9479 17.427 60.8319 17.427C61.3519 17.427 61.8199 17.3143 62.2359 17.089C62.6606 16.855 62.9812 16.5387 63.1979 16.14L64.7449 17.037C64.3549 17.6957 63.8132 18.22 63.1199 18.61C62.4266 18.9913 61.6639 19.182 60.8319 19.182ZM72.958 19H70.891L67.641 14.736V19H65.847V9.9H67.641V13.917L70.761 9.9H72.828L69.461 14.32L72.958 19ZM79.11 19V13.449L75.21 19H73.845V9.9H75.639V15.464L79.539 9.9H80.904V19H79.11ZM84.3861 17.284H88.2211V19H82.5921V9.9H88.1561V11.616H84.3861V13.553H87.8311V15.243H84.3861V17.284ZM99.7296 17.284H100.705V20.742H98.9106V19H93.8796V20.742H92.0856V17.284H92.9696C93.4723 16.582 93.7236 15.555 93.7236 14.203V9.9H99.7296V17.284ZM94.8936 17.284H97.9356V11.577H95.5176V14.229C95.5176 15.5463 95.3096 16.5647 94.8936 17.284ZM107.076 9.9H108.857V19H107.076V15.23H103.696V19H101.902V9.9H103.696V13.514H107.076V9.9ZM115.812 19V13.449L111.912 19H110.547V9.9H112.341V15.464L116.241 9.9H117.606V19H115.812ZM122.544 15.88V14.268H127.419V15.88H122.544ZM135.153 13.215C136.028 13.215 136.765 13.4793 137.363 14.008C137.961 14.528 138.26 15.256 138.26 16.192C138.26 17.128 137.943 17.8603 137.311 18.389C136.687 18.9177 135.92 19.182 135.01 19.182C134.273 19.182 133.623 19.0173 133.06 18.688C132.496 18.3587 132.089 17.8733 131.838 17.232L133.372 16.335C133.597 17.063 134.143 17.427 135.01 17.427C135.469 17.427 135.824 17.3187 136.076 17.102C136.336 16.8767 136.466 16.5733 136.466 16.192C136.466 15.8193 136.34 15.5203 136.089 15.295C135.837 15.0697 135.495 14.957 135.062 14.957H132.306L132.67 9.9H137.831V11.577H134.334L134.217 13.215H135.153ZM142.654 19.182C141.518 19.182 140.626 18.753 139.976 17.895C139.334 17.0283 139.014 15.88 139.014 14.45C139.014 13.02 139.334 11.876 139.976 11.018C140.626 10.1513 141.518 9.718 142.654 9.718C143.798 9.718 144.69 10.1513 145.332 11.018C145.973 11.876 146.294 13.02 146.294 14.45C146.294 15.88 145.973 17.0283 145.332 17.895C144.69 18.753 143.798 19.182 142.654 19.182ZM141.276 16.673C141.588 17.1757 142.047 17.427 142.654 17.427C143.26 17.427 143.72 17.1713 144.032 16.66C144.352 16.1487 144.513 15.412 144.513 14.45C144.513 13.4793 144.352 12.7383 144.032 12.227C143.72 11.7157 143.26 11.46 142.654 11.46C142.047 11.46 141.588 11.7157 141.276 12.227C140.964 12.7383 140.808 13.4793 140.808 14.45C140.808 15.4207 140.964 16.1617 141.276 16.673ZM150.835 13.397C150.419 13.8043 149.899 14.008 149.275 14.008C148.651 14.008 148.127 13.8 147.702 13.384C147.286 12.968 147.078 12.461 147.078 11.863C147.078 11.265 147.286 10.758 147.702 10.342C148.127 9.926 148.651 9.718 149.275 9.718C149.899 9.718 150.419 9.926 150.835 10.342C151.251 10.7493 151.459 11.2563 151.459 11.863C151.459 12.4697 151.251 12.981 150.835 13.397ZM148.001 17.622L153.929 11.031L154.722 11.473L148.781 18.077L148.001 17.622ZM148.677 12.461C148.833 12.617 149.028 12.695 149.262 12.695C149.496 12.695 149.691 12.617 149.847 12.461C150.003 12.2963 150.081 12.097 150.081 11.863C150.081 11.629 150.003 11.434 149.847 11.278C149.691 11.122 149.496 11.044 149.262 11.044C149.028 11.044 148.833 11.122 148.677 11.278C148.53 11.434 148.456 11.629 148.456 11.863C148.456 12.097 148.53 12.2963 148.677 12.461ZM155.229 18.532C154.813 18.948 154.293 19.156 153.669 19.156C153.045 19.156 152.525 18.948 152.109 18.532C151.693 18.116 151.485 17.609 151.485 17.011C151.485 16.413 151.693 15.906 152.109 15.49C152.525 15.074 153.045 14.866 153.669 14.866C154.293 14.866 154.813 15.074 155.229 15.49C155.645 15.906 155.853 16.413 155.853 17.011C155.853 17.609 155.645 18.116 155.229 18.532ZM153.084 17.609C153.24 17.765 153.435 17.843 153.669 17.843C153.903 17.843 154.094 17.7607 154.241 17.596C154.397 17.4313 154.475 17.2363 154.475 17.011C154.475 16.777 154.397 16.582 154.241 16.426C154.094 16.2613 153.903 16.179 153.669 16.179C153.435 16.179 153.24 16.2613 153.084 16.426C152.937 16.582 152.863 16.777 152.863 17.011C152.863 17.245 152.937 17.4443 153.084 17.609Z" fill="black" />
							</svg>
						</div>
					<? endif; ?>
					<div class="swiper-wrapper">
						<!-- СТИКЕР публичной части -->
						<? if ($arItem['PROPERTIES']['STICKER']['VALUE']) { ?>
							<div class="item__sticker fast_sticker" style="color:<?= $arItem['PROPERTIES']['STICKER_TEXT_COLOR']['VALUE'] ?>;background-color:<?= $arItem['PROPERTIES']['STICKER_COLOR']['VALUE'] ?>">
								<?= $arItem['PROPERTIES']['STICKER_TEXT']['VALUE'] ?>
							</div>
						<? } ?>
						<? foreach ($arItem['PROPERTIES']['MORE_PHOTO']['VALUE'] as $key => $photo) : ?>
							<? if ($key < 5) : ?>
								<div class="swiper-slide">
									<a href="<?= $arItem['DETAIL_PAGE_URL'] ?>?PAGEN_1=<?= $request['PAGEN_1'] ?>&SECTION_CATALOG=<?= $arResult['SECTION_PAGE_URL'] ?>" class="item__img img__resize img__resize--product">
										<img data-src="<?= CFile::GetPath($photo); ?>" class="lazyload item__imgSrc img__resizeItem" loading="lazy" alt="">
									</a>
								</div>
							<? endif; ?>
						<? endforeach; ?>
					</div>
					<div class="swiper-pagination"></div>
					<div class="product__reviews">
						<div class="item__rating">
							<div class="rating">
								<div class="rating__item <?= intval($arItem['PROPERTIES']['RATING']['VALUE']) >= 1 ? 'is-selected' : '' ?>"></div>
								<div class="rating__item <?= intval($arItem['PROPERTIES']['RATING']['VALUE']) >= 2 ? 'is-selected' : '' ?>"></div>
								<div class="rating__item <?= intval($arItem['PROPERTIES']['RATING']['VALUE']) >= 3 ? 'is-selected' : '' ?>"></div>
								<div class="rating__item <?= intval($arItem['PROPERTIES']['RATING']['VALUE']) >= 4 ? 'is-selected' : '' ?>"></div>
								<div class="rating__item <?= intval($arItem['PROPERTIES']['RATING']['VALUE']) >= 5 ? 'is-selected' : '' ?>"></div>
							</div>
							<div class="rating_count_num">(<?= count(reviews_list($arItem['ID'])) ?>)</div>
						</div>
					</div>
					<div class="item__bottom">
						<div class="item__title">
							<a href="<?= $arItem['DETAIL_PAGE_URL'] ?>?PAGEN_1=<?= $request['PAGEN_1'] ?>&SECTION_CATALOG=<?= $arResult['SECTION_PAGE_URL'] ?>" class="item__link">
								<? //=$arItem['PROPERTIES']['VID_IZDELIYA']['VALUE']
								?> <? //=$v_namerod
									?> <? //=$arItem['PROPERTIES']['TORGOVAYA_MARKA']['VALUE']
										?>
								<?= $arItem['NAME']; ?>
							</a>
						</div>

						<div class="item__price">
							<? if ($price7 > 0 && $price7 != $price5) : ?>
								<div class="price price--old"><?= round($price5) ?></div>
								<div class="price price--default price--discount"><?= round($price7) ?></div>
							<? else : ?>
								<div class="price price--default"><?= round($price5) ?></div>
							<? endif; ?>
						</div>
					</div>
					<div class="controls">
						<div class="i-heart control__item control__item--favorites js-favorite <?= $favoriteAdd ?>" data-id='<?= $arItem['ID']; ?>'></div>
						<!-- <button class="i-cart control__item control__item--cart js-fastview"  data-id='<?= $arItem['ID']; ?>' ></button> -->
					</div>
				</div>
			</div>
		<? } ?>
	</div>
<? } ?>
<?
if ($arParams["DISPLAY_BOTTOM_PAGER"]) {
?><? echo $arResult["NAV_STRING"]; ?><?
									}

										?>
<div style="display: none; max-width: 750px;" id="cart">
</div>
<style>
	#cart {
		border: 1px solid #656565;
		border-radius: 14px;
		padding: 38px 30px;
		font-size: 16px;
		margin: 0 10px;
	}
</style>
<script>
	$(document).on("click", '.js-buy', function() {
		$.fancybox.open({
			src: '#cart',
			type: 'inline'
		});
		let id = $(this).data('id');

		BX.ajax({
			url: '/local/ajax/popitem.php',
			method: 'POST',
			data: {
				'id': id,

			},
			dataType: 'html',
			onsuccess: function(result) {


				//$('#cart').html(result);
			},
			complete: function(result) {


				$('body .product__title').html('123123');
			},
		});

	});
</script>


<script>
	window.onload = async function() {
		// Прокрутка к элементу при возврате по кнопке назад в мобиле
		let elementToScroll = document.getElementById(<?= $_GET["el"] ?>);
		if (elementToScroll) {
			elementToScroll.scrollIntoView(true);
		}
	}
</script>