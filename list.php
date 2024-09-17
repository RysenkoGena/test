<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/*$t = memory_get_peak_usage();
var_dump( $t);
lg($t);*/
//$APPLICATION->SetTitle("Список полезных ссылок");
?>

ИТ:<br>
 <a href="/inc/orders/?status=N&pass=kabel1God">Необработанные новые заказы без оплаты</a><br>
 <a href="/inc/orders/?status=NS&pass=kabel1God">Необработанные новые заказы c оплатой</a><br>
    <a href="/inc/users/?pass=kabel1God">Обмен пользовательскими свойствами</a><br>
    <a href="/test/clearLog.php">Очистить лог файл log.txt</a><br>


    <br>Маркетинг: <br>
    <a href="/test/search/searchStat.php">WordStat</a><br>
    <a href="/test/SEO/sinonim.php">Поисковые синонимы</a><br>
    <a href="/test/log/">Журналы посещений авторизованных пользователей</a><br>
 <a href="/test/no-photo.php">Список товаров без фото</a><br>
    <a href="/test/no-photo2.php">Список товаров без фото в 1с (но есть на сайте)</a><br>

    <a href="/test/checkDescriptions.php">Отчет по заполненности описаний товаров</a><br>

    <a href="/test/VK/exist.php?section=95383">Фиды для каталогов</a><br>

<div>
 <a href="/test/VK/xmlForVK.php">для ВК 40 позиций</a> или <a href="https://yugkabel.ru/test/VK/xmlForVK2.php">каких-то 34 позиции</a> или <a href="/test/VK/exist.php">Все что в наличии</a> или <a href="/test/VK/all.php">Весь каталог</a>
</div>
<div>
 <a href="/test/users/users.php">Список клиентов с ClientId от Yandex.Metrika</a>
</div>

    <a href="/test/MyGiftBalance.php">Баланс MyGift</a><br>
    <a href="/test/SEO/getSeoText.php?section=53">SEO тексты</a><br>
    <div>
        <a href="/test/users/test.php">Список последних 100 авторизованных посетителя с yandex_id</a><br>
    </div>

<div>
	 YMetric clientId = <span id="clientId"></span>
    <script>
    ym(135067, 'getClientID', function(clientID) {
        document.getElementById("clientId").innerHTML = clientID;
    });

</script>
</div>
    <br></br>Телефония: <br>
    <a href="/test/tel/ext.php">Количеcтво принятых внешний звонков, помесячно.</a><br>
    <a href="/test/tel/">Количеcтво принятых внешний звонков, в разрезе добавочных.</a><br>
    <a href="/test/m2.php">Количеcтво принятых внешний звонков, по дням.</a><br>
    <a href="/test/tel/outbound.php">Журнал записей разговоров внешних исходящих звонков.</a><br>

 <br>
Сбыт:<br>
        <a href="/inc/ajax/away_basket.php">Список забытых корзин</a><br>


<?
 //d($_SERVER);
$e = print_r($_SERVER, true);
//echo $e;

 require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>