<?php
class Mergefiles {
        var $fichero1;
        var $fichero2;
        var $csv_end;
        var $csv_sep;
        var $csv_file;

        // Declaro el constructor
        public function __construct($fich1,$fich2){
                $this->fichero1=$fich1;
                $this->fichero2=$fich2;
                $this->csv_end="\n";
                $this->csv_sep=",";
                $this->csv_file="fichero_merge.csv";
	}

	// Funcion que saca los datos de la cabecera de un fichero dado
	public function sacarcabeceraCsv($fich){
		// Declaramos las variables necesarias
		$arrayhead=array();
		$arrayfin=array();

		//Abrimos nuestro archivo
		$archivo = fopen($fich, "r");

		//Recorremos solo la primera linea para guardar las cabeceras en un array que utilizaremos como claves de array
                while (($datos = fgetcsv($archivo, ",")) == true)
                {
                        $arrayhead=$datos;
                        break;
		}

		//Creo un array con los valores como claves y las inicializamos a vacio
		$arrayfin=array_fill_keys($arrayhead, '');

		//Cerramos el archivo
		fclose($archivo);

		return $arrayfin;
	}

	// Funcion que une dos arrays asociativos
	public function unircabecerasCsv($array1, $array2){
		return array_merge($array1, $array2);
	}


	// Funcion que saca los datos de un fichero y los devuelve en un array sin incluir la cabecera
        public function sacardatosCsv($urlfich){
                // Declaramos las variables necesarias
		$arrayhead=array();
		$arraytmp=array();
                $arrayfin=array();

                //Abrimos nuestro archivo
		$archivo = fopen($urlfich, "r");

		// Guardamos en un array los datos de la cabecera del fichero
		while (($datos = fgetcsv($archivo, ",")) == true)
                {
                        $arrayhead=$datos;
                        break;
		}

		// Seguimos leyendo el fichero y volcamos los datos a un array asociando el valor a la clave correspondiente
		while (($datos = fgetcsv($archivo, ",")) == true)
		{
                        for ($columna=0; $columna<count($datos); $columna++)
                        {
                                $arraytmp[$arrayhead[$columna]] = $datos[$columna];
                        }
                        array_push($arrayfin, $arraytmp);
                }

                //Cerramos el archivo
		fclose($archivo);

                return $arrayfin;
	}

	// Funcion que crea finalmente el fichero nuevo a raiz de los datos por separado
	public function guardardatosCsv($arraycab, $array1, $array2){
		// Declaro la variables locales que necesito utilizar
		$arraytmp=array();
		$csv = "";
		$contlinea=0;

		// Construyo la cabecera con las keys del array correspondiente 
		$csv.=implode($this->csv_sep, array_keys($arraycab));
		$csv.=$this->csv_end;
		
		// Abrimos el fichero para escribir
                if (!$handle = fopen($this->csv_file, "w")) {
                    echo "Cannot open file";
                    exit;
		}

		// Recorro el primer array
		foreach($array1 as $fila1)
		{
			$arraytmp=$arraycab;
			// Cada valor lo incluyo en la clave del array que corresponda
			foreach($fila1 as $clave=>$valor)
			{
				$arraytmp[$clave] = $valor;
			}
			// Construyo la linea una vez que tengo el array relleno
			$csv.=implode($this->csv_sep, array_values($arraytmp));
			$csv.=$this->csv_end;
		}

		// Recorro el segundo array
		foreach($array2 as $fila2)
                {
			$arraytmp=$arraycab;
			// Cada valor lo incluyo en la clave del array que corresponda
                        foreach($fila2 as $cla=>$val)
                        {
                                $arraytmp[$cla] = $val;
                        }
			// Construyo la linea una vez que tengo el array relleno
                        $csv.=implode($this->csv_sep, array_values($arraytmp));
                        $csv.=$this->csv_end;
                }

		// Guardamos los datos en el fichero
                if (fwrite($handle, utf8_decode($csv)) === FALSE) {
                    echo "Cannot write to file";
                    exit;
                }
                fclose($handle);
        }

	function crearMerge(){
		$arraycabecera1 = Mergefiles::sacarcabeceraCsv($this->fichero1);
		$arraycabecera2 = Mergefiles::sacarcabeceraCsv($this->fichero2);
		$arraymergehead = Mergefiles::unircabecerasCsv($arraycabecera1, $arraycabecera2);
                $arraycsv1 = Mergefiles::sacardatosCsv($this->fichero1);
                $arraycsv2 = Mergefiles::sacardatosCsv($this->fichero2);
                Mergefiles::guardardatosCsv($arraymergehead, $arraycsv1, $arraycsv2);
        }

}

?>
