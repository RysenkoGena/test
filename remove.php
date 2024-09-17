<?php
$path = "/home/bitrix/ext_www/yugkabel.ru/bitrix/modules/iarga.exchange/upload/images/";
$filesArr = array();
		$dir = opendir($path); $i = 0;
		while($FileImg = readdir($dir)){ //проверяем, нет ли новых картинок для загрузки
            $i++;
			if($FileImg != "." && $FileImg != ".." && !is_dir($path.$FileImg)){
				$filesArr[] = $FileImg;

                if(strpos($FileImg, ".jpg")){
                    //file_put_contents($tmpImg, date("Y-m-d H:i:s")." Найден валидный файл картинки для товара  ".$pos."\n", FILE_APPEND);
                    if(!strpos($FileImg, "_")){
                        $kodTovara = substr($FileImg, 0, -4);
                        //$fileStruct[$kodTovara][] = $FileImg;
                    }else{
                        $offset = strlen($FileImg) - strripos($FileImg, "_");
                        $kodTovara = substr($FileImg, 0, -$offset);
                    }
                    //echo "Код товара => ".$kodTovara.PHP_EOL;
                    $subCatalog = (strlen($kodTovara) < 4) ? "0000/" : substr($kodTovara, 0, -3) . "000/";
                    echo "Код товара => ".$kodTovara." подкаталог => ".$subCatalog.PHP_EOL;

                    if(!is_dir($path.$subCatalog)){
                        echo "Каталог не существует: ".$path.$subCatalog.PHP_EOL;
                        if(!mkdir($path.$subCatalog, 0777)) echo "Ошибка создания каталога".PHP_EOL;
                    }
                    if(!rename($path.$FileImg, $path.$subCatalog.$FileImg)) echo "Ошибка переноса файла".PHP_EOL;


                }
                else{
                    //unlink($pathDir.$FileImg); //удалить невалидный файл
                    echo "Не валидный вайл. Удалиить!".PHP_EOL;
                }
			}
            //if($i > 1000) break;
		}
echo count($filesArr).PHP_EOL;
?>