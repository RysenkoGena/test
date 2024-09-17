<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
</head>
<body>
<?PHP

/**
 * Decode 7-bit packed PDU messages
 */

//$text = pdu2str($pdu);
$text = iconv('UCS-2', 'UTF-8', $pdu);

echo $text."<br>";

$string = "0412043004480020043D043E043C04350440003A0020002B00370039003900350032003200370033003300310032000A0414043E044104420443043F043D043E003A0020003000200440000A";

$convertedString = mb_convert_encoding(hex2bin($string), 'UTF-8', 'UCS-2');

echo $convertedString;


?>
</body>
</html>
