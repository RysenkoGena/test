<?
//move_uploaded_file($_FILES['file']['tmp_name'], __DIR__.'/uploads/'. $_FILES["image"]['name']);
/*
    access_token	"y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4"
    expires_in	31536000 (20.12.2022)
    refresh_token	"1:aFv7pMXyBiclm2U1:p-GZW7XkNa_dc8l78yGUo6wiqqaI7w3nnA7b6VDBCi-a7Hug4FWGOvoLD8GNb8iBt_ohztYmlxJAxw:WfVSTr7OZ9NxJRg2wM2TxA"
    token_type	"bearer"
*/



$authorization = 'Authorization: OAuth y0_AgAAAAAC_m26AAjmRAAAAADXRfeb-Vh9foDeQxiDcZrFMtlRyW6dvS4';
//echo $authorization.PHP_EOL;

$curl = curl_init();
var_dump($curl);
$a = curl_setopt($curl, CURLOPT_URL, "https://api-metrika.yandex.net/management/v1/counter/135067/offline_conversions/uploading/92a4e367-da1b-44d2-8bf0-d9482432f82a");
print_r($a);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, [$authorization]);

//curl_setopt($curl, CURLOPT_POSTFIELDS, ['file' => new \CurlFile($file)]);

//$cfile = new CurlFile($file,  'text/csv');

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$curl_response = curl_exec($curl);
echo "<pre>";
    var_dump($curl_response);
echo "</pre>";

curl_close($curl);

