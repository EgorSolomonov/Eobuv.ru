$(document).ready(function () {
  $(document).on("click", ".js_onclick", function () {
    let $top = $(document).scrollTop();
    $('.onclick__form input[name="id"]').val($(this).attr("data-id"));
    $('.onclick__form input[name="price"]').val($(this).attr("data-store"));
    $('.onclick__form input[name="store"]').val($(this).attr("data-market"));

    if ($(this).hasClass("js-offer")) {
      $(".popup--fastbuy").addClass("is-shown");
      $(".popup--fastbuy").css("top", $top);
      $("body").addClass("js-popup-show");
      $.ajax({
        url: "/local/ajax/get_size.php" /* Куда пойдет запрос */,
        method: "post" /* Метод передачи (post или get) */,
        dataType: "html" /* Тип данных в ответе (xml, json, script, html). */,
        data: {
          id: $(this).data("id"),
        } /* Параметры передаваемые в запросе. */,
        success: function (data) {
          /* функция которая будет выполнена после успешного запроса.  */
          $(".popup--fastbuy .size__wrapper").html(
            data
          ); /* В переменной data содержится ответ от index.php. */
        },
      });
    } else {
      $(".popup--user").addClass("is-shown");
      $(".popup--user").css("top", $top);
      $("body").addClass("js-popup-show");
    }
  });

  $(document).on("click", ".js-add-to-cart", function (e) {
    if ($(this).attr("href") !== "/personal/order/make/") {
      e.preventDefault();
      let btn = $(this);
      // Для цели в Яндекс метрику. Чтобы не юзать onclick в component_epilog из-за кеша оно неправильно рендерится
      // onclick не появляется в торговых предложениях, если какой-либо товар уже есть в корзине.
      ym(20757979, "reachGoal", "add2basket");
      $.ajax({
        url: "/local/ajax/addbasket.php",
        method: "post",
        dataType: "html",
        data: {
          id: btn.data("id"),
          price: $(".product .prices__values .price--default").text(),
        },
        success: function (data) {
          BX.onCustomEvent("OnBasketChange"); // Обновляем корзину
          btn.attr("href", "/personal/order/make/");
          btn.text("Перейти в корзину");
          btn.addClass("active");
        },
      });
    }
  });

  $(document).on("click", ".js_store", function (e) {
    e.preventDefault();
    let selectedSize =
      document.querySelector(".selected").lastElementChild.innerHTML;
    // let size = $(".product .product__size").html();
    // $(".popup--fastbuy .size__wrapper").html(size);
    $.ajax({
      url: "/local/ajax/get_store.php",
      method: "post",
      dataType: "json",
      data: {
        id: $(this).data("id"),
        size: selectedSize,
      },
      success: function (data) {
        // console.log(data);
        $("select.js-choose-shop .choice_shop").css("display", "block");
        for (var key in data) {
          $("select.js-choose-shop option[value=" + data[key] + "]").addClass(
            "active"
          );
        }
        $("select.js-choose-shop option").not(".active").hide();
        $("select.js-choose-shop option.active").show();
        //$('.form--review__action').html('<div style="font-size: 14px; color: red;">Отзыв успешно добавлен. Скоро он появиться на сайте.</div>')
      },
    });
  });
  /*отзыв*/
  $(document).on("submit", ".form--review__action", function (e) {
    e.preventDefault();
    let $form = $(this);
    let $rating = $(this).find('input[name="REVIEW_RATING"]');
    let $item = $(this).find('input[name="ITEM_REVIEW"]');
    var ajaxDataSend = $form.serialize();
    // Проверим
    if ($item.val() === "") {
      alert("ID товара не попало в форму!!!");
      return false;
    }
    if ($rating.val() === "") {
      if ($(this).find(".item__rating .error")) {
        $(this).find(".item__rating .error").remove();
        $(this)
          .find(".item__rating")
          .append(
            `<div class="error" style="color:red;font-size:12px;width:100%">Пожалуйста, введите рейтинг.</div>`
          );
      } else {
        $(this)
          .find(".item__rating")
          .append(
            `<div class="error" style="color:red;font-size:12px;width:100%">Пожалуйста, введите рейтинг.</div>`
          );
      }
      return false;
    }
    $.ajax({
      url: "/local/ajax/reviews.php",
      method: "post",
      data: ajaxDataSend,
      success: function (data) {
        $(".form--review__action").html(data);
      },
      error: function () {
        alert("Ошибка связи с обработчиком!");
      },
    });
  });
  /*отзыв*/
});

function changeInputChecked(container, checked = true) {
  if (checked) {
    $(container).find("input.js-change-offer").prop("checked", "checked");
  } else {
    $(container).find("input.js-change-offer").prop("checked", false);
  }
}

const splitAccept = document.querySelector("#splitAccept");
const splitButton = document.querySelector("#priceSplitButton");
const splitPopup = document.querySelector("#popup-split");

splitButton.addEventListener("click", function () {
  // Добавляем класс к элементу
  splitPopup.classList.add("is-shown");
  document.body.classList.add("js-popup-show");
});

splitAccept.addEventListener("click", function () {
  // Добавляем класс к элементу
  splitPopup.classList.remove("is-shown");
  document.body.classList.remove("js-popup-show");
});

(function (window) {
  "use strict";

  if (window.JCCatalogElement) return;

  window.JCCatalogElement = function (arParams) {
    this.config = {
      showAbsent: false,
    };
    this.errorCode = 0;

    if (typeof arParams === "object") {
      this.params = arParams;
      this.initConfig();
      this.initOffersData();
    }

    if (this.errorCode === 0) {
      BX.ready(BX.delegate(this.init, this));
    }

    this.params = {};
  };

  window.JCCatalogElement.prototype = {
    getEntity: function (parent, entity, additionalFilter) {
      if (!parent || !entity) return null;

      additionalFilter = additionalFilter || "";

      return parent.querySelector(
        additionalFilter + '[data-entity="' + entity + '"]'
      );
    },

    getEntities: function (parent, entity, additionalFilter) {
      if (!parent || !entity) return { length: 0 };

      additionalFilter = additionalFilter || "";

      return parent.querySelectorAll(
        additionalFilter + '[data-entity="' + entity + '"]'
      );
    },

    setOffer: function (offerNum) {
      this.offerNum = parseInt(offerNum);
      this.setCurrent();
    },

    init: function () {
      var i = 0,
        treeItems = null;

      if (this.productType === 3) {
        if (this.visual.TREE_ID) {
          this.obTree = BX(this.visual.TREE_ID);
          if (!this.obTree) {
            this.errorCode = -256;
          }
        }
      }

      if (this.errorCode === 0) {
        switch (this.productType) {
          case 0: // no catalog
          case 1: // product
          case 2: // set
          case 3: // sku
            treeItems = this.obTree.querySelectorAll(".js-sku-prop-container");
            for (i = 0; i < treeItems.length; i++) {
              BX.bind(
                treeItems[i],
                "click",
                BX.delegate(this.selectOfferProp, this)
              );
            }
            this.setCurrent();
            break;
        }
      }
    },

    initConfig: function () {
      if (this.params.PRODUCT_TYPE) {
        this.productType = parseInt(this.params.PRODUCT_TYPE, 10);
      }
      if (this.params.MAIN_BLOCK_OFFERS_PROPERTY_CODE) {
        this.mainBlockOffersPropertyCode = BX.util.array_keys(
          this.params.MAIN_BLOCK_OFFERS_PROPERTY_CODE
        );
      }
      this.visual = this.params.VISUAL;
    },

    initOffersData: function () {
      if (this.params.OFFERS && BX.type.isArray(this.params.OFFERS)) {
        this.offers = this.params.OFFERS;
        this.offerNum = 0;

        if (this.params.OFFER_SELECTED) {
          this.offerNum = parseInt(this.params.OFFER_SELECTED, 10) || 0;
        }

        if (this.params.TREE_PROPS) {
          this.treeProps = this.params.TREE_PROPS;
        }
      } else {
        this.errorCode = -1;
      }
    },

    selectOfferProp: function () {
      var i = 0,
        strTreeValue = "",
        arTreeItem = [],
        rowItems = null,
        target = BX.proxy_context;

      if (target && target.hasAttribute("data-treevalue")) {
        if (BX.hasClass(target, "selected")) return;

        if (typeof document.activeElement === "object") {
          document.activeElement.blur();
        }

        strTreeValue = target.getAttribute("data-treevalue");
        arTreeItem = strTreeValue.split("_");
        this.searchOfferPropIndex(arTreeItem[0], arTreeItem[1]);
        rowItems = BX.findChildren(
          target.parentNode,
          { class: "js-sku-prop-container" },
          false
        );

        if (rowItems && rowItems.length) {
          for (i = 0; i < rowItems.length; i++) {
            BX.removeClass(rowItems[i], "selected");
            changeInputChecked(rowItems[i], false);
          }
        }

        BX.addClass(target, "selected");
        changeInputChecked(rowItems[i], true);
      }
    },

    searchOfferPropIndex: function (strPropID, strPropValue) {
      var strName = "",
        arShowValues = false,
        arCanBuyValues = [],
        allValues = [],
        index = -1,
        i,
        j,
        arFilter = {},
        tmpFilter = [];

      for (i = 0; i < this.treeProps.length; i++) {
        if (this.treeProps[i].ID === strPropID) {
          index = i;
          break;
        }
      }

      if (index > -1) {
        for (i = 0; i < index; i++) {
          strName = "PROP_" + this.treeProps[i].ID;
          arFilter[strName] = this.selectedValues[strName];
        }

        strName = "PROP_" + this.treeProps[index].ID;
        arFilter[strName] = strPropValue;

        for (i = index + 1; i < this.treeProps.length; i++) {
          strName = "PROP_" + this.treeProps[i].ID;
          arShowValues = this.getRowValues(arFilter, strName);

          if (!arShowValues) break;

          allValues = [];

          if (this.config.showAbsent) {
            arCanBuyValues = [];
            tmpFilter = [];
            tmpFilter = BX.clone(arFilter, true);

            for (j = 0; j < arShowValues.length; j++) {
              tmpFilter[strName] = arShowValues[j];
              allValues[allValues.length] = arShowValues[j];
              if (this.getCanBuy(tmpFilter))
                arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
            }
          } else {
            arCanBuyValues = arShowValues;
          }

          if (
            this.selectedValues[strName] &&
            BX.util.in_array(this.selectedValues[strName], arCanBuyValues)
          ) {
            arFilter[strName] = this.selectedValues[strName];
          } else {
            if (this.config.showAbsent) {
              arFilter[strName] = arCanBuyValues.length
                ? arCanBuyValues[0]
                : allValues[0];
            } else {
              arFilter[strName] = arCanBuyValues[0];
            }
          }

          this.updateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
        }

        this.selectedValues = arFilter;
        this.changeInfo();
      }
    },

    updateRow: function (intNumber, activeId, showId, canBuyId) {
      var i = 0,
        value = "",
        isCurrent = false,
        rowItems = null;

      var lineContainer = this.getEntities(this.obTree, "sku-line-block");

      if (intNumber > -1 && intNumber < lineContainer.length) {
        rowItems = lineContainer[intNumber].querySelectorAll(
          ".js-sku-prop-container"
        );
        for (i = 0; i < rowItems.length; i++) {
          value = rowItems[i].getAttribute("data-onevalue");
          isCurrent = value === activeId;

          if (isCurrent) {
            BX.addClass(rowItems[i], "selected");
            changeInputChecked(rowItems[i], true);
          } else {
            BX.removeClass(rowItems[i], "selected");
            changeInputChecked(rowItems[i], false);
          }

          if (BX.util.in_array(value, canBuyId)) {
            BX.removeClass(rowItems[i], "notallowed");
          } else {
            BX.addClass(rowItems[i], "notallowed");
          }

          rowItems[i].style.display = BX.util.in_array(value, showId)
            ? ""
            : "none";

          if (isCurrent) {
            lineContainer[intNumber].style.display =
              value == 0 && canBuyId.length == 1 ? "none" : "";
          }
        }

        setTimeout(function () {
          offers.forEach(function (el) {
            var i = 0,
              main_checked = false;

            for (var key in el.TREE) {
              var val = el.TREE[key];

              if (i === 0) {
                main_checked =
                  $(
                    '.js-main-offer[name="' +
                      key +
                      '"][value="' +
                      val +
                      '"]:checked'
                  ).length > 0;
                i++;
                continue;
              }

              if (!main_checked) {
                continue;
              }

              var color_itm = $(
                '.js-change-offer[name="' + key + '"][value="' + val + '"]'
              ).closest(".js-sku-prop-container");

              color_itm.find("img").attr("src", el.ICON);
            }
          });
        });
      }
    },

    getRowValues: function (arFilter, index) {
      var arValues = [],
        i = 0,
        j = 0,
        boolSearch = false,
        boolOneSearch = true;

      if (arFilter.length === 0) {
        for (i = 0; i < this.offers.length; i++) {
          if (!BX.util.in_array(this.offers[i].TREE[index], arValues)) {
            arValues[arValues.length] = this.offers[i].TREE[index];
          }
        }
        boolSearch = true;
      } else {
        for (i = 0; i < this.offers.length; i++) {
          boolOneSearch = true;

          for (j in arFilter) {
            if (arFilter[j] !== this.offers[i].TREE[j]) {
              boolOneSearch = false;
              break;
            }
          }

          if (boolOneSearch) {
            if (!BX.util.in_array(this.offers[i].TREE[index], arValues)) {
              arValues[arValues.length] = this.offers[i].TREE[index];
            }

            boolSearch = true;
          }
        }
      }

      return boolSearch ? arValues : false;
    },

    getCanBuy: function (arFilter) {
      var i,
        j = 0,
        boolOneSearch = true,
        boolSearch = false;

      for (i = 0; i < this.offers.length; i++) {
        boolOneSearch = true;

        for (j in arFilter) {
          if (arFilter[j] !== this.offers[i].TREE[j]) {
            boolOneSearch = false;
            break;
          }
        }

        if (boolOneSearch) {
          if (this.offers[i].CAN_BUY) {
            boolSearch = true;
            break;
          }
        }
      }

      return boolSearch;
    },

    setCurrent: function () {
      var i,
        j = 0,
        strName = "",
        arShowValues = false,
        arCanBuyValues = [],
        arFilter = {},
        tmpFilter = [],
        current = this.offers[this.offerNum].TREE;

      for (i = 0; i < this.treeProps.length; i++) {
        strName = "PROP_" + this.treeProps[i].ID;
        arShowValues = this.getRowValues(arFilter, strName);

        if (!arShowValues) break;

        if (BX.util.in_array(current[strName], arShowValues)) {
          arFilter[strName] = current[strName];
        } else {
          arFilter[strName] = arShowValues[0];
          this.offerNum = 0;
        }

        if (this.config.showAbsent) {
          arCanBuyValues = [];
          tmpFilter = [];
          tmpFilter = BX.clone(arFilter, true);

          for (j = 0; j < arShowValues.length; j++) {
            tmpFilter[strName] = arShowValues[j];

            if (this.getCanBuy(tmpFilter)) {
              arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
            }
          }
        } else {
          arCanBuyValues = arShowValues;
        }

        this.updateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
      }

      this.selectedValues = arFilter;
      this.changeInfo();
    },

    updateOfferData: function (offer) {
      //alert(offer.ID);
      // console.log(offer);
      $(".js-add-to-cart").attr("data-id", offer.ID);
      $('.popup input[name="ITEM"]').val(offer.ID);
      // $('.js_onclick').attr('data-id', (offer.ID));
      $(".js_onclick").attr("data-price", offer.PRICE);
      $(".js_onclick").attr(
        "data-market",
        $(".js-choose-shop option:selected").val()
      );
      let articleValue = offer.PROPERTIES.CML2_ARTICLE.VALUE,
        priceFormat = Math.round(Number(offer.PRICE)),
        basePriceFormat =
          Math.round(Number(offer.BASE_PRICE)) + '<span class="ruble">c</span>',
        instructionValue =
          typeof offer.PROPERTIES.INSTRUKTSIYA != "undefined"
            ? offer.PROPERTIES.INSTRUKTSIYA.VALUE
            : "",
        consistValue =
          typeof offer.PROPERTIES.SOSTAV != "undefined"
            ? offer.PROPERTIES.SOSTAV.VALUE
            : "",
        instructionsItemClass = instructionValue === "" ? "hide" : "",
        consistItemClass = consistValue === "" ? "hide" : "",
        newValue =
          typeof offer.PROPERTIES.NEW != "undefined"
            ? offer.PROPERTIES.NEW.VALUE
            : "",
        saleValue =
          typeof offer.PROPERTIES.DISCOUNT != "undefined" &&
          offer.PROPERTIES.DISCOUNT.VALUE > 0
            ? offer.PROPERTIES.DISCOUNT.VALUE
            : "",
        hitValue =
          typeof offer.PROPERTIES.HIT != "undefined"
            ? offer.PROPERTIES.HIT.VALUE
            : "",
        newLabelClass = newValue === "Да" ? "" : "hide",
        saleLabelClass = saleValue ? "" : "hide",
        hitLabelClass = hitValue === "Да" ? "" : "hide";

      //артикул
      $(".js-offer-article").text(articleValue);

      //Смена изображений
      /*let smallPhotos = '';
            if (offer.SMALL_PHOTOS) {
                offer.SMALL_PHOTOS.forEach(function(html) {
                    smallPhotos += '<div class="swiper-slide"><div class="product-card__gallery-thumbs-item" data-big="product-card/product-b">' + html + '<div class="swiper-lazy-preloader"></div></div></div>';
                });
            }
            $('.js-offer-more-photos').html(smallPhotos);

            let bigPhotos = '';
            if (offer.BIG_PHOTOS) {
                offer.BIG_PHOTOS.forEach(function(html) {
                    bigPhotos += '<div class="swiper-slide"><div class="product-card__gallery-big-item">' + html + '</div></div>';
                });
            }

            $('.js-offer-detail-picture').html(bigPhotos);
            */

      //цены
      // $('.product .prices__values .price--default').html(priceFormat);
      //$('.js-offer-base-price').html(basePriceFormat);

      //Кнопка Добавить в корзину
      if (this.checkBasketIds(offer.ID)) {
        $("a.js-add-to-cart")
          .text("Перейти в корзину")
          .attr("href", "/personal/order/make/")
          .addClass("active");
      } else {
        $("a.js-add-to-cart")
          .text("Добавить в корзину")
          .data("id", String(offer.ID))
          .attr("href", "javascript:void(0)")
          .removeClass("active");
      }

      if (offer.CAN_BUY) {
        $(".js-price-container").show();
        $(".js-buy-controls").show();
        $(".js-not-available-container").hide();
        $(".js-subscribe-controls").hide();
        $(".js-subscribe-btn").hide();
        if (offer.PRICE_FORMAT !== offer.BASE_PRICE_FORMAT) {
          $(".js-base-price-container").show();
        }
      } else {
        $(".js-subscribe-btn").hide();
        $(".js-price-container").hide();
        $(".js-base-price-container").hide();
        $(".js-buy-controls").hide();
        $(".js-not-available-container").show();
        $(".js-subscribe-controls").show();
        $(".js-subscribe-btn-" + offer.ID).show();
      }
    },

    checkBasketIds: function (id) {
      let bids = $(".section--product").data("bids");

      if (bids.length) {
        if (bids.indexOf(Number(id)) > -1) {
          return true;
        }
      }
      return false;
    },

    changeInfo: function () {
      var index = -1,
        j = 0,
        boolOneSearch = true,
        eventData = {
          currentId: this.offerNum > -1 ? this.offers[this.offerNum].ID : 0,
          newId: 0,
        };

      var i, offerGroupNode;

      for (i = 0; i < this.offers.length; i++) {
        boolOneSearch = true;

        for (j in this.selectedValues) {
          if (this.selectedValues[j] !== this.offers[i].TREE[j]) {
            boolOneSearch = false;
            break;
          }
        }

        if (boolOneSearch) {
          index = i;
          break;
        }
      }

      this.updateOfferData(this.offers[index]);
    },
  };
})(window);
const galleryThumbs = new Swiper(".js-gallery-thumbs .swiper", {
  direction: "vertical",
  slidesPerView: 5,
  spaceBetween: 10,
  watchSlidesVisibility: true,
  watchSlidesProgress: true,
});
const galleryPreview = new Swiper(".js-gallery-preview .swiper", {
  watchSlidesVisibility: true,
  spaceBetween: 0,
  thumbs: { swiper: galleryThumbs },
  pagination: { el: ".js-gallery-preview .swiper-pagination", clickable: true },
  navigation: {
    nextEl: ".js-gallery-preview .swiper__buttons .swiper__button--next",
    prevEl: ".js-gallery-preview .swiper__buttons .swiper__button--prev",
  },
});
// Cтрелки thumbs на api swiper-slider.
//document.querySelector('.js-gallery-thumbs .swiper__button--next').addEventListener('click', () => {
//    galleryPreview.slideNext();
//});
//document.querySelector('.js-gallery-thumbs .swiper__button--prev').addEventListener('click', () => {
//    galleryPreview.slidePrev();
//});
//galleryPreview.on('slideChange', () => {
//    if(galleryPreview.isEnd){
//        document.querySelector('.js-gallery-thumbs .swiper__button--prev').classList.remove('swiper-button-disabled');
//        document.querySelector('.js-gallery-thumbs .swiper__button--next').classList.add('swiper-button-disabled');
//    }
//    else{
//        document.querySelector('.js-gallery-thumbs .swiper__button--prev').classList.remove('swiper-button-disabled');
//        document.querySelector('.js-gallery-thumbs .swiper__button--next').classList.remove('swiper-button-disabled');
//    }
//    if(galleryPreview.isBeginning){
//        document.querySelector('.js-gallery-thumbs .swiper__button--next').classList.remove('swiper-button-disabled');
//        document.querySelector('.js-gallery-thumbs .swiper__button--prev').classList.add('swiper-button-disabled');
//    }
//});
//END стрелки thumbs
/*
$(document).on('click', '.js-sku-prop-container', function (e) {
	e.preventDefault();
	$('.js-sku-prop-container').removeClass('selected');
	$('.js-sku-prop-container input').prop('checked', false);
	$(this).addClass('selected');
	$(this).find('input').prop('checked', true);
});
*/

// ПОДКЛЮЧЕНИЕ FANCYBOX
const container = document.getElementById("myCarousel_catalog");
// console.log(container);

const options = {
  Thumbs: {
    type: "classic",
    showOnStart: true,
  },
  Dots: false,
  preload: 0,
  Carousel: {
    infinite: true,
    center: true,
  },
};

new Carousel(container, options, {
  Thumbs,
});

Fancybox.bind('[data-fancybox="gallery_catalog"]', {
  Toolbar: {
    display: {
      left: ["infobar"],
      middle: ["zoomIn", "zoomOut", "rotateCCW", "rotateCW"],
      right: ["slideshow", "thumbs", "close"],
    },
  },

  Thumbs: {
    type: "classic",
  },
});
