<?php
require_once 'include.php';
?>
<html>
    <head>
        <title>AzerothShard Tests</title>
    </head>
    <body>
        <?= \Enhance\Core::runTests(\Enhance\TemplateType::Html); ?>
    </body>
</html>
