<?php
$content_charset = "utf-8";
$content_type = "text/javascript";
$main_file = "js/main.js";
$cache_folder = 'filecache';
$standalone = !class_exists( 'FileData' );
if( !empty( $standalone ) ) {
    //рутовый путь
    $root = realpath( "." );
    if(defined("PHP_OS")) $os = PHP_OS; else $os = php_uname();
    if(strtolower(substr( $os, 0, 3 ) ) == "win" )  $root = str_replace( "\\", '/', $root );
    define( "ROOT_PATH", $root );

    require(ROOT_PATH.'/includes/class.config.php');
    Config::Init();
    include(ROOT_PATH.'/includes/class.host.php');
    Host::Init();
    include(ROOT_PATH.'/includes/class.filedata.php');
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
} else $id = isset($_GET['id']) ? intval($_GET['id']) : $js_id;
$scripts_and_css = FileData::Load();
if(!empty($scripts_and_css['js'])){
    $md5 = null;
    foreach($scripts_and_css['js'] as $key=>$js_set){
        if($js_set['id']==$id) {
            $md5 = $key;
            break;
        }
    }
}

if(empty($md5)) {
    $cached_filename = Host::getRealPath($main_file);
    $last_modified = filemtime($cached_filename);
} else {
    $cached_filename = Host::getRealPath($cache_folder.'/j_'.$md5.'.cache');
    $max_mtime = 0;
    foreach($scripts_and_css['js'][$md5]['files'] as $_file){
        $mtime = filemtime(Host::getRealPath($_file));
        if($mtime>$max_mtime) $max_mtime = $mtime;
    }
    if(!file_exists($cached_filename) || filemtime($cached_filename) < $max_mtime) {
        $_content = "";
        foreach($scripts_and_css['js'][$md5]['files'] as $_filename){
            $_file = Host::getRealPath($_filename);
            if(file_exists($_file)) {
                $_content .= file_get_contents( $_file)."\n";
            }
        }

        include(ROOT_PATH . '/includes/minify-master/src/Minify.php');
        include(ROOT_PATH . '/includes/minify-master/src/JS.php');
            
            $minifier = new MatthiasMullie\Minify\JS( $_content );
            $_content = $minifier->minify();
        if(file_exists($cached_filename)) unlink($cached_filename);
        $fpointer = fopen($cached_filename,'w');
        fwrite($fpointer,$_content);
        fclose($fpointer);
        usleep(100);
        chmod($cached_filename,0666);

    }
    $last_modified = filemtime($cached_filename);
}
if( empty( $standalone ) ) Response::SetString('js_content', file_get_contents( $cached_filename ) );
else {
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
        $if_modified_since = strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']));
        if ($if_modified_since == $last_modified) {
            header('HTTP/1.0 304 Not Modified');
            exit(0);
        }
    }
    ob_start ("ob_gzhandler");
    header("Content-type: ".$content_type."; charset=".$content_charset);
    header("Last-Modified: ".gmdate('D, d M Y H:i:s', $last_modified).' GMT');
    header("Content-Length: ".filesize($cached_filename));
    readfile($cached_filename);
    exit(0);     
}
  
?>
