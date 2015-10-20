<?php
$isp = $_SESSION['isp'];
if ($yaml["envirement"] === "prod") {
    if (!isset($_SESSION['isp'])) {
        header("Location: /index.php");
        die();
    }
}
?>

<html>
    <head>
        <title>Am I being tracked?</title>
        <meta charset='UTF-8'/>
        <link rel="stylesheet" href="./css/main.css">
    </head>

    <body>
        <div class="container">
            <h1>It looks like you are not using 3G or LTE.</h1>
            <p> Feel Free to Add or delete any content</p>
            <div class="footer">
                <span>Powered By </span><a href="https://www.accessnow.org/"><img src="./img/access.png" alt="Access" width="150"></a>
            </div>
        </div>
    </body>
</html>