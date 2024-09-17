<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

?><div>
YMetric clientId = <span id="clientId"></span><script>
ym(135067, 'getClientID', function(clientID) {
    document.getElementById("clientId").innerHTML = clientID;
});

</script>
