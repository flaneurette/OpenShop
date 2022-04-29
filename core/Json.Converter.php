<?php

class Logging {
	
	public function __construct($params = array()) 
	{ 
		$this->init($params);
	}
	
	/**
	* Initializes object.
	* @param array $params
	* @throws Exception
	*/	
	
    public function init($params)
    {
			
		try {
			isset($params['var'])  ? $this->var  = $params['var'] : false; 
			} catch(Exception $e) {}
    }
	
	/**
	* Converter for data, types and strings.
	* @param string $string
	* @return array
	*/	
	public function convert($file,$method,$name=false,$backup=false){
		
	$data = [];
	
		switch($method) {
			
			case 'csv_to_json':
			
				if(!isset($file)) {
					$this->message('Please choose a CSV file to convert.');
					break;
				}

				if(in_array($name,$this->serverconfig_csv)) {
					$server_path = '../server/config/';
					} else {
					$server_path = '../inventory/';
				}
				
				// Back-up CSV before processing.
				$this->backup($server_path.'/csv/'.$name,'../'.self::BACKUPS); 
				
				$csv1 = explode("\r\n", $file);
				$c1 = count($csv1);
				
				$csv2 = explode("\n", $file);
				$c2 = count($csv2);
				
				if($c1 <= $c2) {
					$counter = $c1;
					$csv = $csv1;
					} else {
					$counter = $c2;
					$csv = $csv2;
				}
				
				$buildcsv = "";

				$find = ["\r\n","\n\r","\n","\r","\t"];
				$replace = ["\\n","\\n","\\n","","\\t"];
							
				for($i=0;$i<$counter;$i++) {
					if(strlen($csv[$i]) <= 4) { 
						if($i < ($counter-1) ) {
							$buildcsv .= "\\n" . str_ireplace($find,$replace,$csv[$i]);
						} 
					} else {
						$csv[$i] = str_ireplace($find,$replace,$csv[$i]);
						$buildcsv .= $csv[$i].PHP_EOL;
					}
				}

				$find 		= ["\n\r\n", "\r\n\\n", "\r\n\\n", "\r\n\\n", "\r\n\\n", "\n\n\\n", "\r\\n",  "\r\s\n"];
				$replace 	= ["\\n\\n", "\\n\\n",  "\\n\\n",  "\\n\\n",  "\\n\\n",  "\\n\\n",  "\\n\\n", "\\n\\n"];

				$buildcsv = str_ireplace($find,$replace,$buildcsv);

					$data = array_map("str_getcsv", explode("\n", $buildcsv));
					$columns = $data[0];
					
							foreach ($data as $row_index => $row_data) {
								if($row_index === 0) continue;
								$data[$row_index] = [];
								foreach ($row_data as $column_index => $column_value) {
									$label = $columns[$column_index];
									$data[$row_index][$label] = $column_value;       
								}
								unset($data[0]);
							}
							
							$c = count($data);
							if($c > 1) {
								unset($data[$c]);
							}
							
				$data = array_values($data);
				
			break;

			case 'json_to_csv_admin':
				
				if($name) {
					if(in_array($name,$this->serverconfig_json)) {
						$server_path = '../'.self::SERVERCSV;
						} else {
						$server_path = '../'.self::CSV;
					}
				}
					
				// Back-up JSON before processing.
				$this->backup($server_path.$name,'../'.self::BACKUPS); 
				
				$json_data =  json_decode($file, true, self::DEPTH, JSON_BIGINT_AS_STRING);
				
				array_keys($json_data);

				$csv 	= '';
				$header = [];
				$data 	= [];
				
				$csv_keys = array_keys($json_data[0]);

				for($i=0; $i < count($csv_keys); $i++) {
					$csv .= $csv_keys[$i] . ',';
				}	
	
				$csv .= PHP_EOL;

				$csv_vals = array_values($json_data);
				
				for($i=0; $i < count($csv_vals); $i++) {

					$csv .=  $this->csvstring(array_values($csv_vals[$i])) . PHP_EOL;
				}

				$find 	 = ['.json','../','./','\\'];
				$replace = ['.csv','','',''];
				
				$csv_file_upload = $server_path . str_ireplace($find,$replace,$name);
				
				$pointer = fopen($csv_file_upload,'w');
				fwrite($pointer,$csv);

				$data = $csv;
				
			break;
			
			case 'json_to_csv':

				if(!defined(self::SHOP)) {
					$this->message('Conversion failed: JSON file not found.');
					break;
				}
				
				if(!defined(self::CSV)) {
					$this->message('Conversion failed: CSV file not found.');
					break;
				}
				
				$json_data = $this->convert(self::SHOP,'json_decode');
				$csv_file = fopen(self::CSV, 'w');
				
				$header = false;
				
				foreach ($json_data as $line){

					if (empty($header)) {
						$header = array_keys($line);
						fputcsv($f, $header);
						$header = array_flip($header);
					}
					
					$data = array($line['type']);
					foreach ($line as $value) {
						array_push($data,$value);
					}
					
					array_push($data,$line['stream_type']);
					fputcsv($csv_file, $data);
				}
				
			break;
			
			case 'json_decode':
			$data = json_decode(file_get_contents($file), true, self::DEPTH, JSON_BIGINT_AS_STRING);
			break;

			case 'json_encode':
			$data = json_encode($shop, JSON_PRETTY_PRINT);
			break;
		}
		
		return $data;
	}
	
}

?>