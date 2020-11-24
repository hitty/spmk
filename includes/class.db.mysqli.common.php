<?php

class CommonDb{
    private static $tables = array();   
    
    public static function Init() {
        self::$tables = Config::$sys_tables;    
    }
    public static function getItem( $table, $where = false, $groupby = false )  {
        global $db;   
        
        $item_info = !empty( self::$tables[$table] ) ? $db->prepareNewRecord( self::$tables[$table] ) : false ;
        $category_record_info = !empty( self::$tables[$table . '_categories'] ) ? $db->prepareNewRecord( self::$tables[$table . '_categories'] ) : false ;
        
        $item = $db->fetch("
            SELECT 
                " .  self::$tables[$table] . ".*
                " . ( array_key_exists( 'description', $item_info ) ? ", IF(" . self::$tables[$table] .".description = '', LEFT(". self::$tables[$table] .".content,200), ". self::$tables[$table] .".description) as `description` " : "" ) . "
                " . ( array_key_exists( 'datetime', $item_info ) ? ", DATE_FORMAT( " . self::$tables[$table] .".`datetime`,'%d.%m.%Y %H:%i' ) as `normal_datetime` " : "" ) . "
                " . ( array_key_exists( 'date', $item_info ) ? ", IF(  YEAR(". self::$tables[$table] .".`date`) = Year(CURDATE()), DATE_FORMAT(". self::$tables[$table] .".`date`,'%e %M'),DATE_FORMAT(". self::$tables[$table] .".`date`,'%d.%m.%Y') ) as `normal_date` " : "" ) . "
                " . ( array_key_exists( 'date_start', $item_info ) ? ", IF(  YEAR(". self::$tables[$table] .".`date_start`) = Year(CURDATE()), DATE_FORMAT(". self::$tables[$table] .".`date_start`,'%e %M'),DATE_FORMAT(". self::$tables[$table] .".`date_start`,'%d.%m.%Y') ) as `normal_date_start` " : "" ) . "
                " . ( array_key_exists( 'date_end', $item_info ) ? ", IF(  YEAR(". self::$tables[$table] .".`date_end`) = Year(CURDATE()), DATE_FORMAT(". self::$tables[$table] .".`date_end`,'%e %M'),DATE_FORMAT(". self::$tables[$table] .".`date_end`,'%d.%m.%Y') ) as `normal_date_end` " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title',$category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title as category_title " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_short', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_short as category_title_short " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_prepositional', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_prepositional as category_title_prepositional " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_genitive', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_genitive as category_title_genitive " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_genitive_plural', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_genitive_plural as category_title_genitive_plural " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'chpu_title' ,$category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".chpu_title as category_chpu_title " : "" ) . "
                " . ( !empty( self::$tables[$table . '_photos']) ? ", " . self::$tables[$table . '_photos'] . ".name as photo " : "" ) . "
                " . ( !empty( self::$tables[$table . '_photos']) ? ", LEFT (" . self::$tables[$table . '_photos'] . ".`name`,2) as subfolder " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories_photos']) ? ", " . self::$tables[$table . '_categories_photos'] . ".name as category_photo " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories_photos']) ? ", LEFT (" . self::$tables[$table . '_categories_photos'] . ".`name`,2) as category_subfolder " : "" ) . "
            FROM " .  self::$tables[$table] . "
                " . ( !empty( self::$tables[$table . '_categories']) ? 
                    " LEFT JOIN " . self::$tables[$table . '_categories'] . " ON " . self::$tables[$table . '_categories'] . ".id = " . self::$tables[ $table ] . ".id_category "
                    : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories_photos']) ? 
                    " LEFT JOIN " . self::$tables[$table . '_categories_photos'] . " ON " . self::$tables[$table . '_categories_photos'] . ".id = " . self::$tables[ $table . '_categories' ] . ".id_main_photo "
                    : "" ) . "
                " . ( !empty( self::$tables[$table . '_photos']) ? 
                    " LEFT JOIN " . self::$tables[$table . '_photos'] . " ON " . self::$tables[$table . '_photos'] . ".id = " . self::$tables[ $table ] . ".id_main_photo "
                    : "" ) . "
            " . ( !empty( $where ) ? " WHERE " . $where : "" ) . "
            " . ( !empty( $groupby ) ? " GROUP BY  " . $groupby : " GROUP BY " .  self::$tables[$table] . ".id" )
        ); 
        return $item;
    }

    public static function getList( $table, $from = 0, $where = false, $order = false, $groupby = false ) {
        global $db;    
        
        
        $item_info = !empty( self::$tables[$table] ) ? $db->prepareNewRecord( self::$tables[$table] ) : false ;
        $category_record_info = !empty( self::$tables[$table . '_categories'] ) ? $db->prepareNewRecord( self::$tables[$table . '_categories'] ) : false ;
        
        $list = $db->fetchall("
            SELECT 
                " .  self::$tables[$table] . ".*
                " . ( array_key_exists( 'description', $item_info ) ? ", IF(" . self::$tables[$table] .".description = '', LEFT(". self::$tables[$table] .".content,200), ". self::$tables[$table] .".description) as `description` " : "" ) . "
                " . ( array_key_exists( 'datetime', $item_info ) ? ", DATE_FORMAT( " . self::$tables[$table] .".`datetime`,'%d.%m.%Y %H:%i' ) as `normal_datetime` " : "" ) . "
                " . ( array_key_exists( 'date', $item_info ) ? ", IF(  YEAR(". self::$tables[$table] .".`date`) = Year(CURDATE()), DATE_FORMAT(". self::$tables[$table] .".`date`,'%e %M'),DATE_FORMAT(". self::$tables[$table] .".`date`,'%d.%m.%Y') ) as `normal_date` " : "" ) . "
                " . ( array_key_exists( 'date_start', $item_info ) ? ", IF(  YEAR(". self::$tables[$table] .".`date_start`) = Year(CURDATE()), DATE_FORMAT(". self::$tables[$table] .".`date_start`,'%e %M'),DATE_FORMAT(". self::$tables[$table] .".`date_start`,'%d.%m.%Y') ) as `normal_date_start` " : "" ) . "
                " . ( array_key_exists( 'date_end', $item_info ) ? ", IF(  YEAR(". self::$tables[$table] .".`date_end`) = Year(CURDATE()), DATE_FORMAT(". self::$tables[$table] .".`date_end`,'%e %M'),DATE_FORMAT(". self::$tables[$table] .".`date_end`,'%d.%m.%Y') ) as `normal_date_end` " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title',$category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title as category_title " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_short', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_short as category_title_short " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_prepositional', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_prepositional as category_title_prepositional " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_genitive', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_genitive as category_title_genitive " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'title_genitive_plural', $category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".title_genitive_plural as category_title_genitive_plural " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories'] ) && array_key_exists( 'chpu_title' ,$category_record_info ) ? ", " . self::$tables[$table . '_categories'] . ".chpu_title as category_chpu_title " : "" ) . "
                " . ( !empty( self::$tables[$table . '_photos']) ? ", " . self::$tables[$table . '_photos'] . ".name as photo " : "" ) . "
                " . ( !empty( self::$tables[$table . '_photos']) ? ", LEFT (" . self::$tables[$table . '_photos'] . ".`name`,2) as subfolder " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories_photos']) ? ", " . self::$tables[$table . '_categories_photos'] . ".name as category_photo " : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories_photos']) ? ", LEFT (" . self::$tables[$table . '_categories_photos'] . ".`name`,2) as category_subfolder " : "" ) . "
            FROM " .  self::$tables[$table] . "
                " . ( !empty( self::$tables[$table . '_categories']) ? 
                    " LEFT JOIN " . self::$tables[$table . '_categories'] . " ON " . self::$tables[$table . '_categories'] . ".id = " . self::$tables[ $table ] . ".id_category "
                    : "" ) . "
                " . ( !empty( self::$tables[$table . '_categories_photos']) ? 
                    " LEFT JOIN " . self::$tables[$table . '_categories_photos'] . " ON " . self::$tables[$table . '_categories_photos'] . ".id = " . self::$tables[ $table . '_categories' ] . ".id_main_photo "
                    : "" ) . "
                " . ( !empty( self::$tables[$table . '_photos']) ? 
                    " LEFT JOIN " . self::$tables[$table . '_photos'] . " ON " . self::$tables[$table . '_photos'] . ".id = " . self::$tables[ $table ] . ".id_main_photo "
                    : "" ) . "
            " . ( !empty( $where ) ? " WHERE " . $where : "" ) . "
            " . ( !empty( $groupby ) ? " GROUP BY  " . $groupby : " GROUP BY " .  self::$tables[$table] . ".id" ) . "
            " . ( !empty( $order ) ? " ORDER BY " . $order : "" ) . "
            " . ( !empty( $from ) ? " LIMIT " . $from : "" )
        );
        return $list;        
    }
    
    
}
?>