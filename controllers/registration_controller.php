<?php

/**
 * Class RegistrationController
 * Displays and submits the registration form.
 */
class RegistrationController {

    /**
     * Show registration form.
     */
    public function registration() {
//      preventing double submitting
        $token = self::set_submit_token();

        require_once('views/registration/index.php');
    }

    /**
     * Submit registration form.
     */
    public function registration_submit() {
//      check if form wasn't submitted for a second time
        if(isset($_POST['token']) && isset($_SESSION[$_POST['token']])) {
            unset($_SESSION[$_POST['token']]);

            if (isset($_POST['email']) && isset($_POST['password'])) {
                /* input data */
                $email = xssafe($_POST['email']);
                $password = xssafe($_POST['password']);

                User::create_user($email, $password);
            }
        } else {
//          form was submitted for the second time
//          redirect to registration page
            call('registration', 'registration');
            Message::info('Second attempt to submit a form was denied.');
        }
    }

    /**
     * Generates unique submit token to prevent form double submitting.
     * Token is stored in the session and also returned.
     * @return string
     */
    private static function set_submit_token() {
//      preventing double submitting
        $token = md5(time());
        $_SESSION[$token] = true;

        return $token;
    }
}
