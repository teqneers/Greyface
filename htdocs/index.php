<?php
    $language = array_key_exists('language', $_GET) ? $_GET['language'] : "en";
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title id="page-title">Greyface2</title>
        <link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all-gray.css">
        <script type="text/javascript" src="app/tools/Detect.js"></script>
        <script type="text/javascript" src="extjs/ext-all-debug-w-comments.js"></script>
        <script type="text/javascript" src="app/Application_MVC.js"></script>
        <script type="text/javascript" src="extjs/resources/locale/ext-lang-<?php echo $language ?>.js"></script>
    </head>
    <body>

    </body>
</html>
