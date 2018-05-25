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

#$item = $itemRepo->fetch(, 'OBJECT');

$fieldDocumento = $fieldsRepo->fetch(['name'=>'v070'], 'OBJECT');
$fieldDocumento = $fieldDocumento[0];

$fieldAttch = $fieldsRepo->fetch(['name'=>'v084'], 'OBJECT');
$fieldAttch = $fieldAttch[0];

$meta_query = [
	[
		'key' => $fieldDocumento->get_id(),
		'value' => '',
		'compare' => 'NOT IN'
	]
];

$items = $itemsRepo->fetch(['meta_query' => $meta_query, 'posts_per_page' => -1], $fieldAttch->get_collection(), 'OBJECT');

//var_dump(sizeof($items)); die;


foreach ($items as $item) {
	$metaDocument = new \Tainacan\Entities\Item_Metadata_Entity($item, $fieldDocumento);
	//var_dump($metaDocument->get_value());
	$idMedia = $itemMedia->insert_attachment_from_url($metaDocument->get_value());

	if (false != $idMedia){
		$item->set_document($idMedia);
		$item->set_document_type('attachment');
		$item->set__thumbnail_id($idMedia);
		if ($item->validate()){
			$itemsRepo->insert($item);
		} else{
			echo 'Item não validado: ', $item->get_title();
		}

	} else {
		echo 'Erro ao adicionar a media ', $metaDocument->get_value(), "\n\n";
	}

}


?>
