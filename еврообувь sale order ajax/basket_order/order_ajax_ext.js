(function () {
  "use strict";

  var initParent = BX.Sale.OrderAjaxComponent.init,
    editActiveDeliveryBlock =
      BX.Sale.OrderAjaxComponent.editActiveDeliveryBlock,
    editFadeDeliveryContent =
      BX.Sale.OrderAjaxComponent.editFadeDeliveryContent,
    editFadeDeliveryBlock = BX.Sale.OrderAjaxComponent.editFadeDeliveryBlock,
    editDeliveryItems = BX.Sale.OrderAjaxComponent.editDeliveryItems,
    editDeliveryInfo = BX.Sale.OrderAjaxComponent.editDeliveryInfo,
    getSelectedDelivery = BX.Sale.OrderAjaxComponent.getSelectedDelivery,
    editSection = BX.Sale.OrderAjaxComponent.editSection,
    editActiveRegionBlock = BX.Sale.OrderAjaxComponent.editActiveRegionBlock,
    getPersonTypeControl = BX.Sale.OrderAjaxComponent.getPersonTypeControl,
    getDeliveryLocationInput =
      BX.Sale.OrderAjaxComponent.getDeliveryLocationInput,
    getZipLocationInput = BX.Sale.OrderAjaxComponent.getZipLocationInput,
    editActivePropsBlock = BX.Sale.OrderAjaxComponent.editActivePropsBlock,
    editPropsItems = BX.Sale.OrderAjaxComponent.editPropsItems,
    getPropertyRowNode = BX.Sale.OrderAjaxComponent.getPropertyRowNode,
    alterProperty = BX.Sale.OrderAjaxComponent.alterProperty,
    editActivePaySystemBlock =
      BX.Sale.OrderAjaxComponent.editActivePaySystemBlock,
    editPaySystemItems = BX.Sale.OrderAjaxComponent.editPaySystemItems,
    createPaySystemItem = BX.Sale.OrderAjaxComponent.createPaySystemItem,
    selectPaySystem = BX.Sale.OrderAjaxComponent.selectPaySystem,
    getSelectedPaySystem = BX.Sale.OrderAjaxComponent.getSelectedPaySystem,
    switchOrderSaveButtons = BX.Sale.OrderAjaxComponent.switchOrderSaveButtons,
    alignBasketColumns = BX.Sale.OrderAjaxComponent.alignBasketColumns,
    editBasketBlock = BX.Sale.OrderAjaxComponent.editBasketBlock,
    editActiveBasketBlock = BX.Sale.OrderAjaxComponent.editActiveBasketBlock,
    editFadeBasketBlock = BX.Sale.OrderAjaxComponent.editFadeBasketBlock,
    editBasketItems = BX.Sale.OrderAjaxComponent.editBasketItems,
    editBasketItemsHeader = BX.Sale.OrderAjaxComponent.editBasketItemsHeader,
    createBasketItem = BX.Sale.OrderAjaxComponent.createBasketItem,
    createBasketItemColumn = BX.Sale.OrderAjaxComponent.createBasketItemColumn,
    createBasketItemHiddenColumn =
      BX.Sale.OrderAjaxComponent.createBasketItemHiddenColumn,
    changeVisibleSection = BX.Sale.OrderAjaxComponent.changeVisibleSection,
    editMobileTotalBlock = BX.Sale.OrderAjaxComponent.editMobileTotalBlock,
    editOrder = BX.Sale.OrderAjaxComponent.editOrder,
    showValidationResult = BX.Sale.OrderAjaxComponent.showValidationResult,
    isValidPropertiesBlock = BX.Sale.OrderAjaxComponent.isValidPropertiesBlock,
    isValidForm = BX.Sale.OrderAjaxComponent.isValidForm,
    editTotalBlock = BX.Sale.OrderAjaxComponent.editTotalBlock,
    activatePickUp = BX.Sale.OrderAjaxComponent.activatePickUp,
    deactivatePickUp = BX.Sale.OrderAjaxComponent.deactivatePickUp,
    editActivePickUpBlock = BX.Sale.OrderAjaxComponent.editActivePickUpBlock,
    editPickUpBlock = BX.Sale.OrderAjaxComponent.editPickUpBlock,
    pickUpFinalAction = BX.Sale.OrderAjaxComponent.pickUpFinalAction,
    createPickUpItem = BX.Sale.OrderAjaxComponent.createPickUpItem,
    createPickUpItem_sovpad =
      BX.Sale.OrderAjaxComponent.createPickUpItem_sovpad;
  // НЕ ЗАБЫВАЕМ ПОДКЛЮЧИТЬ СЮДА ФУНКЦИИ!!!
  BX.namespace("BX.Sale.OrderAjaxComponentExt");

  BX.Sale.OrderAjaxComponentExt = BX.Sale.OrderAjaxComponent;

  BX.Sale.OrderAjaxComponentExt.init = function (parameters) {
    initParent.apply(this, arguments);
    let formData = new FormData();
  };
  BX.Sale.OrderAjaxComponentExt.editSection = function (section) {
    if (section.id == this.pickUpBlockNode.id) {
      this.editPickUpBlock(true);
    } else {
      this.editRegionBlock(true);
      this.editDeliveryBlock(true);
      this.editPropsBlock(true);
      this.editPaySystemBlock(true);
    }
  };
  BX.Sale.OrderAjaxComponentExt.isValidForm = function () {
    if (!this.options.propertyValidation) return true;

    var regionErrors = this.isValidRegionBlock(),
      propsErrors = this.isValidPropertiesBlock(),
      consentError = false,
      userConsentInput = BX("user-consent-input"),
      navigated = false,
      tooltips,
      i;

    if (propsErrors.length) {
      tooltips = this.propsBlockNode.querySelectorAll("div.form-error");
      for (i = 0; i < tooltips.length; i++) {
        navigated = true;
        this.animateScrollTo(
          BX.findParent(tooltips[i], { className: "form-group" }),
          800,
          120
        );
        break;
      }
    }

    if (regionErrors.length && !navigated) {
      this.animateScrollTo(this.regionBlockNode, 800, 120);
    }

    if (userConsentInput && !userConsentInput.checked) {
      consentError = true;
      if (!navigated) {
        this.animateScrollTo(userConsentInput, 800, 120);
      }
    }

    return !(regionErrors.length + propsErrors.length) && !consentError;
  };

  BX.Sale.OrderAjaxComponentExt.isValidPropertiesBlock = function (
    excludeLocation
  ) {
    if (!this.options.propertyValidation) return [];

    var props = this.orderBlockNode.querySelectorAll(
        ".form-group[data-property-id-row]"
      ),
      propsErrors = [],
      id,
      propContainer,
      arProperty,
      data,
      i;

    for (i = 0; i < props.length; i++) {
      id = props[i].getAttribute("data-property-id-row");

      if (!!excludeLocation && this.locations[id]) continue;

      propContainer = props[i].querySelector(".soa-property-container");
      if (propContainer) {
        arProperty = this.validation.properties[id];
        data = this.getValidationData(arProperty, propContainer);
        propsErrors = propsErrors.concat(this.isValidProperty(data, true));
      }
    }
    return propsErrors;
  };

  BX.Sale.OrderAjaxComponentExt.showValidationResult = function (
    inputs,
    errors
  ) {
    if (!inputs || !inputs.length || !errors) return;

    let parentContainer = $(inputs).parent();
    let errorsText = errors.join("<br>");
    if (parentContainer.hasClass("bx-ui-sls-container")) {
      parentContainer.parent().parent().find(".form-error").remove();
      parentContainer.parent().css({ border: "none" });
    } else {
      parentContainer.find(".form-error").remove();
      parentContainer.find("input").removeClass("input-novalidate");
    }
    if (errors.length) {
      if (parentContainer.hasClass("bx-ui-sls-container")) {
        parentContainer
          .parent()
          .parent()
          .append('<div class="form-error">' + errorsText + "</div>");
        parentContainer.parent().parent().find(".form-error").show();
        parentContainer.parent().css({
          border: "1px solid",
          "border-color": "var(--main-accent-color)",
        });
      } else {
        parentContainer.append(
          '<div class="form-error">' + errorsText + "</div>"
        );
        parentContainer.find(".form-error").show();
        parentContainer.find("input").addClass("input-novalidate");
      }
    }
  };

  BX.Sale.OrderAjaxComponentExt.editActivePaySystemBlock = function () {
    var paySystemContent = this.paySystemBlockNode.querySelector(
        ".js-pay-system-items-container"
      ),
      paySystemErrors = this.paySystemBlockNode.querySelector(
        ".js-pay-system-errors"
      ),
      paySystemInfo = this.paySystemBlockNode.querySelector(
        ".js-pay-system-info"
      );

    $(paySystemContent).html("");
    $(paySystemErrors).html("");
    $(paySystemInfo).html("");

    this.getErrorContainer(paySystemContent);
    this.editPaySystemItems(paySystemContent);
  };

  BX.Sale.OrderAjaxComponentExt.editPaySystemItems = function (paySystemNode) {
    if (!this.result.PAY_SYSTEM || this.result.PAY_SYSTEM.length <= 0) return;

    var paySystemItemNode, i;

    for (i = 0; i < this.paySystemPagination.currentPage.length; i++) {
      paySystemItemNode = this.createPaySystemItem(
        this.paySystemPagination.currentPage[i]
      );
      paySystemNode.appendChild(paySystemItemNode);
    }

    if (this.paySystemPagination.show)
      this.showPagination("paySystem", paySystemNode);
  };

  BX.Sale.OrderAjaxComponentExt.createPaySystemItem = function (item) {
    var checked = item.CHECKED == "Y",
      paySystemId = parseInt(item.ID),
      label,
      itemNode,
      labelDesc,
      paySystemInput;

    if (item.DESCRIPTION.length) {
      labelDesc = BX.create("SPAN", {
        props: { className: "payment-types__item-desc" },
        html: item.DESCRIPTION,
      });
    }

    label = BX.create("LABEL", {
      attrs: { for: "ID_PAY_SYSTEM_ID_" + paySystemId },
      children: [
        BX.create("SPAN", {
          props: { className: "payment-types__item-name" },
          html: item.NAME,
        }),
        labelDesc,
      ],
    });

    paySystemInput = BX.create("INPUT", {
      props: {
        id: "ID_PAY_SYSTEM_ID_" + paySystemId,
        name: "PAY_SYSTEM_ID",
        type: "radio",
        value: paySystemId,
        checked: checked,
      },
    });

    itemNode = BX.create("DIV", {
      props: { className: "payment-types__item" },
      children: [paySystemInput, label],
      events: {
        click: BX.proxy(this.selectPaySystem, this),
      },
    });

    if (paySystemId == 4) {
      $(itemNode)
        .find("label")
        .append(
          '<div class="payment-list"><span class="payment-list__item">\n' +
            "</div>"
        );
    }

    if (checked) BX.addClass(itemNode, "bx-selected");

    return itemNode;
  };

  BX.Sale.OrderAjaxComponentExt.selectPaySystem = function (event) {
    if (!this.orderBlockNode || !event) return;

    var target = event.target || event.srcElement;

    var actionSection = BX.hasClass(target, "payment-types__item")
        ? target
        : BX.findParent(target, { className: "payment-types__item" }),
      actionInput,
      selectedSection;

    if (BX.hasClass(actionSection, "bx-selected"))
      return BX.PreventDefault(event);

    selectedSection = this.paySystemBlockNode.querySelector(
      ".payment-types__item.bx-selected"
    );
    BX.addClass(actionSection, "bx-selected");
    actionInput = actionSection.querySelector("input[type=radio]");
    actionInput.checked = true;

    if (selectedSection) {
      BX.removeClass(selectedSection, "bx-selected");
      selectedSection.querySelector("input[type=radio]").checked = false;
    }

    this.sendRequest();
  };

  BX.Sale.OrderAjaxComponentExt.getSelectedPaySystem = function () {
    var paySystemCheckbox = this.paySystemBlockNode.querySelector(
        "input[type=radio][name=PAY_SYSTEM_ID]:checked"
      ),
      currentPaySystem = null,
      paySystemId,
      i;

    if (!paySystemCheckbox)
      paySystemCheckbox = this.paySystemHiddenBlockNode.querySelector(
        "input[type=radio][name=PAY_SYSTEM_ID]:checked"
      );

    if (!paySystemCheckbox)
      paySystemCheckbox = this.paySystemHiddenBlockNode.querySelector(
        "input[type=hidden][name=PAY_SYSTEM_ID]"
      );

    if (paySystemCheckbox) {
      paySystemId = paySystemCheckbox.value;

      for (i = 0; i < this.result.PAY_SYSTEM.length; i++) {
        if (this.result.PAY_SYSTEM[i].ID == paySystemId) {
          currentPaySystem = this.result.PAY_SYSTEM[i];
          break;
        }
      }
    }

    return currentPaySystem;
  };

  BX.Sale.OrderAjaxComponentExt.editActivePropsBlock = function () {
    let propsContent = this.propsBlockNode.querySelector(".js-props-content");
    let propsErrors = this.propsBlockNode.querySelector(".js-props-errors");
    let propsDeliveryAddressContent = document.querySelector(
      ".js-delivery-address-content"
    );
    let propsDeliveryHouseContent = document.querySelector(
      ".js-delivery-house-content"
    );

    $(propsContent).html("");
    $(propsErrors).html("");
    $(propsDeliveryAddressContent).html("");
    $(propsDeliveryHouseContent).html("");

    // this.getErrorContainer(propsErrors);
    this.editPropsItems(propsContent);
  };

  BX.Sale.OrderAjaxComponentExt.editPropsItems = function (propsNode) {
    if (!this.result.ORDER_PROP || !this.propertyCollection) return;

    var group,
      property,
      groupIterator = this.propertyCollection.getGroupIterator(),
      propsIterator;

    while ((group = groupIterator())) {
      propsIterator = group.getIterator();
      while ((property = propsIterator())) {
        if (
          this.deliveryLocationInfo.loc == property.getId() ||
          this.deliveryLocationInfo.zip == property.getId() ||
          this.deliveryLocationInfo.city == property.getId()
        )
          continue;

        this.getPropertyRowNode(property, propsNode, false);
      }
    }
  };

  BX.Sale.OrderAjaxComponentExt.getPropertyRowNode = function (
    property,
    propsItemsContainer,
    disabled
  ) {
    var propsItemNode = BX.create("DIV"),
      propertyType = property.getType() || "",
      settings = property.getSettings(),
      propsDeliveryAddressContent = document.querySelector(
        ".js-delivery-address-content"
      ),
      propsDeliveryHouseContent = document.querySelector(
        ".js-delivery-house-content"
      );

    BX.addClass(propsItemNode, "form-group");

    propsItemNode.setAttribute("data-property-id-row", property.getId());

    switch (propertyType) {
      case "LOCATION":
        this.insertLocationProperty(property, propsItemNode, disabled);
        break;
      case "DATE":
        this.insertDateProperty(property, propsItemNode, disabled);
        break;
      case "FILE":
        this.insertFileProperty(property, propsItemNode, disabled);
        break;
      case "STRING":
        this.insertStringProperty(property, propsItemNode, disabled);
        break;
      case "ENUM":
        this.insertEnumProperty(property, propsItemNode, disabled);
        break;
      case "Y/N":
        this.insertYNProperty(property, propsItemNode, disabled);
        break;
      case "NUMBER":
        this.insertNumberProperty(property, propsItemNode, disabled);
    }

    if (
      settings.CODE === "STREET" ||
      settings.CODE === "POST_OFFICE" ||
      settings.CODE === "SDEK_OFFICE" ||
      settings.CODE === "IPOL_OZON_PVZ" ||
      settings.CODE === "PICKPOINT_OFFICE" ||
      settings.CODE === "STORE_ADDRESS" ||
      settings.CODE === "ADDRESS"
    ) {
      if (settings.CODE === "STORE_ADDRESS") {
        $(propsItemNode).addClass("js-store-address");
        $(propsItemNode).hide();
      }
      if (settings.CODE === "PICKPOINT_OFFICE") {
        $(propsItemNode).find("input").val($("#sPPDelivery").text());
      }
      propsDeliveryAddressContent.appendChild(propsItemNode);
    } else if (
      settings.CODE === "HOUSE" ||
      settings.CODE === "BUILDING" ||
      settings.CODE === "FLAT"
    ) {
      propsDeliveryHouseContent.appendChild(propsItemNode);
    } else {
      propsItemsContainer.appendChild(propsItemNode);
    }
  };

  BX.Sale.OrderAjaxComponentExt.alterProperty = function (
    settings,
    propContainer
  ) {
    var i, textNode, inputs;

    textNode = propContainer.querySelector("input[type=text], input[type=tel]");
    if (!textNode) textNode = propContainer.querySelector("textarea");

    if (textNode) {
      textNode.id = "soa-property-" + settings.ID;
      if (settings.IS_ADDRESS == "Y")
        textNode.setAttribute("autocomplete", "address");
      if (settings.IS_EMAIL == "Y") {
        textNode.setAttribute("autocomplete", "email");
        textNode.classList.add("js-user-email");
      }
      if (settings.IS_PAYER == "Y")
        textNode.setAttribute("autocomplete", "name");
      if (settings.IS_PHONE == "Y") {
        textNode.setAttribute("autocomplete", "tel");
        textNode.classList.add("js-user-phone");
        // textNode.setAttribute('type', 'tel');
      }

      if (settings.PATTERN && settings.PATTERN.length) {
        textNode.removeAttribute("pattern");
      }
    }

    inputs = propContainer.querySelectorAll(
      "input[type=text], input[type=tel]"
    );
    for (i = 0; i < inputs.length; i++) {
      inputs[i].placeholder = settings.NAME;
    }
  };

  BX.Sale.OrderAjaxComponentExt.editActiveRegionBlock = function () {
    let regionInput = this.propsBlockNode.querySelector(".js-region-input");
    let regionContent = this.propsBlockNode.querySelector(".js-region-content");
    let regionErrors = this.propsBlockNode.querySelector(".js-region-errors");
    $(regionErrors).html("");
    $(regionContent).html("");
    $(regionInput).html("");

    // this.getErrorContainer(regionErrors);
    this.getPersonTypeControl(regionContent);
    this.getDeliveryLocationInput(regionInput);
  };

  BX.Sale.OrderAjaxComponentExt.getPersonTypeControl = function (
    regionContent
  ) {
    $(regionContent).append(
      '<input type="hidden" name="PERSON_TYPE" value="' +
        this.result.PERSON_TYPE[1].ID +
        '">'
    );
  };

  BX.Sale.OrderAjaxComponentExt.getDeliveryLocationInput = function (node) {
    var currentProperty,
      locationId,
      altId,
      location,
      k,
      altProperty,
      labelHtml,
      currentLocation,
      insertedLoc,
      labelTextHtml,
      label,
      input,
      altNode;

    for (k in this.result.ORDER_PROP.properties) {
      if (this.result.ORDER_PROP.properties.hasOwnProperty(k)) {
        currentProperty = this.result.ORDER_PROP.properties[k];
        if (currentProperty.IS_LOCATION == "Y") {
          locationId = currentProperty.ID;
          altId = parseInt(currentProperty.INPUT_FIELD_LOCATION);
          break;
        }
      }
    }

    location = this.locations[locationId];
    if (location && location[0] && location[0].output) {
      this.regionBlockNotEmpty = true;

      currentLocation = location[0].output;

      insertedLoc = BX.create("DIV", {
        attrs: { "data-property-id-row": locationId },
        props: { className: "form-group bx-soa-location-input-container" },
        style: { visibility: "hidden" },
        html: currentLocation.HTML,
      });

      node.appendChild(insertedLoc);
      node.appendChild(
        BX.create("INPUT", {
          props: {
            type: "hidden",
            name: "RECENT_DELIVERY_VALUE",
            value: location[0].lastValue,
          },
        })
      );

      for (k in currentLocation.SCRIPT)
        if (currentLocation.SCRIPT.hasOwnProperty(k))
          BX.evalGlobal(currentLocation.SCRIPT[k].JS);
    }

    if (location && location[0] && location[0].showAlt && altId > 0) {
      for (k in this.result.ORDER_PROP.properties) {
        if (parseInt(this.result.ORDER_PROP.properties[k].ID) == altId) {
          altProperty = this.result.ORDER_PROP.properties[k];
          break;
        }
      }
    }

    this.getZipLocationInput(node);
  };

  BX.Sale.OrderAjaxComponentExt.getZipLocationInput = function (node) {
    var zipProperty, i, propsItemNode, labelTextHtml, label, input;

    for (i in this.result.ORDER_PROP.properties) {
      if (
        this.result.ORDER_PROP.properties.hasOwnProperty(i) &&
        this.result.ORDER_PROP.properties[i].IS_ZIP == "Y"
      ) {
        zipProperty = this.result.ORDER_PROP.properties[i];
        break;
      }
    }

    if (zipProperty) {
      this.regionBlockNotEmpty = true;

      propsItemNode = BX.create("DIV");
      propsItemNode.setAttribute("data-property-id-row", zipProperty.ID);

      input = BX.create("INPUT", {
        props: {
          id: "zipProperty",
          type: "text",
          placeholder: zipProperty.NAME,
          autocomplete: "zip",
          name: "ORDER_PROP_" + zipProperty.ID,
          value: zipProperty.VALUE,
        },
      });

      propsItemNode.appendChild(input);
      node.appendChild(propsItemNode);
      node.appendChild(
        BX.create("input", {
          props: {
            id: "ZIP_PROPERTY_CHANGED",
            name: "ZIP_PROPERTY_CHANGED",
            type: "hidden",
            value: this.result.ZIP_PROPERTY_CHANGED || "N",
          },
        })
      );

      this.bindValidation(zipProperty.ID, propsItemNode);
    }
  };

  BX.Sale.OrderAjaxComponentExt.editActiveDeliveryBlock = function () {
    let deliveryErrors = this.deliveryBlockNode.querySelector(
      ".js-delivery-errors"
    );
    $(".js-delivery-errors").html("");
    $(".js-pickup-content").html("");
    $(".bx_soa_pickup .col-xs-12").html("");
    $(this.pickUpBlockNode).hide();
    $(".js-checkout-step-delivery").show();

    this.getErrorContainer(deliveryErrors);
    this.editDeliveryItems();
    this.editDeliveryInfo();
  };

  BX.Sale.OrderAjaxComponentExt.editFadeDeliveryBlock = function () {};

  BX.Sale.OrderAjaxComponentExt.editFadeDeliveryContent = function () {};

  BX.Sale.OrderAjaxComponentExt.editDeliveryItems = function () {
    if (!this.result.DELIVERY || this.result.DELIVERY.length <= 0) return;

    let deliveryItemsContainer = $(".js-delivery-items-container");
    let that = this;
    let currentDelivery = this.getSelectedDelivery();

    // console.log(currentDelivery);

    if (currentDelivery.ID === "17") {
      $("#IPOL_OZON_btn").show();
    } else {
      $("#IPOL_OZON_btn").hide();
    }

    if (currentDelivery.ID === "18") {
      $("#IPOL_OZON_post").show();
    } else {
      $("#IPOL_OZON_post").hide();
    }

    deliveryItemsContainer.html("");
    $("#IPOLSDEK_injectHere").html("");

    $(this.deliveryPagination.currentPage).each(function () {
      let deliveryName = this.NAME.replace("(" + this.OWN_NAME + ")", "");
      let checked = this.CHECKED === "Y" ? ' checked="checked"' : "";
      let deliveryItem =
        '<div class="delivery__data__input">\n' +
        '    <input type="radio" name="DELIVERY_ID" value="' +
        this.ID +
        '" id="delivery-type-' +
        this.ID +
        '"' +
        checked +
        ">\n" +
        '    <label for="delivery-type-' +
        this.ID +
        '">\n' +
        '        <span class="delivery__name">' +
        deliveryName +
        "</span>\n" +
        '        <span class="delivery__value"> Стоимость доставки: <span class="js-delivery-price-value">\n' +
        "    </label>\n" +
        "</div>";
      deliveryItemsContainer.append(deliveryItem);
      BX.bind(
        BX("delivery-type-" + this.ID),
        "click",
        BX.proxy(that.selectDelivery, that)
      );
    });
  };

  BX.Sale.OrderAjaxComponentExt.editDeliveryInfo = function () {
    if (!this.result.DELIVERY) return;

    let currentDelivery = this.getSelectedDelivery(),
      currentDeliveryResult = $(".js-delivery-result");

    // console.log(this.result);

    currentDeliveryResult.hide();
    $(".js-delivery-description").html(currentDelivery.DESCRIPTION);

    if (currentDelivery.PRICE >= 0) {
      currentDeliveryResult.show();
      $(".js-delivery-price-value").html(currentDelivery.PRICE);
    }
    /* if (currentDelivery.PERIOD_TEXT && currentDelivery.PERIOD_TEXT.length) {
            currentDeliveryResult.show();
            $('.js-delivery-period-value').text(currentDelivery.PERIOD_TEXT);
        }
		*/
    //alert($('#soa-property-3').val());
    //var tel=$('#soa-property-3').val();
    //alert(tel);

    if (this.params.DELIVERY_NO_AJAX != "Y")
      this.deliveryCachedInfo[currentDelivery.ID] = currentDelivery;
  };

  BX.Sale.OrderAjaxComponentExt.getSelectedDelivery = function (item) {
    var deliveryCheckbox = this.deliveryBlockNode.querySelector(
        "input[type=radio][name=DELIVERY_ID]:checked"
      ),
      currentDelivery = false,
      deliveryId,
      i;

    if (!deliveryCheckbox)
      deliveryCheckbox = this.deliveryHiddenBlockNode.querySelector(
        "input[type=radio][name=DELIVERY_ID]:checked"
      );

    if (!deliveryCheckbox)
      deliveryCheckbox = this.deliveryHiddenBlockNode.querySelector(
        "input[type=hidden][name=DELIVERY_ID]"
      );

    if (deliveryCheckbox) {
      deliveryId = deliveryCheckbox.value;

      for (i in this.result.DELIVERY) {
        if (this.result.DELIVERY[i].ID == deliveryId) {
          currentDelivery = this.result.DELIVERY[i];
          break;
        }
      }
    }
    /**/

    $("#soa-property-3").mask("+7(999)999-99-99");
    return currentDelivery;
  };

  BX.Sale.OrderAjaxComponentExt.editOrder = function () {
    if (!this.orderBlockNode || !this.result || !this.orderSaveBlockNode)
      return;

    if (this.result.DELIVERY.length > 0) {
      BX.addClass(this.deliveryBlockNode, "bx-active");
      this.deliveryBlockNode.removeAttribute("style");
    } else {
      BX.removeClass(this.deliveryBlockNode, "bx-active");
      this.deliveryBlockNode.style.display = "none";
    }

    this.orderSaveBlockNode.style.display = this.result.SHOW_AUTH ? "none" : "";

    this.checkPickUpShow();

    var sections = this.orderBlockNode.querySelectorAll(
        ".bx-soa-section.bx-active"
      ),
      i;
    for (i in sections) {
      if (sections.hasOwnProperty(i)) {
        this.editSection(sections[i]);
      }
    }

    this.editTotalBlock();

    this.showErrors(this.result.ERROR, false);
    this.showWarnings();
  };

  BX.Sale.OrderAjaxComponentExt.editTotalBlock = function () {
    if (!this.result.TOTAL) return;
    var total = this.result.TOTAL;
    $(".js-total").html(total.ORDER_TOTAL_PRICE + " руб.");
    if (total.ORDER_TOTAL_PRICE > 0) {
      $(".js-delivery-price-value").text(total.ORDER_TOTAL_PRICE + " руб.");
      $(".js-order-total-container").show();
    } else {
      $(".js-delivery-price-value").text("");
      $(".js-order-total-container").hide();
    }
    if (total.DELIVERY_PRICE > 0) {
      $(".js-delivery-price-value").text(total.DELIVERY_PRICE + " руб.");
      $(".js-total-delivery-container").show();
    } else {
      $(".js-delivery-price-value").text("");
      $(".js-total-delivery-container").hide();
    }
  };

  BX.Sale.OrderAjaxComponentExt.activatePickUp = function (deliveryName) {
    this.pickUpBlockNode.style.display = "";
  };

  BX.Sale.OrderAjaxComponentExt.deactivatePickUp = function () {
    this.pickUpBlockNode.style.display = "none";
  };

  BX.Sale.OrderAjaxComponentExt.editPickUpBlock = function (active) {
    if (!this.pickUpBlockNode || !this.result.DELIVERY) return;

    this.initialized.pickup = false;

    this.editActivePickUpBlock(true);

    this.initialized.pickup = true;
  };

  BX.Sale.OrderAjaxComponentExt.editActivePickUpBlock = function () {
    var node = this.pickUpBlockNode,
      pickUpContent,
      pickUpContentCol;

    $(".js-checkout-step-delivery").hide();
    $(".js-pickup-content").show();
    $(".js-pickup-map").show();

    if (this.initialized.pickup) {
      if (
        this.params.SHOW_NEAREST_PICKUP === "Y" &&
        this.maps &&
        !this.maps.maxWaitTimeExpired
      ) {
        this.maps.maxWaitTimeExpired = true;
        this.initPickUpPagination();
        this.editPickUpList(true);
        this.pickUpFinalAction();
      }

      if (this.maps && !this.pickUpMapFocused) {
        this.pickUpMapFocused = true;
        setTimeout(BX.proxy(this.maps.pickUpMapFocusWaiter, this.maps), 200);
      }
    } else {
      pickUpContent = node.querySelector(".js-pickup-content");
      $(pickUpContent).html("");

      this.editPickUpMap(pickUpContent);
      this.editPickUpLoader(pickUpContent);

      if (
        this.params.SHOW_PICKUP_MAP != "Y" ||
        this.params.SHOW_NEAREST_PICKUP != "Y"
      ) {
        this.initPickUpPagination();
        this.editPickUpList(true);
        this.pickUpFinalAction();
      }
    }
  };

  BX.Sale.OrderAjaxComponentExt.pickUpFinalAction = function () {
    var selectedDelivery = this.getSelectedDelivery();

    if (selectedDelivery) {
      this.lastSelectedDelivery = parseInt(selectedDelivery.ID);
    }

    this.maps && this.maps.pickUpFinalAction();
  };

  BX.Sale.OrderAjaxComponentExt.createPickUpItem_sovpad = function (
    currentStore,
    options
  ) {
    options = options || {};
    var imgClassName = "bx-soa-pickup-l-item-detail",
      buttonClassName = "bx-soa-pickup-l-item-btn",
      logoNode,
      logotype,
      html,
      storeNode,
      imgSrc;

    if (this.params.SHOW_STORES_IMAGES === "Y") {
      logotype = this.getImageSources(currentStore, "IMAGE_ID");
      imgSrc = (logotype && logotype.src_1x) || this.defaultStoreLogo;
      logoNode = BX.create("IMG", {
        props: {
          src: imgSrc,
          className: "bx-soa-pickup-l-item-img",
        },
        events: {
          click: BX.delegate(function (e) {
            this.popupShow(e, (logotype && logotype.src_orig) || imgSrc);
          }, this),
        },
      });
    } else {
      imgClassName += " no-image";
      buttonClassName += " no-image";
    }

    html = this.getStoreInfoHtml(currentStore);

    // Для того, чтобы мы могли фильтровать рендер складов по городам, нам необходимо получить текущий город, а именно его ID.
    let city_id = document.querySelector(
      '.js-region-input input[name="ORDER_PROP_6"]'
    ).value;
    // В описании сейчас хранится ID города. Если есть совпадение соответственно фильтруем. Если нет, то не фильтруем.
    // Проверяем на совпадение и выводим.
    if (currentStore.DESCRIPTION !== city_id) {
      storeNode = BX.create("DIV", {
        props: {
          className: "bx-soa-pickup-list-item hide",
          id: "store-" + currentStore.ID,
        },
        children: [
          BX.create("DIV", {
            props: { className: "bx-soa-pickup-l-item-adress" },
            children: options.distance
              ? [
                  BX.util.htmlspecialchars(currentStore.ADDRESS),
                  " ( ~" +
                    options.distance +
                    " " +
                    BX.message("SOA_DISTANCE_KM") +
                    " ) ",
                ]
              : [BX.util.htmlspecialchars(currentStore.ADDRESS)],
          }),
          BX.create("DIV", {
            props: { className: imgClassName },
            children: [logoNode],
          }),
        ],
        events: {
          click: BX.proxy(this.selectStore, this),
        },
      });
    } else {
      storeNode = BX.create("DIV", {
        props: {
          className: "bx-soa-pickup-list-item",
          id: "store-" + currentStore.ID,
        },
        children: [
          BX.create("DIV", {
            props: { className: "bx-soa-pickup-l-item-adress" },
            children: options.distance
              ? [
                  BX.util.htmlspecialchars(currentStore.ADDRESS),
                  " ( ~" +
                    options.distance +
                    " " +
                    BX.message("SOA_DISTANCE_KM") +
                    " ) ",
                ]
              : [BX.util.htmlspecialchars(currentStore.ADDRESS)],
          }),
          BX.create("DIV", {
            props: { className: imgClassName },
            children: [logoNode],
          }),
        ],
        events: {
          click: BX.proxy(this.selectStore, this),
        },
      });
    }
    if (options.selected) BX.addClass(storeNode, "bx-selected");

    return storeNode;
  };
  // Рендер списка пунктов самовывоза (стандартная функция)
  BX.Sale.OrderAjaxComponentExt.createPickUpItem = function (
    currentStore,
    options
  ) {
    options = options || {};
    var imgClassName = "bx-soa-pickup-l-item-detail",
      buttonClassName = "bx-soa-pickup-l-item-btn",
      logoNode,
      logotype,
      html,
      storeNode,
      imgSrc;

    if (this.params.SHOW_STORES_IMAGES === "Y") {
      logotype = this.getImageSources(currentStore, "IMAGE_ID");
      imgSrc = (logotype && logotype.src_1x) || this.defaultStoreLogo;
      logoNode = BX.create("IMG", {
        props: {
          src: imgSrc,
          className: "bx-soa-pickup-l-item-img",
        },
        events: {
          click: BX.delegate(function (e) {
            this.popupShow(e, (logotype && logotype.src_orig) || imgSrc);
          }, this),
        },
      });
    } else {
      imgClassName += " no-image";
      buttonClassName += " no-image";
    }

    html = this.getStoreInfoHtml(currentStore);
    storeNode = BX.create("DIV", {
      props: {
        className: "bx-soa-pickup-list-item",
        id: "store-" + currentStore.ID,
      },
      children: [
        BX.create("DIV", {
          props: { className: "bx-soa-pickup-l-item-adress" },
          children: options.distance
            ? [
                BX.util.htmlspecialchars(currentStore.ADDRESS),
                " ( ~" +
                  options.distance +
                  " " +
                  BX.message("SOA_DISTANCE_KM") +
                  " ) ",
              ]
            : [BX.util.htmlspecialchars(currentStore.ADDRESS)],
        }),
        BX.create("DIV", {
          props: { className: imgClassName },
          children: [
            logoNode,
            // BX.create('DIV', {props: {className: 'bx-soa-pickup-l-item-name'}, text: currentStore.TITLE}),
            // BX.create('DIV', {props: {className: 'bx-soa-pickup-l-item-desc'}, html: html})
          ],
        }),
        // BX.create('DIV', {
        //     props: {className: buttonClassName},
        //     children: [
        //         BX.create('A', {
        //             props: {href: '', className: 'btn btn-sm btn-default'},
        //             html: this.params.MESS_SELECT_PICKUP,

        //         })
        //     ]
        // })
      ],
      events: {
        click: BX.proxy(this.selectStore, this),
      },
    });
    if (options.selected) BX.addClass(storeNode, "bx-selected");

    return storeNode;
  };

  BX.Sale.OrderAjaxComponentExt.selectStore = function (event) {
    var storeItem,
      storeInput = BX("BUYER_STORE"),
      selectedPickUp,
      storeItemId,
      i,
      k,
      page,
      target,
      h1,
      h2;

    if (BX.type.isString(event)) {
      storeItem = BX("store-" + event);
      if (!storeItem) {
        for (i = 0; i < this.pickUpPagination.pages.length; i++) {
          page = this.pickUpPagination.pages[i];
          for (k = 0; k < page.length; k++) {
            if (page[k].ID == event) {
              this.showPickUpItemsPage(++i);
              break;
            }
          }
        }
        storeItem = BX("store-" + event);
      }
    } else {
      target = event.target || event.srcElement;
      storeItem = BX.hasClass(target, "bx-soa-pickup-list-item")
        ? target
        : BX.findParent(target, { className: "bx-soa-pickup-list-item" });
    }

    if (storeItem && storeInput) {
      if (BX.hasClass(storeItem, "bx-selected")) return;

      selectedPickUp = this.pickUpBlockNode.querySelector(".bx-selected");
      storeItemId = storeItem.id.substr("store-".length);

      BX.removeClass(selectedPickUp, "bx-selected");
      BX.addClass(storeItem, "bx-selected");

      h1 = storeItem.clientHeight;
      // storeItem.style.overflow = 'hidden';

      h2 = storeItem.clientHeight;
      // storeItem.style.height = h1 + 'px';

      let storeName = $(storeItem).find(".bx-soa-pickup-l-item-name").text();
      let storeAddress = $(storeItem)
        .find(".bx-soa-pickup-l-item-adress")
        .text();

      // $('.js-pickup-description-name').text(storeName);
      // $('.js-pickup-description-address').text(storeAddress);
      // $('.js-pickup-description').show();

      // $('.bx-soa-pickup-list-item').not('.bx-selected').css('height', 'inherit');

      $(".js-store-address input").val(storeName + ": " + storeAddress);

      storeInput.setAttribute("value", storeItemId);
      this.maps && this.maps.selectBalloon(storeItemId);
    }
  };

  BX.Sale.OrderAjaxComponentExt.alignBasketColumns = function () {};
  BX.Sale.OrderAjaxComponentExt.editBasketBlock = function () {};
  BX.Sale.OrderAjaxComponentExt.editActiveBasketBlock = function () {};
  BX.Sale.OrderAjaxComponentExt.editFadeBasketBlock = function () {};
  BX.Sale.OrderAjaxComponentExt.editBasketItems = function () {};
  BX.Sale.OrderAjaxComponentExt.editBasketItemsHeader = function () {};
  BX.Sale.OrderAjaxComponentExt.createBasketItem = function () {};
  BX.Sale.OrderAjaxComponentExt.createBasketItemColumn = function () {};
  BX.Sale.OrderAjaxComponentExt.createBasketItemHiddenColumn = function () {};
  BX.Sale.OrderAjaxComponentExt.changeVisibleSection = function () {};
  BX.Sale.OrderAjaxComponentExt.switchOrderSaveButtons = function (state) {};
  BX.Sale.OrderAjaxComponentExt.editMobileTotalBlock = function (state) {};
})();

// НАЧАЛО ОБРАБОТЧИКА КОРЗИНЫ
// Клики плюс - минус
$(document).on("click", ".js-count-plus, .js-count-minus", function (e) {
  if ($(this).attr("disabled")) {
    return false;
  }
  $(this).closest(".cart-table__control").find(".js-product-quantity").change();
});

$(document).on("click", ".js-count-minus", function (e) {
  if ($(this).attr("disabled")) {
    return false;
  }
  var $input = $(this)
    .parent(".item__quantity__minus")
    .next()
    .find(".js-count-input");
  var count = parseInt($input.val()) - 1;
  if (count < 1) {
    $('.js-product-delete[data-id="' + $input.attr("data-id") + '"]').addClass(
      "bounce"
    );
    setTimeout(function () {
      if ($(".js-product-delete").hasClass("bounce")) {
        $(".js-product-delete").removeClass("bounce");
      }
    }, 2000);
    return false;
  }
  count = count < 1 ? 1 : count;
  $input.val(count);
  $input.change();
  return false;
});

$(document).on("click", ".js-count-plus", function (e) {
  if ($(this).attr("disabled")) {
    return false;
  }
  var $input = $(this)
    .parent(".item__quantity__plus")
    .prev()
    .find(".js-count-input");
  $input.val(parseInt($input.val()) + 1);
  $input.change();
  return false;
});
// END клики плюс - минус

document.addEventListener("DOMContentLoaded", () => {
  document
    .querySelector('.promocode__checkbox input[type="checkbox"]')
    .addEventListener("change", (e) => {
      document.querySelector(".promocode__block").classList.toggle("active");
    });
  // Jquery, но по другому событие не отрабатывало. Изменение инпута количества товаров Плюс и Минус.
  $(document).on("change", ".js-count-input", function (e) {
    updateBasket(
      "changeQuantity",
      $(this).attr("data-id"),
      $(this).val(),
      "",
      $(this).attr("quantity-id") //добавил id для сравнения с кнопкой на которую нажимаем
    );
  });
  // Удаляем товар по кнопке удалить.
  $(document).on("click", ".js-product-delete", function (e) {
    let deletedBlock = document.querySelector(
      '.basket__item[data-id="' + e.target.dataset.id + '"]'
    );
    deletedBlock.remove();
    updateBasket("delete", $(this).attr("data-id"));
  });
  // Применяем промокод.
  document
    .querySelector(".promocode__button .promocode__apply")
    .addEventListener("click", (e) => {
      let promo__input = document.querySelector("#promocode");
      if (promo__input.value.length === 0) {
        let promo__callback = document.querySelector(".promocode__callback");
        promo__input.style.boxShadow = "0px 0px 0px 1px #C10017";
        promo__callback.style.color = "#C10017";
        promo__callback.textContent = "Введите промокод";
        promo__callback.classList.remove("hide");
      } else {
        updateBasket("applyCoupon", "", "", promo__input.value);
      }
    });
  // Удаляем промокод.
  document.querySelector(".js-delete-coupon").addEventListener("click", (e) => {
    updateBasket("deleteCoupon", "", "", e.target.dataset.id);
    e.target.parentNode.classList.add("promo__empty");
  });
});

let BasketUpdate = (data) => {
  let price = document.querySelector(".js-price-value"),
    discount_price = document.querySelector(".cart__panel__discount"),
    delivery = document.querySelectorAll(".js-delivery-price-value"),
    total = document.querySelector(".js-total");
  discountDK = document.querySelector(".cart__panel__discountDK");
  // Если цена = 0 (товаров в корзине нет!).
  if (
    data.PRICE_INFORMATION.PRICE === 0 &&
    data.PRICE_INFORMATION.BASE_PRICE === 0
  ) {
    let basket = document.querySelector(".basket");
    let basket__empty = document.querySelector(".basket__empty");
    basket.style.opacity = "0";
    basket.style.visibility = "hidden";
    setTimeout(() => {
      basket.remove();
    }, 1000);
    setTimeout(() => {
      basket__empty.classList.add("show");
      basket__empty.innerHTML = `
            <div class="bx-soa-empty-cart-container">
            <div class="basket__empty show">Ваша корзина пуста, добавьте товары в корзину
            <div class="bx-soa-empty-cart-desc">
            <a href="/">Нажмите здесь</a>, чтобы продолжить покупки		</div>
            </div>
            </div>
            `;
    }, 900);
    return false;
  }

  // Обновляем цену для всех доставок.
  for (i = 0; i < delivery.length; i++) {
    delivery[i].textContent = data.PRICE_INFORMATION.PRICE_DELIVERY + " руб.";
  }

  for (i = 0; i < data.BASKET_ITEMS.length; i++) {
    document.querySelector(
      '.basket__item__price[data-id="' + data.BASKET_ITEMS[i].ID + '"]'
    ).textContent = data.BASKET_ITEMS[i].PRICE + " руб.";
  }
  // Обновляем цену на панели.
  price.textContent = data.PRICE_INFORMATION.BASE_PRICE + " руб.";
  total.textContent = data.PRICE_INFORMATION.PRICE + " руб.";

  // Если есть скидка при рассчетах.
  if (data.PRICE_INFORMATION.DISCOUNT_VALUE > 0) {
    discount_price.classList.remove("discount__empty");
    discount_price
      .querySelector(".cart__panel__discount__value")
      .classList.remove("discount__empty");
    discount_price.querySelector(".cart__panel__discount__value").textContent =
      "- " + data.PRICE_INFORMATION.DISCOUNT_VALUE + " руб.";
  } else {
    discount_price.classList.add("discount__empty");
    discount_price
      .querySelector(".cart__panel__discount__value")
      .classList.add("discount__empty");
    discount_price.querySelector(".cart__panel__discount__value").textContent =
      "";
  }

  if (data.PRICE_INFORMATION.DISCOUNT_DK > 0) {
    discountDK.classList.remove("discountDK__empty");
    discountDK
      .querySelector(".cart__panel__discountDK__value")
      .classList.remove("discountDK__empty");
    discountDK.querySelector(".cart__panel__discountDK__value").textContent =
      "- " + data.PRICE_INFORMATION.DISCOUNT_DK + " руб.";
  } else {
    discountDK.classList.add("discountDK__empty");
    discountDK
      .querySelector(".cart__panel__discountDK__value")
      .classList.add("discountDK__empty");
    discountDK.querySelector(".cart__panel__discountDK__value").textContent =
      "";
  }

  // Если мы обновляем корзину по купону.
  if (data.COUPON) {
    let promo__input = document.querySelector(".promocode__input input");
    let promo__callback = document.querySelector(".promocode__callback");
    let promocode_block = document.querySelector(".promocode");
    let promo__delete = document.querySelector(".js-delete-coupon");
    // Если статус TRUE.
    if (data.COUPON.STATUS) {
      // Если применяем купон
      if (data.COUPON.ACTION === "APPLY") {
        promo__input.style.boxShadow = "none";
        promo__callback.removeAttribute("style");
        promocode_block.classList.remove("promo__empty");
        promo__delete.setAttribute("data-id", data.COUPON.NAME);
        promocode_block.querySelector(".promocode__value").textContent =
          data.COUPON.NAME;
      }
      // Если удаляем купон
      else if (data.COUPON.ACTION === "DELETE") {
        promo__input.style.boxShadow = "none";
        promocode_block.classList.add("promo__empty");
        promo__delete.removeAttribute("data-id");
        promocode_block.querySelector(".promocode__value").textContent = "";
      }
    }
    // Если статус обработчика false.
    else {
      promo__input.style.boxShadow = "0px 0px 0px 1px #C10017";
      promo__callback.style.color = "#C10017";
    }
    promo__callback.classList.remove("hide");
    promo__callback.textContent = data.COUPON.TEXT;
    promo__input.value = "";
  }
};

// ТУТ ИЗМЕНЕНИЯ
//  Осуществляем проверку по кол-ву товара, который доступен к заказу
let CheckQuantityOfGoods = async (id) => {
  // let plustBtn = document.querySelector(`[quantity-id='${id}'] .js-count-plus`);
  let quantityInput = document.querySelector(
    `[quantity-id='${id}'] .js-count-input`
  );

  quantityInput.value = quantityInput.value - 1;

  updateBasket(
    "changeQuantity",
    quantityInput.dataset.id,
    quantityInput.value,
    "",
    id
  );
};
// ТУТ ИЗМЕНЕНИЯ КОНЕЦ

let updateBasket = async (
  action,
  id_element,
  quantity,
  promocode,
  quantityId
) => {
  // Функция начала обновления. Тут запрос один, поэтому промисы пока что оставим, хватит и async/await.
  waitUpdate("[data-update]");
  let delivery_id = document.querySelector(
    'input[name="DELIVERY_ID"]:checked'
  ).value;
  // на дворе существует ES6, поэтому зачем нам XMLHttpRequest или Ajax, если есть асинхронный fetch с Promise
  let url = "/local/ajax/UpdateBasket.php"; // Пишем URL запроса
  let data = {
    action: action,
    id_element: id_element,
    quantity: quantity,
    delivery_id: delivery_id,
    promocode: promocode,
  }; // Указываем какие данные отправляем (для тела запроса), в нашем случае объект
  try {
    // Выполним конструкцию try и catch (можно выполнить конструкцию then/catch)
    let response = await fetch(url, {
      // выполняем fetch
      method: "POST",
      // указываем, что это POST
      headers: {
        // укажем заголовок объекта
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    });
    // переменная выше
    let callback = await response.json(); // укажем, что получаем json
    // Делаем зависимости, конечно будет CallBackHall, но а куда деваться.
    // Проверяем на то, что в обработчике STATUS = true.
    // console.log(callback);
    if (callback.STATUS) {
      // ТУТ ИЗМЕНЕНИЯ
      callback.BASKET_ITEMS.map((element) => {
        if (Number(quantityId) === element.ID) {
          if (element.PRICE === 0 && element.BASE_PRICE === 0) {
            // блочим обновление корзины у элемента с недостаточным кол-м и откатываем назад на имеющееся значение
            CheckQuantityOfGoods(Number(quantityId));
            // Обновляем корзину
            BasketUpdate(callback);
          } else {
            // Обновляем корзину
            BasketUpdate(callback);
          }
        }
      });
      // ТУТ ИЗМЕНЕНИЯ КОНЕЦ
      // Обновляем корзину
      // BasketUpdate(callback);
    } else {
      alert("Ошибка в обработчике");
    }
  } catch (error) {
    // что будем делать, если ошибка (К примеру коллбэка нет или ошибка 500, а также коллбэк не JSON.
    alert("Ошибка связи с обработчиком -" + error);
  }
  // Конец обновления.
  endUpdate("[data-update]");
};

let waitUpdate = (items) => {
  let update__items = document.querySelectorAll(items);
  for (let i = 0; i < update__items.length; i++) {
    let update__item = update__items[i];
    update__item.classList.add("updating");
    if (update__item.tagName === "BUTTON" || update__item.tagName === "INPUT") {
      update__item.setAttribute("disabled", "");
    }
  }
};
let endUpdate = (items) => {
  let update__items = document.querySelectorAll(items);
  for (let i = 0; i < update__items.length; i++) {
    let update__item = update__items[i];
    update__item.classList.remove("updating");
    if (update__item.tagName === "BUTTON" || update__item.tagName === "INPUT") {
      update__item.removeAttribute("disabled");
    }
  }
};

// КОНЕЦ ОБРАБОТЧИКА КОРЗИНЫ
