<?
$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__)."/..";
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); 
ob_end_flush(); //отключить буферизацию*/

function DeleteOld($nDays)  //очистить все корзины кроме последних nDays дней
   {
      global $DB;

      $nDays = IntVal($nDays);
      $strSql = "SELECT ID, DATE_INSERT FROM b_sale_fuser WHERE TO_DAYS(DATE_UPDATE) < (TO_DAYS(NOW())-".$nDays.") LIMIT 1000000";
      $db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
      
      while ($ar_res = $db_res->Fetch()){
         CSaleBasket::DeleteAll($ar_res["ID"], false);
         CSaleUser::Delete($ar_res["ID"]);
         echo "удаляем ".$ar_res["DATE_INSERT"]."\n";
      }
      return true;
   }

   DeleteOld(5);