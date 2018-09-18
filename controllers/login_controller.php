<?php

/**
 * Class LoginController
 * Handles user login and logout
 */
class LoginController {

    /**
     * Show login form to the user.
     * If he is logged in, redirect him to the gallery.
     */
    public function login() {
        $db = Db::getInstance();
        $user = new User($db);

//      preventing double submitting
        $token = self::setSubmitToken();

//      if user is already logged in redirect him to gallery
        if( $user->is_logged_in() ){
            call('posts', 'index');
        } else {
//          otherwise show login form
            require_once('views/login/index.php');
//          and refresh navigation (Login/Logout)
            require('views/navigation.php');
        }
    }

    /**
     * Handles login form POST request in views/login/index.php
     */
    public function login_submit() {
//      check if form wasn't submitted for a second time
        if(isset($_POST['token']) && isset($_SESSION[$_POST['token']])) {
            unset($_SESSION[$_POST['token']]);

            if (isset($_POST['email']) && isset($_POST['password'])) {
                $db = Db::getInstance();
                $user = new User($db);

                /* Input data check */
                $email = xssafe($_POST["email"]);
                $password = xssafe($_POST["password"]);

                if ($user->login($email, $password)) {
//                  refresh page to update navigation menu ("Login" is changed to "Logout")
                    header('Location: ' . $_SERVER['PHP_SELF']);
//                  redirect to gallery
                    call('posts', 'index');
                } else {
                    $token = self::setSubmitToken();
                    $error['login'] = 'Wrong email or password.';
                    require_once('views/login/index.php');
                }
            }
        } else {
//          form was submitted for the second time
//          redirect to login page
            call('login', 'login');
            Message::info('Second attempt to submit a form was denied.');
        }
    }

    /**
     * Logs out user when he uses the "Logout" button in the navigation
     */
    public function logout() {
        $db = Db::getInstance();
        $user = new User($db);
        $user->logout();
    }

    /**
     * Generates unique token which is used to determine whether a form was submitted for a second time.
     * This token is stored in session so it can be invalidated after a form is submitted.
     * Function returns the token so it can be used in hidden input in form which needs to be protected against
     * repeated submits.
     * @return string
     */
    private static function setSubmitToken() {
//      preventing double submitting
        $token = md5(time());
        $_SESSION[$token] = true;

        return $token;
    }
}
