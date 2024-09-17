<?
//move_uploaded_file($_FILES['file']['tmp_name'], __DIR__.'/uploads/'. $_FILES["image"]['name']);
/*
    access_token	"y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4"
    expires_in	31536000 (20.12.2022)
    refresh_token	"1:aFv7pMXyBiclm2U1:p-GZW7XkNa_dc8l78yGUo6wiqqaI7w3nnA7b6VDBCi-a7Hug4FWGOvoLD8GNb8iBt_ohztYmlxJAxw:WfVSTr7OZ9NxJRg2wM2TxA"
    token_type	"bearer"
*/



/*$counter = "";            // Укажите номер счетчика
$token = "";              // Укажите OAuth-токен
$client_id_type = "";     // Укажите тип идентификаторов посетителей – CLIENT_ID, USER_ID или YCLID

$curl = curl_init("https://api-metrika.yandex.ru/management/v1/counter/$counter/offline_conversions/upload?client_id_type=$client_id_type");

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CurlFile(realpath('file.csv'))));
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data", "Authorization: OAuth $token"));

$result = curl_exec($curl);

echo $result;

curl_close($curl);*/








$file = "orders_full(5).csv";
echo $file;
$a = new \CurlFile($file);

var_dump($a);

$authorization = 'Authorization: OAuth y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4';

$curl = curl_init();
//var_dump($curl);
curl_setopt($curl, CURLOPT_URL, "https://api-metrika.yandex.net/cdp/api/v1/counter/135067/data/simple_orders?merge_mode=SAVE&");
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, [$authorization]);

curl_setopt($curl, CURLOPT_POSTFIELDS, ['file' => new \CurlFile($file)]);



//$cfile = new CurlFile($file,  'text/csv');


//curl file itself return the realpath with prefix of @
//$data = array('data-binary' => $cfile);
//curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$curl_response = curl_exec($curl);
echo "<pre>";
    print_r($curl_response);
echo "</pre>";
curl_close($curl);

