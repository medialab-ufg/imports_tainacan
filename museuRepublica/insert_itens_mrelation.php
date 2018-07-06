
<?php

#Connecting to Wordpress
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";

define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( 'C:\wamp\www\wordpress\wp-blog-header.php' );

#Generating object instances for Collection, Metadata, Items, and Item_Metadata
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$metadataRepo = \Tainacan\Repositories\Metadata::get_instance();
$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

#Setting relational collections
/*
$pessoas_collection = $collectionsRepo->fetch(['name'=>'Museu da República - Pessoas'], 'OBJECT');
$pessoas_collection = $pessoas_collection[0];

$entidades_collection = $collectionsRepo->fetch(['name'=>'Museu da República - Entidades'], 'OBJECT');
$entidades_collection = $entidades_collection[0];

$pessoas_id = $metadataRepo->fetch(['name'=>'ID Pessoas'],[$pessoas_collection], 'OBJECT');
$pessoas_id = $pessoas_id[0];

$entidades_id = $metadataRepo->fetch(['name'=>'ID'],[$entidades_collection], 'OBJECT');
$entidades_id = $entidades_id[0];
*/

#Getting the Colletion
$collection = $collectionsRepo->fetch(['name'=>'Museu da República - Pessoas (Teste)'], 'OBJECT');
$collection = $collection[0];

$fh = fopen("Collections/Teste/pessoas_itens.csv", "r") or die("ERROR OPENING DATA");

while (($data = fgetcsv($fh, 0, ",")) == TRUE){
	$linecount++;
}
fclose($fh);


#Getting metadata title from csv array

if (($handle = fopen("Collections/Teste/pessoas_itens.csv", "r")) == TRUE) {
	
	$cont = 0;
	
	while (($data = fgetcsv($handle, 0, ",")) == TRUE){
		
		if($cont == 0){
			
			echo "Tratando primeira linha \n";
			$headers = array_map('trim', $data);
			
		}else{
			
			$item = new \Tainacan\Entities\Item();
			
			$item->set_title($data[0]);
			$item->set_status('publish');
			$item->set_collection($collection);
			
			if ($item->validate()) {
				
				$item = $itemsRepo->insert($item);
				for ($i = 0; $i <=sizeof($data); $i++) {
					
					$metadatum = $metadataRepo->fetch(['name' => $headers[$i]], 'OBJECT');
					$metadatum = $metadatum[0];
					
					
					if ($metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Taxonomy'){
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$taxonomy_value = explode("||",$data[$i]);
						$itemMetadata->set_value($taxonomy_value);
						
					} else if ($metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Relationship'){
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$relationship_value = explode("||",$data[$i]);
						$itemMetadata->set_value($relationship_value);
					
					}else{
						
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadatum);
						$itemMetadata->set_value($data[$i]);
					}
					
					if ($itemMetadata->validate()) {
						
						$itemMetadataRepo->insert($itemMetadata);
						
					}else {
						echo 'Erro no metadado ', $metadatum->get_name(), ' no item ', $data[0];
						$erro = $itemMetadata->get_errors();
						echo var_dump($erro);
					}
				}
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
				echo  "\n\n";
				var_dump($item);
				echo  "\n\n";
				die;
			}
			
		}
		$cont+=1;
	}
fclose($handle);
}


?>
