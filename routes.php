<?php
// All controllers and actions


/**
 * Call action in controller.
 *
 * @param $controller string Controller
 * @param $action     string Action
 */
function call($controller, $action) {
    require_once('controllers/' . $controller . '_controller.php');

    switch($controller) {
        case 'pages':
            $controller = new PagesController();
            break;
        case 'registration':
            $controller = new RegistrationController();
            break;
        case 'login':
            $controller = new LoginController();
            break;
        case 'posts':
            $controller = new PostsController();
            break;
    }

    $controller->{ $action }();
}


/**
 * Redirect to specified controller and action in URL.
 *
 * @param $controller string Controller
 * @param $action     string Action
 */
function redirect($controller, $action) {
    header("Location: index.php?controller=" . $controller . "&action=" . $action);
}

// List of all controllers and actions
$controllers = array(
    'pages' => ['toggle_theme', 'error'],
    'registration' => ['registration', 'registration_submit'],
    'login' => ['login', 'login_submit', 'logout'],
    'posts' => ['index', 'create', 'create_submit', 'show', 'like', 'unlike', 'edit', 'edit_submit', 'get_posts']
);

// Call action in controller on the current route if it exists
if (array_key_exists($controller, $controllers)) {
    if (in_array($action, $controllers[$controller])) {
        call($controller, $action);
    } else {
        call('pages', 'error');
    }
} else {
    call('pages', 'error');
}
