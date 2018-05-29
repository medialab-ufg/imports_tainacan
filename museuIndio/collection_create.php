<?php
#Connecting with Wordpress:
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/wp-blog-header.php' );

#Delete Existing Collections:
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();

$collections = $collectionsRepo->fetch([], 'OBJECT');
foreach ($collections as $col) {
	echo "Deleting collection ", $col->get_id(), "\n";
	$collectionsRepo->delete([$col->get_id(), true]);
}

#Create Collection and Metadata:
$fieldsRepo = \Tainacan\Repositories\Fields::get_instance();
$collection = new \Tainacan\Entities\Collection();
$collection->set_name('Museu do Índio');
$collection->set_status('publish');
$collection->set_description('Coleção com informações sobre os itens museológicos do Museu do Índio.');

$cont = 0;
if ($collection->validate()) {

	$insertedCollection = $collectionsRepo->insert($collection);
	
	
	if (($handle = fopen("metadadosMIndio.csv", "r")) == TRUE) {
		
		$collection_core_fields = $fieldsRepo->get_core_fields($insertedCollection);
		
		while (($data = fgetcsv($handle, 0, ",")) == TRUE){
			if ($cont == 0){
				echo "Pulando linha das colunas", "\n";
			} else{
				if (trim($data[3])){
					foreach ($collection_core_fields as $corefield) {
						if($corefield->get_name() == $data[3]){
							$corefield->set_name($data[0]);
							$corefield->set_description($data[1]);
							echo "Atualizando Core Field: ", $data[0], "\n";
						
							if ($corefield->validate()){
								$insertedField = $fieldsRepo->insert($corefield);
							} else {
								$erro = $corefield->get_errors();
								var_dump($erro);
							}
						}
					}
				} else {

					$metadado = new \Tainacan\Entities\Field();
					$metadado->set_collection($insertedCollection);
					$metadado->set_name(trim($data[0]));
					$metadado->set_description($data[1]);
					$metadado->set_field_type(trim($data[2]));
					$metadado->set_status('publish');
				
					if ($metadado->validate()){
						$insertedField = $fieldsRepo->insert($metadado);
					} else {
						$erro = $metadado->get_errors();
						var_dump($erro);
					}
				}
			}
		$cont+=1;
		}
	}

	fclose($handle);
	
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
