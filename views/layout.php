<?php
if (isset($_GET['controller']) && isset($_GET['action']) && $_GET['controller'] == 'posts' && $_GET['action'] == 'get_posts') {
//  this is a data endpoint where the only output comes from the controller
    require_once('routes.php');
} else {
//  these are all routes that render whole HTML pages to the user
    ob_start();

    require_once("navigation.php");
    require_once("routes.php");

    $output_buffer = ob_get_contents();
    ob_end_clean();

    $theme = $GLOBALS['theme'];
    echo "
        <!DOCTYPE html>
        <html>
            <head>
                <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
                <link rel='stylesheet' type='text/css' href='style.css'>
                <link rel='stylesheet' type='text/css' href='print.css'>
                <link rel='stylesheet' type='text/css' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'>
                <title>Gallery</title>
            </head>
        
            <body class='$theme'>
                $output_buffer
            </body>
        </html>
    ";
}
