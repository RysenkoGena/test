<?php
function check_mobile_device() {
    $mobile_agent_array = array('ipad', 'iphone', 'android', 'pocket', 'palm', 'windows ce', 'windowsce', 'cellphone', 'opera mobi', 'ipod', 'small', 'sharp', 'sonyericsson', 'symbian', 'opera mini', 'nokia', 'htc_', 'samsung', 'motorola', 'smartphone', 'blackberry', 'playstation portable', 'tablet browser');
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    foreach ($mobile_agent_array as $value)	if (strpos($agent, $value) !== false) return "Мобильный";
    return "Десктоп";
}

if(isset($_POST) && !empty($_POST)) {
    file_put_contents("logTargets.txt", date("Y-m-d H:i:s ") . "\t" . $_POST["target"] . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . check_mobile_device() ."\t".$_SERVER['HTTP_USER_AGENT']. PHP_EOL, FILE_APPEND);
}

