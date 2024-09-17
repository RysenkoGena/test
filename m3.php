<?PHP
$jsonData = file_get_contents('https://portal.yugkabel.ru/test.php');
//echo $jsonData;
// Разбор JSON-строки в ассоциативный массив
$data = json_decode($jsonData, true);

// Проверка успешности разбора JSON
if ($data === null) {
    //die('Ошибка разбора JSON.');
}
print_r($data);
