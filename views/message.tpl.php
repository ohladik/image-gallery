<?php
// Template for Message

// icon based on message type
switch($message_type) {
    case 'info':
        $icon_class = 'fa-exclamation-circle';
        break;
    case 'success':
        $icon_class = 'fa-check-circle-o';
        break;
    case 'error':
        $icon_class = 'fa-exclamation-circle';
        break;
}
echo "
    <div class='message-container $message_type'>
        <i class='fa $icon_class' aria-hidden='true'></i>
    <span>$message</span>
</div>
";
