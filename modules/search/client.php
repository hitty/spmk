<?php
require_once('vendor/autoload.php');
$action = !empty( $this_page->page_parameters[0] ) ? $this_page->page_parameters[0] : false;
switch( true ){
    case empty( $action ):
        $query = trim( Request::GetString( 'query', METHOD_GET ) );
        Response::SetString( 'query', $query );
        $submit = Request::GetString( 'submit', METHOD_GET );
        Response::SetString( 'submit', $submit );
        
        if( !empty( $query ) ) {
            
            $search_params = [
                'index' => 'awards',
                'body' => [
                    "query"=> [
                        "query_string"=> [
                            "query" => $db->real_escape_string( $query ) . "*"
                        ]
                    ],
                    "highlight" => [
                        "order" => "score",
                        "fields" => [
                            "content" => ["force_source" => true ],
                            "title" => ["force_source" => true ]
                        ]
                    ]
                ]
            ];
            
            $search_tables = [ 'news', 'nominees' ];
            $list = array();
            //Search index
            $client = Elasticsearch\ClientBuilder::create()->build();
            $params = [ 'index' => Config::Get('elasticsearch/index') ];
            $index = $client->indices()->getSettings( $params );

            foreach( $search_tables as $search_table ) {
                $search_params['type'] = $search_table;
                $results = $client->search($search_params);
                
                foreach( $results['hits']['hits'] as $result ){
                    $title =  !empty( $result['highlight']['title'][0] ) ? $result['highlight']['title'][0] : ( !empty( $result['highlight']['title'] ) ? $result['highlight']['title'][0] : $result['_source']['title'] ) ;    
                    $content = !empty( $result['highlight']['content'][0] ) ? $result['highlight']['content'][0] : ( !empty( $result['highlight']['content'] ) ? $result['highlight']['content'][0] : $result['_source']['content'] ) ;    
                    $list[ $search_table ][] = [
                        'title' => $title,
                        'content' => strip_tags( $content ),
                        'photo' => !empty( $result['_source']['photo'] ) ? $result['_source']['photo'] : '',
                        'date' => !empty( $result['_source']['normal_date'] ) ? $result['_source']['normal_date'] : '',
                        'chpu_title' => !empty( $result['_source']['chpu_title'] ) ? $result['_source']['chpu_title'] : ''
                    ];
                }
            }
            Response::SetArray( 'list', $list );
        }
        $module_template = '/modules/search/templates/results.html';
        break;
    default:
        Host::RedirectLevelUp();
        break;
}

?>