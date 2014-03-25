<?php

define('WORDPRESS_PATH', '.');
define('TARGET_PATH', 'static-blog');

include WORDPRESS_PATH . '/wp-load.php';

function save_post($post)
{
    $post_filename = get_post_filename($post);
    $post_filepath = TARGET_PATH . "/$post_filename";
    $post_html = get_post_html($post);
    file_put_contents($post_filepath, $post_html);
    return $post_filename;
}

function get_post_html($post)
{
    $title = htmlspecialchars($post->post_title);
    $date = $post->post_date_gmt . " GMT";
    $html = wpautop($post->post_content);

    return
"<!DOCTYPE html>
<html>
<head>
<title>$title</title>
<meta charset=\"utf-8\">
</head>
<body>
<h1>$title</h1>
<p>$date</p>
$html</body>
</html>
";

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

// Extract posts into individual files.

$posts = get_posts(array('posts_per_page'=>PHP_INT_MAX));
$post_filenames = array();
$table_of_contents = '';
foreach ($posts as $post) {
    $post_filename = save_post($post);
    $post_filenames[] = $post_filename;
    $table_of_contents .= "<a href=\"$post_filename\" target=\"post\">".
                          $post->post_date_gmt.' GMT<br>'.htmlspecialchars($post->post_title).
                          "</a><hr>\n";
}

// Create table of contents.

$table_of_contents =
"<!DOCTYPE html>
<html>
<head>
<title>Table of Contents</title>
<meta charset=\"utf-8\">
</head>
<body>
$table_of_contents
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

// Create default post.

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

print count($posts) . " posts have been archived\n";

?>
