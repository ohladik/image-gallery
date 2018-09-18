<!-- Navigation bar -->
<nav class="navigation-header <?php echo $GLOBALS['theme']; ?>">
    <div class="navigation-container">
    <?php
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        echo '
                        <a class="navigation-link" href=\'?controller=posts&action=index\'>Gallery</a>
                        <a class="navigation-link" href=\'?controller=posts&action=create\'>Create post</a>
                        <a class="navigation-link" href=\'?controller=login&action=logout\'>Logout</a>      
                    ';
    } else {
        echo '
                        <a class="navigation-link" href=\'?controller=posts&action=index\'>Gallery</a>
                        <a class="navigation-link" href=\'?controller=registration&action=registration\'>Register</a>
                        <a class="navigation-link" href=\'?controller=login&action=login\'>Login</a>      
                    ';
    }
    ?>
    </div>
    <a class="navigation-link" href="?controller=pages&action=toggle_theme">
        <div class="theme-toggle-container">
            <span class="theme-toggle-label">Toggle theme</span>
            <span>
                <i class="fa fa-sun-o"></i>
                /
                <i class="fa fa-moon-o"></i>
            </span>
        </div>
    </a>
</nav>