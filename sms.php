<?php
include($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
echo memory_get_usage()."<br>";

class Person
{ 
    public $name, $age;
      
    function hello()
    {
        echo "Hello!<br>";
    }
}
 
$tom = new Person;
$tom->name = "Tom"; // установка свойства $name
$tom->age = 36; // установка свойства $age
$personName = $tom->name;    // получение значения свойства $name
echo "Имя пользователя: " . $personName . "<br>";
$tom->hello(); // вызов метода hello()
debug($tom);

?>