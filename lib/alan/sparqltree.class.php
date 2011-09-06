<?php

class SparqlTree {
	var $class;
	var $subject;
	var $fields;
	var $fld_info;
	function __construct($class,$fields,$subject="s",$used_vars=false) {
		$this->class = $class;
		$this->fields = $fields;
		$this->subject = $subject;
		if ( $used_vars ) {
			$vars = $used_vars;
		} else {
			$vars = array();
		}
		$vars[] = $subject;
		$this->fld_info = array();
		foreach( $fields as &$field ) {
			$name = $field['name'];
			$ct = 0;
			$var = $name;
			while ( in_array( $var, $vars ) ) {
				$ct++;
				$var = $name . $ct;
			}
			$vars[] = $var;
			$field['var'] = $var;
			$this->fld_info[$field['id']] = $field; 
		}
	}
	var $vars;
	function is_yes_tree($tree) {
		return $tree['kind'] == 'YES';
	}
	function is_no_tree($tree) {
		return $tree['kind'] == 'NO';
	}
	function format_tree($tree,$indent="  ",$allvars=false) {
		$this->vars = $this->vars_in_branch($tree);
		//echo "=== vars_in_branch returns: ".print_r($this->vars,1)."<br>\n";

		$branch = $this->format_branch($tree);
		if ( $allvars ) {
			$var_list = array_keys($this->field_info);
		} else {
			$var_list = $this->vars;
		}
		$fvar_list = $this->format_var_list($var_list);
		$fclass = $this->format_class($indent);
		$fvar_clauses = $this->format_vars($var_list,$indent);
		$where = $fclass . $fvar_clauses;
		if ( $branch == "TRUE" ) {
		} elseif ( $branch == "FALSE" ) {
			$where .= $indent . " FILTER( false ) .\n";
		} else {
			$where .= $indent . " FILTER( " . $branch .") .\n";
		}
		$formatted = "SELECT " . $fvar_list . "\n" . "WHERE {\n" . $where . "}";
		return $formatted;
	}
	function format_class($indent="  ") {
		$formatted = $indent . "?".$this->subject." <".RDF_TYPE."> <".$this->class."> .\n";
		return $formatted;
	}
	function format_var_list($vars) {
		$formatted = "";
		foreach( $vars as $var ) {
			$info = $this->fld_info[$var];
			//echo "=== format_vars $var has info: ".print_r($info,1)."<br>\n";
			$formatted .=  "?".$info['var']." ";
		}
		return $formatted;
	}
	function format_vars($vars,$indent="  ") {
		$formatted = "";
		foreach( $vars as $var ) {
			$info = $this->fld_info[$var];
			//echo "=== format_vars $var has info: ".print_r($info,1)."<br>\n";
			$formatted .= $indent . "?".$this->subject." <".$info['id']."> ?".$info['var']." .\n";
		}
		return $formatted;
	}
	function format_branch($tree) {
		switch( $tree['kind'] ) {
			case 'YES':
					return "TRUE";
			case 'NO':
					return "FALSE";
			default:
					$yes = $this->format_branch($tree['yes_branch']);
					$no  = $this->format_branch($tree['no_branch']);
					$choice = $this->format_choice($tree['choice']);
					
					//echo "format choice: \n    choice = $choice  \n    yes = $yes  \n    no = $no <br>\n";
					
					if ( $yes == 'TRUE' ) {
						if ( $no == 'TRUE' ) {
							return 'TRUE';
						} elseif ( $no == 'FALSE' ) {
							return $choice;
						} else { // no is complex
							//return "( " . $choice . " ) || ( !(" . $choice . ") && (" . $no . ") )";
							return "( " . $choice . " ) ||  (" . $no . ")";
						}
					} elseif ( $yes == 'FALSE' ) {
						if ( $no == 'TRUE' ) {
							return "!(".$choice.")";
						} elseif ( $no == 'FALSE' ) {
							return 'FALSE';
						} else { // no is complex
							return "!(" . $choice . ") && (" . $no . ")";
						}
					} else { // yes is complex
						if ( $no == 'TRUE' ) {
							//return "( (" . $choice . ") && (" . $yes . ") ) || !(" . $choice . ") )";
							return "( (" . $yes . ") || !(" . $choice . ") )";
						} elseif ( $no == 'FALSE' ) {
							return "(" . $choice . ") && (" . $yes . ")";
						} else { // no is complex
							return "( (" . $choice . ") && (" . $yes . ") ) || ( !(" . $choice . ") && (" . $no . ") )";
						}
					}
		}
	}
	function format_choice($choice) {
		//echo "======  format_choice : ".print_r($choice,1)."<br>\n";

		$vars = array();
		$value1 = '?' . $this->fld_info[$choice['field']]['var'];
		if ( $choice['arg_type'] == 'FIELD' ) {
			$value2 = '?' . $this->fld_info[$choice['arg']]['var'];
		} else {
			$value2 = $this->format_literal ( $choice['arg'] );
		}
		switch ( $choice['operator'] ) {
			case '<':	return $value1 ."<". $value2;
			case '>':	return $value1 .">". $value2;
			case '<=':	return $value1 ."<=". $value2;
			case '>=':	return $value1 .">=". $value2;
			case '=':	return $value1 ."=". $value2;
			default:	echo "unexpected operator \"".$choice['operator']."\" <br>\n";
						return 'FALSE';
		}
	}
	
	static function format_literal( $value, $lang=false, $datatype=false ) {
		/* literal  adapted from SRC2_TurtleSerializer */
		$quot = '"';
		if (preg_match('/\"/', $value)) {
		  $quot = "'";
		  if (preg_match('/\'/', $value) || preg_match('/[\x0d\x0a]/', $value)) {
			$quot = '"""';
			if (preg_match('/\"\"\"/', $value) || preg_match('/\"$/', $value) || preg_match('/^\"/', $value)) {
			  $quot = "'''";
			  $value = preg_replace("/'$/", "' ", $value);
			  $value = preg_replace("/^'/", " '", $value);
			  $value = str_replace("'''", '\\\'\\\'\\\'', $value);
			}
		  }
		}
		if ((strlen($quot) == 1) && preg_match('/[\x0d\x0a]/', $value)) {
		  $quot = $quot . $quot . $quot;
		}
		$suffix = $lang ? '@' . $lang : '';
		$suffix = $datatype ? '^^' . $this->getTerm($datatype, 'dt') : $suffix;
		return $quot . $value . $quot . $suffix;
	}
	
	function vars_in_branch($tree) {
		switch( $tree['kind'] ) {
			case 'YES':
					return array();
			case 'NO':
					return array();
			default:
					$yes_vars = $this->vars_in_branch($tree['yes_branch']);
					$no_vars  = $this->vars_in_branch($tree['no_branch']);
					$choice_vars = $this->vars_in_choice($tree['choice']);
					$all_vars = array_unique( array_merge( $yes_vars, $no_vars, $choice_vars ) );
					return $all_vars;
		}
	}
	
	function vars_in_choice($choice) {
		$vars = array( $choice['field'] );
		if ( $choice['arg_type'] == 'FIELD' ) {
			$vars[] = $choice['arg'];
		}
		return $vars;
	}

}

?>