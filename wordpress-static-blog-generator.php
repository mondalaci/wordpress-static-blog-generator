<?php

if (file_exists('config.php')) {
    require 'config.php';
}

if (!defined('WORDPRESS_PATH')) {
    define('WORDPRESS_PATH', '.');
}
if (!defined('TARGET_PATH')) {
    define('TARGET_PATH', 'static-blog');
}

require WORDPRESS_PATH . '/wp-load.php';

// Extract pages and posts into individual files.

$posts = array_merge(get_pages(), get_posts(array('posts_per_page'=>PHP_INT_MAX)));
$post_filenames = array();
foreach ($posts as $post) {
    $post_filename = save_post($post);
    $post_filenames[] = $post_filename;
    $post_date = $post->post_type=='page' ? '' : $post->post_date_gmt.' GMT<br>';
    $table_of_contents .= "<a href=\"$post_filename\" target=\"post\" onclick=\"highlight(this)\">".
                          $post_date.htmlspecialchars($post->post_title).
                          "</a><hr>\n";
}

// Create table of contents page.

$table_of_contents =
"<!DOCTYPE html>
<html>
<head>
<title>Table of Contents</title>
<meta charset=\"utf-8\">
</head>
<body>
$table_of_contents
<script>
function highlight(link)
{
    var elements = document.getElementsByTagName('a');
    for (var i=0; i<elements.length; i++) {
        elements[i].style.background = 'white';
    }
    link.style.background = 'yellow';
}
</script>
</body>
</html>
";
file_put_contents(TARGET_PATH . '/toc.html', $table_of_contents);

// Create index page.

$index =
"<!DOCTYPE html>
<html>
<head>
<title>My backup blog</title>
<meta charset=\"utf-8\">
</head>
<frameset cols=\"25%,*\">
  <frame src=\"toc.html\">
  <frame name=\"post\" src=\"welcome.html\">
</frameset>
</html>
";
$index_filepath = TARGET_PATH . '/index.html';
if (!file_exists($index_filepath)) {
    file_put_contents($index_filepath, $index);
}

// Create welcome page.

$welcome =
"<!DOCTYPE html>
<html>
<head>
<title>Welcome!</title>
<meta charset=\"utf-8\">
</head>
<body>
</body>
<h1>Welcome!</h1>
<p>Welcome to my backup blog - a static, backed-up version of my blog created with
   <a href=\"https://github.com/mondalaci/wordpress-static-blog-generator\" target=\"_blank\">
   WordPress Static Blog Generator</a>.</p>
<p>Feel free to navigate around using the Table of Contents of the left frame.</p>
</html>
";
$welcome_filepath = TARGET_PATH . '/welcome.html';
if (!file_exists($welcome_filepath)) {
    file_put_contents($welcome_filepath, $welcome);
}

// Print status message.

print count($posts) . " posts have been archived\n";

// Helper functions

function save_post($post)
{
    $post_filename = get_post_filename($post);
    if ($post->post_type == 'page') {
        $post_filename = "page-$post_filename";
    }
    $post_filepath = TARGET_PATH . "/$post_filename";
    $post_html = get_post_html($post);
    file_put_contents($post_filepath, $post_html);
    return $post_filename;
}

function get_post_html($post)
{
    $title = htmlspecialchars($post->post_title);
    $date = $post->post_type=='page' ? '' : "<p>" . $post->post_date_gmt . " GMT</p>\n";
    $comments = get_post_comments($post);
    $html = wpautop($post->post_content) . ($comments ? '<hr>' : '') . $comments;

    return
"<!DOCTYPE html>
<html>
<head>
<title>$title</title>
<meta charset=\"utf-8\">
</head>
<body>
<h1>$title</h1>
$date$html</body>
</html>
";
}

function get_post_comments($post)
{
    $comments = get_comments(array('post_id'=>$post->ID, 'order'=>'ASC'));
    $comments_html = array();
    foreach ($comments as $comment) {
        $comments_html[] = get_comment_html($comment);
    }
    return implode("<hr>", $comments_html);
}

function get_comment_html($comment)
{
    $author = htmlspecialchars($comment->comment_author);
    $author_url = htmlspecialchars($comment->comment_author_url);
    $linked_author = $author_url ? '<a href="'.$author_url.'">'.$author.'</a>' : $author;
    $datestamp = $comment->comment_date_gmt . ' GMT:';
    $content = wpautop($comment->comment_content);
    $comment_html = "Comment written by $linked_author at $datestamp<br>$content\n";
    return $comment_html;
}

function get_post_filename($post)
{
    $post_permalink = get_permalink($post->ID);
    $post_absolute_path = parse_url($post_permalink, PHP_URL_PATH);
    $post_relative_path = substr($post_absolute_path, 1, -1);  // Strip starting and ending slash
    $post_basename = str_replace('/', '-', $post_relative_path);
    $post_filename = "$post_basename.html";
    return $post_filename;
}
