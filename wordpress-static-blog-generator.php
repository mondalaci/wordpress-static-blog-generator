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

$posts = get_posts(array('posts_per_page'=>PHP_INT_MAX));

$post_filenames = array();
$table_of_contents = '';
foreach ($posts as $post) {
    $post_filename = save_post($post);
    $post_filenames[] = $post_filename;
    $table_of_contents .= "<li><a href=\"$post_filename\">".htmlspecialchars($post->post_title)."</a></li>\n";
}

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

print count($posts) . " posts have been archived\n";

?>
