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

#DEFINIR O VALOR DO CAMPO COM O THUMBNAIL
$metadataDocumento = $metadataRepo->fetch(['name'=>'V070 - Imagem do objeto'], 'OBJECT');
$metadataDocumento = $metadataDocumento[0];

#DEFINIR O VALOR DO CAMPO COM A URL DAS IMAGENS DE ANEXO
$metadataAttch = $metadataRepo->fetch(['name'=>'V084 - Outras Imagens'], 'OBJECT');
$metadataAttch = $metadataAttch[0];

$meta_query = [
	[
		'key' => $metadataDocumento->get_id(),
		'value' => '',
		'compare' => 'NOT IN'
	]
];

$items = $itemsRepo->fetch(['meta_query' => $meta_query, 'posts_per_page' => -1], $metadataAttch->get_collection(), 'OBJECT');



function mindio_extract_img_urls_from_url($url) {
	
	$encodedUrl = urlencode($url);
	$urlEncoded = str_replace(['%2F', '%3A'], ['/', ':'], $encodedUrl);
	$page_content = file_get_contents($urlEncoded);
	$base_url = substr($urlEncoded, 0, strrpos($urlEncoded, '/') + 1);
	$urls = [];
	
	if ($page_content) {
	
			preg_match_all('/href=[\"\']([^\"\']+\.html)/', $page_content, $matches);
			if ($matches[1]){
				foreach($matches[1] as $end){
					$html_content = file_get_contents($base_url . $end);
					preg_match_all('/src=[\"\']([^\"\']+\.jpg)/', $html_content, $matches);
					$urls[] = $base_url . $matches[1][0];
					}
			}else {
				preg_match_all('/src=[\"\']([^\"\']+\.jpg)/', $page_content, $matches);
				if (is_array($matches[1])) {
					foreach ($matches[1] as $crush) {
						$urls[] = $base_url . $crush;
					}
				}
			}
		}	
	return $urls;
}
$conta = 1;
$items_size = sizeof($items);

foreach ($items as $item) {
	$metaDocument = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadataDocumento);

	echo "Processando item {$item->get_title()}\n\n";

	$idMedia = $itemMedia->insert_attachment_from_url($metaDocument->get_value());
	echo "Adicionando documento: {$metaDocument->get_value()} \n";
	

	if (false != $idMedia){
		$item->set_document($idMedia);
		$item->set_document_type('attachment');
		$item->set__thumbnail_id($idMedia);
		
		if ($item->validate()){
			$itemsRepo->insert($item);
			echo "Salvando item com documento setado\n";
		} else{
			echo 'Item não validado: ', $item->get_title();
		} 
		
	}else {
		echo 'Erro ao adicionar a media ', $metaDocument->get_value(), "\n\n";
	}

	$metaAttach = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadataAttch);
	
	echo "Pegando urls das imagens a partir do link html... ";
	$images = mindio_extract_img_urls_from_url($metaAttach->get_value());
	echo sizeof($images), " imagens encontradas.\n";

	foreach ($images as $image) {
		$itemMedia->insert_attachment_from_url($image, $item->get_id());
		echo "Adicionando anexo: $image \n";
	}

	echo "Remain ", $items_size-$conta;
	echo "\n\n";
	$conta+=1;
}

?>
