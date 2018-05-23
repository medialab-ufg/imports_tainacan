
<?php

$base_tombo = 'COL_baseTombo.csv';
$base_fichaCat = 'COL_fichaCatalografica.csv';



#Lendo o CSV:
$arquivo = file($base_tombo);
foreach($arquivo as $k)
	$csv[]=explode('|',$k);


$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/wp-blog-header.php' );


$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$fieldsRepo = \Tainacan\Repositories\Fields::get_instance();



$collection = $collectionsRepo->fetch(['name'=>'Base Tombo'], 'OBJECT');
$collection = $collection[0];

for ($j=1; $j<sizeof($csv[0]); $j++){
	$headers[] = trim($csv[0][$j]);
}



$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

for ($line=1; $line <sizeof($csv); $line++){

	$item = new \Tainacan\Entities\Item();
	$item->set_title($csv[$line][0]);
	$item->set_collection($collection);

	if ($item->validate())  {
		$item = $itemsRepo->insert($item);

		for ($i = 0; $i <sizeof($csv[$line]); $i++) {

			$field = $fieldsRepo->fetch(['name' => $headers[$i]], 'OBJECT');
			$field = $field[0];


			$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $field);
			$itemMetadata->set_value($csv[$line][$i]);

			if ($itemMetadata->validate()) {
				$itemMetadataRepo->insert($itemMetadata);			
				} else {
				echo 'erro no metadado ', $field->get_name(), ' no item ', $csv[$line][0];
			}

		}

		$item->set_status('publish');

		if ($item->validate()) {
			$item = $itemsRepo->insert($item);
			echo 'Item', $csv[$line][0], 'inserido', "\n"
		}


	} else {
		echo 'erro no item ', $line;
	}

}

?>
