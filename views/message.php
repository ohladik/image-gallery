<?php

/**
 * Class Message
 * Displays message which disappears after 5 seconds.
 */
class Message {

    /**
     * Displays the message based on provided type using a template.
     * @param $message_type string Type of message - 'info', 'success' or 'error'
     * @param $message string Message which is displayed to the user
     */
    private static function showMessage($message_type, $message) {
        require_once('message.tpl.php');
    }

    /**
     * Displays informational message
     * @param $message string Message to be shown
     */
    public static function info($message) {
        self::showMessage('info', $message);
    }

    /**
     * Displays message that an action was successful
     * @param $message string Message to be shown
     */
    public static function success($message) {
        self::showMessage('success', $message);
    }

    /**
     * Displays message with an error
     * @param $message string Message to be shown
     */
    public static function error($message) {
        self::showMessage('error', $message);
    }

}
