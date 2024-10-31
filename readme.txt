=== News-Parser ===
Contributors: bikkel
Donate link: 
Author: Evgeniy Zalevskiy
Tags: scraper,rss,news,autopilot,ai
Requires PHP: 8.1
Requires at least: 5.2.0
Tested up to: 6.3.1
Stable tag: 2.2.0
License: MIT
License URI: https://opensource.org/licenses/MIT

=== News-parser WordPress Plugin ===

News-parser is a plugin for WordPress that allows you to easily receive the full text of the article, as well as images from the site using an RSS feed. Parsed information from the site is saved as a draft,which you can just publish or edit at your discretion. This makes it easy to create content for your site.


== New Features ==

The following new features are available in the new version of the plugin:
*   You can now generate featured images for your posts using OpenAI's Dall-e model.
*   Now you can generate even better content using the latest Gemeni Pro model from Google, support of which has been added to the new version of the plugin.

== New Website and Documentation! == 

We're excited to announce that the plugin now has its own dedicated website and comprehensive documentation!

Explore the new resources:

Website: [https://www.news-parser.com/](https://www.news-parser.com/)
Documentation: [Docs](https://news-parser.gitbook.io/news-parser-plugin/)

==  Features ==

*   Gutenberg editor support.
*   Autopilot Function for Automatic RSS Parsing
*   Visual content extractor.
*   WP-CLI support
*   Flexible template creation system to speed up parsing.
*   Ability to parse not only from RSS XML source but also from url.


== Installing ==

1. You can clone the GitHub repository: `https://github.com/zalevsk1y/news-parser.git`
2. Or download it directly as a ZIP file: `https://github.com/zalevsk1y/news-parser/archive/master.zip`

This will download the latest developer copy of News-parser.

== How to use NewsParserPlugin\this plugin? ==

= Parsing RSS =

To parse RSS, go to the News-Parsing->Parsing RSS menu in the admin panel of your site. Enter the RSS feed address in the search bar. Click on the Parse RSS Feed button. When parsed data is fetched from the server,it will appear on your screen. You can open the visual extractor by clicking on the icon and create a template for parsing posts from this RSS source or simply select the content you are interested in and save it as a draft.

Watch this short video to learn HOW TO PARSE FROM RSS with news-parser plugin:

https://www.youtube.com/watch?v=Aiye15Cp5_8

To parse several posts, select posts and press the Parse Selected button. Wait for the data to be saved,you`ll be notified by the message at the top of the screen. The icon at the bottom of the post allows you to go on to edit or publish a saved draft.Note that parsing selected post could be done only if you created parsing template!

Watch this short video to learn HOW TO PARSE SEVERAL POSTS with news-parser plugin:

https://www.youtube.com/watch?v=m85PExDeAMA

= Visual Constructor. =

To create a template or simply select the content you are interested in, use the visual constructor. You can open visual constructor by clicking icon at the bottom of post box.  
To select content, click on the block that you need in the main window and it will be marked with a turquoise frame. When you hover over the content, the expected area will be painted in turquoise color.
To cancel the selection, click on the block again. Try to separate different types of content (pictures, video, text) into separate blocks. The YouTube video will be replaced with a picture of the YouTube logo. You can extrude it and this video will be inserted into your post. Parsing videos from other sources is not yet supported. Pictures are inserted into your post as a link; the exception is a featured image which is saved in your media library.
In the sidebar, you can change the featured image of your post. Just select the appropriate image on the left side of the designer and click the Change Image button. The last image you selected will be selected as featured image. You can also create a post without featured image. Just click on No featured image.
You can change the name of the post in the next submenu 'Post title'. Write your version of the post title in textaria and click the Change Title button.
To add a source link, check the box labeled 'Add source link' to the post. in the 'Extra Options' submenu.

Watch this short video to learn HOW TO USE VISUAL CONSTRUCTOR:

https://www.youtube.com/watch?v=0yS0ptvBpzY

= Create Parsing Template =

To save the template, it is necessary to mark the content in the main window of the visual constructor, select the 'Save parsing template that you can use in automatic parsing from this source item.' and click the Save Tempalte button. It is important to understand that individual posts even from one source can be very different, therefore parsed pages may not contain the content you need.

Watch this short video to learn HOW TO CREATE PARSING TEMPLATE:

https://www.youtube.com/watch?v=0awSRLWsP-I

= Parse single page. =

To parse a single page, select News-Parsing-> Parse Page in the admin panel of your site. In the search bar, enter the site address URL and press Parse Page button. Visual constructor will open. In the visual constructor, select the content and click the Create Post Draft button. The draft will be automatically created and you can edit it in the Posts editor.If everything suits you, you can simply publish this post or edit it at your discretion.

Watch this short video to learn HOW TO PARSE SINGLE PAGE with news-parser plugin:

https://www.youtube.com/watch?v=Sbke_LF-TFA

= Autopilot Function for Automatic RSS Parsing =

The autopilot function is now available to automatically parse posts from an RSS feed. Please note that the wp-cron (`https://developer.wordpress.org/plugins/cron/`) is used for scheduling the autopilot function, which triggers the task scheduler only when the website is visited. If you encounter issues with this function, you can add the following option to the wp-config.php file: `define('ALTERNATE_WP_CRON', true);`

To configure the autopilot settings, follow these steps:

1. Navigate to the Autopilot tab in the menu (News Parser -> Autopilot).
2. In the Schedule Options, select the URL that corresponds to the RSS source from which parsing will take place.
3. Click on the Select button.
4. After the data is loaded, the following options will be available:

   - Status: Determines whether the autopilot is activated for this source.
   - Maximum Number of Posts: Sets the maximum number of posts to be parsed from this source.
   - Maximum Number of Autopilot Runs: Specifies the maximum number of times the autopilot should run for this source.
   - Parsing Frequency: Defines the frequency at which parsing should occur from this source.

5. Additionally, in this menu, you can delete previously saved parsing templates.


Watch this short video to learn HOW TO USE AUTOPILOT with news-parser plugin:

https://www.youtube.com/watch?v=Eu_5GR32nB0

= AI Feature =

To use AI feature you need to get API key from [OpenAI](https://platform.openai.com/api-keys) or from [Google](https://makersuite.google.com/app/apikey).
To use AI for generating content on your website, follow these instructions:
1. Set up the Open API Key: 
   - To setup OpenAPI key, open the `wp-config.php` file and add the following line to define your API key: `define('NEWS_PARSER_OPENAI_API_KEY', 'your_key')`. 
   - To setup Google key, open the `wp-config.php` file and add the following line to define your API key: `define('NEWS_PARSER_GEMINI_API_KEY','your_key')`.
2. Access the Visual Constructor: Go to the Visual Constructor within your website's admin panel.
3. Select the AI Provider: In the AI tab of the Visual Constructor, you will see a list of available AI providers. Choose the desired provider from the list.
3. Generate Featured Image: In the Featured Image section, check the "Generate using AI" option if you want to generate a new featured image for your post. Select the model and enter a prompt. Use `${title}` in the prompt wherever you want to insert the original title.
4. Generate Post Title: In the Post Title section, check the "Generate using AI" option if you want to generate a new title for your post. Select the model and enter a prompt. Use `${title}` in the prompt wherever you want to insert the original title.
5. Generate Post Body: In the Post Body section, select the model for generating the post's content. Enter a prompt in the request field and use `${post}` where you want to include the original article's text and you can use tag `${title}` here as well.
6. Add Pipeline (Optional): If you need to modify the text using multiple requests, open the "Add Pipeline" tab. Enter the additional request text, which will automatically include the result of the previous request. Click "Add Prompt" to add this request to the pipeline. With the pipeline, you can ask the AI to review the results of the previous generation or request translation of the generated text into another language.
7. Apply AI Modifications: After configuring the AI settings for content modification, you can apply them immediately by selecting the content in the Visual Constructor and clicking the "Create Post" button. Alternatively, you can save these settings as a template for applying them during RSS parsing or when using the autopilot function.
Please note that when using AI for content generation, it requires making API requests to the OpenAI API, which will incur charges on your OpenAI account. Additionally, the AI generation process can take a significant amount of time.
By following these instructions, you can use artificial intelligence to create content for your website, whether it's parsing individual pages, parsing from RSS feeds, or using the autopilot function.

https://www.youtube.com/watch?v=jF3pYio7H9w

= WP-CLI Support  =

With the latest update, a new feature has been introduced that leverages wp-cli. This feature enables users to activate an autopilot function, allowing for automated parsing and saving of posts from RSS feeds. The autopilot function can now be accessed directly from the command-line interface, providing a convenient way to manage this process.

To utilize this functionality, you'll need to install wp-cli and execute the command `wp autopilot` in the command-line interface. Additionally, you'll need to specify the desired interval at which the autopilot function should be triggered by including the additional parameter `wp autopilot --interval=`. This allows you to customize the frequency of the autopilot function according to your specific needs.

By incorporating wp-cli and the new "wp autopilot" command, managing the automatic parsing and saving of posts from RSS feeds becomes more efficient and streamlined. This feature provides enhanced control and flexibility, empowering users to automate their post management tasks with ease.

Example: 

`wp autopilot --interval=hourly`


== Dependencies ==

*  php-simple-html-dom-parser https://github.com/sunra/php-simple-html-dom-parser

== Bugs ==

If you find an issue, let us know [here](https://github.com/zalevsk1y/news-parser/issues?state=open) or [Discord](https://discord.gg/NewZGdxud9)

== Contacts ==

*   [GitHub](https://github.com/zalevsk1y/news-parser)
*   [Discord](https://discord.gg/9XkdNfRrrK)
*   [Instagram](https://www.instagram.com/wp_news_parser)

== Changelog ==

= 2.2.0 - 12-03-24 =

* Added: AI Image generation.
* Added: Google Gemini AI support.
* Fix: some bugs.

= 2.1.1 - 12-02-24 =

* Fix: some bugs.

= 2.1.0 - 18-01-24 =

* Added: AI features.
* Added: New WP-CLI command. 
* Fix: some bugs.

= 2.0.1 - 10-11-23 =

* Added: WP-CLI support.
* Added: Internationalization. 
* Fix: some bugs.

= 2.0.0 - 11-10-23 =

* Added: Autopilot functions for automate post parsing.
* Added: Post options system 
* Fix: some bugs.

= 1.0.2 - 20-04-21 =

* Fix: some bugs.

= 1.0.1 - 20-03-21 =

* Fix: some bugs.

= 1.0.0 - 2020-02-18 =

* Added:  Parsing Template system.
* Added:  Visual-constructor.
* Added:  You-Tube videos parsing.
* Fix:	some bugs
