<?php
class Aplanamiento {
	// Declaro el constructor
	public function __construct(){
	}
	
	function crearCsv($urlfich){
		// Declaramos las variables oportunas
		$csv_end = "\n";  
		$csv_sep = ";";  
		$csv_file = "fichero_aplanamiento.csv";  
		$csv="";  

		// Abrimos el fichero para escribir  
		if (!$handle = fopen($csv_file, "w")) {  
		    echo "Cannot open file";  
		    exit;  
		}  

		// Guardamos en la variable correspondiente la cabecera
		$csv.="name_header".$csv_sep."description_header".$csv_sep."image_1_header".$csv_sep."colour_header".$csv_sep."size_header".$csv_sep."sleeve_length_header".$csv_sep."sku_header".$csv_sep."heel_height_header".$csv_sep."laces_header".$csv_end;

		// Cargamos el fichero deseado
		$fichorig = simplexml_load_file($urlfich);

		// Recorremos el array con los datos del xml
		foreach($fichorig as $producto)
		{
			foreach($producto as $fila)
			{
				// Guardamos en la variable correspondiente el resto de datos del csv
				$csv.=$fila->name_header.$csv_sep.$fila->description_header.$csv_sep.$fila->image_1_header.$csv_sep.$fila->colour_header.$csv_sep.$fila->size_header.$csv_sep.$fila->sleeve_length_header.$csv_sep.$fila->sku_header.$csv_sep.$fila->heel_height_header.$csv_sep.$fila->laces_header.$csv_end;
			}
		}

		// Guardamos los datos en el fichero
		if (fwrite($handle, utf8_decode($csv)) === FALSE) {  
		    echo "Cannot write to file";  
		    exit;  
		}
		fclose($handle);
	}
}

?>
