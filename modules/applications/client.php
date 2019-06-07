<?php
$parameters = Request::GetParameters( METHOD_POST );
$get_parameters = Request::GetParameters( METHOD_GET );
Response::SetArray( 'parameters', $parameters );
Response::SetArray( 'get_parameters', $get_parameters );
$GLOBALS['css_set'][] = '/modules/applications/css/style.css';
// определяем запрошенный экшн
$action = empty($this_page->page_parameters[1]) ? "" : $this_page->page_parameters[1];
$module_template = '/modules/applications/templates/form.html';
switch( true ){
    ////////////////////////////////////////////////////////////////////
    ///отправка почты
    ////////////////////////////////////////////////////////////////////
    case !empty( $parameters['submit'] ) :
        if( !class_exists('Sendpulse') ) require_once("includes/class.sendpulse.php");
        
        Response::SetArray( 'data', $parameters );
        $db->insertFromArray( 'applications', $parameters );
        $emails = !empty( $this_page->settings['admin_emails'] ) ? explode( ';', $this_page->settings['admin_emails'] ) : false;
        if( !empty( $emails ) ) {
            foreach( $emails as $email ){
                if( Validate::isEmail( $email ) ) {
                    // инициализация шаблонизатора
                    $eml_tpl = new Template( '/modules/applications/templates/send.email.html' );
                    $html = $eml_tpl->Processing();
                    //отправка письма
                    $sendpulse = new Sendpulse( 'subscriberes' );
                    $result = $sendpulse->sendMail( 'Получена заявка с сайта Архитектурной премии Москвы  ' . Host::$host, $html, '', $email );
                }
            }
        }
        Response::SetBoolean( 'submit', true );
        break;
    default:
        
        break;
}

?>
