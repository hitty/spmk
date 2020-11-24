<?php
$search_params = Request::GetParameters(METHOD_GET);
Response::SetArray('search_params', $search_params);
$module_template = 'mainpage.html';
$ajax_result['ok'] = true;
?>