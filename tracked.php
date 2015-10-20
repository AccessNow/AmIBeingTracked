<?php
$isp = $_SESSION['isp'];
$permacookie = $_SESSION['header_id'];
if ($yaml["envirement"] === "prod") {
    if (!isset($_SESSION['isp'])) {
        header("Location: /index.php");
        die();
    }
} else {
    if ($_GET["isp"] && $_GET["perma"]) {
        $isp = $_GET["isp"];
        $permacookie = $_GET["perma"];
    } else {
        die("To test, please use ?isp=SomeCarrier&perma=UID34DFN");
    }
}
?>
<html>
    <head>
        <title>Am I being tracked?</title>
        <link rel="stylesheet" href="./css/main.css">
        <meta charset='UTF-8'/>
    </head>

    <body>
        <div class="container">
            <h1><?php echo str_replace("%perma%", $permacookie, "You're Being Tracked by %perma%."); ?></h1>
            <p> Feel Free to Add or delete any content</p>
            <div class="footer">
                <span>Powred By </span><a href="https://www.accessnow.org/"><img src="./img/access.png" alt="Access" width="150"></a>
            </div>
        </div>
    </body>
</html>