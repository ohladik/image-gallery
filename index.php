<?php
// application entry point
session_start();
require_once('connection.php');
require_once('models/user.php');
require_once('views/message.php');
// functions to use when outputting data from user
require('xss.php');

// get current theme
$GLOBALS['theme'] = '';
if (isset($_COOKIE['theme'])) {
    $GLOBALS['theme'] = $_COOKIE['theme'] == 'dark' ? 'dark' : '';
}

// set controller and action based on the request
if (isset($_GET['controller']) && isset($_GET['action'])) {
    $controller = $_GET['controller'];
    $action     = $_GET['action'];
} else {
    $controller = 'posts';
    $action     = 'index';
}

// main HTML document for the whole application
require_once('views/layout.php');
