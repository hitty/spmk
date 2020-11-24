<?php
class Production {
    
    protected $sys_tables = array();
    public function __construct( ){
        $this->sys_tables = Config::Get('sys_tables');    
    }
    
    /**
    * получение списков категорий и регионов
    * @param string $table - таблица
    * @param string $order - набор полей сортировки
    * @param string $where - дополнительные условия
    * @return array
    */
    public function getSimpleList( $table, $order, $where='', $from = '' ){
        global $db;
        $sql = "
            SELECT 
                *

            FROM " . $table;
        if(!empty($where)) $sql .=" WHERE ".$where;
        $sql .= " ORDER BY ".$order;
        if ( !empty( $from ) ) $sql .= " LIMIT " . $from;
        $list = $db->fetchall($sql);
        return $list;
    }   
    
    /**
    * получение списков страниц
    * @param string $table - таблица
    * @param string $order - набор полей сортировки
    * @param string $where - дополнительные условия
    * @return array
    */
    public function getList( $order = false, $where = false, $from = false ){
        global $db;
        $list = $db->fetchall(
        "
            SELECT 
                " . $this->sys_tables['production'] . ".*,
                " . $this->sys_tables['production_photos'] . ".name as photo_name,
                LEFT(" . $this->sys_tables['production_photos'] . ".name,2) as subfolder
            FROM 
                " . $this->sys_tables['production'] . "
            LEFT JOIN " . $this->sys_tables['production_photos'] . " ON " . $this->sys_tables['production_photos'] . ".id = " . $this->sys_tables['production'] . ".id_main_photo
             
                " . ( !empty( $where ) ? " WHERE " . $where : "" ) . "
            GROUP BY " . $this->sys_tables['production'] . ".id
            " . ( !empty( $order_by ) ? " ORDER BY " . $order_by : "" ) . 
            ( !empty( $from ) ? " LIMIT " . $from : "" )
            ,false
        );
      
        return $list;
    }    
    
    /**
    * получение курса
    * @param string $table - таблица
    * @param string $order - набор полей сортировки
    * @param string $where - дополнительные условия
    * @return array
    */
    public function getItem( $id = false, $added_where = false ){
        global $db;
        $where = array();
        if( !empty( $id ) ) $where[] = $this->sys_tables['production'] . ".id = " . $id; 
        if( !empty( $added_where ) ) $where[] = $added_where; 
        $where = implode( " AND ", $where );

        $list = $db->fetch(
        "
            SELECT 
                " . $this->sys_tables['production'] . ".*,
                " . $this->sys_tables['production_photos'] . ".name as photo_name,
                LEFT(" . $this->sys_tables['production_photos'] . ".name,2) as subfolder
            FROM 
                " . $this->sys_tables['production'] . "
            RIGHT JOIN " . $this->sys_tables['production_photos'] . " ON " . $this->sys_tables['production_photos'] . ".id = " . $this->sys_tables['production'] . ".id_main_photo
            WHERE " . $where . "
            GROUP BY " . $this->sys_tables['production'] . ".id
            " . ( !empty( $from ) ? " LIMIT " . $from : "" )
            
        );
      
        return $list;
    }    
    
}


?>