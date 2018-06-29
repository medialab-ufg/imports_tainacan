<?php
#Connecting with Wordpress:
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );

#ALTERAR O PATH ONDE O wp-blog-header.php SE ENCONTRA
require( '/var/www/html/wp-blog-header.php' );

#Delete Existing Collections:
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();

#O BLOCO ASSEGUIR DELETA COLEÇÕES JÁ EXISTENTES - PARA ATIVÁ-LO RETIRE OS COMENTÁRIOS
/*
$collections = $collectionsRepo->fetch([], 'OBJECT');
foreach ($collections as $col) {
	echo "Deleting collection ", $col->get_id(), "\n";
	$collectionsRepo->delete([$col->get_id(), true]);
}
*/

#Create Collection and Metadata:
$metadataRepo = \Tainacan\Repositories\Metadata::get_instance();
$taxonomyRepo = \Tainacan\Repositories\Taxonomies::get_instance();

#INSERIR INFORMAÇÕES SOBRE A COLEÇÃO
$collection = new \Tainacan\Entities\Collection();
$collection->set_name('Museu do Índio');
$collection->set_status('publish');
$collection->set_description('Coleção com informações sobre os itens museológicos do Museu do Índio.');

$cont = 0;
if ($collection->validate()) {
	$insertedCollection = $collectionsRepo->insert($collection);

	# MODIFICAR O NOME DO ARQUIVO E SE HOUVER, O CAMINHO PARA O ARQUIVO CSV
	if (($handle = fopen("metadadosMIndio(teste).csv", "r")) == TRUE) {
		
		$collection_core_metadata = $metdataRepo->get_core_metadata($insertedCollection);
		
		while (($data = fgetcsv($handle, 0, ",")) == TRUE){
			if ($cont == 0){
				echo "Pulando linha das colunas", "\n";
			} else{
				if (trim($data[3])){
					foreach ($collection_core_metadata as $coremetadata) {
						if($coremetadata->get_name() == $data[3]){
							$coremetadata->set_name($data[0]);
							$coremetadata->set_description($data[1]);
							echo "Atualizando Core Metadata: ", $data[0], "\n";
						
							if ($coremetadata->validate()){
								$insertedMetadata = $metadataRepo->insert($coremetadata);
							} else {
								$erro = $coremetadata->get_errors();
								var_dump($erro);
							}
						}
					}
				} else {
					if (trim($data[2]) == 'Tainacan\Metadata_Types\Taxonomy'){
						
						$taxonomy = new \Tainacan\Entities\Taxonomy;
						$taxonomy->set_name(trim($data[0]));
						$taxonomy->set_description("Taxonomia para o metadado ". trim($data[0]));
						$taxonomy->set_allow_insert('yes');
						$taxonomy->set_status('publish');
						
						if ($taxonomy->validate()) {
							$insertedTaxonomy = $taxonomyRepo->insert($taxonomy);
							echo 'Taxonomy created with ID -  ' . $taxonomy->get_id(), "\n";
							
						} else {
								$error = $taxonomy->get_errors();
								var_dump($error);
						}
						
						$metadata_type_options = ['taxonomy_id' => $insertedTaxonomy->get_id(), 'input_type' => 'tainacan-category-tag-input', 'allow_new_terms' => 'yes'];
						
						$metadado = new \Tainacan\Entities\Metadatum();
						$metadado->set_collection($insertedCollection);
						$metadado->set_name(trim($data[0]));
						$metadado->set_description($data[1]);
						$metadado->set_metadata_type(trim($data[2]));
						$metadado->set_multiple('yes');
						$metadado->set_status('publish');
						$metadado->set_metadata_type_options($metadata_type_options);

						
					} else{
						$metadado = new \Tainacan\Entities\Metadatum();
						$metadado->set_collection($insertedCollection);
						$metadado->set_name(trim($data[0]));
						$metadado->set_description($data[1]);
						$metadado->set_metadatum_type(trim($data[2]));
						$metadado->set_status('publish');
					}
				
					if ($metadado->validate()){
						$insertedMetadata = $metadataRepo->insert($metadado);
					} else {
						$erro = $metadado->get_errors();
						var_dump($erro);
					}
				}
			}
		$cont+=1;
		}
	fclose($handle);
	}
	
	if ($insertedCollection->validate()) {
		$insertedCollection = $collectionsRepo->insert($insertedCollection);
		echo 'Collection created with ID -  ' . $insertedCollection->get_id(), "\n";
	} else {
		$errors = $insertedCollection->get_errors();
	}
	
}else {
	$validationErrors = $collection->get_errors();
	die;
}
?>
