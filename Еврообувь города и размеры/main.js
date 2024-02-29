/**/
function getBaseFormData() {
  let formData = new FormData();
  formData.set("lastAppliedDiscounts", jsonData.lastAppliedDiscounts);
  formData.set("basketAction", "recalculateAjax");
  formData.set("via_ajax", "Y");
  formData.set("site_id", jsonData.siteId);
  formData.set("site_template_id", jsonData.siteTemplateId);
  formData.set("sessid", BX.bitrix_sessid());
  formData.set("template", jsonData.template);
  formData.set("signedParamsString", jsonData.signedParamsString);
  return formData;
}

function updateCart() {
  let formData = new FormData();
  formData.set("template_name", "new");

  let request = BX.ajax.runComponentAction("custom:ajax", "refresh_cart", {
    mode: "class",
    data: formData,
  });

  request.then(
    function (nextesitemesult) {
      $(".cart-container").html(nextesitemesult.data.html);
    },
    function (errorResult) {}
  );
}
/**/

let getParameterValue = (sParam) => {
  let sPageURL = window.location.search.substring(1),
    sURLVariables = sPageURL.split("&"),
    sParameterName,
    i;
  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split("=");
    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined
        ? true
        : decodeURIComponent(sParameterName[1]);
    }
  }
  return false;
};

// Функция для Fetch запросов. Ошибки нужно рендерить через .error запроса, либо вызывать try/catch при запросе
FetchRequest = (url, data) => {
  // Если это FormData.
  if (data.constructor.name === "FormData") {
    let FormData = Object.fromEntries(data.entries());
    data = JSON.stringify(FormData);
  } else {
    data = JSON.stringify(data);
  }
  let RequestOptions = {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: data,
  };
  return fetch(url, RequestOptions).then((response) => response.json());
};

$(document).ready(function () {
  var tags = "";
  tags += '<div class="container sorter__selected">';
  tags += '<div class="item__label">ВЫБРАННЫЕ ФИЛЬТРЫ:</div>';
  $(' .selectBox__item input[type="checkbox"]:checked').each(function (
    i,
    elem
  ) {
    tags +=
      '<div class="item" data-role="' +
      $(this).attr("id") +
      '">' +
      $(this).data("name") +
      "</div>";
  });
  if (getParameterValue("arrFilter_392_MAX")) {
    let min_value = 0;
    if (getParameterValue("arrFilter_392_MIN")) {
      min_value = getParameterValue("arrFilter_392_MIN");
    }
    tags +=
      '<div class="item" data-role="price">Цена: ' +
      min_value +
      "-" +
      getParameterValue("arrFilter_392_MAX") +
      "</div>";
  }
  tags += "</div>";
  $(".sorter__selected").html(tags);
});

$(document).on("click", "#set_filter", function () {
  var tags = "";
  tags += '<div class="container sorter__selected">';
  tags += '<div class="item__label">ВЫБРАННЫЕ ФИЛЬТРЫ:</div>';
  $(' .selectBox__item input[type="checkbox"]:checked').each(function (
    i,
    elem
  ) {
    tags +=
      '<div class="item" data-role="' +
      $(this).attr("id") +
      '">' +
      $(this).data("name") +
      "</div>";
  });
  tags += "</div>";
  $(".sorter__selected").html(tags);
});

$("body").on(
  "click",
  ".size__values .size__item:not(.is-disabled)",
  function () {
    $(".js-sku-prop-container").removeClass("selected");
    $(this).addClass("selected");
    $(".size__values .size__input").prop("checked", false);
    $(this).children(".size__input").prop("checked", true);
  }
);

BX.ready(function () {
  $(document).on("click", ".js-favorite", function () {
    let action;
    let $count = Number($(".item--heart span.item__counter").text());
    if ($(this).hasClass("active")) {
      $(this).removeClass("active");
      //удаляем
      action = "delete";
      $(".item--heart span.item__counter").text($count - 1);
    } else {
      //добавляем
      $(this).addClass("active");
      action = "add";
      $(".item--heart span.item__counter").text($count + 1);
    }
    let id = $(this).data("id");

    var postData = {
      sessid: BX.bitrix_sessid(),
      site_id: BX.message("SITE_ID"),
      id: id,
      action: action,
    };
    BX.ajax({
      url: "/local/ajax/favorites.php",
      method: "POST",
      data: postData,
      dataType: "json",
      onnextess: function (result) {
        //ShowCountFavorites(result);
        //alert(result);
      },
    });
  });

  $("body").on("click", ".container .sorter__selected .item", function () {
    var role = $(this).data("role");
    if (role === "price") {
      $(".price-max").val("");
      $(".price-min").val("");
    } else {
      $("input#" + role).trigger("click");
    }
    $("#set_filter").trigger("click");
  });
  $(document).on("click", 'a[data-popup="registration"]', function (e) {
    e.preventDefault();
    let $top = $(document).scrollTop();
    $(".popup--registration").addClass("is-shown");
    $(".popup--registration").currents("top", $top);
    $("body").addClass("js-popup-show");
  });

  $(document).on("click", ".js-toggle", function (e) {
    e.preventDefault();

    $(this).parent().toggleClass("is-open");
  });
});

BX.ready(function () {
  var postData = {
    sessid: BX.bitrix_sessid(),
    site_id: BX.message("SITE_ID"),
    action: "getlist",
  };

  BX.ajax({
    url: "/local/ajax/favorites.php",
    method: "POST",
    data: postData,
    dataType: "json",
    onnextess: function (result) {
      // ShowCountFavorites(result);
      if (result) {
        $(".js-favorite").each(function (ind, el) {
          let id = $(el).data("id");
          //alert(id);
          if (id && result.includes(id)) {
            $(el).addClass("active");
          }
        });
      }
    },
  });
});

BX.ready(function () {
  $(document).on("click", ".is-shown li", function () {
    /*$('.is-shown li input').prop('checked',false);
  $(this).find('input').prop('checked',true);*/
    $(".js_item").val($(this).data("id"));
  });

  $(document).on("click", ".js_user", function () {
    $("body").addClass("js-popup-show");
    $(".popup--fastbuy.is-shown").removeClass("is-shown");
    $("#popup_oneclick").addClass("is-shown");
    let id = $(".select_item").data("id");
    let price = $(".select_item").data("price");
    $("input.js_item").val(id);
    $("input.js_price").val(price);
  });

  $(document).on("click", ".js-close", function () {
    $("body").removeClass("js-popup-show");
    // возвращаем селект к дефолту
    $("select.js-choose-shop").val("Выбрать магазин");
    $(".shop__item").removeClass("is-selected");
    $("select.js-choose-shop").find("option.active").removeClass("active");

    $(".popup.is-shown").removeClass("is-shown");
  });

  $(document).on("click", ".js-fastview", function (e) {
    e.preventDefault();
    let $top = $(document).scrollTop();
    $(".popup--cart").addClass("is-shown");
    $(".popup--cart").css("top", $top);
    $("body").addClass("js-popup-show");
    let name = $(this).data("name"),
      id = $(this).data("id"),
      price = $(this).data("price"),
      picture = $(this).data("picture");
    $.ajax({
      url: "/local/ajax/fastview.php",
      method: "post" /* Метод передачи (post или get) */,
      dataType: "html" /* Тип данных в ответе (xml, json, script, html). */,
      data: { name: name, id: id, price: price, picture: picture },
      success: function (data) {
        $(".popup--cart .product__mini").html(data);
      },
    });
  });
  $(".onclick__form").on("submit", function () {
    $.ajax({
      url: "/local/ajax/oneclick.php",
      method: "post",
      dataType: "html",
      data: $(this).serialize(),
      success: function (data) {
        $(".onclick__form").html(data);
      },
    });
    return false;
  });
});

$(document).ready(function () {
  $('input[name="ORDER_PROP_3"]').mask("+7(999)999-99-99");
  $(".input-phone").mask("+7(999)999-99-99");
});
$(document).on("click", ".js_store", function (e) {
  e.preventDefault();
  let $top = $(document).scrollTop();
  $(".popup--choose-shop").addClass("is-shown");
  $(".popup--choose-shop").css("top", $top);
  $("body").addClass("js-popup-show");
  // $.ajax({
  //   url: "/local/ajax/stores.php",
  //   method: "post",
  //   dataType: "html",
  //   data: {
  //     id: $(this).data("id"),
  //     store_id: $(".js-choose-shop option:first").val(),
  //     size: selectedSize,
  //   },
  //   success: function (data) {
  //     //alert(data);
  //     $(".is-shown .shop__list").html(data);
  //   },
  // });
});
$(document).on("change", ".js-choose-shop", function (e) {
  e.preventDefault();
  let selectedSize =
    document.querySelector(".selected").lastElementChild.innerHTML;
  //alert($('.js_store').data('id'));
  $.ajax({
    url: "/local/ajax/stores.php",
    method: "post",
    dataType: "html",
    data: {
      id: $(".js_store").data("id"),
      store_id: $(this).val(),
      size: selectedSize,
    },
    success: function (data) {
      //alert(data);
      // console.log(data);
      $(".is-shown .shop__list").html(data);
    },
  });
});
// $(document).on("click", '.size__item:not(.is-disabled)',  function(e) {
// 	 e.preventDefault();
// 	let price=$('.product .prices__values .price--default').text();
// 	let id=$(this).data('id');
// 	let store=$(".js-choose-shop option:selected").val();
// 	$('.onclick__form .js_store').val(store);
// 	$('.onclick__form .js_item').val(id);
// 	$('.onclick__form .js_price').val(price);
// });
// $(document).on("click", '.card-vacancy', function() {

// 	$(this).find('.card-vacancy__collapse').toggleClass('show');

// });

/**/
let jsonData;
let templateName;
$(document).ready(function () {
  let quantityTimerId = 0;
  // $(document).on('click', '.js-promo-submit', function (e) {
  //     e.preventDefault();
  //     let coupon = $('.js-promo-input').val();
  //     let formData = getBaseFormData();
  //     if(coupon.length !== 0){
  //         formData.set('basket[coupon]', coupon);
  //         $.ajax({
  //             url: '/bitrix/components/bitrix/sale.basket.basket/ajax.php',
  //             data: formData,
  //             processData: false,
  //             contentType: false,
  //             type: 'POST',
  //             success: function(data){
  //                updateQuantity($('js-count-input'));
  //             }
  //         });
  //     }
  // });
  // $(document).on('click', '.js-coupon-delete', function (e) {
  //     e.preventDefault();
  //     let coupon = $(this).data('id');
  //     let couponWrapper = $(this).closest('.order-summary__box-info');
  //     let formData = getBaseFormData();
  //     formData.set('basket[delete_coupon]['+coupon+']', coupon);

  //     couponWrapper.remove();

  //     $.ajax({
  //         url: '/bitrix/components/bitrix/sale.basket.basket/ajax.php',
  //         data: formData,
  //         processData: false,
  //         contentType: false,
  //         type: 'POST',
  //         success: function(data){
  //             updateQuantity($('js-count-input'));
  //         }
  //     });
  // });

  function updateQuantity(quantityInput) {
    let itemId = quantityInput.data("id");

    let formData = new FormData();
    formData.set("item_id", itemId);
    formData.set("quantity", quantityInput.val());

    let request = BX.ajax.runComponentAction(
      "custom:ajax",
      "change_basket_item_quantity",
      {
        mode: "class",
        data: formData,
      }
    );

    request.then(
      function (nextesitemesult) {
        $(".cart-container").html(nextesitemesult.data.html);
      },
      function (errorResult) {}
    );
  }

  // $(document).on('click', '.js-product-delete', function (e) {
  //     e.preventDefault();
  //     let itemId = $(this).data('id');

  //     let formData = new FormData();
  //     formData.set('item_id', itemId);

  //     let request = BX.ajax.runComponentAction('custom:ajax', 'delete_basket_item', {
  //         mode:'class',
  //         data: formData
  //     });

  //     request.then(function(nextesitemesult){
  // 		//alert(nextesitemesult.data.html);
  //          $('.cart-container').html(nextesitemesult.data.html);
  //     }, function (errorResult) {

  //     });
  // });

  /**
   *
   * @returns {FormData}
   */
  function getBaseFormData() {
    let formData = new FormData();
    formData.set("lastAppliedDiscounts", jsonData.lastAppliedDiscounts);
    formData.set("basketAction", "recalculateAjax");
    formData.set("via_ajax", "Y");
    formData.set("site_id", jsonData.siteId);
    formData.set("site_template_id", jsonData.siteTemplateId);
    formData.set("sessid", BX.bitrix_sessid());
    formData.set("template", jsonData.template);
    formData.set("signedParamsString", jsonData.signedParamsString);
    return formData;
  }
});

/*
$(".js-favorite").on("click", function(){
  var $id=$(this).attr('data-id');
	
  $.ajax({
    url: '/ajax/favorite.php',
    method: 'post',
    dataType: 'html',
    data: {id: $id},
    nextess: function(data){
      //alert(data);
      //deleteCookie('BITRIX_SM_favorites');
      //setCookie('BITRIX_SM_favorites',data,'/');
      //$(this).parent().toggleClass('is-added');
      updateFavoritesIcon();
    }
  });
});*/

function updateCart() {
  let formData = new FormData();
  formData.set("template_name", templateName);
  let request = BX.ajax.runComponentAction("profline:ajax", "get_cart", {
    mode: "class",
    data: formData,
  });

  request.then(
    function (nextesitemesult) {
      $(".js-cart-container .js-order-cart-content").replaceWith(
        $(nextesitemesult.data.html).find(".js-order-cart-content")
      );
    },
    function (errorResult) {}
  );
}

// Fixed меню при скролле
document.addEventListener("DOMContentLoaded", () => {
  window.addEventListener("scroll", () => {
    navbar_height = document.querySelector("header.header").offsetHeight;
    if (window.scrollY > 300) {
      document.querySelector("header.header").classList.add("header__fixed");
      // Тут раньше поиск убирался только на мобиле.
      // window.innerWidth > 768 ? document.querySelector(".item.item--search").classList.remove("item--search__hide") : document.querySelector(".item.item--search").classList.add("item--search__hide");
      document
        .querySelector("header.header")
        .classList.contains("header--index")
        ? ""
        : (document.querySelector(".layout").style.paddingTop =
            navbar_height +
            parseInt(
              getComputedStyle(document.querySelector("header.header"))
                .marginBottom
            ) +
            "px");
    } else {
      document.querySelector("header.header").classList.remove("header__fixed");
      // Тут он возвращался только на мобиле.
      // window.innerWidth > 768 ? '' : document.querySelector(".item.item--search").classList.remove("item--search__hide");
      document
        .querySelector("header.header")
        .classList.contains("header--index")
        ? ""
        : (document.querySelector(".layout").style.paddingTop = "0");
    }
  });
  // инициализируем наш рейтинг
  star_rating();
  // Соберем все элементы Collapse.
  let togglebuttons = document.querySelectorAll('[data-toggle="collapse"]');
  for (i = 0; i < togglebuttons.length; i++) {
    let target = togglebuttons[i];
    // Определим цель collapse через data-target.
    let toggletarget = togglebuttons[i].dataset.target;
    target.addEventListener("click", (e) => {
      target.classList.toggle("collapsed");
      // Определим теперь, цель на которой выполняем collapse.
      let collapsed_block = document.querySelector(toggletarget);
      // Создадим экземпляр класса Collapse на чистом JS. Сам класс находится в plugins.js
      let collapse = new ItcCollapse(collapsed_block);
      // Сделаем функцию toggle.
      collapse.toggle();
    });
  }
});

window.addEventListener("beforeunload", () => {
  setCookie("prev_page", window.location.pathname);
});
// Самый премитивный Star_rating, чтобы не засорять проект миллионами библиотек.
let star_rating = () => {
  let item = document.querySelectorAll(".rating__item[data-value]");
  let i = 0;
  while (i < item.length) {
    item[i].addEventListener("click", (e) => {
      let current = parseInt(e.target.getAttribute("data-value"));
      document.querySelector(
        '.form--review__action input[name="REVIEW_RATING"]'
      ).value = current;
      let pre = current;
      while (1 <= pre) {
        if (
          !document
            .querySelector(".rating__item--" + pre)
            .classList.contains("is-selected")
        ) {
          document
            .querySelector(".rating__item--" + pre)
            .classList.add("is-selected");
        }
        --pre;
      }
      let next = current + 1;
      while (5 >= next) {
        if (
          document
            .querySelector(".rating__item--" + next)
            .classList.contains("is-selected")
        ) {
          document
            .querySelector(".rating__item--" + next)
            .classList.remove("is-selected");
        }
        ++next;
      }
    });
    i++;
  }
};
// Получить куки
const getCookie = (name, json = false) => {
  if (!name) {
    return undefined;
  }
  // Используем регулярное выражение чтобы декодировать
  let matches = document.cookie.match(
    new RegExp(
      "(?:^|; )" + name.replace(/([.$?*|{}()\[\]\\\/+^])/g, "\\$1") + "=([^;]*)"
    )
  );
  // Если есть куки, то через decode и контструкцию try - catch пропустим и спарсим наш JSON результат, чтобы исключить косячки и добавить хранить json в куки.
  if (matches) {
    let res = decodeURIComponent(matches[1]);
    if (json) {
      try {
        return JSON.parse(res);
      } catch (e) {}
    }
    // Вернем результат
    return res;
  }
  // Ну или не вернем ничего, если есть еще ошибка.
  return undefined;
};
// Пример с json в cookie - setCookie('zxc', {'key' : '123', 'ccc' : '222'});
// Установить куки
const setCookie = (name, value, options = { path: "/" }) => {
  if (!name) {
    return;
  }
  options = options || {};
  // Проверим дата ли это
  if (options.expires instanceof Date) {
    // Превратим дату в строку
    options.expires = options.expires.toUTCString();
  }
  // Проверим объект ли это
  if (value instanceof Object) {
    // Превратим JSON в строку
    value = JSON.stringify(value);
  }
  // переменная Ключ = значение
  let updatedCookie =
    encodeURIComponent(name) + "=" + encodeURIComponent(value);
  // Пробежимся циклом и добавим возможность ассоциативности
  for (let optionKey in options) {
    updatedCookie += "; " + optionKey;
    let optionValue = options[optionKey];
    if (optionValue !== true) {
      updatedCookie += "=" + optionValue;
    }
  }
  document.cookie = updatedCookie;
};
// Удалить куки
const deleteCookie = (name) => {
  // Просто создадим новый куки с пустыми значениями name and value.
  setCookie(name, null, {
    expires: new Date(),
    path: "/",
  });
};

// Объект с капчами
// Временно убрать логинизацию отсюда. После рефракторинга сделаем по уму.
const captchaItems = {
  items: [
    document.querySelector("#g-recatpcha-auth"),
    document.querySelector("#g-recatpcha-arenda"),
    document.querySelector("#g-recatpcha-any-questions"),
    document.querySelector("#g-recatpcha-charity"),
    document.querySelector("#g-recatpcha-optovikam"),
    document.querySelector("#g-recatpcha-send"),
    document.querySelector("#g-recatpcha-vacancie"),
  ],
};

captchaRender = function () {
  if (captchaItems.items.length > 0) {
    for (i = 0; i < captchaItems.items.length; i++) {
      if (!captchaItems.items[i]) continue;
      let item = captchaItems.items[i];
      window.item = grecaptcha.render(item, {
        sitekey: "6Ldz-ZIgAAAAAO_0GhwGQVhfDl4jtPmLchNWmvvM",
        // 'callback': function (response) {
        //   console.log(response)
        //   // app.Captcha.WordCallback(response, item)
        // },
        theme: "light",
      });
      item.dataset.captchaId = window.item;
    }
  }
};
