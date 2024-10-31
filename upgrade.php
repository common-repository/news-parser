<?php
function news_parser_plugin_upgrade() {
    // Delete cron options
    delete_option(NEWS_PURSER_PLUGIN_CRON_OPTIONS_NAME);
     // Delete template options
    delete_option(NEWS_PURSER_PLUGIN_TEMPLATE_OPTIONS_NAME);
    // Delete AI options
    delete_option(NEWS_PURSER_PLUGIN_AI_OPTIONS_TABLE_NAME);
   

}

// Register the upgrade hook
register_activation_hook( __FILE__, 'news_parser_plugin_upgrade' );
