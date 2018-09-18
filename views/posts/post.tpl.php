<?php
// Template for post

$edit_container_class = 'post-edit-container hidden';
$edit_link = "index.php?controller=posts&action=edit&post_id=$post->id";

$container_class = 'post-heart-container';
$heart_link = "index.php?controller=posts&action=like&post_id=$post->id";
$heart_class = 'fa fa-heart-o post-heart not-liked';

if ($post->liked) {
    $heart_class = 'fa fa-heart post-heart';
    $heart_link = "index.php?controller=posts&action=unlike&post_id=$post->id";
}

// user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $heart_link = '';
    $edit_link = '';
    $container_class = 'post-heart-container anonymous';
} else if ($_SESSION['user_id'] == $post->authorId || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'])) {
//  user is logged in so determine if he will see the edit button
//  he must be author of the post or admin
    $edit_container_class = 'post-edit-container';
}

// one exception to forbidden inline styles - placing an image in the background
$theme = $GLOBALS['theme'];
echo "
<div class='post $theme' style='background-image: url(images/$post->imageFile)'>
    <div class='post-top-container'>
        <div class='post-label-container'>
            <span class='post-label'>$post->description</span>
            <span class='post-label date'>
                <i class='fa fa-clock-o' aria-hidden='true'></i>
                <time>$post->createdAt</time>
            </span>
        </div>
        <a href='$edit_link' class='$edit_container_class'>
            <i class='fa fa-pencil-square-o' aria-hidden='true'></i>
        </a>
    </div>
    <a href='$heart_link' class='$container_class'>
        <span class='post-likes'>$post->likes_count</span>
        <i class='$heart_class' aria-hidden='true'></i>
    </a>
</div>
";

?>
