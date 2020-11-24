<?php
/**    
* Переопределение обработки ошибок
*/

if(!defined('NEW_LINE_SEPARATOR')) define('NEW_LINE_SEPARATOR',"\n");
if(!defined('SITE_ERROR_LOG_FILE')) define('SITE_ERROR_LOG_FILE', ROOT_PATH . "/errors.log");
if(!defined('SITE_ERROR_LOG_BACKUP')) define('SITE_ERROR_LOG_BACKUP', ROOT_PATH . "/errors.log.bkp");
if(!defined('ERROR_LOG_SIZE')) define('ERROR_LOG_SIZE', 1048576);

function newErrorHandler($type, $message, $file, $line, $vars){
    static $titles = array(
        E_WARNING           => 'Предупреждение',
        E_NOTICE            => 'Уведомление',
        E_USER_ERROR        => 'Пользовательская ошибка',
        E_USER_WARNING      => 'Пользовательское предупреждение',
        E_USER_NOTICE       => 'Пользовательское уведомление',
        //E_USER_DEPRECATED   => 'Пользовательсккое уведомление об использовании устаревшей конструкции',
        E_STRICT            => 'Проблема совместимости в коде',
        E_RECOVERABLE_ERROR => 'Поправимая ошибка',
        E_CORE_ERROR        => 'Фатальная ошибка при запуске PHP',
        E_CORE_WARNING      => 'Предупреждение при запуске PHP',
        E_COMPILE_ERROR     => 'Ошибка при компиляции',
        E_COMPILE_WARNING   => 'Предупреждение при компиляции',
        E_PARSE             => 'Синтаксическая ошибка',
        E_ERROR             => 'Фатальная ошибка',
        E_STRICT            => 'Cоветы помогающие сделать ваш код более совместимым с будущими версиями PHP',
        //E_DEPRECATED        => 'Уведомление об использовании устаревших конструкций, несовместимых с будущими версиями PHP'
    );

    $str = "*=============================*".NEW_LINE_SEPARATOR;
    $str .= "|     ".date("d.m.Y H:i:s")."     |".NEW_LINE_SEPARATOR;
    $str .= "*=============================*".NEW_LINE_SEPARATOR;
    $str .= $titles[$type].NEW_LINE_SEPARATOR;
    $str .= "MESSAGE: ".$message.NEW_LINE_SEPARATOR;
    $str .= "FILE: ".$file.NEW_LINE_SEPARATOR;
    $str .= "LINE: ".$line.NEW_LINE_SEPARATOR;
    $str .= "VARS TRACE:".NEW_LINE_SEPARATOR;
    unset($vars['GLOBALS']);
    unset($vars['_SERVER']);
    unset($vars['_ENV']);
    $str .= var_export($vars, true).NEW_LINE_SEPARATOR;
    $backtrace = debug_backtrace();
    array_shift($backtrace); // удалим вызов самого обработчика
    $str .= "CALL STACK: ".NEW_LINE_SEPARATOR;
    foreach ($backtrace as $call) {
        $str .=  "    ";
        if(!empty($call['file']))
            $str .= basename($call['file']).", line ".$call['line'].':';
        if (!empty($call['object']) && method_exists($call['object'], '__toString'))
            $str .= $call['object'];
        if (!empty($call['type'])) {
            if ($call['type'] == '->') $str .= $call['class'].'->';
            elseif ($call['type'] == '::') $str .= $call['class'].'::';
        }
        $str .= $call['function'].'(';
        $args = array();
        foreach ($call['args'] as $arg) {
            if(is_null($arg)) $args[] = 'null';
            elseif(is_bool($arg)) $args[] = ($arg) ? 'true' : 'false';
            elseif(is_string($arg)) $args[] = '"'.$arg.'"';
            elseif(is_integer($arg) || is_float($arg)) $args[] = $arg;
            elseif(is_array($arg)) $args[] = 'array('.sizeof($arg).')';
            elseif(is_object($arg)) $args[] = 'object('.get_class($arg).')';
            elseif(is_resource($arg)) $args[] = 'resource('.get_resource_type($arg).')';
        }
        $strArgs = implode(', ',$args);
        $str .= $strArgs.')'.NEW_LINE_SEPARATOR;
    }
    $str .= NEW_LINE_SEPARATOR.NEW_LINE_SEPARATOR.NEW_LINE_SEPARATOR;
    if(filesize(SITE_ERROR_LOG_FILE)>ERROR_LOG_SIZE){
        if(file_exists(SITE_ERROR_LOG_BACKUP)) unlink(SITE_ERROR_LOG_BACKUP);
        rename(SITE_ERROR_LOG_FILE, SITE_ERROR_LOG_BACKUP);
    }
    file_put_contents(SITE_ERROR_LOG_FILE, $str, FILE_APPEND | LOCK_EX);
    if(DEBUG_MODE) return false;
    else return true;
}

function newFatalCatcher(){
    $error = error_get_last();
    if ($error['type'] == E_ERROR ||
        $error['type'] == E_CORE_ERROR ||
        $error['type'] == E_COMPILE_ERROR ||
        $error['type'] == E_USER_ERROR) {
        newErrorHandler($error['type'], $error['message'], $error['file'], $error['line'],$error['vars']);
    }
}

?>