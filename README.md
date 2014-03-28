WordPress static blog generator
===============================

Generates style-free static HTML pages from your blog for archival purposes.  See [my archived blog](http://mondalaci.github.io/) for example.

After pulling `wordpress-static-blog-generator.php` you may wanna create `config.php` in the same directory if the following default values need to be changed:

```
<?php
define('WORDPRESS_PATH', '/var/www/yourblog.com/blog');  // Defaults to "."
define('TARGET_PATH', '/save/static/files/here');        // Defaults to "static-blog"
```

At this point you should execute `wordpress-static-blog-generator.php` for the static HTML files to be generated.  You're also encouraged to push the archived files to `youraccount.github.io` along with your `/blog/wp-content/uploads/*` files in which case be sure to use relative paths in your posts and pages.
