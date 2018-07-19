<?php
#Connecting to Wordpress
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( 'C:\wamp\www\wordpress\wp-blog-header.php' );

$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$metadataRepo = \Tainacan\Repositories\Metadata::get_instance();
$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMedia = \Tainacan\Media::get_instance();
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

#DEFINIR O VALOR DO CAMPO COM O THUMBNAIL
$metadataImages = $metadataRepo->fetch(['name'=>'Imgs'], 'OBJECT');
$metadataImages = $metadataImages[0];


$meta_query_exists = [
	[
		'key' => $metadataImages->get_id(),
		'value' => '',
		'compare' => 'NOT IN'
	]
];

$meta_query_not_exists = [
	[
		'key' => $metadataImages->get_id(),
		'value' => '',
		'compare' => '='
	]
];

$items_exists = $itemsRepo->fetch(['meta_query' => $meta_query_exists, 'posts_per_page' => -1], $metadataImages->get_collection(), 'OBJECT');

$items_not_exists = $itemsRepo->fetch(['meta_query' => $meta_query_not_exists, 'posts_per_page' => -1], $metadataImages->get_collection(), 'OBJECT');

foreach ($items_exists as $item) {
	
	$metaDocument = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadataImages);
	
	$metaDocument->set_value("Sim");

	if ($metaDocument->validate()){
		$insertedMetadata = $itemMetadataRepo->insert($metaDocument);
	}else{
		echo var_dump($metaDocument->get_errors());
	}
}

foreach ($items_not_exists as $item) {
	
	$metaDocument = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadataImages);
	
	echo $metaDocument->get_value();

	$metaDocument->set_value("Não");

	if ($metaDocument->validate()){
		$insertedMetadata = $itemMetadataRepo->insert($metaDocument);
	}else{
		echo var_dump($metaDocument->get_errors());
	}
}
?>
