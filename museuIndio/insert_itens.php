<?php

#Connecting to Wordpress
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";

define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', false );
require( '/var/www/html/wp-blog-header.php' );

#Generating object instances for Collection, Metadata, Items, and Item_Metadata
$collectionsRepo = \Tainacan\Repositories\Collections::get_instance();
$metadataRepo = \Tainacan\Repositories\Metadata::get_instance();
$itemsRepo = \Tainacan\Repositories\Items::get_instance();
$itemMetadataRepo = \Tainacan\Repositories\Item_Metadata::get_instance();

#Getting the Colletion -- # MODIFICAR O NOME DA COLEÇÃO PARA O DESEJADO
$collection = $collectionsRepo->fetch(['name'=>'Museu do Índio'], 'OBJECT');
$collection = $collection[0];

# MODIFICAR O NOME DO ARQUIVO E SE HOUVER, O CAMINHO PARA O ARQUIVO CSV
$fh = fopen("baseMindio(teste).csv", "r") or die("ERROR OPENING DATA");

while (($data = fgetcsv($fh, 0, ",")) == TRUE){
	$linecount++;
}
fclose($fh);


#Getting metadata title from csv array

# MODIFICAR O NOME DO ARQUIVO E SE HOUVER, O CAMINHO PARA O ARQUIVO CSV
if (($handle = fopen("baseMindio(teste).csv", "r")) == TRUE) {
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
			
			if ($item->validate())  {
				$item = $itemsRepo->insert($item);
				
				for ($i = 0; $i <=sizeof($data); $i++) {
					
					$metadata = $metatadaRepo->fetch(['name' => $headers[$i]], 'OBJECT');
					$metadata = $metadata[0];
					
					if ($metatada->get_metadata_type() == 'Tainacan\Metadata_Types\Category'){
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
						$category_value = explode("||",$data[$i]);
						$itemMetadata->set_value($category_value);
					}else{
						$itemMetadata = new \Tainacan\Entities\Item_Metadata_Entity($item, $metadata);
						$itemMetadata->set_value($data[$i]);
					}
					
					if ($itemMetadata->validate()) {
						$itemMetadataRepo->insert($itemMetadata);			
						} else {
							echo 'Erro no metadado ', $metadata->get_name(), ' no item ', $data[0];
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
			}
			
		}
		$cont+=1;
	}
fclose($handle);
}


?>
