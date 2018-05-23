<?php

$base_tombo = 'COL_baseTombo.csv';
$base_fichaCat = 'COL_fichaCatalografica.csv';
$base_MD_fichaCat = 'MD_fichaCat.csv';
$base_MD_Tombo = 'MD_fichaTombo.csv';


#Lendo o CSV:
$arquivo = file($base_MD_Tombo);
foreach($arquivo as $k)
	$csv[]=explode(',',$k);


$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/wp-blog-header.php' );


#Apaga as Coleções Existentes

$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();

$collections = $collectionsRepo->fetch([], 'OBJECT');
foreach ($collections as $col) {
	echo "Apagando coleção: ", $col->get_id(), "\n";
	$collectionsRepo->delete([$col->get_id(), true]);
}


#Inserindo Coleção e Metadados
$fieldsRepo = \Tainacan\Repositories\Fields::get_instance();
$collection = new \Tainacan\Entities\Collection();
$collection->set_name('Base Tombo');
$collection->set_status('publish');
$collection->set_description('Base com as informações de Tombo do Museu do Índio');

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
			$corefield->set_name($csv[$line][0]);
			$corefield->set_description($csv[$line][1]);
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
		echo 'I still have the same ID! ' . $insertedCollection->get_id();
	} else {
		$errors = $insertedCollection->get_errors();
	}
	
} else {
	$validationErrors = $collection->get_errors();
	die;
}


?>
