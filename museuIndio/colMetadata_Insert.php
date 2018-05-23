<?php

#CSV Data to Array:
$arquivo = file('MD_CatTombo.csv');
foreach($arquivo as $k)
	$csv[]=explode('|',$k);

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

if ($collection->validate()) {

	$insertedCollection = $collectionsRepo->insert($collection);

	$coreFields = [];

	for ($line=1; $line < sizeof($csv); $line++){

		if ( trim($csv[$line][3]) ) {
			$coreFields[ trim($csv[$line][3]) ] = $line;
			continue;
		}

		$metadado = new \Tainacan\Entities\Field();
		$metadado->set_collection($insertedCollection);
		$metadado->set_name($csv[$line][0]);
		$metadado->set_description($csv[$line][1]);
		$metadado->set_field_type(trim($csv[$line][2]));
		$metadado->set_status('publish');
		if ($metadado->validate()){
			$insertedField = $fieldsRepo->insert($metadado);
		} else {
			$erro = $metadado->get_errors();
			var_dump($erro);
		}
	
	}

	$collection_core_fields = $fieldsRepo->get_core_fields($insertedCollection);

	foreach ($collection_core_fields as $corefield) {
		if (isset( $coreFields[$corefield->get_name()] )) {
			$line = $coreFields[$corefield->get_name()];
			$line = $line+1;
			$corefield->set_name($csv[$line][0]);
			$corefield->set_description($csv[$line][1]);
			echo $line, "\n";
			if ($corefield->validate()){
				$insertedField = $fieldsRepo->insert($corefield);
			} else {
				$erro = $corefield->get_errors();
				var_dump($erro);
			}
		}

	}

	if ($insertedCollection->validate()) {
		$insertedCollection = $collectionsRepo->insert($insertedCollection);
		echo 'Collection created with ID -  ' . $insertedCollection->get_id(), "\n";
	} else {
		$errors = $insertedCollection->get_errors();
	}
	
} else {
	$validationErrors = $collection->get_errors();
	die;
}


?>