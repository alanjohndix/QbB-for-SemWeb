

function set_query(display_query) {
	//$('#query_content').html(display_query);
	$('#query_content').text(display_query);
}

function label_or_raw(raw) {
	var label = labels[raw];
	//alert( "label for "+raw+" is " + label );
	if ( label ) {
		return label;
	} else {
		return raw;
	}
}

function link_if_labelled(val) {
	var label = label_or_raw(val);
	if ( label != val ) {
		val = "<a href=\""+val+"\" target=\"_new\">"+label+"</a>";
	}
	return val;
}



function set_tables(tables,table) {
	  var num_tables = tables.length;
	  var t_html = "";
	  for ( t=0; t<num_tables; t++ )
	  {
		  var tname = tables[t];
		  var tselected;
		  if ( table==tname ) tselected="SELECTED";
		  else tselected = '';
		  t_html = t_html + "<option value='"+tname+"' "+tselected+">"+label_or_raw(tname)+"</option>";
	  }
	  $('#table_options').html(t_html);
}

function set_data(rows,fields,selected,opts) {
	  //alert("set_data:c" + rows);
	  var num_rows = rows.length;
	 // alert("num_rows="+num_rows);
	  var html = "";
	  if ( num_rows == 0 ) {
		  html = "<p>no data available</p>\n";
	  } else {
		html += "<table>\n";
		html += "<input type='hidden' name='num_rows' value='"+num_rows+"'>";
		
		//alert("make_data_header");
		html += make_data_header(fields);

		for ( r=0; r<num_rows; r++ )
		{
		//alert("make_data_row "+r);
			html += make_data_row(r,rows[r],fields,selected[r],opts[r]);
		}
		html += "</table>";
	  }
	  //alert("set data_content");
	  $('#data_content').html(html);
}




function make_data_header(fields) {
		//alert("in make_data_header");
	var num_fields = fields.length;
	//alert("num_fields="+num_fields);

	var html = "<tr><th width=18 ><image src='icons/invis.gif' width=18 height=17 border=0></th>";
	for ( f=0; f<num_fields; f++ ) {
		label = link_if_labelled(fields[f]['id']);
		html += "<th valign=bottom align=center>"+label+"</th>";
	//	html += "<th valign=bottom align=center>"+fields[f]['name']+"</th>";
	}
	html += "</tr>\n"
	return html;
}

function make_data_row(row_index,row,fields,qbb_select,opt) {
	var num_fields = fields.length;
	var yes_checked, no_checked;
	if ( opt == 'Y' ) yes_checked = "CHECKED";
	else yes_checked = "";
	if ( opt == 'N' ) no_checked = "CHECKED";
	else no_checked = "";

	var html = "";
	if ( qbb_select )   html += "<tr id='row"+row_index+"'class='selected' >\n";  // bgcolor='#9999ff'
	else   html += "<tr id='row"+row_index+"'>\n";
	if ( opt == 'Y' ) icon_src = "icons/tick18x17.gif";
	else if ( opt == 'N' ) icon_src = "icons/cross18x17.gif";
	else icon_src = "icons/invis.gif";

	html += "  <td valign=top nowrap  class=\"selectbox\">";
	html += "<input type='hidden' id='select"+row_index+"' name='select"+row_index+"' value='"+opt+"'>";
	html += "<a href='#' onClick='selectRow("+row_index+"); return false;'>";
	html += "<image name='icon"+row_index+"' src='"+icon_src+"' width=18 height=17 border=0>";
	html += "</a>";
	html += "</td>\n";

	for ( f=0; f<num_fields; f++ )
	{
		var val = row[fields[f]['id']];
		if ( val==undefined ) val="";
		//var label = label_or_raw(val);
		//if ( label != val ) {
		//	val = "<a href=\""+val+"\" target=\"_new\">"+label+"</a>";
		//}
		val = link_if_labelled(val);
		//if ( is_numeric(val) ) {
			html += "<td valign=top align=right>"+val+"</td>";
		//} else {
		//	html += "<td valign=top align=left>".htmlspecialchars(val)."</td>";
		//}
	}

	html += "</tr>\n";
	return html;
}


