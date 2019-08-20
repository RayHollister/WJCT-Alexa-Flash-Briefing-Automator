<?php
   /*
   Plugin Name: WJCT Alexa Flash Briefing Automator
   Plugin URI: https://www.wjct.org
   description: A plugin that automatically updates the WJCT Alexa Flash Briefing when the NPR One newscast has been uploaded.
   Version: 0.04
   Author: Ray Hollister
   Author URI: https://rayhollister.com
   License:
   License URI:
   Text Domain: wjct-alexa-flash-briefing-automator
   */


   // The URL of the MP3 file uploaded to NPR One.
   $mp3url = 'https://media.publicbroadcasting.net/wjct/newscast/newscast.mp3';

   // Add a widget to the dashboard.

   function WJCT_Latest_Newscast_add_dashboard_widget()
   {
       wp_add_dashboard_widget(
           'WJCT_Latest_Newscast_widget',         // Widget slug.
                    'Latest Newscast',         // Title.
                    'WJCT_Latest_Newscast_widget_function' // Display function.
           );
   }
   add_action('wp_dashboard_setup', 'WJCT_Latest_Newscast_add_dashboard_widget');


   function WJCT_Latest_Newscast_widget_function()
   {
       global $mp3url;
       echo '<div>';

       // get the datetime when the newscast was last updated
       $lastupdate = get_option('lastupdated');

       $headers = get_headers($mp3url);

       // Show the entire PHP server header of the file
       // https://www.php.net/manual/en/function.get-headers.php
       /*
       print_r(get_headers($url));
       echo '</p>';
       */

       // Show just the header that we need, the modified date/time
       $recentlastmod = substr($headers[7], 15, 29);
       // convert string to Unix Timestamp
       $recenttimestamp = strtotime($recentlastmod);
       // format timestamp and convert to local timezone
       $recentupdate = date("m/d/Y h:i:s A T", $recenttimestamp);

       // echo '<p>The last update was ' . $lastupdate . '</p>';
       // echo '<p>The most recent update was ' . $recentupdate . '</p>';

       if ($recentupdate != $lastupdate) {
         echo "<p>The newcast was updated " . $recentupdate . ".<br/>A new flash briefing is being published now.</p>";
         programmatically_create_post();
       }
       else {
         echo "<p>The newscast was last updated " . $lastupdate . ".</p>";
       }

       echo '</div>';

       // store the most recent datetime the newscast was updated in the website database
       update_option('lastupdated', $recentupdate);
       // update_option('lastupdated', 'tents');
   }

 /**
 * A function used to programmatically create a post in WordPress. The slug, author ID, and title
 * are defined within the context of the function.
 *
 * @returns -1 if the post was never created, -2 if a post with the same title exists, or the ID
 *          of the post if successful.
 *
 * Credit: Tom McFarlin https://tommcfarlin.com/programmatically-create-a-post-in-wordpress/#code
 *
 */

function programmatically_create_post()
{
    global $mp3url;

    $headers = get_headers($mp3url);
    // Show just the header that we need, the modified date/time
    $recentlastmod = substr($headers[7], 15, 29);
    // convert string to Unix Timestamp
    $recenttimestamp = strtotime($recentlastmod);

    // Initialize the page ID to -1. This indicates no action has been taken.
    $post_id = -1;

    // Setup the author, slug, category and title for the post
    // User ID 636 is the generic 'WJCT' User
    $author_id = 636;

    // set the slug to the Unix Timestamp
    $slug = $recenttimestamp;

    // $title = 'My Example Post';
    // Set the title to the most recent updated date time
    $title = 'Flash Briefing ' . date("m/d/Y H:i:s", $recenttimestamp);

    // 1605 is WJCT's 'News Flash' category id
    $category_ids = array (1605);

    // This sets the content of the post to the embed link for the mp3.
    $post_content = '[embed]' . $mp3url . '[/embed]';

    // If the page doesn't already exist, then create it
    if (null == get_page_by_title($title)) {

        // Set the post ID so that we know the post was created successfully
        $post_id = wp_insert_post(
            array(
                'comment_status'	=>	'closed',
                'ping_status'	=>	'closed',
                'post_author'	=>	$author_id,
                'post_content'	=>	$post_content,
                'post_name'	=>	$slug,
                'post_title'	=>	$title,
                'post_category'	=>	$category_ids,
                'post_status'	=>	'publish',
                'post_type' =>	'post'
            )
        );

    // Otherwise, we'll stop
    } else {

        // Arbitrarily use -2 to indicate that the page with the title already exists
        $post_id = -2;
    } // end if
} // end programmatically_create_post

// uncomment this to test:
//add_filter('after_setup_theme', 'programmatically_create_post');
