<?php

class entitygen implements codegen {

	private $db ;
	private $tableName ; 
	private $aditionalParams ;
	private $config ;
	private $dictionary = array(
		'descricao' => "descrição",
	);

	public function __construct ( $db ) {
		$this->db = $db ;
		$this->config = include "config.php";
	}

	public function getOutput ( $params = "" ) {
		
		if ( empty ( $params ) ) return $this->help();
		if ( !preg_match ( '/^([\w]+)\s*(.*)?$/' , $params , $result ) ) throw new Exception ( "Something wrong in parameters: " . $params ) ;

		$this->tableName = $result[1];
		$this->aditionalParams = $result[2];
		$modelName = ucwords ( $this->getVarName ( $this->tableName ) );

		$query = "DESCRIBE " . $this->tableName ;
		$fieldList = $this->db->fetchAll ($query);

		foreach ( $fieldList as $key => $field ) {
			$fieldList[$key]['Label'] = $this->makeLabel ( $field['Field'] ) ;
			$fieldList[$key]['Inputtype'] = $this->getType ( $field ) ;
			$fieldList[$key]['Varname'] = $this->getVarName ( $field['Field'] );
		}

		ob_start();
			include $this->config['default_template'] ;
			$result = ob_get_contents();
		ob_end_clean();
		
		return htmlentities($result) ;

	}

	public function getVarName ( $field ) {
		$field = str_replace ( "_" , " " , $field );
		$field = strtolower( $field );
		$field = ucwords( $field );
		$field = str_replace ( " " , "" , $field );
		$field[0] = strtolower($field[0]);
		return $field;
	}

	public function getType ( $field ) {
// var_dump($field);
		if ( preg_match ( '/^varchar\([1-5]?[0-9]\)$/' , $field['Type'] ) ) {
			return "text";
		} else if ( preg_match ( '/^varchar\([0-9]+\)$/' , $field['Type'] ) ) {
			return "textarea";
		} else if ( preg_match ( '/^text$/' , $field['Type'] ) ) {
			return "textarea";
		} else {
			return "";
		}

	}

	public function makeLabel ( $fieldName ) {
		foreach ( $this->dictionary as $key => $val ) {
			$fieldName = preg_replace ( "/$key/" , $val , $fieldName );
		}
		$fieldName = ucwords($fieldName);
		$fieldName = str_replace( "_" , " " , $fieldName );
		return $fieldName ;
	}

	public function translate ( $fieldName ) {

	}

	public function help ( ) {

$help = <<<MANPAGE
=========================================================
FORM GENERATOR

USE: 
&gt; entity modelgen &lt;tablename&gt; &lt;aditional params&gt;
MANPAGE;

		return $help;

	}

}