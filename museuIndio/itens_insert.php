
<?php

#CSV to Array:
$arquivo = file('Base_MuseuIndio.csv');
foreach($arquivo as $k)
	$csv[]=explode('|',$k);

#Connecting to Wordpress
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
        
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/wp-blog-header.php' );

#Generating object instances for Collection, Fields, Items, and Item_Metadata
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$fieldsRepo = \Tainacan\Repositories\Fields::get_instance();
$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

#Getting the Colletion
$collection = $collectionsRepo->fetch(['name'=>'Museu do Índio'], 'OBJECT');
$collection = $collection[0];

#Getting metadata title from csv array
for ($j=0; $j<sizeof($csv[0]); $j++){
	$headers[] = trim($csv[0][$j]);
}


#Inserting Items
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
			echo 'Item ', $csv[$line][0], ' - inserted', "\n";
			echo sizeof($csv)-$line, ' remain', "\n";
			echo ($line/sizeof($csv))*100, '% Completed', "\n" ,"\n";
		}
		else{
			echo 'erro no preenchientos dos campos', $line, "\n";
			$errors = $item->get_errors();
			var_dump($errors); 
			echo  "\n\n";
			die;
		}

	} else {
		echo 'erro no item ', $line;
	}

}

?>
