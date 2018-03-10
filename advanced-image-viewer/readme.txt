=== Advanced Image Viewer ===
Contributors: francescor93
Tags: image,image viewer,image manager,advanced,download
Requires at least: 4.0
Tested up to: 4.9.4
Stable tag: 1.0.0
License: Creative Commons Attribution-ShareAlike 4.0 International
License URI: http://creativecommons.org/licenses/by-sa/4.0/

Advanced attachment page for viewing image details with description and tags and download it in various formats.

== Description ==
Advanced Image Viewer is a WordPress plugin that allows you to change the default attachment page: inserting only a customizable shortcode instead of the default loop inside the attachment.php or image.php file will be shown on the page:
- The selected image or optionally a defined related video
- A list with all the available image sizes, allowing you to download them
- A form that allows administrators to attach to the current image additional files in other defined formats that will also be downloadable 
- The image description
- A list of clickable tags assigned to the image
- A list of other similar images sorted by number of common tags.
Through the appropriate administration menu you can also customize some plugin options, such as the sizes, the additional allowed extensions and the maximum number of related images.

== Installation ==
1. Install the plugin via the default "Add plugin" page.
2. Edit the image.php or attachment.php file of your template by inserting the shortcode [aiv-view] instead of the loop. This will be the point where the plugin content will be shown.
3. You can also add some parameters to the shortcode:
3a. hide-tags=1 will hide the image tags section
3b. hide-related=1 will hide the related images section
3c. hide-description=1 will hide the image description section
3d. size=CUSTOMSIZE (for example size=thumbnail) will show the image with the set size (default is largeWM)
4. Additionally, go to "AIV" admin page to set your preferences for this plugin

== Changelog ==
1.0.0: Initial release