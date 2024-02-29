<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var $APPLICATION CMain
 */

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}
?>

<? if (!empty($arResult["ORDER"])): ?>

    <div class="checkout-steps offset-lg-1">
        <div class="checkout-step">
            <div class="checkout-step__body checkout-step__body--thank-you">
                <div class="checkout-step__header">
                  
                    <div class="checkout-step__title">
                        <?=Loc::getMessage("SOA_ORDER_SUC", array(
                            "#ORDER_DATE#" => $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i'),
                            "#ORDER_ID#" => $arResult["ORDER"]["ACCOUNT_NUMBER"]
                        ))?>
                    </div>
                </div>
               
                <div class="checkout-step__inner">
                    <div>
                        <?
                        if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
                        {
                            if (!empty($arResult["PAYMENT"]))
                            {
                                foreach ($arResult["PAYMENT"] as $payment)
                                {
                                    if ($payment["PAID"] != 'Y')
                                    {
                                        if (!empty($arResult['PAY_SYSTEM_LIST'])
                                            && array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
                                        )
                                        {
                                            $arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];

                                            if (empty($arPaySystem["ERROR"]))
                                            {
                                                ?>
                                                <p style="margin: 30px 0;"><?=Loc::getMessage("SOA_PAY") ?>: <?=$arPaySystem["NAME"] ?></p>
                                                <div>
                                                    <? if ($arPaySystem["ACTION_FILE"] <> '' && $arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
                                                        <?
                                                        $orderAccountNumber = urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]));
                                                        $paymentAccountNumber = $payment["ACCOUNT_NUMBER"];
                                                        ?>
                                                        <script>
                                                            window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=$orderAccountNumber?>&PAYMENT_ID=<?=$paymentAccountNumber?>');
                                                        </script>
                                                    <?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
                                                    <? if (CSalePdf::isPdfAvailable() && $arPaySystem['IS_AFFORD_PDF']): ?>
                                                    <br/>
                                                        <?=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
                                                    <? endif ?>
                                                    <? else: ?>
                                                        <?=$arPaySystem["BUFFERED_OUTPUT"]?>
                                                    <? endif ?>
                                                </div>
                                                <?
                                            }
                                            else
                                            {
                                                ?>
                                                <span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
                                                <?
                                            }
                                        }
                                        else
                                        {
                                            ?>
                                            <span style="color:red;"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></span>
                                            <?
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            ?>
                            <br /><strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong>
                            <?
                        }
                        ?>

                        <? else: ?>

                            <b><?=Loc::getMessage("SOA_ERROR_ORDER")?></b>
                            <div>
                                <?=Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])])?>
                                <?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
                            </div>

                        <? endif ?>
                    </div>
                </div>
                 </div>
        </div>
    </div>

<?

$order = \Bitrix\Sale\Order::loadByAccountNumber($arResult["ACCOUNT_NUMBER"]);

$order_props = $order->getPropertyCollection();


$phone=$order_props->getPhone()->getValue();     // телефон
$phone=preg_replace('/[^0-9]/', '', $phone);
$to = sprintf("+%s(%s)%s-%s-%s",
	substr($phone, 0, 1),
	substr($phone, 1, 3),
	substr($phone, 4, 3),
	substr($phone, 7, 2),
	substr($phone, 9)
);

$phoneProp = $order_props->getPhone();
$phoneProp->setValue($to);
$order->doFinalAction(true);
$result = $order->save();
?>



