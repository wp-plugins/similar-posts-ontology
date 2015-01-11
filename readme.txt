=== Similar Posts Ontology ===
Contributors: cfischer83
Tags: similar, related, posts, articles, content, associated, taxonomy, category, tags
Requires at least: 4.0.0
Tested up to: 4.1.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show a list of related posts that are similar to another post.

== Description ==

Does your website utilize categories and tags? Does it use custom taxonomies? If so, this plugin will find similar content
based on all your taxonomies. There are two ways to show related posts within your page.

* The first way to show related content on your post is to use the widget provided. This only works when is_single() is true
* The second way to show similar content on your site is to use the pk_related_return($post->ID); function which can be 
called programmatically anywhere you wish!

The Widget included with this plugin gives you the option to limit the amount of posts; it allows you to determine which
fields to show: Featured Image, Author, Date, and Excerpt (Title is required); and it allows you to determine which 
variant of the featured image to show: thumbnail, medium, large, or full.

If you find the Widget doesn't meet your needs or is too limiting, you can call the functionality programmatically using
this function:

pk_related_return($post->ID, $args);

Where $post->ID is the ID of the post for which you are wanting to show related articles.

The $args parameter is an array with the following values available to you (more coming soon):

posts_per_page (int defaults to 5)
thumbnail_size (string consisting of one of these values: "thumbnail", "medium", "large", "full". Defaults to thumbnail).

An example might be:

$args = array (
	'posts_per_page' => 6,
	'thumbnail_size' => 'medium'
);

The return value of pk_related_return is an array of objects that includes most of the fields within WordPress's posts
table plus permalink and featured image.

Future Additions:

Allow the user to specify only certain content types (posts, pages, custom) in a request. This would allow you to specify
only products get returned, or only blog posts. This would only be an issue if content types share taxonomies.

== Installation ==

1. Upload `similar-posts-ontology` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose either the widget from the left menu under 'Appearances' or use the pk_related_return function in your theme.

== Frequently Asked Questions ==

= How does this plugin work? =

There are two aspects to it. First, it finds all similarly tagged, categorized, and otherwise taxonomically created content
on your site, then sorts it by what has the most similarities. Second, if there is a tie between two posts it will give
the edge to the newest content.

= Why Ontology? What's an Ontology? =

Ontology is the study of the nature of 'being'. This plugin uses the ontological philosophy of determining an entities 
placement within its own 'type' by studying the entities relationships.

= Why am I not seeing any content when I install this? =

You can use this in two ways. Either by calling pk_related_return() in your theme or by placing the widget on your site.
If you are using the widget, remember that it only works on any "single" page (where is_single() would return true). The
pk_related_return() can theoretically work anywhere as long as you provide a proper post ID. Try var_dump() with 
pk_related_return() and look at the description for proper usage of this function.

= Why are my results coming back with weird content that I wouldn't expect? =

This issue may be your taxonomies. The content for which you're trying to find related content needs to have tags, 
categories, and/or custom taxonomies. Also, to properly find your content, tags/categories/taxonomies must be used on
the *related* content as well. The more you intentionally use your tags and categories, the better your results set will be.

== Changelog ==

= 1.0 =
*Release Date - January 10th, 2015*
* First Version
