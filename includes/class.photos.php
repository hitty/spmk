<?php
class Photos {
    public static $__folder_options=array(
                            'sm'=>array(90,90,'cut',65),
                            'med'=>array(560,415,'cut',75),
                            'big'=>array(2000,1600,'',70)
                            );                 // свойства папок для загрузки и формата фотографий
    public static $__folder_originals = 'originals';
    public static $id_file = 0;
    /**
    * получение главной фотки
    * @param string $table - основная таблица
    * @param integer $id - ID объекта в основной таблице
    * @return array of arrays
    */
    public static function getMainPhoto($table, $id){
        global $db;
        
        $sql = "SELECT 
                    `photos`.`id`,
                    `photos`.`name`,
                    LEFT (`photos`.`name`,2) as `subfolder`
                FROM ".Config::$sys_tables[$table.'_photos']." photos
                LEFT JOIN ".Config::$sys_tables[$table]." `main` 
                    ON `main`.`id_main_photo` = `photos`.`id`
                WHERE `main`.`id`=".$id."
                LIMIT 1 ";
        $rows = $db->fetch($sql);
        return !empty($rows)>0 ? $rows : false;
    }
   
    /**
    * установка флага "главное фото" для объекта
    * @param string $table - основная таблица
    * @param integer $id - ID объекта в основной таблице
    * @param integer $id_file - ID фото
    * @param integer $id_file - ID фото
    * @param integer $id_file - ID фото
    * @return boolean
    */
    public static function setMain($table, $id, $id_file=null, $parent_field = 'id_parent', $id_parent_common = false ){
        global $db;
        //перенос временных id_parent
        if( !empty( $id_parent_common ) ) $db->query(" UPDATE " . Config::$sys_tables[$table.'_photos'] . " SET id_parent = ?, id_parent_common = 0 WHERE id_parent_common = ?", $id, $id_parent_common );
        
        //установка главной фотки
        if($id>0 && $id_file>0){
        } else if($id>0 && $id_file==null){
            if(!empty($external_img_src)){
               $photo = $db->fetch("SELECT `id` FROM ".Config::$sys_tables[$table.'_photos']." WHERE `" . $parent_field . "` = ? AND external_img_src = ? LIMIT 1", $id,$external_img_src ); 
               if( !empty( $photo ) ) $id_file = $photo['id'];
            } else {
                $nextPhoto = $db->fetch( "SELECT `id` FROM ".Config::$sys_tables[$table.'_photos']." WHERE `" . $parent_field . "` = ? ORDER BY position, id LIMIT 1", $id );
                if( $nextPhoto['id']>0 ) $id_file = $nextPhoto['id'];
            }
        }
        else return false;
        if( !empty( $id_file ) ) $res = $db->query("UPDATE  ".Config::$sys_tables[$table]." SET `id_main_photo` = ? WHERE `id` = ?", $id_file, $id );
        return true;
        
    } 
    
    /**
    * поворот фотки на 90 по часовой стрелке
    * 
    * @param mixed $table
    * @param mixed $id_file
    */
    public static function rotatePhoto($table,$id_file){
        global $db;
        $photo_name = $db->fetch("SELECT name FROM ".Config::$sys_tables[$table."_photos"]." WHERE id = ?",$id_file);
        if(empty($photo_name) || empty($photo_name['name'])) return false;
        else $photo_name = $photo_name['name'];
        
        $result = array();
        
        preg_match('/(?<=\.)[A-z]+$/',$photo_name,$extension);
        $extension = strtolower(array_pop($extension));
        $subfolder_name = substr($photo_name,0,2);
        if(!in_array($extension,array('jpeg','jpg','png','gif'))) return false;
        $degrees = -90;
        $sizes = array('sm','med','big');
        foreach($sizes as $key=>$size){
            $filename="/img/uploads/".$size."/".$subfolder_name."/".$photo_name;
            //header('Content-Type: image/jpeg');
            $source = imagecreatefromstring(file_get_contents(ROOT_PATH.$filename));
            $result[$size.'_reading'] = (!empty($source));
            $result[$size.'_writeable'] = is_writeable(ROOT_PATH.$filename);
            switch(true){
                case $extension == 'jpeg' || $extension == 'jpg':
                    //$source = imagecreatefromjpeg($filename);
                    $rotate = imagerotate($source, $degrees, 0);
                    $result[$size.'_rotating'] = !empty( $rotate );
                    $result[$size.'_saving'] = imagejpeg($rotate, ROOT_PATH.$filename, 99);
                    break;
                case $extension == 'png':
                    //$source = imagecreatefrompng($filename);
                    $rotate = imagerotate($source, $degrees, 0);
                    $result[$size.'_rotating'] = (!empty($rotate));
                    imagepng($rotate, ROOT_PATH.$filename, 99);
                    break;
                case $extension == 'gif':
                    //$source = imagecreatefromgif($filename);
                    $rotate = imagerotate($source, $degrees, 0);
                    $result[$size.'_rotating'] = (!empty($rotate));
                    imagegif($rotate, ROOT_PATH.$filename, 99);
                    break;
            }
            imagedestroy($rotate);
            imagedestroy($source);
            unset($source);
            unset($rotate);
        }
        $result['ok'] = true;
        return $result;
    }
    
    /**
    * перестраиваем порядок фоток для объекта
    * 
    * @param mixed $id_parent
    * @param mixed $new_order
    */
    public static function setListOrder($table,$id_parent,$new_order){
        global $db;
        
        if(!is_array($new_order)) $new_order = explode(',',$new_order);
        $res = true;
        foreach($new_order as $position=>$file_id){
            $res = $res && $db->query("UPDATE ".Config::$sys_tables[$table."_photos"]." SET position = ? WHERE id = ?",($position+1),$file_id);
        }
        return $res;
    }
    
    /**
    * сортировка фотографий
    * @param string $table - основная таблица
    * @param array $order - порядок фото
    * @return boolean
    */
    public static function Sort($table, $order){
        global $db;
        foreach($order as $cnt => $value){
            $res = $db->query("UPDATE  ".Config::$sys_tables[$table.'_photos']." SET `position` = ".($cnt+1)." WHERE `id` = ".$value);
            if(empty($res)) return false;
        }
        return true;    
    }     
     /**
    * получение списка фотографий
    * @param string $table - основная таблица
    * @param integer $id - ID объекта в основной таблице
    * @return array of arrays
    */
    public static function getList($table, $id, $limit = 0){
         global $db;
         $sql = "SELECT 
                `photos`.`name`,
                `photos`.`title`,
                LEFT (`photos`.`name`,2) as `subfolder`,
                `photos`.`id`, 
                IF(`main`.`id_main_photo`=`photos`.`id`,'true','') as `main_photo`
           FROM ".Config::$sys_tables[$table.'_photos']." photos 
           LEFT JOIN ".Config::$sys_tables[$table]." main ON `main`.`id` = `photos`.`id_parent`
           WHERE `photos`.`id_parent` = ".$id."
           ORDER BY main_photo='true' DESC, IF( `photos`.`position`=0, 1, 0 ), `photos`.`position` ASC, `photos`.`id` 
           " . ( !empty( $limit ) ? " LIMIT 0, " . $limit : "" ) ;
        $rows = $db->fetchall($sql);
        if(empty($rows)) return array();
        $main_photo = false;
        foreach($rows as $k=>$item){
            if(!empty($item['main_photo'])) $main_photo = true;
        }
        if(empty($main_photo)) $rows[0]['main_photo'] = 'true';
        return $rows;
    }
    
    /**
    * загрузка фотографии
    * 
    * @param mixed $table - основная таблица
    * @param mixed $id - ID объекта в основной таблице
    * @param mixed $external_img_src - URL фото
    * @param mixed $internal_img_src
    * @param mixed $min_width
    * @param mixed $min_height
    * @param mixed $high_quality
    * @param mixed $watermark_src
    * @param mixed $watermark_alpha_level
    * @param mixed $max_width
    * @param mixed $max_height
    * @param mixed $fixed_sizes
    */
    public static function Add( $table, $id, $external_img_src=false, $internal_img_src = false, $high_quality = false, $watermark_src = '', $watermark_alpha_level = 100, $min_width=null, $min_height=null, $max_width = false, $max_height = false, $fixed_sizes = false, $force_add = false, $save_original = false ){
        global $db, $errors_log;
        
        // временная папка для загрузки фото
        $image_folder = !empty( Config::$values['img_folders'] ) ?  Config::$values['img_folders']  : Config::$values['img_folders'];
        $tempFolder = ROOT_PATH . '/' . $image_folder . '/'; 
        
        //если передана ссылка на скачивание
        if(!empty($external_img_src) && empty($internal_img_src)) $img_url = self::Download($external_img_src,$tempFolder);
        elseif(!empty($internal_img_src)) $img_url = $internal_img_src;
        if(empty($img_url) && !empty($external_img_src)) return false;
        else{
            if(!empty($_FILES)){
                $array_key = array_keys($_FILES);
                $array_key = $array_key[0];
            }
            
            // проверка типа файла
            $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
            $fileParts = !empty($img_url) ? pathinfo($img_url) : (!empty($_FILES[$array_key]['name']) ? pathinfo($_FILES[$array_key]['name']) : false);
            if(!empty($fileParts['extension'])){
                $targetExt = $fileParts['extension'];
                
                if(empty($img_url)) $tempFile = $_FILES[$array_key]['tmp_name'];
                $targetFile = md5(microtime()).'.' . $targetExt;
                $subFolder = substr($targetFile,0,2); 
                // загрузка фотографий в папки
                if (in_array(strtolower($fileParts['extension']),$fileTypes)) {
                    if(empty($img_url)) move_uploaded_file($tempFile, $tempFolder . $targetFile);
                    else rename($tempFolder.$img_url,$tempFolder . $targetFile); 
                    $size =  getimagesize($tempFolder . $targetFile);
                    $write=true; 
                    $filenames = $widths = $heights = $modes = $qualities = array();
                    
                    //если указан фиксированный размер и картинка не подходит, выходим
                    if(!empty($fixed_sizes) && (empty($min_width) || empty($min_height) || $size[0]!=$min_width || $size[1]!=$min_height)){
                        //$errors_log = "Картинка не подходит по размеру: требуемый размер - ".$min_width."x".$min_height;
                        return false;
                    }
                    //если размеры картинки меньше, чем минимальные, выходим
                    elseif( empty( $fixed_sizes ) ) {
                        if( !empty( $min_width ) && $size[0] < $min_width ) return "Минимальная ширина изображения: " . $min_width . 'px';
                        if( !empty( $min_height ) && $size[1] < $min_height ) return "Минимальная высота изображения: " . $min_height . 'px';
                    }
                    
                    foreach(self::$__folder_options as $_folder=>$_options) {
                        $filenames[] = $tempFolder . $_folder . '/' . $subFolder . '/' . $targetFile;
                        $widths[] = $_options[0];
                        $heights[] = $_options[1];
                        $modes[] = $_options[2];   
                        if(!empty($high_quality)) $qualities[] = 95;
                        else $qualities[] = $_options[3];
                        self::makeDir($tempFolder . $_folder . '/' . $subFolder . '/' . $targetFile);
                    }   
                    try{
                        $res = self::imageResize( $tempFolder.$targetFile, $filenames, $widths, $heights, $modes, $qualities, false, $watermark_src, $watermark_alpha_level, !empty($external_img_src) ? true : false);
                    }catch(Exception $e){
                        if(!empty($errors_log)) $errors_log['img'][] = $external_img_src.' - '.$e->getMessage();
                        return false;
                    }
                    
                    /*
                    if(!$res) {
                        //массив логирования при выгрузке объектов из форматов
                        if(!empty($errors_log)) $errors_log['img'][] = $external_img_src.' - не удалось сделать ресайз';
                        return false;
                    }
                    */
                    //переносим в папку с оригиналами иил удаляем
                    if(!empty($save_original)){
                        self::makeDir($tempFolder . self::$__folder_originals . "/" . $subFolder . "/". $targetFile);
                        rename($tempFolder . $targetFile, $tempFolder . self::$__folder_originals . "/" . $subFolder . "/" . $targetFile);
                    }
                    else unlink($tempFolder . $targetFile);
                    $addition_sql = "";
                    if($external_img_src!='') $addition_sql = ", `external_img_src`='".$external_img_src."'";
                    
                    //сохранение фотографии
                    if( empty( $force_add ) ) self::Save( $table, $id, $targetFile, $addition_sql );
                        
                    return array(
                        'filename' => $targetFile ,
                        'fullname' => '/' . $image_folder . '/sm/' . $subFolder . '/' . $targetFile ,
                        'file_id'  => self::$id_file
                    );
                    

                } else return false;
            }  else return false;
        } 
        
    }
    
    public static function Save( $table, $id, $targetFile, $addition_sql ){
        global $db;
        //поиск родительской записи
        $parent = $db->fetch(" SELECT * FROM " . Config::$sys_tables[$table] . " WHERE id = ?", $id );
        $parent_field = !empty( $parent ) ? 'id_parent' : 'id_parent_common' ;
        //запись имени фото в БД
        
        if( !empty( Config::$sys_tables[$table.'_photos'] ) && $db->query("INSERT INTO ".Config::$sys_tables[$table.'_photos']." SET `name` = '".$targetFile."', `" . $parent_field . "` = ".$id.$addition_sql ) ) {
            self::$id_file = $db->insert_id;
            //запись главной фотки если она первая
            if( !empty( $parent ) ) 
                $getMainPhoto = $db->fetch("SELECT ".Config::$sys_tables[$table].".id_main_photo
                                        FROM ".Config::$sys_tables[$table]."
                                        RIGHT JOIN ".Config::$sys_tables[$table.'_photos']." ON ".Config::$sys_tables[$table.'_photos'].".id = ".Config::$sys_tables[$table].".id_main_photo 
                                        WHERE ".Config::$sys_tables[$table].".id_main_photo > 0 AND ".Config::$sys_tables[$table].".id = ".$id);
            if( empty( $getMainPhoto ) ) self::setMain( $table, $id, self::$id_file, $parent_field );
        }
    }
    
     /**
    * название фото
    * @param string $table - основная таблица
    * @param integer $id - ID объекта в основной таблице
    * @param integer $title - название
    * @return array of arrays
    */
    public static function setTitle($table, $id, $title){
         global $db;
         return $db->query("UPDATE ".Config::$sys_tables[$table.'_photos']." SET title = ? WHERE id = ?", $title, $id); 
    }    
    /**
    * скачивание фото по ссылке
    * @param string $url - ссылка на фото
    * return string
    */
    public static function Download($url,$folder){
        global $errors_log;
        //имя файла
        $filename = basename($url);
        //расширение
        $filename_extensions = explode('.',$filename);
        $extension = $filename_extensions[strtolower(count($filename_extensions)-1)];
        //допустимые расширения
        $extensions = array('jpg','jpeg','gif','png');
        if(in_array($extension,$extensions)) $targetExt = $extension;
        else {
            $info = getimagesize($url);
            $targetExt = str_replace('image/','',$info['mime']);
            if($targetExt=='') {
                $errors_log['img'][] = $url." - разрешение файла на внешнем сервере не определено";
                return false;                
            }
        }        
        //имя сохраняемого файла
        $fullname = $folder . md5(microtime()).'.' . $targetExt;
        $ch = curl_init($url);
        $fp = fopen($fullname, "w+");
        $options = array(CURLOPT_FILE => $fp,
                 CURLOPT_HEADER => 0,
                 CURLOPT_FOLLOWLOCATION => 1,
                 CURLOPT_TIMEOUT => 1);

        curl_setopt_array($ch, $options);
        $save = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        exec("chmod 777 ".$fullname);
        //определение расширения файла
        if(empty($info)){
            if(!@is_array(getimagesize($fullname))){
                $errors_log['img'][] = $url." - разрешение файла на сервере не определено";
                unlink($fullname);
                return false;
            }
            $info = getimagesize($fullname);
        }

        
        if(empty($info)){
            exec("wget \"$url\" --output-document=".$fullname);
            exec("chmod 777 ".basename($url));
            $size = getimagesize($fullname);
            $info = pathinfo($fullname);
            if(empty($info)) return false;
            else $size = getimagesize($fullname);
        }  
        return  basename($fullname);
    }     
    
    /**
    * скачивание фото по ссылке
    * @param string $urls - массив с фото
    * return string
    */
    public static function MultiDownload($urls,$folder){
        global $errors_log, $multi_download;

        $mh = curl_multi_init();
        foreach ($urls as $i => $url) {
            $oldname[$i]['filename'] = md5(microtime());
            $filename_extensions = explode('.', trim( basename($url) ));
            $extension = $filename_extensions[strtolower(count($filename_extensions)-1)];
            $extension = str_replace('"','',$extension);
            //допустимые расширения
            $extensions = array('jpg','jpeg','gif','png');
            if(in_array($extension,$extensions)) $oldname[$i]['filename'] .= ".".$extension;

            
            $oldname[$i]['external_img_src'] = $url;
            $g=$folder.$oldname[$i]['filename'];
            $conn[$i]=curl_init($url);
            $fp[$i]=fopen ($g, "w");
            curl_setopt ($conn[$i], CURLOPT_FILE, $fp[$i]);
            curl_setopt ($conn[$i], CURLOPT_HEADER ,0);
            curl_setopt($conn[$i],CURLOPT_CONNECTTIMEOUT,1);
            curl_setopt($conn[$i],CURLOPT_FOLLOWLOCATION,true);
            curl_setopt($conn[$i],CURLOPT_MAXREDIRS,2);
            curl_multi_add_handle ($mh,$conn[$i]);
        }
        $overall_time_counter = microtime(true);
        do {
            $n=curl_multi_exec($mh,$active);   
            usleep(10);
            if(round(microtime(true) - $overall_time_counter, 4) > 10){
                $multi_download = false;
                break;
            }
        }                        
        while ($active);
        foreach ($urls as $i => $url) {
            curl_multi_remove_handle($mh,$conn[$i]);
            curl_close($conn[$i]);
            fclose ($fp[$i]);
        }
        curl_multi_close($mh); 
        $return_files = array();
        foreach($oldname as $k=>$name){
            //имя файла
            $filename = basename($name['filename']);
            //расширение
            $filename_extensions = explode('.',$filename);
            $extension = $filename_extensions[strtolower(count($filename_extensions)-1)];
            //допустимые расширения
            $extensions = array('jpg','jpeg','gif','png');
            if(is_array(@getimagesize($folder.$name['filename']))) $info = getimagesize($folder.$name['filename']);
            if(empty($info)) {
                unlink($folder.$name['filename']);
                $errors_log['img'][] = $name['external_img_src']." - файл скачан в пустой файл";
            } elseif($info[0]<50 || $info[1]<50){
                unlink($folder.$name['filename']);
                $errors_log['img'][] = $name['external_img_src']." - размер файла меньше 50x50";
            }
            elseif(!in_array($extension,$extensions)){
                $targetExt = str_replace('image/','',$info['mime']);
                if($targetExt=='') { 
                    unlink($folder.$name['filename']);
                    $errors_log['img'][] = $name['external_img_src']." - разрешение файла на внешнем сервере не определено";
                } else {
                    rename($folder.$name['filename'],  $folder.$name['filename'].'.'.$targetExt);
                    $name['filename'] .= '.'.$targetExt; 
                    $return_files[]= array('filename'=>$name['filename'], 'external_img_src'=>$name['external_img_src']); 
                }
            } else $return_files[]= array('filename'=>$name['filename'], 'external_img_src'=>$name['external_img_src']); 
        }   
        return $return_files;
    }           
    /**
    * удаление фотографии из базы и из папки на сервере
    * @param string $table - основная таблица
    * @param integer $id_file - ID фотографии в таблице с фотками
    */
    public static function Delete($table, $id_file){
        global $db, $image_folder;
        
        if(empty($image_folder)) $image_folder = !empty( Config::$values['img_folders'] ) ?  Config::$values['img_folders']  : Config::$values['img_folders'];
        
        //определяем имя файла (для удаления всех фото с таким именем)
        $photo_name = $db->fetch("SELECT LEFT(`name`,2) as `subfolder`, `name` FROM ".Config::$sys_tables[$table.'_photos']." WHERE `id` = ?",$id_file); 
        if(empty($photo_name)) return false;        
        $sql = "SELECT `photo`.`name`, `photo`.`id` as file_id, `estate_table`.`id`
                FROM ".Config::$sys_tables[$table.'_photos']." `photo`
                LEFT JOIN ".Config::$sys_tables[$table]." `estate_table` ON `estate_table`.`id` = `photo`.`id_parent`
                WHERE `photo`.`name` = '".$photo_name['name']."'";
        $rows = $db->fetchall($sql);
        if(empty($rows)) return false;
        //удаление фото с сервера
        $cnt=0;
        $unlink_flag = true;
        foreach(self::$__folder_options as $_folder=>$_options)    {
            $filename = ROOT_PATH.'/'.$image_folder."/".$_folder."/".$photo_name['subfolder']."/".$photo_name['name'];
            if(!file_exists($filename) || !unlink($filename)) { $unlink_flag = false; break; }
        }
        foreach($rows as $k=>$item){
            $del = $db->query("DELETE FROM ".Config::$sys_tables[$table.'_photos']." WHERE `id` = ?",$item['file_id']);
            if(empty($item['id'])) continue;
            //если удаленная фотография является главной, то переназначаем главную
            $main_photo = self::getMainPhoto($table,$item['id']);
            if($main_photo['id']>0 && $main_photo['id']==$id_file) self::setMain($table, $item['id'], 0);
        }
        return !empty($del) && !empty($unlink_flag);
    }
    /**
    * удаление всех фотографий из базы и из папки на сервере
    * @param string $table - основная таблица
    * @param integer $id_parent - ID предка
    */
    public static function DeleteAll( $table, $id_parent = false, $id_parent_common = false ){
        global $db, $image_folder;
        if(  empty( $id_parent ) && !empty( $id_parent_common ) ){
             $prefix = '_common';
             $id_parent = $id_parent_common;
        } else $prefix = '';

        $sql = "SELECT *, LEFT(`name`,2) as `subfolder` FROM ".Config::$sys_tables[$table.'_photos']." WHERE `id_parent" . $prefix . "` IN (".$id_parent.")";
        $rows = $db->fetchall($sql);
        if(empty($rows)) return false;
        $cnt=0;
        $unlink_flag = true;
        foreach($rows as $key=>$value){
            foreach(self::$__folder_options as $_folder=>$_options)    {
                if (file_exists(ROOT_PATH.'/'.$image_folder."/".$_folder."/".$value['subfolder']."/".$value['name']))
                    if(!unlink(ROOT_PATH.'/'.$image_folder."/".$_folder."/".$value['subfolder']."/".$value['name'])) { $unlink_flag = false; break; }
            }
            $del = $db->query("DELETE FROM ".Config::$sys_tables[$table.'_photos']." WHERE `id_parent" . $prefix . "` = ?",$id_parent);
            
        }
        $db->query( "UPDATE ".Config::$sys_tables[$table]." SET `id_main_photo` = 0 WHERE id = ?", $id_parent );
        return !empty($del) && !empty($unlink_flag);
    }
    /**
    * Check for folder is exists and create it recursively if it need
    * @param string $path path to the file
    * @return boolean
    */
    public static function makeDir($path){
        if(empty($path)) return false;
        $dir = dirname($path);
        if(is_dir($dir)) return true;
        $result = true;
        if(!mkdir($dir, 0777, true)) return false;
        else chmod($dir, 0777);
        return true;
    }
    /**
    * Метод ресайза фоток
    * @param string $src - файл исходник
    * @param string $dest - новое имя файла
    * @param integer $new_width ширина
    * @param integer $new_height высота
    * @param string $mode 
    *        1. mode!='' - картинка вписывается в размер, оставляя белые края; 
    *        2. mode = ''  - картинка масштабируется относительно размеров и конечный размер зависит от пропорций; 
    *        3. mode='cut' - картинка уменьшается, размер $width и $height, обрезка по центру;
    * @param integer $quality качество сжатия, по умолчанию 80%;
    * @param string $rgb - задний фон
    * @param string $watermark_src - исходный файл водного знака
    * @param string $watermark_alpha_level - прозрачность водного знака
    * @return boolean
    */

    public static function imageResize($src, $dest, $new_width, $new_height, $mode='',  $quality = 85, $rgb='#ffffff', $watermark_src = '', $watermark_alpha_level = 100, $extension_check = false) {
        $datas = array();
        if(!is_array($dest)) $datas[] = array('destination'=>$dest, 'new_width'=>$new_width, 'new_height'=>$new_height, 'mode'=>$mode, 'quality'=>$quality);
        else{
            foreach($dest as $k=>$v){
                $datas[] = array('destination'=>$dest[$k], 'new_width'=>$new_width[$k], 'new_height'=>$new_height[$k], 'mode'=>!empty($mode[$k])?$mode[$k]:'', 'quality'=>!empty($quality[$k])?$quality[$k]:80);
            }
        }
        $info = getimagesize($src);
        $pathinfo = pathinfo($src);                                                      
        $extension = $pathinfo['extension'];
        if(!is_array($info) || empty($extension))throw new Exception("Не удалось получить размер фото",3);
        //проверка на целостность файла
        
        if(!empty($extension_check)){
            if( ($extension == 'gif' && $info['mime'] != 'image/gif' ) ||
                ($extension == 'png' && $info['mime'] != 'image/png' ) ||
                ( in_array($extension,array('jpeg','jpg')) && $info['mime'] != 'image/jpg' && $info['mime'] != 'image/jpeg' )
            ) {
                unlink($src);
                throw new Exception("Заявленный(".$extension.") и mime-тип(".$info['mime'].") не совпадают",1);
            } 
        }
        $width = $info[0]; 
        $height = $info[1]; 
        $ext = pathinfo($src);    

        if (class_exists('Imagick')) {  //ресайз библиотекой Imagick 
            $new_image = new Imagick(); 
            $read = @$new_image->readImage($src); 
            $watermark_src = !empty( $watermark_src ) ? $watermark_src : ( !empty( Config::Get( 'watermark_src' ) ) ? Config::Get( 'watermark_src' ) : false ) ;
            if(!empty($watermark_src)){
                // Open the watermark
                $watermark = new Imagick();
                $watermark->readImage(ROOT_PATH.$watermark_src);
            }
            if(!empty($read)){
                foreach($datas as $k=>$data){
                    $image = clone $new_image;
                    
                    // если одно из измерений пустое - пропорционально подгоняем его
                    if(empty($data['new_height'])) {
                        if(empty($data['new_width'])) $data['new_width'] = $width;
                        $data['new_height'] = intval($data['new_width']*$height/$width);
                    }
                    if(empty($data['new_width'])) $data['new_width'] = intval($data['new_height']*$width/$height);
                    
                    // если размеры картинки меньше требуемых - то принимаем их за требуемые
                    if($data['mode']!='cut'){
                        if($width < $data['new_width']) {
                            $data['new_width'] = $width;
                            $data['new_height'] = intval($data['new_width']*$height/$width);
                        } 
                        if($height < $data['new_height']) {
                            $data['new_height'] = $height;
                            $data['new_width'] = intval($data['new_height']*$width/$height);
                        }
                        if($width < $data['new_width'] && $height < $data['new_height']) {
                            $data['new_width'] = $width;
                            $data['new_height'] = $height;
                        } 
                    }     
                    
                    switch($data['mode']){
                        case 'cut'://обрез картинки по заданному размеру
                        case 'cut_wo_resize'://обрез картинки по заданному размеру без сжатия размеров
                                if(($width/$data['new_width']) < ($height/$data['new_height'])) $image->cropImage($width, floor($data['new_height']*$width/$data['new_width']), 0, 0);
                                else $image->cropImage(ceil($data['new_width']*$height/$data['new_height']), $height, (($width-($data['new_width']*$height/$data['new_height']))/2), 0);
                                // thumbnail the image
                                if($data['mode']=='cut') $image->ThumbnailImage($data['new_width'],$data['new_height'],false);
                                else  {
                                    $image->cropImage($data['new_width'],$data['new_height'],($width-$data['new_width'])/2, ($height-$data['new_height'])/2);
                                }
                            break;
                        case '': //картинка масштабируется относительно размеров и конечный размер зависит от пропорций;
                            if(($width/$data['new_width']) > ($height/$data['new_height'])) $image->ThumbnailImage($data['new_width'], 0, false);
                            else $image->ThumbnailImage(0, $data['new_height'], false);
                            break;
                        default: //картинка вписывается в размер, оставляя белые края 

                            if(($width/$data['new_width']) > ($height/$data['new_height'])) $image->ThumbnailImage($data['new_width'], 0, false);
                            else $image->ThumbnailImage(0, $data['new_height'], false);     

                            $ratio = min($data['new_width']/$width, $data['new_height']/$height);
                            if($ratio>1) $ratio=1;
                    
                            $dx = intval($data['new_width'] - $width*$ratio) / 2;
                            $dy = intval($data['new_height'] - $height*$ratio) / 2;
                        
                            $imageOutput = new Imagick();
                            $imageOutput->newImage( $data['new_width'], $data['new_height'], 'white', $ext['extension'] );
                            if(!empty($watermark_src)) $image->compositeimage( $watermark, imagick::COMPOSITE_OVER, 0, 0 );
                            $imageOutput->compositeimage( $image, Imagick::COMPOSITE_DEFAULT, $dx, $dy );
                            if(!empty($watermark_src)) $image->compositeimage( $watermark, imagick::COMPOSITE_OVER, 0, 0 );
                            
                            $image = clone $imageOutput;
                            $imageOutput->destroy();
                            break;
                    }  
                    if( $ext['extension']=='jpg'){
                        // Set to use jpeg compression
                        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
                        // Set compression level (1 lowest quality, 100 highest quality)
                        $image->setImageCompressionQuality($data['quality']);
                    }
                    if(!empty($watermark_src)) {
                        $iWidth = $image->getImageWidth();
                        $iHeight = $image->getImageHeight();
                        $iwWidth = $watermark->getImageWidth();
                        $iwHeight = $watermark->getImageHeight();
                        if($iWidth > 200 && $iHeight>100){
                            if($iWidth > 600) $cols = 3;
                            elseif($iWidth > 440) $cols = 2;
                            if($iHeight > 330) $rows = 2;
                            else if($iHeight > 160) $rows = 1;
                            if( !empty( $cols ) && !empty( $rows ) ) {
                                $col_part = $iWidth/($cols+1);
                                $row_part = $iHeight/($rows+1);
                                for($col=1;$col<=$cols;$col++){
                                    for($row=1;$row<=$rows;$row++) {
                                        $image->compositeimage($watermark, imagick::COMPOSITE_OVER, (($col_part*$col) - $iwWidth/2), ($row_part*$row - $iwHeight/2));
                                    }
                                }
                            }
                        }
                    }
                        
                    
                    // Strip out unneeded meta data
                    $image->stripImage();  
                    $image->writeImage($data['destination']);
                    $image->destroy();
                }     
                $new_image->destroy();
            } 
        } else {  //ресайз библиотекой GD2 
            if(!empty($info['mime']) && $info['mime'] == 'image/gif')  {
                $clone_isrc = $isrc = @imagecreatefromgif($src);
            }
            elseif(!empty($info['mime']) && $info['mime'] == 'image/png') {
                $clone_isrc = $isrc = @imagecreatefrompng($src);
            }
            else  {
                $clone_isrc = $isrc = @imagecreatefromjpeg($src);'jpg';
            }
            if(!$isrc) return false;
            
            $iwfunc = "imagejpeg";
            if (!function_exists($iwfunc)) throw new Exception("Не удалось сделать ресайз по техническим причинам",2);

            foreach($datas as $k=>$data){
                // если одно из измерений пустое - пропорционально подгоняем его
                if(empty($data['new_height'])) {
                    if(empty($data['new_width'])) $data['new_width'] = $width;
                    $data['new_height'] = intval($data['new_width']*$height/$width);
                }
                if(empty($data['new_width'])) $data['new_width'] = intval($data['new_height']*$width/$height);
                
                // если размеры картинки меньше требуемых - то принимаем их за требуемые
                if($data['mode']!='cut'){
                    /*
                    if($width < $data['new_width']) {
                        $data['new_width'] = $width;
                        $data['new_height'] = intval($data['new_width']*$height/$width);
                    } 
                    if($height < $data['new_height']) {
                        $data['new_height'] = $height;
                        $data['new_width'] = intval($data['new_height']*$width/$height);
                    }
                    */
                    if($width < $data['new_width'] && $height < $data['new_height']) {
                        $data['new_width'] = $width;
                        $data['new_height'] = $height;
                    } 
                }
                
                if($data['mode']=='cut') $ratio = max($data['new_width']/$width, $data['new_height']/$height);
                else {
                    $ratio = min($data['new_width']/$width, $data['new_height']/$height);
                    if($ratio>1) $ratio=1;
                }
                
                $resized = imagecreatetruecolor($data['new_width'], $data['new_height']);
                $dw = intval($width*$ratio);
                $dh = intval($height*$ratio);
                
                if($data['mode'] == 'cut') { /* картинка уменьшается, размер $data['new_width'] и $data['new_height'], обрезка по центру */
                    $sx = intval(($dw-$data['new_width'])/2)/$ratio;
                    $sy = 0;
                    //echo '$sx:'.$sx.'; '.'$sy:'.$sy.'; '.'$dw:'.$dw.'; '.'$dh:'.$dh.'; '.'$width:'.$width.'; '.'$height:'.$height.'; -------------------------- ';
                    $res = @imagecopyresampled($resized,$isrc,0,0,$sx,$sy,$dw,$dh,$width,$height);
                } else {
                    $dx = intval(($data['new_width']-$dw)/2);
                    $dy = intval(($data['new_height']-$dh)/2);
                    if($data['mode']=='') {  /* картинка масштабируется относительно размеров и конечный размер зависит от пропорций, белых полей нет;  */
                        $resized = imagecreatetruecolor($dw, $dh);
                        $res = @imagecopyresampled($resized,$isrc,0,0,0,0,$dw,$dh,$width,$height);
                    } else { // картинка пропорционально вписывается в прямоугольник, белые поля по краям
                        $bgcolor = @imagecolorallocate($resized,255,255,255);
                        $res = @imagefilledrectangle($resized,0,0,$data['new_width'],$data['new_height'],$bgcolor);
                        $res = @imagecopyresampled($resized,$isrc,$dx,$dy,0,0,$dw,$dh,$width,$height);
                    }
                }
                
                //watermark
                if($watermark_src && empty($size_wm)) {
                    $size_wm = getimagesize(ROOT_PATH.$watermark_src);
                    $watermark_width = $size_wm[0]; 
                    $watermark_height = $size_wm[1]; 
                }
                if(!empty($watermark_width) && $watermark_width < $data['new_width']/2 && !empty($watermark_height) && $watermark_height < $data['new_height']/2){
                    $watermark_width.';'.$data['new_width'].';'.$watermark_alpha_level.':';
                    $dest_x = $dw - $watermark_width - 5;
                    $dest_y = $dh - $watermark_height - 5;
                    
                    $wm_src = imagecreatefrompng(ROOT_PATH.$watermark_src);
                    
                    imagecopy($resized, $wm_src, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height);
                }
                imagejpeg($resized, $data['destination'], $data['quality']);
                imagedestroy($resized);
            } 
            imagedestroy($isrc);    
        } 
        return true;        
    } 
    //params: image resource id, opacity in percentage (eg. 80)
    private static function filter_opacity( &$img, $opacity ){
        if( !isset( $opacity ) ) return false;
        $opacity /= 100;
        
        //get image width and height
        $w = imagesx( $img );
        $h = imagesy( $img );
        
        //turn alpha blending off
        imagealphablending( $img, false );
        
        //find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for( $x = 0; $x < $w; $x++ )
            for( $y = 0; $y < $h; $y++ )
                {
                    $alpha = ( imagecolorat( $img, $x, $y ) >> 24 ) & 0xFF;
                    if( $alpha < $minalpha )
                        { $minalpha = $alpha; }
                }
        
        //loop through image pixels and modify alpha for each
        for( $x = 0; $x < $w; $x++ )
            {
                for( $y = 0; $y < $h; $y++ )
                    {
                        //get current alpha value (represents the TANSPARENCY!)
                        $colorxy = imagecolorat( $img, $x, $y );
                        $alpha = ( $colorxy >> 24 ) & 0xFF;
                        //calculate new alpha
                        if( $minalpha !== 127 )
                            { $alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha ); }
                        else
                            { $alpha += 127 * $opacity; }
                        //get the color index with new alpha
                        $alphacolorxy = imagecolorallocatealpha( $img, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
                        //set pixel with the new color + opacity
                        if( !imagesetpixel( $img, $x, $y, $alphacolorxy ) )
                            { return false; }
                    }
            }
        return true;
    }    
    public static function getjpegsize($img_loc) {
        $handle = fopen($img_loc, "rb") or die("Invalid file stream.");
        $new_block = NULL;
        if(!feof($handle)) {
            $new_block = fread($handle, 32);
            $i = 0;
            if($new_block[$i]=="\xFF" && $new_block[$i+1]=="\xD8" && $new_block[$i+2]=="\xFF" && $new_block[$i+3]=="\xE0") {
                $i += 4;
                if($new_block[$i+2]=="\x4A" && $new_block[$i+3]=="\x46" && $new_block[$i+4]=="\x49" && $new_block[$i+5]=="\x46" && $new_block[$i+6]=="\x00") {
                    // Read block size and skip ahead to begin cycling through blocks in search of SOF marker
                    $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                    $block_size = hexdec($block_size[1]);
                    while(!feof($handle)) {
                        $i += $block_size;
                        $new_block .= fread($handle, $block_size);
                        if($new_block[$i]=="\xFF") {
                            // New block detected, check for SOF marker
                            $sof_marker = array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF");
                            if(in_array($new_block[$i+1], $sof_marker)) {
                                // SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
                                $size_data = $new_block[$i+2] . $new_block[$i+3] . $new_block[$i+4] . $new_block[$i+5] . $new_block[$i+6] . $new_block[$i+7] . $new_block[$i+8];
                                $unpacked = unpack("H*", $size_data);
                                $unpacked = $unpacked[1];
                                $height = hexdec($unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9]);
                                $width = hexdec($unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13]);
                                return array($width, $height);
                            } else {
                                // Skip block marker and read block size
                                $i += 2;
                                $block_size = unpack("H*", $new_block[$i] . $new_block[$i+1]);
                                $block_size = hexdec($block_size[1]);
                            }
                        } else {
                            return FALSE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }    
}
?>
