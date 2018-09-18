<?php

/**
 * Class Db
 * A singleton providing access to database
 */
class Db {
    /**
     * @var PDO PDO instance
     */
    private static $instance = NULL;

    /**
     * Db constructor.
     */
    private function __construct() {}

    /**
     * Create database connection if it doesn't exist, otherwise return the existing connection
     * @return null|PDO
     */
    public static function getInstance() {
            if (!isset(self::$instance)) {
                $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
//              disable emulated prepared statements and use real prepared statements
                $pdo_options[PDO::ATTR_EMULATE_PREPARES] = false;
//              use UTF-8 charset
                $pdo_options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
                try {
                    self::$instance = new PDO('mysql:host=localhost;dbname=gallery_app', 'root', '', $pdo_options);
                } catch (PDOException $e) {
                    Message::error('Something went wrong. Please try again later.');
                    die($e->getMessage());
                }
            }
            return self::$instance;
        }
}
