<div class='container w-100 h-100 d-flex flex-column justify-content-center align-items-center'>
          <h1><?php echo __('Welcome to the about page!','news-parser'); ?></h1>
          <p class='fs-5'><?php echo __('We are excited to announce that our documentation has been migrated to GitBook🚀 for enhanced user convenience.','news-parser') ?></p>
          <p class='fs-5'><?php echo __('Starting from the latest update, you can now access our documentation','news-parser') ?></p>
          <p class='fs-4 fw-bold'><a class='text-decoration-none' href="<?php 
            $locale= get_locale();
            $urls=include_once NEWS_PARSER_PLUGIN_DIR.'inc/Config/docs-urls.php';
            if(array_key_exists($locale,$urls)){
                echo $urls[$locale];
            }else{
                echo $urls['en_US'];
            }
          ?>">👉<?php echo __('HERE','news-parser') ?>👈</a></p>
          
        </div>