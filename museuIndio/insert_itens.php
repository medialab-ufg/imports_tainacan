<?php

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


$fh = fopen("baseMuseuIndio(test).csv", "r") or die("ERROR OPENING DATA");

while (($data = fgetcsv($fh, 0, ",")) == TRUE){
	$linecount++;
}
$linecount = $linecount-1;
fclose($fh);


#Getting metadata title from csv array

if (($handle = fopen("baseMuseuIndio(test).csv", "r")) == TRUE) {
	$cont = 0;
	while (($data = fgetcsv($handle, 0, ",")) == TRUE){
		
		if($cont == 0){
			$headers = array_map('trim', $data);
		}else{
			$item = new \Tainacan\Entities\Item();
			$item->set_title($data[0]);
			$item->set_collection($collection);
			
			if ($item->validate())  {
				$item = $itemsRepo->insert($item);
				
				for ($i = 0; $i <=sizeof($data); $i++) {
					
					$field = $fieldsRepo->fetch(['name' => $headers[$i]], 'OBJECT');
					$field = $field[0];
					
					$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $field);
					$itemMetadata->set_value($data[$i]);
					
					if ($itemMetadata->validate()) {
						$itemMetadataRepo->insert($itemMetadata);			
						} else {
							echo 'Erro no metadado ', $field->get_name(), ' no item ', $data[0];
						}
				}
				$item->set_status('publish');
				
				if ($item->validate()) {
					$item = $itemsRepo->insert($item);
					echo 'Item ', $data[0], ' - inserted', "\n";
					echo $linecount-$cont, ' remain', "\n";
					echo ($cont/$linecount)*100, '% Completed', "\n" ,"\n";
				}else{
					echo 'Erro no preenchientos dos campos', $cont, "\n";
					$errors = $item->get_errors();
					var_dump($errors);
					echo  "\n\n";
					die;
				}
				
			}else {
				echo 'Erro na linha ', $cont;
			}
			
		}
		$cont+=1;
	}
}
fclose($handle);

?>
