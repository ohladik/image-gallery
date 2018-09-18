<?php
//  XSS mitigation functions
//  source: https://www.owasp.org/index.php/PHP_Security_Cheat_Sheet
//  usage: use while outputing content which was provided by a user

/**
 * @param $data string String which should be cleared of all special characters
 * @param $encoding string Encoding of the returned string with UTF-8 used if not specified
 * @return string String cleared of all special characters
 */
function xssafe($data, $encoding='UTF-8') {
        return htmlspecialchars($data,ENT_QUOTES | ENT_HTML401, $encoding);
    }

/**
 * Wrapper around the xssafe
 * Should be use instead of regular echo when output is served to a user containing data which came from user input
 * @param $data string String to be echoed safely
 */
function xecho($data) {
        echo xssafe($data);
    }
?>