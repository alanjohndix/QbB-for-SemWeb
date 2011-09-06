<?php


class DecisionTree {
	static function apply_tree($tree,$row) {
		switch( $tree['kind'] ) {
			case 'YES':	return true;
			case 'NO':	return false;
			default:	if ( self::apply_choice($tree['choice'],$row) ) {
							return self::apply_tree($tree['yes_branch'],$row);
						} else {
							return self::apply_tree($tree['no_branch'],$row);
						}
		}
	}
	static function filter_by_choice($choice,$rows) {
		$yes = array();
		$no = array();
		foreach ( $rows as $row ) {
			if ( self::apply_choice($choice,$row) ) {
				$yes[] = $row;
			} else {
				$no[] = $row;
			}
		}
		//echo "*** filter_by_choice: ".print_r($choice,1)."<br>\n";
		//echo "===             rows: ".print_r($rows,1)."<br>\n";
		//echo "===              yes: ".print_r($yes,1)."<br>\n";
		//echo "===               no: ".print_r($no,1)."<br>\n";

		return array( 'yes'=>$yes, 'no'=>$no );
	}
	static function apply_choice($choice,$row) {
		$value1 = $row[$choice['field']];
		if ( $choice['arg_type'] == 'FIELD' ) {
			$value2 = $row[$choice['arg']];
		} else {
			$value2 = $choice['arg'];
		}

		switch ( $choice['operator'] ) {
			case '<':	$res = $value1 < $value2;  break;
			case '>':	$res = $value1 > $value2;  break;
			case '<=':	$res = $value1 <= $value2;  break;
			case '>=':	$res = $value1 >= $value2;  break;
			case '=':	$res = $value1 == $value2;  break;
			default:	$res = false;  break;
		}
		
		//echo "*** apply_choice: $value1 ".$choice['operator']." $value2   gives $res<br>\n";
		return $res;
	}
}


?>