<main class="content">
    <div>
        <form
                class="view-settings <?php echo $GLOBALS['theme']; ?>"
                id="new-post"
                enctype="multipart/form-data"
                action="index.php?controller=posts&action=index"
                method="get"
        >
            <input type="hidden" name="controller" value="posts">
            <input type="hidden" name="action" value="index">
            <div class="category-filter filter-item">
                <span class="filter-label">Category:</span>
                <select class="form-input" id="category" name="category">
                    <option value="all">All</option>
                    <?php
                    foreach($categories as $cat) {
                        $selected = '';
                        if(isset($_GET['category']) && $cat->id == $_GET['category']){
                            $selected = 'selected';
                        }
                        echo "<option value=\"{$cat->id}\" $selected>{$cat->name}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="sort filter-item">
                <span class="filter-label">Sort by:</span>
                <select class="form-input" id="sort-method" name="sort-method">
                    <option value="date-desc" <?php echo isset($_GET['sort-method']) && $_GET['sort-method'] == 'date-desc' ? 'selected' : null ?> >Date (descending)</option>
                    <option value="date-asc" <?php echo isset($_GET['sort-method']) && $_GET['sort-method'] == 'date-asc' ? 'selected' : null ?> >Date (ascending)</option>
                    <option value="likes-desc" <?php echo isset($_GET['sort-method']) && $_GET['sort-method'] == 'likes-desc' ? 'selected' : null ?> >Likes (descending)</option>
                    <option value="likes-asc" <?php echo isset($_GET['sort-method']) && $_GET['sort-method'] == 'likes-asc' ? 'selected' : null ?>>Likes (ascending)</option>
                </select>
            </div>
            <div class="filter-item">
                <?php
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                        $checked = isset($_GET['post-author']) && $_GET['post-author'] == 'user' ? 'checked' : null;
                        echo "
                        <label class='filter-label' for='post-author'>My posts only:</label>
                        <input id='post-author' name='post-author' type='checkbox' value='user' $checked>
                        ";
                    }
                ?>
            </div>
            <button class="form-button" type="submit">Apply</button>
        </form>
    </div>
    <div class="posts-container <?php echo $GLOBALS['theme']; ?>">
        <?php
            $posts_in_batch = $posts['batch_size'];
            $posts_total = $posts['total_posts'];

            $author = '';
            if (isset($_GET['post-author']) && $_GET['post-author'] == 'user') {
                $author = '&post-author=user';
            }
            $category = '';
            if (isset($_GET['category'])) {
                $category = '&category='.$_GET['category'];
            }
            $sort_method = '';
            if (isset($_GET['sort-method'])) {
                $sort_method = '&sort-method='.$_GET['sort-method'];
            }

            if (isset($posts['html']) && $posts_in_batch > 0) {
                echo $posts['html'];

                $posts_remaining = $posts_total - $posts_in_batch;
                $current_limit = isset($_GET['limit']) ? $_GET['limit'] : 6;
                $new_limit = $current_limit + 6;
                $limit = '&limit='.$new_limit;
            }

        ?>
    </div>
    <div class="posts-container-bottom <?php echo $GLOBALS['theme'] ?>">
    <?php
    if ($posts && $posts_in_batch > 0) {
        if ($posts_in_batch < $posts_total) {
            //                  this button is used to load more posts if JavaScript is disabled
            echo "
                        <a class='load-more-button' href='index.php?controller=posts&action=index$limit$author$category$sort_method'>Load more</a>
                    ";
        } else {
            echo "
                    <div class='posts-row'><i class='fa fa-info-circle posts-message' aria-hidden='true'></i><span class='posts-message'>No more posts</span></div>
                    ";
        }
    } else {
        echo "
            <div class='posts-row'><i class='fa fa-info-circle posts-message' aria-hidden='true'></i><span class='posts-message'>No posts</span></div>
        ";
    }
    ?>
    </div>
</main>

<script>
//  offset is needed when loading batch of posts via AJAX
    var posts_offset = 0;

    window.onload = function() {
        // remove 'Load more' button
        var button = document.getElementsByClassName('load-more-button')[0];
        if (button) {
            button.parentNode.removeChild(button);
        }

        document.addEventListener('scroll', scrollHandler, false);
    };

    function scrollHandler(event) {
        var scrollContainer = document.body;
        // detect when user scrolls all the way down
        if (document.documentElement.scrollHeight - document.documentElement.scrollTop === document.documentElement.clientHeight) {
            posts_offset += 6;
            getPosts(posts_offset);
        }
    }

    function addLoadingIndicator() {
        var container = document.getElementsByClassName('posts-container-bottom')[0];
        container.innerHTML = '<div class="posts-row">' +
                '                   <i class="fa fa-spinner fa-spin"></i>' +
            '                       <span class="posts-message">Loading more posts...</span>' +
            '                  </div';
    }

    function removeLoadingIndicator() {
        var container = document.getElementsByClassName('posts-container-bottom')[0];
        container.innerHTML = '';
    }

    function appendMessage(message) {
        var container = document.getElementsByClassName('posts-container-bottom')[0];
        container.innerHTML = '<div class="posts-row">' +
            '                       <i class=\'fa fa-info-circle posts-message\' aria-hidden=\'true\'></i>' +
            '                       <span class="posts-message">' + message + '</span>' +
            '                  </div';
    }

    function getPosts(offset) {
        addLoadingIndicator();
        // get all default parameters
        var authorPar = '<?php echo $author; ?>';
        var sortMethodPar = '<?php echo $sort_method; ?>';
        var categoryPar = '<?php echo $category; ?>';
        var offsetPar = `&offset=${offset}`;


        fetch(`index.php?controller=posts&action=get_posts${authorPar}${sortMethodPar}${categoryPar}${offsetPar}`, {
            method: 'GET',
            credentials: 'include'
        })
            .then(response => response.json())
            .then(data => {
                var container = document.getElementsByClassName('posts-container')[0];
                container.insertAdjacentHTML('beforeend', data.html);
                removeLoadingIndicator();

        //      check if there are posts left
                if (offset + data.batch_size >= data.total_posts) {
                    document.removeEventListener('scroll', scrollHandler, false);
                    appendMessage('No more posts');
                }
            })
            .catch(e => {
                console.log(`Error: ${e}`);
                appendMessage('An error occurred. Please try refreshing the page.');
            })
    }
</script>
