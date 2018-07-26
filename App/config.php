<?php

define('ROOT_DIR',dirname(__DIR__));
define('TMP_JSON_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'tmp_json');
define('TMP_CSV_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'tmp_csv');
define('INPUT_TEMPLATES_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'input_templates');
define('WEB_VIEW_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'web_view');
define('HTML_OUTPUT_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'html_output');
define('LOGS_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'logs');
define('CREDENTIALS_DIR',dirname(__DIR__).DIRECTORY_SEPARATOR.'credentials');
define('APACHE_SITES_DIR','/etc/apache2/sites-available');
define('POSTS_PER_PAGE',16);
define('SPREADSHEET_RANGE','!A1:J');
define('TEMPLATE_EXTENSION','.html');
define('INDEX_TEMPLATE_NAME','index');
define('TAG_TEMPLATE_NAME','tag');
define('POST_TEMPLATE_NAME','post');
define('GOOGLE_APP_NAME','Static Site Generator');
define('GOOGLE_INFO_SHEET_NAME','sample');
define('ZIP_FILE_NAME','archive.zip');