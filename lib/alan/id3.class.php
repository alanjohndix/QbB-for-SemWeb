<?php
require_once 'decisiontree.class.php';

define( 'NO_FITNESS', -99 );


class ID3 {
	var $fields;
	var $field_types;
	function __construct() {
	}
	function set_fields($fields,$field_types) {
		//echo "******  set_fields <br>\n";
		//echo "======  fields: ".print_r($fields,1)." <br>\n";
		//echo "======  field_types: ".print_r($field_types,1)." <br>\n";
		//echo "======   <br>\n";
		$this->fields = $fields;
		$this->field_types = $field_types;
	}
	function learn_tree($postive,$negative) {
		//echo "******  learn_tree <br>\n";
		//echo "postive = " .print_r($postive,1) . "<br>\n";
		//echo "negative = " .print_r($negative,1) . "<br>\n";
		if ( count($negative) == 0 ) {
			$tree = array( 'kind'=>'YES' );
		} elseif ( count($postive) == 0 ) {
			$tree = array( 'kind'=>'NO' );
		} else {
			$choice = $this->best_choice($postive,$negative);
		//echo "=== best chocie is: ".print_r($choice,1)."<br>\n";
			if ( $choice == false ) {
				$tree = array( 'kind'=>'UNKNOWN' );
			} else {
			
				$positive_branches = DecisionTree::filter_by_choice($choice,$postive);
				$negative_branches = DecisionTree::filter_by_choice($choice,$negative);
				
				$total_yes = count($positive_branches['yes']) + count($negative_branches['yes']);
				$total_no  = count($positive_branches['no'])  + count($negative_branches['no']);
				
				
		   //echo "yss " . count($positive_branches['yes']) . ":" . count($negative_branches['yes']) . "  <br>\n";
		  // echo "no  " . count($positive_branches['no']) . ":" . count($negative_branches['no']) . "  <br>\n";
																						  
				if ( $total_yes==0 || $total_no==0 ) {
					$tree = array( 'kind'=>'UNKNOWN' );
				} else {
					$yes_branch = $this->learn_tree($positive_branches['yes'],$negative_branches['yes']);
					$no_branch  = $this->learn_tree($positive_branches['no'],$negative_branches['no']);
					$tree = array( 'kind'=>'CHOOSE', 'choice'=>$choice, 'yes_branch'=>$yes_branch, 'no_branch'=>$no_branch );
				}
			}
		}
		//echo "=== learntree returns: ".print_r($tree,1)."<br>\n";
		return $tree;
	}
	function best_choice($postive,$negative) {
		//echo "******  best_choice <br>\n";
		//echo "postive = " .print_r($postive,1) . "<br>\n";
		//echo "negative = " .print_r($negative,1) . "<br>\n";
		$best_fitness = NO_FITNESS;
		$best_choice = false;
		$seen = false;
		foreach( $this->fields as $field ) {
			$field_id = $field['id'];
			$pos_vals = table_select_column($postive,$field_id);
			$neg_vals = table_select_column($negative,$field_id);
			list($fitness,$choice) = $this->best_choice_one_field($field_id,$pos_vals,$neg_vals);
			//echo "=====  field: $field_id  fitness=$fitness <br>\n";
			if ( $fitness > $best_fitness ) {
			//echo "=====  field: $field_id  best so far <br>\n";
				$best_fitness = $fitness;
				$best_choice = $choice;
			}
			$seen[] = $field;
			foreach( $this->fields as $field_2 ) {
				if ( in_array($field_2,$seen) ) continue;  // don't count both ways round
				$field_id_2 = $field_2['id'];
				//echo "=====  try fields: $field_id $field_id_2  <br>\n";
				list($fitness_2,$choice_2) = $this->best_choice_two_field($field_id,$field_id_2,$postive,$negative);
				//echo "=====  fields: $field_id $field_id_2  fitness=$fitness_2 <br>\n";
				if ( $fitness_2 > $best_fitness ) {
				//echo "=====  field: $field_id  best so far <br>\n";
					$best_fitness = $fitness_2;
					$best_choice = $choice_2;
				}
			}
		}
				
		//echo "======  best_choice is : ".print_r($best_choice,1)."<br>\n";

		return $best_choice;
	}
	function plog($p,$tot=1.0) {
		if ( $p <= 0 || $tot <= 0 ) {
			return 0;
		} else {
			$p = $p/$tot;
			return - $p * log($p,2);
		}
	}
	function single_choice_fitness($yes_pos_ct,$yes_neg_ct,$no_pos_ct,$no_neg_ct) {
		$yes_ct = $yes_pos_ct + $yes_neg_ct;
		$no_ct  = $no_pos_ct  + $no_neg_ct;
		$yes_entropy = $this->plog( $yes_pos_ct, $yes_ct )   +   $this->plog( $yes_neg_ct, $yes_ct );
		$no_entropy  = $this->plog( $no_pos_ct,  $no_ct )    +   $this->plog( $no_neg_ct,  $no_ct );
		$entropy = ($yes_ct * $yes_entropy  + $no_ct * $no_entropy) / ( $yes_ct + $no_ct );
		$fitness =  1.0 - $entropy;  // makes fitness bigger is better
		//echo "single_choice_fitness($yes_pos_ct,$yes_neg_ct,$no_pos_ct,$no_neg_ct) = $fitness <br>\n";
		return $fitness;
	}
	function best_choice_two_field($field_id_1,$field_id_2,$postive,$negative) {
		list( $fitness, $operator ) = $this->best_operator_two_field($field_id_1,$field_id_2,$postive,$negative);
		if ( $fitness == NO_FITNESS || ! $operator ) {
			return array( NO_FITNESS, false );
		}
		if ( $operator == '=' ) {
			$kind = 'COMPARE';
		} else {
			$kind = 'COMPARE_NUMBER';
		}
		$choice = array( 'kind'=>$kind, 'field'=>$field_id_1, 'operator'=>$operator, 'arg_type'=>'FIELD', 'arg'=>$field_id_2);
		return array( $fitness, $choice );
	}
	function best_operator_two_field($field_id_1,$field_id_2,$positive,$negative) {
		$pos = $this->compare_two_field_stats($field_id_1,$field_id_2,$positive);
		$neg = $this->compare_two_field_stats($field_id_1,$field_id_2,$negative);
		
		if ( $pos['tot'] ==0 || $neg['tot']==0 ) {
			return array( NO_FITNESS, false );
		}
		
		$fitness_equal   = $this->single_choice_fitness($pos['equal'],$neg['equal'],$pos['tot']-$pos['equal'],$neg['tot']-$neg['equal']);
		$fitness_less    = $this->single_choice_fitness($pos['less'],$neg['less'],$pos['tot']-$pos['less'],$neg['tot']-$neg['less']);
		$fitness_greater = $this->single_choice_fitness($pos['greater'],$neg['greater'],$pos['tot']-$pos['greater'],$neg['tot']-$neg['greater']);
		if ( $fitness_equal >= $fitness_less && $fitness_equal >= $fitness_greater  ) {
			$best_fitness = $fitness_equal;
			$operator = '=';
		} elseif ( $fitness_greater >= $fitness_less  ) {
			$best_fitness = $fitness_greater;
			$pos_greater = ( $pos['greater'] * ($neg['tot']-$neg['greater']) )  >  ( ($pos['tot']-$pos['greater']) * $neg['greater'] );
			if ( $pos_greater ) {
				$operator = '>';
			} else {
				$operator = '<=';
			}
		} else {  // less best
			$best_fitness = $fitness_less;
			$pos_less = ( $pos['less'] * ($neg['tot']-$neg['less']) )  >  ( ($pos['tot']-$pos['less']) * $neg['less'] );
			if ( $pos_less ) {
				$operator = '<';
			} else {
				$operator = '>=';
			}
		}
		// $best_choice = array( 'kind'=>$kind, 'field'=>$field_id_1, 'operator'=>'=', 'arg_type'=>'FIELD', 'arg'=>$field_id_2);
		return array( $best_fitness, $operator );
	}
	function compare_two_field_stats($field_id_1,$field_id_2,$rows) {
		$tot = 0;
		$equal = 0;
		$less = 0;
		$greater = 0;
		$one_missing = 0;
		foreach( $rows as $row ) {
			$tot++;
			if ( ! array_key_exists($field_id_1,$row) || ! array_key_exists($field_id_2,$row) ) {
				$one_missing++;
			} else {
				$val1 = $row[$field_id_1];
				$val2 = $row[$field_id_2];
				if ( $val1 == $val2 ) {
					$equal++;
				} elseif ( is_numeric($val1) && is_numeric($val2) ) {
					if ( $val1 < $val2 ) $less++;
					elseif ( $val1 > $val2 ) $greater++;
				}
			}
		}
		return array( 'tot'=>$tot, 'equal'=>$equal, 'less'=>$less, 'greater'=>$greater, 'missing'=>$one_missing );
	}
	function best_choice_one_field($field_id,$postive,$negative) {
		$type = $this->field_types[$field_id];
		//echo "******  best_choice_one_field [$type] $field_id <br>\n";
		// check exact values first
		list($best_fitness,$best_value) = $this->best_single_value($postive,$negative);
		//echo "   fitness=$best_fitness  value=$best_value <br>\n";
		$best_choice = array( 'kind'=>'COMPARE_EQUAL', 'field'=>$field_id, 'operator'=>'=', 'arg_type'=>'LITERAL', 'arg'=>$best_value);
		if ( $type['IS_NUMBER'] ) {
			list($fitness,$value,$operator) = $this->best_numeric_range($postive,$negative);
			if ( $fitness > $best_fitness ) {
				$best_fitness = $fitness;
				$best_choice = array( 'kind'=>'COMPARE_NUMBER', 'field'=>$field_id, 'operator'=>$operator, 'arg_type'=>'LITERAL', 'arg'=>$value);
			}
			// do nout
		} elseif ( $type['IS_STRING'] ) {
			// do nout
		} elseif ( $type['IS_DATE'] ) {
			// do nout
		} else {
			// do nout
		}
		return array($best_fitness,$best_choice);
	}
	function best_single_value($postive,$negative) {
		$all = array_merge($postive,$negative);
		$all_cts = array_value_counts($all);
		$pos_cts = array_value_counts($postive);
		$neg_cts = array_value_counts($negative);
		$pos_tot = count($postive);
		$neg_tot = count($negative);
		$best_fitness = NO_FITNESS;
		$best_value = false;
		
		//echo "postive = " .print_r($postive,1) . "<br>\n";
		//echo "negative = " .print_r($negative,1) . "<br>\n";
		//echo "pos_cts = " .print_r($pos_cts,1) . "<br>\n";
		//echo "neg_cts = " .print_r($neg_cts,1) . "<br>\n";
		
		foreach( $all_cts as $value => $count ) {
			//if ( $count > 1 ) {
				$yes_pos_ct = $pos_cts[$value];
				$yes_neg_ct = $neg_cts[$value];
				$no_pos_ct  = $pos_tot - $yes_pos_ct;
				$no_neg_ct  = $neg_tot - $yes_neg_ct;
				$fitness = $this->single_choice_fitness($yes_pos_ct,$yes_neg_ct,$no_pos_ct,$no_neg_ct);
				if ( $fitness > $best_fitness ) {
					$best_fitness = $fitness;
					$best_value = $value;
				}
			//}
		}
		if ( $best_fitness == NO_FITNESS ) {
			return array( NO_FITNESS, array( 'kind'=>'UNKNOWN' ) );
		} else {
			return array( $best_fitness, $best_value );
		}
	}
	function best_numeric_range($positive,$negative) {
		//echo "******  best_numeric_range <br>\n";
		//echo "postive = " .print_r($positive,1) . "<br>\n";
		//echo "negative = " .print_r($negative,1) . "<br>\n";
		$pos_num =  array_filter( $positive, 'is_numeric' );
		$neg_num =  array_filter( $negative, 'is_numeric' );
		
		sort($pos_num, SORT_NUMERIC  );
		sort($neg_num, SORT_NUMERIC  );

		//echo "pos_num = " .print_r($pos_num,1) . "<br>\n";
		//echo "neg_num = " .print_r($neg_num,1) . "<br>\n";

		$pos_smaller = 0;
		$neg_smaller = 0;
		$pos_bigger = $pos_tot = count($pos_num);
		$neg_bigger = $neg_tot = count($neg_num);
		
		if ( $pos_bigger == 0 || $neg_bigger == 0 ) {
			return array( NO_FITNESS, array( 'kind'=>'UNKNOWN' ) );
		}
		
		$best_fitness = NO_FITNESS;
		$best_lower = false;
		$best_upper = false;
		$best_pos_upper = false;
		
		$pos_index = 0;
		$neg_index = 0;
		
		while ( $pos_bigger > 0 && $neg_bigger > 0 ) {
			$next_pos_val = $pos_num[$pos_index];
			$next_neg_val = $neg_num[$neg_index];
			//echo "at pos_bigger=$pos_bigger neg_bigger=$neg_bigger $next_pos_val = pos_num[$pos_index]  $next_neg_val = neg_num[$neg_index]   <br>\n";
			if ( $next_pos_val < $next_neg_val ) {
				$upper = $next_neg_val;
				while ( $pos_bigger > 0 && $pos_num[$pos_index] < $next_neg_val ) {
					$lower = $pos_num[$pos_index];
					$pos_index++; $pos_bigger--; $pos_smaller++;
				}
			} elseif ( $next_pos_val > $next_neg_val ) {
				$upper = $next_pos_val;
				while ( $neg_bigger > 0 && $next_pos_val > $neg_num[$neg_index] ) {
					$lower = $neg_num[$neg_index];
					$neg_index++; $neg_bigger--; $neg_smaller++;
				}
			} else { // $next_pos_val == $next_neg_val
				$lower = $next_pos_val;
				while ( $pos_bigger > 0 && $pos_num[$pos_index] == $lower ) {
					$pos_index++; $pos_bigger--; $pos_smaller++;
				}
				while ( $neg_bigger > 0 && $neg_num[$neg_index] == $lower ) {
					$neg_index++; $neg_bigger--; $neg_smaller++;
				}
				if ( $pos_bigger == 0 ) {
					if ( $neg_bigger == 0 ) { // both zero 
						break;  // no more to try
					} else {  // just pos zero
						$upper = $neg_num[$neg_index];
					}
				} else {
					if ( $neg_bigger == 0 ) { // just neg zero
						$upper = $pos_num[$pos_index];
					} else {  // both OK
					}
						$upper = min( $pos_num[$pos_index], $neg_num[$neg_index] );
				}
			}
			//echo "moved to pos_bigger=$pos_bigger neg_bigger=$neg_bigger {$pos_num[$pos_index]} = pos_num[$pos_index]  {$neg_num[$neg_index]} = neg_num[$neg_index]   <br>\n";
			$fitness = $this->single_choice_fitness($pos_bigger,$neg_bigger,$pos_smaller,$neg_smaller);
			//echo "range [$lower,$upper] = $fitness = single_choice_fitness($pos_bigger,$neg_bigger,$pos_smaller,$neg_smaller)   <br>\n";
			if ( $fitness > $best_fitness ) {
				$best_fitness = $fitness;
				$best_lower = $lower;
				$best_upper = $upper;
				$best_pos_upper = $pos_bigger * $neg_smaller  >= $pos_smaller * $neg_bigger  ; 
				                  // equivalent to $pos_bigger / $neg_bigger >= $pos_smaller / $neg_smaller ;
			}
		}
		
		$best_value = nice_value_to_split_range($best_lower,$best_upper);
		
		if ( $best_value == $best_lower ) {
			if ( $best_pos_upper ) {
				$operator = ">";
			} else {
				$operator = "<=";
			}
		} elseif ( $best_value == $best_upper ) {
			if ( $best_pos_upper ) {
				$operator = ">=";
			} else {
				$operator = "<";
			}
		} else   {
			if ( $best_pos_upper ) {
				$operator = ">";
			} else {
				$operator = "<";
			}
		}
		
		//echo "best_value=$best_value  best_lower=$best_lower  best_pos_upper=$best_pos_upper  operator=$operator   <br>\n";
		
		if ( $best_fitness == NO_FITNESS ) {
			return array( NO_FITNESS, array( 'kind'=>'UNKNOWN' ) );
		} else {
			return array( $best_fitness, $best_value, $operator );
		}
	}
	function best_choice_two_fields($field_id,$postive,$negative) {
		return array( 1.0, array( 'kind'=>'YES' ) );
	}
}

?>