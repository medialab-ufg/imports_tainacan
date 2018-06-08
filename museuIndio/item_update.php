<?php

#Connecting to Wordpress
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/wp-blog-header.php' );

$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$fieldsRepo = \Tainacan\Repositories\Fields::get_instance();
$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMedia = \Tainacan\Media::get_instance();

$fieldDocumento = $fieldsRepo->fetch(['name'=>'V018 – Nome do Objeto'], 'OBJECT');
$fieldDocumento = $fieldDocumento[0];

$items = $itemsRepo->fetch(['title' => 'Sugerir Excluir', 'posts_per_page' => -1], $fieldDocumento->get_collection(), 'OBJECT');
echo var_dump($items); die;

foreach($items as $item){
	$item->set_status('Draft');
	$itemsRepo->update($item)
}
?>
