<?php

/**
 * Class Post
 * Model for post.
 * Posts are stored in DB table 'posts'.
 * They can be created, edited, deleted, liked and unliked.
 */
class Post {

    /**
     * @var int Stored in column 'id' in the DB table.
     */
    public $id;
    /**
     * @var int User ID of the post. Same ID as in users table. Stored in column 'authorId' in the DB table.
     */
    public $authorId;
    /**
     * @var int ID of the post category. Same ID as in categories table. Stored in column 'category' in the DB table.
     */
    public $category;
    /**
     * @var string Text description of the post. Stored in column 'description' in the DB table.
     */
    public $description;
    /**
     * @var string Name of post image in folder 'images' on filesystem. Stored in column 'imageFile' in the DB table.
     */
    public $imageFile;
    /**
     * @var string Datetime when the post was created (server time). Stored in column 'createdAt' in the DB table.
     */
    public $createdAt;
    /**
     * @var int Count of records in 'likes' table with the ID of this post.
     */
    public $likes_count;
    /**
     * @var bool Whether the current user likes this post.
     */
    public $liked;

    /**
     * Post constructor.
     * @param $id
     * @param $authorId
     * @param $category
     * @param $description
     * @param $imageFile
     * @param $createdAt
     * @param $likes_count
     * @param $liked
     */
    public function __construct($id, $authorId, $category, $description, $imageFile, $createdAt, $likes_count, $liked) {
        $this->id           = $id;
        $this->authorId     = $authorId;
        $this->category     = $category;
        $this->description  = $description;
        $this->imageFile    = $imageFile;
        $this->createdAt    = $createdAt;
        $this->likes_count  = $likes_count;
        $this->liked        = $liked;
    }

    /**
     * Gets all posts based on provided parameters and returns them rendered in HTML along with some additional
     * information.
     * @param $author       int     ID of post author (all posts if null)
     * @param $category     int     ID of post category (all posts if null)
     * @param $sort_method  string  date-asc, date-desc, likes-asc, likes-desc
     * @param $limit        int     Limit for returned posts
     * @param $offset       int     Offset for returned posts
     * @return array        Rendered posts in HTML are available at key 'html', total count of rendered posts is at
     * key 'batch_size' and total count of posts if no LIMIT and OFFSET were used is at key 'posts_total'
     */
    public static function render_all($author, $category, $sort_method, $limit, $offset) {
        $posts = [];
        $db = Db::getInstance();
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
//      build query according to method parameters
        $query_vars = [];

        $query = 'SELECT SQL_CALC_FOUND_ROWS posts.*, COUNT(likes.post_id) AS likes_count
                  FROM posts
                  LEFT JOIN likes ON posts.id = likes.post_id';

        $category_filter = '';
        if (isset($category)) {
            $category_filter = ' WHERE posts.category = :category';
            $query_vars['category'] = $category;
        }
        $query .= $category_filter;

        $user_filter = '';
        if (isset($author)) {
            $query_vars['user_id'] = $user_id;
            if (isset($category)) {
                $user_filter = ' AND posts.authorId = :user_id';
            } else {
                $user_filter = ' WHERE posts.authorId = :user_id';
            }
        }
        $query .= $user_filter;

//      for each post there will be only 1 aggregated row
        $query .= ' GROUP BY posts.id';

//      by default sort by date of creation (most recent first)
        $order = ' ORDER BY posts.createdAt DESC';
        if ($sort_method == 'date-asc') {
            $order = ' ORDER BY posts.createdAt ASC';
        } else if ($sort_method == 'likes-asc') {
            $order = ' ORDER BY likes_count ASC';
        } else if ($sort_method == 'likes-desc') {
            $order = ' ORDER BY likes_count DESC';
        }

        $query .= $order;
//      apply limit and offset
        $query .= ' LIMIT :limit OFFSET :offset';
        $query_vars['limit'] = $limit;
        $query_vars['offset'] = $offset;
//        echo $query;

//      get total count of posts
        $query_count = 'SELECT FOUND_ROWS()';

//      determine whether user likes a post
        $query_likes = 'SELECT COUNT(1) FROM likes WHERE post_id = :post_id AND user_id = :user_id';

        try {
//          get posts using LIMIT and OFFSET
            $req = $db->prepare($query);
            $req->execute($query_vars);
//          get total count of posts as if LIMIT wasn't specified
            $req_count = $db->query($query_count);
            $posts_count = $req_count->fetch();

            // we create a list of Post objects from the database results
            ob_start();
            $batch_size = 0;
            foreach($req->fetchAll() as $post) {
                $batch_size++;
//              check whether user likes this post
                $req = $db->prepare($query_likes);
                $req->execute(array('user_id' => $user_id, 'post_id' => $post['id']));
                $result = $req->fetch();
                $liked = $result[0];

//              escape values provided from user which might be used for an XSS attack
                $post = new Post($post['id'], $post['authorId'], xssafe($post['category']), xssafe($post['description']),
                    $post['imageFile'], $post['createdAt'], $post['likes_count'], $liked );
                require('views/posts/post.tpl.php');
            }
            $rendered_posts = ob_get_contents();
            ob_end_clean();

            return array('html' => $rendered_posts, 'batch_size' => $batch_size, 'total_posts' => $posts_count[0]);
        } catch (PDOException $e) {
            Message::error('Something went wrong. Please try again later.');
        }
    }

    /**
     * Returns an array representing post specified by ID parameter
     * @param $id int ID of post
     * @return array All post attributes
     */
    public static function find($id) {
        $db = Db::getInstance();
        // we make sure $id is an integer
        $id = intval($id);

        try {
            $req = $db->prepare('SELECT * FROM posts WHERE id = :id');
            // the query was prepared, now we replace :id with our actual $id value
            $req->execute(array('id' => $id));
            $post = $req->fetch();

            $arr = array(
                'id' => $post['id'],
                'authorId' => $post['authorId'],
                'category' => $post['category'],
                'description' => $post['description'],
                'imageFile' => $post['imageFile'],
                'createdAt' => $post['createdAt']
            );
            return $arr;
        } catch (PDOException $e) {
            Message::error('Something went wrong. Please try again.');
        }

    }

    /**
     * Determines whether file supplied in the parameter is an image
     * @param $image object
     * @return bool
     */
    private static function isImage($image) {
        $imageDetails = getimagesize($image);
        $image_type = $imageDetails[2];

        if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
        {
            return true;
        }
        return false;
    }

    /**
     * Validates post based on provided parameters
     * @param $description      string Post text description
     * @param $categoryId       int    Post category ID
     * @param $imageFile        object File from form
     * @param $change_picture   bool   Whether it's needed to validate image file
     * @param $error            array  Empty array where are stored found errors during validation
     * @return bool
     */
    private static function isPostValid($description, $categoryId, $imageFile, $change_picture, &$error) {
//      validate description
        if (strlen($description) > 90)
            $error['description'] = 'Description is too long.';
        elseif (strlen($description) == 0)
            $error['description'] = 'Please provide a description.';

//      check if category exists in database
        try {
            $db = Db::getInstance();
            $req = $db->prepare('SELECT * FROM categories WHERE id = :categoryId');
            // the query was prepared, now we replace :id with our actual $id value
            $req->execute(array('categoryId' => $categoryId));
            $match = $req->fetch();
            if (empty($match))
                $error['category'] = 'Category does not exist.';
        } catch (PDOException $e) {
            Message::error('Something went wrong. Please try again.');
        }


//      validate image only if post is created or edited with an image
        if (isset($change_picture)) {
            $file = $imageFile['tmp_name'];
            if (isset($_SERVER['CONTENT_LENGTH']) && (int) $_SERVER['CONTENT_LENGTH'] > 2000000) {
                $error['image'] = 'Maximum allowed image size is 2MB.';
            }
            elseif (!isset($imageFile) || !file_exists($file))
                $error['image'] = 'Image is required.';
            elseif (!self::isImage($file))
                $error['image'] = 'File is not an image.';
        }
//        return array of errors
//        e.g. error['description'] = "Not enough characters"
        if (isset($error)) {
            return false;
        }

        return true;
    }

    /**
     * Determines whether current user can edit post with ID provided as the parameter
     * @param $post_id int ID of the post
     * @return bool
     */
    private static function canUserEdit($post_id) {
        if (isset($post_id)) {
            try {
                $db = Db::getInstance();
                $author_id = $_SESSION['user_id'];
                $req = $db->prepare('SELECT COUNT(*) FROM posts WHERE id = :post_id AND authorId = :author_id');
                $req->execute(array(
                    'post_id' => $post_id,
                    'author_id' => $author_id
                ));
                $posts = $req->fetch();
//              user can only edit post that he created
                $is_post_author = $posts[0] == 1;
//              admin can edit all posts
                $is_admin = $_SESSION['is_admin'];
                return $is_post_author || $is_admin;
            } catch (PDOException $e) {
                Message::error('Something went wrong. Please try again');
            }
        }
        return false;
    }

    /**
     * Create new post in DB table 'posts'
     * @param $description string Text description provided by user
     * @param $categoryId  int    ID of category
     * @param $imageFile   object Post image file
     */
    public static function create($description, $categoryId, $imageFile) {
//      validate and if ok create in DB
        $error = null;
        $change_picture = true;

        if (self::isPostValid($description, $categoryId, $imageFile, $change_picture, $error)) {
//        create unique name for uploaded photo which will be stored in filesystem
//        and store the filename in DB
            try {
                $fileTmpName = $imageFile['tmp_name'];
                $fileName = $imageFile['name'];
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid(rand(), true) . ".$fileExtension";

//              store photo on disk
                $target = 'images/' . $uniqueFileName;
                if (!move_uploaded_file($fileTmpName, $target)) {
                    throw new Exception("Couldn't store file on disk.");
                }
//              create post in db
                $user_id = $_SESSION['user_id'];
                $db = db::getinstance();
                $req = $db->prepare('INSERT INTO posts (authorId, description, category, imageFile) VALUES (:authorId, :description, :category, :imageFile)');
                $req->execute(array(
                    ':authorId' => $user_id,
                    ':description' => $description,
                    ':category' => $categoryId,
                    ':imageFile' => $uniqueFileName
                ));

                call('posts', 'index');
                Message::success('Post was successfully created.');
            } catch (Exception $e) {
                Message::error('Something went wrong. Please try again.');
            }

        } else {
            $token = self::set_submit_token();
            $categories = Category::all();
            require_once('views/posts/create.php');
        }
    }

    /**
     * Edit existing post in DB table 'posts'
     * @param $description    string Post text description
     * @param $categoryId     int    Post category ID
     * @param $imageFile      object Post image file
     * @param $post_id        int    ID of edited post
     * @param $change_picture bool   Whether post image should be changed
     */
    public static function edit($description, $categoryId, $imageFile, $post_id, $change_picture) {
//      validate and if ok create in DB
        $error = null;

        if (self::isPostValid($description, $categoryId, $imageFile, $change_picture, $error) && self::canUserEdit($post_id)) {
            try {
    //          check if picture in the post should be changed
                if (isset($change_picture)) {
          //        create unique name for uploaded photo which will be stored in filesystem
          //        and store the filename in DB
                    $fileTmpName = $imageFile['tmp_name'];
                    $fileName = $imageFile['name'];
                    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $uniqueFileName = uniqid(rand(), true) . ".$fileExtension";
          //        store photo on disk
                    $target = 'images/' . $uniqueFileName;
                    if (!move_uploaded_file($fileTmpName, $target)) {
                        throw new Exception("Couldn't store file on disk.");
                    }

//                  edit post in db
                    $db = db::getinstance();
                    $req = $db->prepare('UPDATE posts SET description = :description, category = :category, imageFile = :imageFile WHERE id = :post_id');
                    $req->execute(array(
                        ':post_id' => $post_id,
                        ':description' => $description,
                        ':category' => $categoryId,
                        ':imageFile' => $uniqueFileName
                    ));
                } else {
//                  don't change the picture in the post
//                  edit post in db
                    $db = db::getinstance();
                    $req = $db->prepare('UPDATE posts SET description = :description, category = :category WHERE id = :post_id');
                    $req->execute(array(
                        ':post_id' => $post_id,
                        ':description' => $description,
                        ':category' => $categoryId
                    ));
                }

                call('posts', 'index');
                Message::success('Post was successfully updated.');
            } catch (Exception $e) {
                Message::error('Something went wrong. Please try again.');
            }

        } else {
            $token = self::set_submit_token();
            $categories = Category::all();
            require_once('views/posts/edit.php');
        }
    }

    /**
     * Delete a post
     * Removes the record with provided ID in the 'posts' table and all records with this post_id in table 'likes'
     * @param $post_id int ID of post which should be deleted
     */
    public static function delete($post_id) {
//      check if user is author of this post
        if (self::canUserEdit($post_id)) {
            try {
                $db = Db::getInstance();

//              remove all records in likes table
                $user_id = $_SESSION['user_id'];
                $req = $db->prepare('DELETE FROM likes WHERE post_id = :post_id');
                $req->execute(array(
                    'post_id' => $post_id
                ));
//              remove post
                $req = $db->prepare('DELETE FROM posts WHERE id = :post_id');
                $req->execute(array(
                    'post_id' => $post_id
                ));
                //  redirect to gallery
                call('posts', 'index');
                Message::success('Post was successfully deleted.');
            } catch (Exception $e) {
                Message::error('Something went wrong. Please try again.');
            }

        }
    }

    /**
     * Like a post
     * Create record in 'likes' table using provided post ID and ID of the current user
     * @param $post_id int ID of liked post
     */
    public static function like($post_id) {
        try {
            $db = Db::getInstance();
//          preventing "double" liking
//          check whether this post isn't liked already by this user
            $user_id = $_SESSION['user_id'];
            $req_likes = $db->prepare('SELECT COUNT(*) FROM likes WHERE post_id = :post_id AND user_id = :user_id');
            $req_likes->execute(array(
                'post_id' => $post_id,
                'user_id' => $user_id
            ));
            $likes = $req_likes->fetch();
            $liked = $likes[0] > 0;

            if (!$liked) {
        //      create record in likes table
                $req = $db->prepare('INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)');
                $req->execute(array(
                    'post_id' => $post_id,
                    'user_id' => $user_id
                ));
            }
        } catch (Exception $e) {
            Message::error('Something went wrong. Please try again later.');
        }
//      redirect to gallery with previously applied filters and sort method if the gallery was the previous page
        if (isset($_SERVER["HTTP_REFERER"]) && (strpos($_SERVER["HTTP_REFERER"], 'controller=posts&action=index') !== false)) {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
    }

    /**
     * Unlike a post
     * Remove record in 'likes' table with provided post ID and ID of the current user
     * @param $post_id int ID of the unliked post
     */
    public static function unlike($post_id) {
        try {
            $db = Db::getInstance();

//          remove record in likes table
//          there is no problem if user unlikes post more times in a row
            $user_id = $_SESSION['user_id'];
            $req = $db->prepare('DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id');
            $req->execute(array(
                'post_id' => $post_id,
                'user_id' => $user_id
            ));
        } catch (Exception $e) {
            Message::error('Something went wrong. Please try again later.');
        }
//      redirect to gallery with previously applied filters and sort method if the gallery was the previous page
        if (isset($_SERVER["HTTP_REFERER"]) && (strpos($_SERVER["HTTP_REFERER"], 'controller=posts&action=index') !== false)) {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
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
