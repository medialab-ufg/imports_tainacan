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
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();


$fieldDocumento = $fieldsRepo->fetch(['name'=>'Possui Fotografia'], 'OBJECT');
$fieldDocumento = $fieldDocumento[0];

$items = $itemsRepo->fetch(['status' => 'publish', 'posts_per_page' => -1], $fieldDocumento->get_collection(), 'OBJECT');

$conta = 1;
$items_size = sizeof($items);
#$fd = fopen('img_nfound.png', 'r');
$idMedia = $itemMedia->insert_attachment_from_url("https://lh3.googleusercontent.com/-2TvMNC7DQrs/Wxp4xmDX5FI/AAAAAAAALFo/_0tLQBNxqa46e1ehRXcKp6v91EvPqSO6gCL0BGAYYCw/h537/2018-06-08.png");

foreach ($items as $item){
	$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $fieldDocumento);
	
	$thumbId = $item->get__thumbnail_id();
	if ($thumbId == 0){
		
		echo "Atualizando a Thumbnail do Item {$item->get_title()} \n\n";
		
		$itemMetadata->set_value(['Não']);
		
		if($itemMetadata->validate()){
			$itemMetadataRepo->update($itemMetadata);		
			
		}else{
			echo var_dump($itemMetadata->get_errors());		
		}

		
		if (false != $idMedia){
			

			$item->set_document($idMedia);
			$item->set_document_type('attachment');
			$item->set__thumbnail_id($idMedia);
			if($item->validate()){
				$itemsRepo->update($item);
				echo "Success! Thumbnail do Item --{$item->get_title()}-- Atualizada! \n\n";
				echo $items_size-$conta, " Remain \n\n";
			}
			else{
				echo var_dump($item->get_errors());
			}
		} else {
			echo 'Erro ao adicionar a media ', $idMedia->get_id(), "\n\n";
		}
	
	}
	$conta+=1;
}

?>
