<?php
/*
Plugin Name: TOP-10 Zinsen
Plugin URI: http://www.toptentagesgeld.de/plugin
Description: Sidebar-Widget zur Anzeige einer TOP-10-Liste von Tagesgeld-Angeboten
Version: 1.5.1
Author: finango
Author URI: http://www.finango.de/
License: Copyright 2009  finango.de  (email : mail@finango.de)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function top10_tagesgeld_init() {
 
	// Überprüft Wordpress-Funktion, Abbruch wenn nicht vorhanden

	if ( !function_exists('wp_register_sidebar_widget') )
		return;
 

	// Ausgabe auf der eigentlichen Seite!

	function top10_tagesgeld($args) {
	
		extract($args);
	
		$options = get_option('top10_tagesgeld');
	    $titel = htmlspecialchars($options['titel'], ENT_QUOTES);
		$text=get_option('top_10_tagesgeld_footer_text');
		$url=get_option('top_10_tagesgeld_footer_url');
	
		echo $before_widget;
		echo $before_title . $titel . $after_title;
	
		// Wenn keine Daten geladen, dann Link suchen...
		if(!isset($text) || $text=="" || $text==FALSE){
			if(@fopen("http://www.toptentagesgeld.de/widgetfooterlink.txt","r")){
				$file=file("http://www.toptentagesgeld.de/widgetfooterlink.txt");
				$zeilen=count($file);
				$zufall=rand(1,$zeilen);
	
				$row=0;
				$fp=fopen("http://www.toptentagesgeld.de/widgetfooterlink.txt","r");
				while($data=fgetcsv($fp,1000,";")){
					$row++;
					if($row==$zufall){
						add_option('top_10_tagesgeld_footer_text', $data[1], '', 'yes');
						add_option('top_10_tagesgeld_footer_url', $data[0], '', 'yes');
						$text=get_option('top_10_tagesgeld_footer_text');
						$url=get_option('top_10_tagesgeld_footer_url');
					}
				}
			}else{
				$text="Kann Datei nicht laden!";
				$url="";
			}
		}
	
	
		// Ausgabe des Widgets
	
		echo "<table class=tabelle_innen>";
	
		function sortmddata($array, $by, $order = 'DESC', $type = 'STRING') { 
	
	    			// $array  - Zu sortierendes Multidimensionales Array 
	    			// $by - Spalte nach der Sortiert werden soll 
	    			// $order - ASC (aufsteigend) oder DESC (absteigend) 
	    			// $type - NUM (numerisch) oder STRING 
	
	    			$sortby = "sort$by"; 
	    			$firstval = current($array); 
	    			$vals = array_keys($firstval); 
	
	    			foreach ($vals as $init) { 
	        			$keyname = "sort$init"; 
	        			$$keyname = array(); 
	    			} 
	    			foreach ($array as $key => $row) { 
	        			foreach ($vals as $names) { 
	            				$keyname = "sort$names"; 
	            				$test = array(); 
	            				$test[$key] = $row[$names]; 
	            				$$keyname = array_merge($$keyname, $test); 
	        			} 
	    			} 
	
	    			if ($order == "DESC") { 
	        			if ($type == "num") { 
	            				array_multisort($$sortby, SORT_DESC, SORT_NUMERIC, $array); 
	        			} else { 
	            				array_multisort($$sortby, SORT_DESC, SORT_STRING, $array); 
	        			} 
	    			} else { 
	        			if ($type == "num") { 
	            				array_multisort($$sortby, SORT_ASC, SORT_NUMERIC, $array); 
	        			} else { 
	            				array_multisort($$sortby, SORT_ASC, SORT_STRING, $array); 
	        			} 
	    			} 
	    			return $array; 
		} 
	
		if(@fopen("http://www.toptentagesgeld.de/top10.txt","r")){
			$fp=fopen("http://www.toptentagesgeld.de/top10.txt","r");
	
			while($data=fgetcsv($fp,1000,"#")){
				$csv[]=$data;
			}
	
			$sort_column = (isset($_GET['sortcolumn']) ? $_GET['sortcolumn'] : 0); 
			$sort_order  = (isset($_GET['sortorder']) ? $_GET['sortorder'] : 'ASC'); 
			$csv = sortmddata($csv, 1, DESC); 
	
			foreach ($csv as $row) {
				echo "<tr><td width=75%>$row[0]</td><td align=right width=25%>$row[1] %</td></tr>";
			}
			fclose($fp);
		} else {
			echo "<tr><td width=100%>Keine Daten gefunden!</td></tr>";
		}
	
		echo "</table>";
	
		// Footer-Link
		echo "<span style=\"font-size: 0.8em;\"><a href=\"$url\" >$text</a></span>";
	
	        echo $after_widget;
	}

 
	// Anzeige im Admin -> Widgets
	function top10_tagesgeld_control() {
	 
		// Auslesen der Optionen

		$options = get_option('top10_tagesgeld');

		// Wenn Optionen nicht angegeben, Default-Werte setzen

		if ( !is_array($options) )
		$options = array('titel'=>'TOP 10 Tagesgeld',
		                  'inhalt'=>'TOP 10 Tagesgeld');

		if ( $_POST['top10_tagesgeld-submit'] ) {
		     $options['titel'] = strip_tags(stripslashes($_POST['top10_tagesgeld-titel']));
		     update_option('top10_tagesgeld', $options);
		}

		$titel = htmlspecialchars($options['titel'], ENT_QUOTES);
		$inhalt = htmlspecialchars($options['inhalt'], ENT_QUOTES);
	 
		echo '
			<p style="text-align:right;"><label for="top10_tagesgeld-titel">Titel
			<input style="width: 150px;" id="top10_tagesgeld-titel" name="top10_tagesgeld-titel" type="text" value="'.$titel.'" /></label>
	 
		';
	
		echo '
			<input type="hidden" id="top10_tagesgeld-submit" name="top10_tagesgeld-submit" value="1" />';
   	}
	
	wp_register_sidebar_widget('top10_tagesgeld', 'TOP 10 Tagesgeld',
	                               'top10_tagesgeld',
	                            array(
	                                     'classname' => 'top10_tagesgeld',
	     	                             'description' =>'Anzeige einer TOP-10-Liste von Tagesgeld-Angeboten' ) );
	
	// Anzeige im Admin -> Widgets
	wp_register_widget_control('top10_tagesgeld', 'TOP 10 Tagesgeld',
	                               'top10_tagesgeld_control',
	                                array( 'width' => 200  ) );
}
 
add_action('widgets_init', 'top10_tagesgeld_init');
 
?>