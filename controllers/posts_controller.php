<?php
require_once('models/post.php');
require_once('models/category.php');

/**
 * Class PostsController
 * Handles displaying the Gallery with posts, sending additional rendered posts when user scrolls down in the Gallery,
 * displaying and submitting both CREATE and EDIT post forms and liking/unliking a post.
 */
class PostsController {

    /**
     *  Display gallery with posts using parameters from request.
     *  See get_rendered_posts() for details about these parameters.
     *  Posts are displayed in gallery.
     *  Categories are used in the filter.
     */
    public function index() {
        $posts = self::get_rendered_posts();
        $categories = Category::all();
        require_once('views/posts/index.php');
    }

    /**
     * Outputs all posts based on request parameters in JSON.
     * Used to get additional posts when user scrolls down in the Gallery.
     */
    public function get_posts() {
        $posts = self::get_rendered_posts();
        $json_data = json_encode($posts);
        header( 'Content-Type: application/json' );
        echo $json_data;
    }

    /**
     * Based on request parameters get an array with rendered posts in HTML, count of posts in this batch
     * and total count of posts with these parameters applied.
     * @return array
     */
    private function get_rendered_posts() {
        // get all posts
//      leave only user's posts
        $author = null;
        if (isset($_GET['post-author']) && $_GET['post-author'] == 'user') {
            $author = $_SESSION['user_id'];
        }
        $category = null;
//      filter them by category
        if (isset($_GET['category']) && $_GET['category'] != 'all') {
            $category = $_GET['category'];
        }
//      sort them
        $sort_method = null;
        if (isset($_GET['sort-method'])) {
            $sort_method = $_GET['sort-method'];
        }
        $limit = 6;
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }
        $offset = 0;
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        $posts = Post::render_all($author, $category, $sort_method, $limit, $offset);
        return $posts;
    }

    /**
     * Display CREATE post form.
     * A token to prevent form double submitting is generated.
     */
    public function create() {
//      user must be logged in
        if (User::is_logged_in()) {
//          preventing double submitting
            $token = md5(time());
            $_SESSION[$token] = true;

//          get all categories for the select box in form
            $categories = Category::all();
//          show post creation form
            require_once('views/posts/create.php');
        }
    }

    /**
     * Submits CREATE post form.
     */
    public function create_submit() {
        //          check if form wasn't submitted for a second time
        if(isset($_POST['token']) && isset($_SESSION[$_POST['token']])) {
            unset($_SESSION[$_POST['token']]);
            Post::create($_POST['description'], $_POST['category'], $_FILES['picture']);
        } else {
//          form was submitted for the second time
//          redirect to home page
            call('posts', 'index');
            Message::info('Second attempt to submit a form was denied.');
        }
    }

    /**
     * Displays EDIT post form.
     * A token to prevent form double submitting is generated.
     */
    public function edit() {
//      user must be logged in
        if (User::is_logged_in()) {
//          preventing double submitting
            $token = md5(time());
            $_SESSION[$token] = true;

            $user_id = $_SESSION['user_id'];
            $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
            $post_id = $_GET['post_id'];
            $post = Post::find($post_id);

            $description = $post['description'];
            $categoryId = $post['category'];
            $imageFile = $post['imageFile'];
//          user can edit only his post or he must be an admin
            if ($user_id == $post['authorId'] || $is_admin) {
                // get all categories for the select box in form
                $categories = Category::all();
//              show post edit form
                require_once('views/posts/edit.php');
            }
        }
    }

    /**
     * Submits EDIT post form.
     * Checks if the post image should be changed.
     * Post is deleted if user submits the form with the "Delete" button.
     */
    public function edit_submit() {
        if (User::is_logged_in()) {
//          check if form wasn't submitted for a second time
            if(isset($_POST['token']) && isset($_SESSION[$_POST['token']])) {
                unset($_SESSION[$_POST['token']]);

                if (isset($_POST['edit'])) {
                    // edit post
                    $change_picture = null;
                    if (isset($_POST['form-change-picture']) && isset($_FILES['picture'])) {
                        $change_picture = true;
                    }
                    Post::edit($_POST['description'], $_POST['category'], $_FILES['picture'], $_GET['post_id'], $change_picture);
                } else if (isset($_POST['delete'])) {
                    //delete post
                    Post::delete($_GET['post_id']);
                }
            } else {
    //          form was submitted for the second time
//              redirect to home page
                call('posts', 'index');
                Message::info('Second attempt to submit a form was denied.');
            }
        }
    }

    /**
     * Called when user clicks on the heart icon in post which he hasn't liked yet.
     */
    public function like() {
        if (isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
            Post::like($post_id);
        }
//      redirect to gallery
        call('posts', 'index');
    }

    /**
     * Called when user clicks on the heart icon in post which he has liked.
     */
    public function unlike() {
        if (isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
            Post::unlike($_GET['post_id']);
        }
//      redirect to gallery
        call('posts', 'index');
    }
}
