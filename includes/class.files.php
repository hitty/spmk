<?php
class Files {
    public static $__folder = 'files';
    
     /**
    * получение списка фотографий
    * @param string $table - основная таблица
    * @param integer $id - ID объекта в основной таблице
    * @return array of arrays
    */
    public static function getList($table, $id, $type = 'files'){
         global $db;
         $sql = "SELECT 
                `files`.`name`,
                `files`.`title`,
                `files`.`filesize`,
                `files`.`file_extension`,
                `files`.`type`,
                LEFT (`files`.`name`,2) as `subfolder`,
                `files`.`id`
           FROM " .Config::$sys_tables[$table.'_files'] . " files 
           WHERE `files`.`id_parent` = " . $id. " AND `files`.`type` = '" . $type. "'
           ORDER BY IF( `files`.`position`=0, 1, 0 ), `files`.`position` ASC, `files`.`id` ";
        $rows = $db->fetchall($sql);
        return $rows;
    }
    
    /**
    * загрузка фотографии
    * 
    * @param mixed $table - основная таблица
    * @param mixed $id - ID объекта в основной таблице
    */
    public static function Add( $table, $id, $type = '' ){
        global $db, $errors_log;
        
        // временная папка для загрузки фото
        $file_folder = !empty( Config::$values['file_folders'] ) ?  Config::$values['file_folders']  : Config::$values['file_folders'];
        $tempFolder = ROOT_PATH . '/' . $file_folder.'/'; 
        
        //если передана ссылка на скачивание
        if(!empty($_FILES ) ){
            $array_key = array_keys($_FILES);
            $array_key = $array_key[0];
        }
        
        // проверка типа файла
        $fileParts = !empty( $_FILES[$array_key]['name'] ) ? pathinfo( $_FILES[$array_key]['name'] ) : "";
        if( !empty( $fileParts['extension'] ) ){
            $targetExt = $fileParts['extension'];
            $tempFile = $_FILES[$array_key]['tmp_name'];
            $targetFile = Convert::chpuTitle(str_replace( '.' . $targetExt, '', $_FILES[$array_key]['name'])) . '.' . $targetExt;
            $folder = '/' . $file_folder . '/' . substr( $targetFile ,0 ,2 ) . '/'; 
            $filename = $folder . $targetFile; 
            //добавление номера файла при совпадении имени
            if( file_exists( ROOT_PATH . $filename ) ) {
                $targetFile = Convert::chpuTitle(str_replace( '.' . $targetExt, '', $_FILES[$array_key]['name'])) . '_' . mt_rand(1,10) . '.' . $targetExt;
                $folder = '/' . $file_folder . '/' . substr( $targetFile ,0 ,2 ) . '/'; 
                $filename = $folder . $targetFile; 
            }
            self::makeDir( ROOT_PATH . $filename );         
            // загрузка файла в папки
            move_uploaded_file($tempFile, ROOT_PATH . $filename);
            //поиск родительской записи
            $parent = $db->fetch(" SELECT * FROM " . Config::$sys_tables[$table] . " WHERE id = ?", $id );
            $parent_field = !empty( $parent ) ? 'id_parent' : 'id_parent_common' ;
            //запись данных
            $data = array(
                'name' => $targetFile,
                'subfolder' => substr( $targetFile, 0, 2),
                'filesize' => Convert::filesizeInBytes( filesize( ROOT_PATH . $filename ) ),
                'file_extension' => $targetExt,
                'type' => $type,
                $parent_field => $id,
                'title' => str_replace( '.' . $targetExt, '', $_FILES[$array_key]['name']),
            );
            $res = $db->insertFromArray( Config::$sys_tables[$table.'_files'], $data );
            
            if( !empty( Config::$sys_tables[$table.'_files'] ) && $res ) {
                $id_file = $db->insert_id;
                $data['id'] = $id_file;
                return $data;
            } else return array(
                    'file_name' => $filename
                   );
        }  else return false;
        
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
         return $db->query("UPDATE " .Config::$sys_tables[$table.'_files'] . " SET title = ? WHERE id = ?", $title, $id); 
    }    
     /**
    * перенос временных полей
    * @param string $table - основная таблица
    * @param integer $id - ID объекта в основной таблице
    * @param integer $id_common - времемнный ID объекта
    */
    public static function updateCommonFields($table, $id, $id_common){
         global $db;
         $db->query("UPDATE " .Config::$sys_tables[$table.'_files'] . " SET id_parent = ?, id_parent_common = 0 WHERE id_parent_common = ?", $id, $id_common); 
    }    
        
    /**
    * удаление фотографии из базы и из папки на сервере
    * @param string $table - основная таблица
    * @param integer $id_file - ID фотографии в таблице с фотками
    */
    public static function Delete($table, $id_file){
        global $db, $file_folder;
        
        if(empty($file_folder ) ) $file_folder = !empty( Config::$values['file_folders'] ) ?  Config::$values['file_folders']  : Config::$values['file_folders'];
        
        //определяем имя файла (для удаления всех фото с таким именем)
        $file_name = $db->fetch("SELECT LEFT(`name`,2) as `subfolder`, `name` FROM " .Config::$sys_tables[$table.'_files'] . " WHERE `id` = ?",$id_file); 
        if(empty($file_name ) ) return false;        
        //удаление фото с сервера
        $filename = ROOT_PATH.'/' . $file_folder. "/" . $file_name['subfolder'] . "/" . $file_name['name'];
        if( file_exists( $filename ) ) unlink( $filename );
        
        $del = $db->query("DELETE FROM " .Config::$sys_tables[$table.'_files'] . " WHERE `id` = ?", $id_file);
        return !empty($del);
    }
    /**
    * удаление всех фотографий из базы и из папки на сервере
    * @param string $table - основная таблица
    * @param integer $id_parent - ID предка
    */
    public static function DeleteAll( $table, $id_parent = false, $id_parent_common = false ){
        global $db, $file_folder;
        if(  empty( $id_parent ) && !empty( $id_parent_common ) ){
             $prefix = '_common';
             $id_parent = $id_parent_common;
        } else $prefix = '';

        $sql = "SELECT *, LEFT(`name`,2) as `subfolder` FROM " .Config::$sys_tables[$table.'_files'] . " WHERE `id_parent" . $prefix . "` IN (" . $id_parent. ")";
        $rows = $db->fetchall($sql);
        if(empty($rows ) ) return false;
        $cnt=0;
        $unlink_flag = true;
        foreach($rows as $key=>$value){
            foreach(self::$__folder_options as $_folder=>$_options)    {
                if (file_exists(ROOT_PATH.'/' . $file_folder. "/" . $_folder. "/" . $value['subfolder'] . "/" . $value['name'] ) )
                    if(!unlink(ROOT_PATH.'/' . $file_folder. "/" . $_folder. "/" . $value['subfolder'] . "/" . $value['name'] ) ) { $unlink_flag = false; break; }
            }
            $del = $db->query("DELETE FROM " .Config::$sys_tables[$table.'_files'] . " WHERE `id_parent" . $prefix . "` = ?",$id_parent);
            
        }
        return !empty($del) && !empty($unlink_flag);
    }
    /**
    * Check for folder is exists and create it recursively if it need
    * @param string $path path to the file
    * @return boolean
    */
    private static function makeDir($path){
        if(empty($path ) ) return false;
        $dir = dirname($path);
        if(is_dir($dir ) ) return true;
        $result = true;
        if(!mkdir($dir, 0777, true ) ) return false;
        else chmod($dir, 0777);
        return true;
    }

    
}
?>
