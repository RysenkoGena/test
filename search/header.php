<?
session_start();
if(!isset($_SESSION['ab'] ) || isset($_GET['utm_source'])){
    $_SESSION['ab'] = rand(1,2);
    $_SESSION['adr'] = $_SERVER['REQUEST_URI'];
    $_SESSION['ref'] = $_SERVER['HTTP_REFERER'];
    if(isset($_GET['keyword'])) $_SESSION['key'] = $_GET['keyword'];
}
if(isset($_GET['ab'])) $_SESSION['ab'] = $_GET['ab'];
// Не пустим в кабинет, если создан из заказа
if(CSite::InDir("/auth/") && $_SESSION['iarga_mail_auth']=="1"){
    $_SESSION['iarga_mail_auth']="0";
    global $USER;
    $USER->Logout();
}
$arr = explode("/", $_SERVER['REQUEST_URI']);
$tetx = "-is-";
$pos = strpos($_SERVER['REQUEST_URI'], $tetx);
$detail_page = sizeof($arr) > 5  && CSite::InDir("/products/") && $pos === false;
if(CSite::InDir("/products/all/")){$detail_page = false;}
if(CSite::InDir("/basket/")) $detail_page = true;
if(CSite::InDir("/basket/order/")) $detail_page = true;
if(CSite::InDir("/products/index.php")) $detail_page = true;
// if(CSite::InDir("/special/index.php")) $detail_page = true;
$templateFolder = SITE_TEMPLATE_PATH;
$templatePath = $_SERVER['DOCUMENT_ROOT'].$templateFolder;
include($templatePath.'/inc/functions.php');
IncludeTemplateLangFile(__FILE__);

?><!DOCTYPE html>
<html>
<head>
    <title><?=$APPLICATION->ShowTitle()?></title>
    <meta name="cmsmagazine" content="90d3bb21e7721baf5d3909d423ae0f21" />
    <meta property="og:title" content="<?=$APPLICATION->ShowTitle()?>" />
    <meta property="og:description" content="<?=$APPLICATION->ShowProperty("description")?>" />
    <meta name="yandex-verification" content="798163a9de1cfb29" />
    <meta charset="<?=BX_UTF?'utf-8':'cp-1251'?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.5, user-scalable=yes">
    <link rel="shortcut icon" href="/favicon.png" type="image/png" />

    <?if(isset($_SERVER['argv'][0])){
        $adr=explode("?",$_SERVER["REQUEST_URI"]);?>
        <link rel="canonical" href="http://<?=$_SERVER["HTTP_HOST"].$adr[0]?>"/>
    <?}?>
    <?$APPLICATION->ShowViewContent('Canonical');?>

    <script>var SITE_DIR = "<?=SITE_DIR?>";</script>


    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/styles/base.css")?>
    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/styles/media-queries.css")?>
    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/styles/iarga.css")?>
    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/styles/jquery.fancybox.css")?>
	<?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/styles/faq.css")?>
    
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery-1.11.1.min.js")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/base.js")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.fancybox.pack.js")?>

    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/func.js")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/shop.js")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/iarga.js")?>

    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/royalslider/royalslider.css")?>
    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/royalslider/skins/default/rs-default.css")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/royalslider/jquery.royalslider.min.js")?>

    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.maskedinput.min.js")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.touchSwipe.min.js")?>

    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/magnific-popup/jquery.magnific-popup.min.js")?>
    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/magnific-popup/magnific-popup.css")?>

    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.selectric.min.js")?>

    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/owl-carousel/owl.carousel.css")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/owl-carousel/owl.carousel.min.js")?>

    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.sticky.js")?>

    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/jscroll/jquery.jscrollpane.css")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jscroll/jquery.jscrollpane.min.js")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jscroll/jquery.mousewheel.js")?>

    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/mmenu/jquery.mmenu.all.css")?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/mmenu/jquery.mmenu.all.min.js")?>
                   
    <?$APPLICATION->ShowHead()?>

    <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!--[if lte IE 7]>
        <link href="<?=SITE_TEMPLATE_PATH?>/styles/base.ie.css" rel="stylesheet">
    <![endif]-->
    <!--[if lt IE 9]>
        <script src="<?=SITE_TEMPLATE_PATH?>/js/respond.min.js"></script>
    <![endif]-->

<script src="https://vk.com/js/api/openapi.js?169"></script>
<script>
    VK.Retargeting.Init("VK-RTRG-1084059-6d1ow");
    VK.Retargeting.Hit();
    function VkSearch(){
        VK.Retargeting.Event('search')
        //console.log(VK);
    }
</script>
</head>
<body>
<!-- Rating@Mail.ru counter -->
<script type="text/javascript">//<![CDATA[
var _tmr = _tmr || [];
_tmr.push({id: "2399489", type: "pageView", start: (new Date()).getTime()});
(function (d, w) {
   var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true;
   ts.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//top-fwz1.mail.ru/js/code.js";
   var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
   if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
})(document, window);
//]]></script><noscript><div style="position:absolute;left:-10000px;">
<img src="//top-fwz1.mail.ru/counter?id=2399489;js=na" style="border:0;" height="1" width="1" alt="Рейтинг@Mail.ru" />
</div></noscript>

<?$APPLICATION->ShowPanel()?>
<?CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');

                            if(isset($_GET['city'])){
								$_SESSION['city'] = $_GET['city'] ; //$_SESSION['city_id'] = $_GET['city_id'] ;
								LocalRedirect($_SERVER['HTTP_REFERER']);
							}
                            elseif(!isset($_SESSION['city'])) $_SESSION['city'] = ip_city();

				$cs = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array("IBLOCK_ID"=>"5","ACTIVE"=>"Y","NAME"=>$_SESSION['city']))->GetNext();
				if($cs["NAME"]==NULL){
					$cs = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array("IBLOCK_ID"=>"5","ACTIVE"=>"Y"))->GetNext();
				}
				$c = GetIBlockElement($cs['ID']);

				global $cont;
				$c['PHONE'] = $c['PROPERTIES']['phone']['VALUE'][0];
			        $c['MAIL'] = $c['PROPERTIES']['mail']['VALUE'];
			        $c['TIME'] = $c['PROPERTIES']['worktime']['VALUE'];
			        $c['NUMBER'] = preg_replace("#[^0-9]#","",$c['PHONE']);
			        $c['NUMBER'] = str_replace("++","+",$c['NUMBER']);
				$cont[] = $c;?>
    <div id="card_product" class="lightbox mfp-hide"></div>
    <div class="main-block">
        <header>
<?
\CModule::IncludeModule('highloadblock');
$hl_data = \Bitrix\Highloadblock\HighloadBlockTable::getById(5)->fetch();
$hl_entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hl_data);
$hl_data_class = $hl_data['NAME'] . 'Table'; 
$res = $hl_data_class::getList();

while ($row = $res->fetch()){ 
   $redLineText = $row["UF_TEXT"];
   $redLineLink = $row["UF_LINK"];
   break;
}?>
<table border=0 width=100% bgcolor=red cellpadding="1" height=31>
	<tr align=center><td valign=middle bgcolor><p style="margin:0 0 0 0;color:#FAC407; font-size:140%; font-weight:bold;"><a style="margin:0 0 0 0;color:#FAC407; font-size:140%; font-weight:bold;" href="<?=$row["UF_LINK"]?>"><?=$row["UF_TEXT"]?></a>
</table>
            <div class="hhead">
                <div class="wrapper">
                    <div class="left">
                        <div class="menu">
                            <div class="menu-box">
                                <?$APPLICATION->IncludeComponent(
                                	"bitrix:menu",
                                	"menutop",
                                	array(
                                		"ROOT_MENU_TYPE" => "top",
                                		"MENU_CACHE_TYPE" => "A",
                                		"MENU_CACHE_TIME" => "3600",
                                		"MENU_CACHE_USE_GROUPS" => "Y",
                                		"MENU_CACHE_GET_VARS" => array(
                                		),
                                		"MAX_LEVEL" => "2",
                                		"CHILD_MENU_TYPE" => "left",
                                		"USE_EXT" => "N",
                                		"DELAY" => "N",
                                		"ALLOW_MULTI_SELECT" => "N",
                                		"COMPONENT_TEMPLATE" => "menutop"
                                	),
                                	false
                                );?>
                            </div><!--.menu-box-end-->
                        </div><!--.menu-end-->
                    </div><!--.left-end-->
                   <div class="right">
                        <div class="enter">
                                <?$APPLICATION->IncludeComponent("bitrix:system.auth.form", "authForm", Array(

                                    ),
                                    false
                                );?>
                        </div>
                        <?if($USER->IsAuthorized()) {
	                        ?><?$Users = CUser::GetList(($by="personal_country"), ($order="desc"), array("ID"=>$USER->GetID()), array("SELECT"=>array("UF_*")))->NavNext(true, "f_");?>
                        <span style="color:#ffd700; background-color:#ee0000;border-radius:3px; padding:5px; font-weight: bold;">
                        Бонусы: <?=(int)$Users["UF_BAL"]?> 
                        </span>
                        <?}?>
                        <!--.enter-end-->
                   </div><!--.right-end-->
                   <div class="clr"></div>
                </div><!--.wrapper-end-->
            </div><!--.hhead-end-->
            <div class="bhead">
                <div class="wrapper">
                    <div class="logo">
                        <a <?=(!CSite::InDir('/index.php'))?'href="/"':'';?>><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png"></a>
			<div class="city">
                            <?
                            $frame = new \Bitrix\Main\Page\FrameBuffered("city_dynamic");
                            $frame->begin();?>

                                <span>Ваш город</span>
                                <a href="#city" class="lightbox-inline-open"><?=$_SESSION['city']?> </a>
                                <script>addlightbox();</script>
                            <?$frame->beginStub();?>
                                <span>Ваш город</span>
                                <a href="#city" class="lightbox-inline-open">   </a>
                            <?$frame->end();?>
                        </div><!--.city-end-->
                    </div><!--.logo-end-->
                    <div class="right">
                        <div class="phone">
                            <?global $cont;?>

                             <p>по России звонок бесплатный</p>
                            <a href="tel:<?=$cont[0]['NUMBER']?>" class="tel"><?=$cont[0]['PHONE']?></a>
                        </div><!--.phone-end-->
                        <div class="box">
                            <div><a href="#order-call" class="bt_order_call lightbox-inline-open">заказать звонок</a></div>
                            <div><a href="mailto:<?=$cont[0]['MAIL']?>" class="mail"><?=$cont[0]['MAIL']?></a></div>
                        </div><!--.bo-end-->
                    </div><!--.right-end-->

                    <div class="center-box">
                        <p class="logo-text">Ваша <span>электро</span>безопасность</p>
						<img src="<?=SITE_TEMPLATE_PATH?>/images/sklad.jpg">
                        <div class="search-stick">
                            <form action="/products/">
                                <input type="text"  class="input_search tooltip" value="<?=htmlspecialcharsbx($_GET['qa'])?>" name="qa" placeholder="Поиск">
                                <input type="submit" value="">
                            </form>
                        </div><!--.search-stick-end-->
                    </div><!--.center-box-end-->

                    <div class="clr"></div>
                </div><!--.wrapper-end-->
            </div><!--.bhead-->
            <div class="fhead">
                <div class="wrapper">
					<?if(CSite::InDir("/auth/")):?>
	                    <a href="#mmenu" class="title_menu link-menu-mobile">Кабинет</a>
					<?elseif(CSite::InDir("/products/") or CSite::InDir("/special/") or $detail_page):?>
						<?$APPLICATION->ShowViewContent("filth");?>
					<?else:?>
	                	<p class="title_menu">Каталог товаров</p>
					<?endif;?>
                    <a href="#mmenu" class="link-menu-mobile">
					<?if(CSite::InDir("/auth/")):?>Кабинет<?else:?>Каталог<?endif;?></a>
					<div class="right">
                        <div class="search">
                            <form action="/products/">
                                <input type="text" class="input_search tooltip" value="<?=htmlspecialcharsbx($_GET['qa'])?>" name="qa" placeholder="Поиск"  onClick="VkSearch()">
                				<select name="select">
                					<option value="">Все товары</option>
                					<?$uf_name = Array("UF_ALTNAME");
                					$arSt = array('IBLOCK_ID' => 4, 'ACTIVE'=>'Y', 'DEPTH_LEVEL'=>'1');
                					   $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arSt, false, $uf_name);
                					   while ($Sectlist = $rsSect->GetNext())
                					   {
                					if($Sectlist["UF_ALTNAME"]){$name = $Sectlist["UF_ALTNAME"];}else{$name = $Sectlist['NAME'];}?>
                						<option value="<?=$Sectlist['ID'];?>" <?if($_REQUEST["select"]==$Sectlist['ID']):?>class="selected" selected<?endif;?>><?=$name;?></option>
                					<?}?>
                				</select>
                                <input type="submit"  class="bt_searhc" value="найти"  onClick="VkSearch()">

                            </form>
                                <img src="<?=SITE_TEMPLATE_PATH?>/images/bannerFind.png" width=515>
                        </div><!--.search-end-->
                        <div class="control">
                            <a href="/basket/" class="basket info-amount"><?include($_SERVER['DOCUMENT_ROOT']."/inc/ajax/basket.php")?></a>

                            <a href="/deferred/" class="deferred">
				<?include($_SERVER['DOCUMENT_ROOT']."/inc/ajax/deferred.php");?>

                            </a><!--.deferred-end-->
                            <a href="/favorite/" class="comparison info-favorites">
				<?include($_SERVER['DOCUMENT_ROOT']."/inc/ajax/compare.php");?>
                            </a><!--.comparison-end-->
                        </div><!--.control-end-->
                    </div><!--.right-end-->
                    <div class="clr"></div>
                </div><!--.wrapper-end-->
            </div><!--.fhead-end-->
            <div class="sticky">
                <div class="wrapper">

                </div><!--.wrapper-end-->
            </div><!--.sticky-end-->
        </header>
        <section class="main">
            <div class="wrapper">
				<?if(CSite::InDir("/auth/") && $USER->IsAuthorized()):?>
                	<aside class="l-sb" <?if($detail_page):?>style="display: none;"<?endif;?>>
	                <?$APPLICATION->IncludeComponent(
						"bitrix:menu",
						"aside-left",
						array(
							"ROOT_MENU_TYPE" => "left",
							"MENU_CACHE_TYPE" => "N",
							"MENU_CACHE_TIME" => "3600",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => array(
							),
							"MAX_LEVEL" => "1",
							"CHILD_MENU_TYPE" => "left",
							"USE_EXT" => "Y",
							"DELAY" => "N",
							"ALLOW_MULTI_SELECT" => "N",
							"COMPONENT_TEMPLATE" => "aside-left"
						),
						false
					);?>
				<?elseif(!CSite::InDir("/products/") and !CSite::InDir("/special/") and !CSite::InDir("/basket/")):?>
                	<aside class="l-sb" 
						<?if($detail_page):?>style="display: none;"<?endif;?>
					>
					<?$APPLICATION->IncludeComponent(
						"bitrix:menu",
						"aside",
						array(
							"ROOT_MENU_TYPE" => "cath",
							"MENU_CACHE_TYPE" => "Y",
							"MENU_CACHE_TIME" => "36000",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => array(
							),
							"MAX_LEVEL" => "2",
							"CHILD_MENU_TYPE" => "left",
							"USE_EXT" => "Y",
							"DELAY" => "N",
							"ALLOW_MULTI_SELECT" => "N",
							"COMPONENT_TEMPLATE" => "aside"
						),
						false
					);?>
					<?$APPLICATION->IncludeComponent(
						"bitrix:news.list",
						"reviews_left",
						array(
							"COMPONENT_TEMPLATE" => "reviews_left",
							"IBLOCK_TYPE" => "shop",
							"IBLOCK_ID" => "3",
							"NEWS_COUNT" => "4",
							"SORT_BY1" => "PROPERTY_date",
							"SORT_ORDER1" => "DESC",
							"SORT_BY2" => "SORT",
							"SORT_ORDER2" => "ASC",
							"FILTER_NAME" => "",
							"FIELD_CODE" => array(
								0 => "",
								1 => "",
							),
							"PROPERTY_CODE" => array(
								0 => "date",
								1 => "city",
								2 => "user",
								3 => "",
							),
							"CHECK_DATES" => "Y",
							"DETAIL_URL" => "",
							"AJAX_MODE" => "N",
							"AJAX_OPTION_JUMP" => "N",
							"AJAX_OPTION_STYLE" => "Y",
							"AJAX_OPTION_HISTORY" => "N",
							"AJAX_OPTION_ADDITIONAL" => "",
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "36000000",
							"CACHE_FILTER" => "N",
							"CACHE_GROUPS" => "Y",
							"PREVIEW_TRUNCATE_LEN" => "",
							"ACTIVE_DATE_FORMAT" => "d.m.Y",
							"SET_TITLE" => "N",
							"SET_BROWSER_TITLE" => "N",
							"SET_META_KEYWORDS" => "N",
							"SET_META_DESCRIPTION" => "N",
							"SET_LAST_MODIFIED" => "N",
							"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
							"ADD_SECTIONS_CHAIN" => "N",
							"HIDE_LINK_WHEN_NO_DETAIL" => "N",
							"PARENT_SECTION" => "",
							"PARENT_SECTION_CODE" => "",
							"INCLUDE_SUBSECTIONS" => "Y",
							"DISPLAY_DATE" => "Y",
							"DISPLAY_NAME" => "Y",
							"DISPLAY_PICTURE" => "Y",
							"DISPLAY_PREVIEW_TEXT" => "Y",
							"PAGER_TEMPLATE" => ".default",
							"DISPLAY_TOP_PAGER" => "N",
							"DISPLAY_BOTTOM_PAGER" => "N",
							"PAGER_TITLE" => "Новости",
							"PAGER_SHOW_ALWAYS" => "N",
							"PAGER_DESC_NUMBERING" => "N",
							"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
							"PAGER_SHOW_ALL" => "N",
							"PAGER_BASE_LINK_ENABLE" => "N",
							"SET_STATUS_404" => "N",
							"SHOW_404" => "N",
							"MESSAGE_404" => ""
						),
						false
					);?>
				<?endif;?>

				<aside class="l-sb" 
						<?if($detail_page):?>style="display: none;"<?endif;?>
					>
					<?$APPLICATION->IncludeComponent(
						"bitrix:menu",
						"aside",
						array(
							"ROOT_MENU_TYPE" => "cath",
							"MENU_CACHE_TYPE" => "Y",
							"MENU_CACHE_TIME" => "36000",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => array(
							),
							"MAX_LEVEL" => "2",
							"CHILD_MENU_TYPE" => "left",
							"USE_EXT" => "Y",
							"DELAY" => "N",
							"ALLOW_MULTI_SELECT" => "N",
							"COMPONENT_TEMPLATE" => "aside"
						),
						false
					);?>

                <?$APPLICATION->ShowViewContent("filter");?>
                </aside><!--.l-sb-end-->
				<?if(!CSite::InDir('/index.php') and !CSite::InDir('/products/') and !CSite::InDir('/special/')):?>
					<?$APPLICATION->IncludeComponent(
					    "bitrix:breadcrumb",
						"catalog_breadcrumb_section",
						array(
							"PATH" => "",
							"SITE_ID" => "s1",
							"START_FROM" => "0",
							"COMPONENT_TEMPLATE" => "catalog_breadcrumb_section"
						),
						false
					);?>
				<?if(!CSite::InDir("/basket/")):?>
					<h1 class="g-title"><?=$APPLICATION->ShowTitle(false)?></h1>
				<?endif;?>
			<?endif;?>
			<?if(!CSite::InDir('/products/') && !CSite::InDir('/special/') && !CSite::InDir('/basket/')):?>
               	<div class="container">
			<?endif;?><?userbanner();?>