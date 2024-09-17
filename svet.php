<?PHP require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>

    <div        id="captcha-container"        class="smart-captcha"        data-sitekey="ysc1_b4Q3VoI8YfArvrkNKji1HYE2XCP0CPCRM5kKC82n331ecbca"> </div>
<script>
        function observeHiddenInputValue() {
            var inputElement = document.querySelector('input[name="smart-token"]');
            var currentValue = inputElement.value;
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        console.log('Значение изменилось:', inputElement.value);
                        currentValue = inputElement.value;
                    }
               });
            });
            var config = { attributes: true, attributeOldValue: true, attributeFilter: ['value'] };
            observer.observe(inputElement, config);
        }

    setTimeout(observeHiddenInputValue, 1000);
</script>

<?php
define('SMARTCAPTCHA_SERVER_KEY', 'ysc2_b4Q3VoI8YfArvrkNKji1aJRXrIOHJObR7GLFFawo89bbb436');

function check_captcha($token) {
    $ch = curl_init();
    $args = http_build_query([
        "secret" => SMARTCAPTCHA_SERVER_KEY,
        "token" => $token,
        "ip" => $_SERVER['REMOTE_ADDR'], // Нужно передать IP-адрес пользователя.
        // Способ получения IP-адреса пользователя зависит от вашего прокси.
    ]);
    curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);

    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 200) {
        echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
        return true;
    }
    $resp = json_decode($server_output);
    return $resp->status === "ok";
}

$token = $_POST['smart-token'];
if (check_captcha($token)) {
    echo "Passed\n";
} else {
    echo "Robot\n";
}


?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
