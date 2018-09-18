<?php

/**
 * Class User
 * Model providing user registration, login and logout
 */
class User {

    /**
     * @var object DB instance
     */
    private $_db;

    /**
     * User constructor.
     * @param $db
     */
    function __construct($db) {
        $this->_db = $db;
    }

    /**
     *  Get hashed password, email, id and role of user with provided email address
     * @param $email string Email address
     * @return mixed Array of user info if query is successful
     */
    private function get_user_info($email) {
        try {
            $req = $this->_db->prepare('SELECT password, email, id, role FROM users WHERE email = :email ');
            $req->execute(array('email' => $email));
            return $req->fetch();
        } catch(PDOException $e) {
            Message::error('Something went wrong. Please try again.');
        }
    }

    /**
     * Try to log in user with provided email and password
     * @param $email    string Email from login form
     * @param $password string Password from login form
     * @return bool
     */
    public function login($email, $password) {
        $row = $this->get_user_info($email);

        if(password_verify($password, $row['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['is_admin'] = $row['role'] == 'admin';
            return true;
        }
    }

    /**
     * Logout user
     */
    public function logout(){
        session_unset();
        session_destroy();

//      refresh navigation
        header('Location: ' . $_SERVER['PHP_SELF']);
    }

    /**
     * Determine, whether the current user is logged in
     * @return bool
     */
    public static function is_logged_in(){
        if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
            return true;
        }
        return false;
    }

    /**
     * Create user with provided email and password
     * @param $email    string Email address from registration form
     * @param $password string Password from registration form
     */
    public static function create_user($email, $password) {
//      validate password
        if (strlen($password) < 8)
            $error['password'] = 'Password is too short.';
        if (strlen($password) > 64)
            $error['password'] = 'Password is too long.';

//      validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $error['email'] = 'Invalid email address.';
        else {
            try {
//              check if user with this email doesn't already exist
                $db = db::getinstance();
                $req = $db->prepare('SELECT * FROM users WHERE email = :email');
                // the query was prepared, now we replace :id with our actual $id value
                $req->execute(array('email' => $email));

                $row = $req->fetch(pdo::FETCH_ASSOC);

                if (!empty($row['email'])) {
                    $error['email'] = 'Email provided is already in use.';
                }
            } catch (PDOException $e) {
                Message::error('Something went wrong. Please try again.');
            }
        }
//            show errors in the form
        if (isset($error)) {
            $token = self::set_submit_token();
            require_once('views/registration/index.php');
        }
        else {
//          otherwise create user in db

//          hash the password, salt is automatically generated
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

//          create user record in db
//          always create as a regular user
            try {

                $role = 'user';
                $db = db::getinstance();
                $req = $db->prepare('INSERT INTO users (email,password,role) VALUES (:email, :password, :role)');
                $req->execute(array(
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':role' => $role
                ));

                Message::success('Registration successful. You can now login.');
//              redirect to gallery
                call('posts', 'index');
            } catch (PDOException $e) {
                Message::error('Something went wrong. Please try again.');
            }
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
