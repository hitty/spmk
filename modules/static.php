<?php
// поиск в текстовых страницах
$item = $db->fetch( " SELECT * FROM " . $sys_tables['static_pages'] . " WHERE url = ?", $this_page->requested_url );
if( empty( $item ) ) Host::RedirectLevelUp();
Response::SetArray( 'item', $item );
//SEO данные
$this_page->manageMetadata(
    array(
        'title' => $item['title'] . ' - СПМК.',
        'keywords' => $item['title'],
        'description' => $item['title']
    ), true
);
$module_template = 'templates/static/static.html';
if( $ajax_mode ) $ajax_result['ok'] = true;
?>
