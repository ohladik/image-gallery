<?php

/**
 * Class PagesController
 * Handles displaying of general error page and toggling dark/light theme.
 */
class PagesController {

    /**
     * Toggles light and dark theme.
     * Default theme is light.
     * Current theme is stored in a cookie.
     * Light theme is the default one in CSS. Class "dark" is added to elements when dark theme is used.
     */
    public function toggle_theme() {
        if (isset($_COOKIE['theme'])) {
            $current_theme = $_COOKIE['theme'];
            if ($current_theme == 'dark') {
                setcookie(
                    "theme",
                    "light",
                    time() + (10 * 365 * 24 * 60 * 60)
                );
            } else {
                setcookie(
                    "theme",
                    "dark",
                    time() + (10 * 365 * 24 * 60 * 60)
                );
            }
        } else {
//          theme is not set, set it to dark theme
            setcookie(
                "theme",
                "dark",
                time() + (10 * 365 * 24 * 60 * 60)
            );
        }
//      redirect to the gallery
        redirect('posts', 'index');
    }

    /**
     * Displays general error page.
     * Used when there is a not existing controller or action in a GET request.
     */
    public function error() {
        require_once('views/pages/error.php');
    }
}
