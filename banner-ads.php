<?php
/*
 * Plugin Name:   Banner Ads
 * Version:       1.0
 * Plugin URI:    http://wordpress.org/extend/plugins/banner-ads/
 * Description:   Banner Ads plugin provides you with the feature to manage various banner ads from different affiliate programs along with the options to publish banner in sidebar widget section or in any blog post positions.It also provides additional features to order widget banners,variety of banner ad size with auto image resize feature. Adjust your settings <a href="options-general.php?page=mbp_ba_options">here</a>.
 * Author:        MaxBlogPress
 * Author URI:    http://www.maxblogpress.com
 *
 * License:       GNU General Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * Copyright (C) 2007 www.maxblogpress.com
 *
 * This is the improved version of "Wordpress-Banner" plugin by Alfredo Cubitos
 *
 */
$mbpba_path      = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
$mbpba_path      = str_replace('\\','/',$mbpba_path);
$mbpba_dir       = substr($mban_path,0,strrpos($mbpba_path,'/'));
$mbpba_siteurl   = get_bloginfo('wpurl');
$mbpba_siteurl   = (strpos($mbpba_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $mbpba_siteurl;
$mbpba_fullpath  = $mbpba_siteurl.'/wp-content/plugins/'.$mbpba_dir.'';
$mbpba_fullpath  = $mbpba_fullpath.'banner-ads/';
$mbpba_abspath   = str_replace("\\","/",ABSPATH); 

define('MBP_BA_ABSPATH', $mbpba_path);
define('MBP_BA_LIBPATH', $mbpba_fullpath);
define('MBP_BA_SITEURL', $mbpba_siteurl);
define('MBP_BA_NAME', 'Banner Ads');
define('MBP_BA_VERSION', '1.0');  
///define('MBP_BA_LIBPATH', $mbpba_fullpath);

// Hooks
add_action('admin_menu', 'mbp_ba_add_pages');
add_action ('wp_head','mbp_ba_prototype');
add_action('init', 'mbp_ba_widget_register');
add_action('wp_footer','mbp_ba_banner_display');
add_action('activate_' . $mbpba_path,'mbp_ba_banner_install');

//banner table create
function mbp_ba_banner_install() {
     global $wpdb, $table_prefix;
     $table = $table_prefix . "banner";
      $sql = "CREATE TABLE " . $table ."  (
  				banner_id int(10) unsigned NOT NULL auto_increment,
  				banner_clientname varchar(100) NOT NULL default '',
  				banner_clickurl varchar(150) NOT NULL default '',
  				banner_startdate int(10) unsigned NOT NULL default '0',
  				banner_enddate int(10) unsigned NOT NULL default '0',
  				banner_active tinyint(1) unsigned NOT NULL default '0',
  				banner_clicks int(10) unsigned NOT NULL default '0',
  				banner_url text NOT NULL,
  				banner_size varchar(10) NOT NULL default '',
  				banner_type varchar(10) NOT NULL default '',
  				banner_position varchar(25)  NOT NULL default '',
				sort_fld int(10) default NULL,
  				PRIMARY KEY  (`banner_id`)
				);";
     	mysql_query($sql);
 }

//client section
function mbp_ba_prototype() {
  wp_print_scripts('prototype');
  echo "<link rel=\"stylesheet\" href=\"" . MBP_BA_SITEURL ."/wp-content/plugins/banner-ads/styles/default.css\" type=\"text/css\" media=\"screen\" />\n";
  echo "<script src=\"" . MBP_BA_SITEURL . "/wp-content/plugins/banner-ads/js/tools.js\" type=\"text/javascript\"></script>";
}

function mbp_ba_banner_widget(){
	global $table_prefix;
    $query = "SELECT 
						* FROM ".$table_prefix."banner 
					WHERE 
						banner_active=1 
						AND (banner_startdate=0 OR banner_startdate<=".time().") 
						AND (banner_enddate=0 OR banner_enddate>".time().") 
						AND banner_position='widget' 
					ORDER BY sort_fld ASC";
	$sql   = mysql_query($query);
	$i=0;
	while($rs = mysql_fetch_array($sql)){
		$i++;
		$banner_size = $rs['banner_size'];
		
		if ($banner_size != '') {
			$banner_explode = explode("x",$banner_size);
			$width  = $banner_explode[0];
			$height = $banner_explode[1];
			@list($width_orig, $height_orig) = @getimagesize($rs['banner_url']);
		
			if ($width_orig < $width || $height_orig < $height) {
				$width  = $width_orig;
				$height = $height_orig; 
			} else {
				if ($width && ($width_orig < $height_orig)) {
					$width = ($height / $height_orig) * $width_orig;
				} else {
					$height = ($width / $width_orig) * $height_orig;
				}	
			}	
			$dimension = "width='" . $width . "' height='" . $height . "'";			
		} else {
			$dimension = "";			
		}
?>
	<div id="<?php echo 'widget' . $i;?>">
		<div id="<?php echo 'bannerWidget' . $i?>">
			<?php if ($rs['banner_clickurl'] == '') {?>
				<img src="<?php echo $rs['banner_url'];?>" <?php echo $dimension;?> border="0" />
			<?php } else { ?>
				<a onclick="BannerClick('<?php echo $rs["banner_id"]?>')" href="<?php echo $rs['banner_clickurl']?>" target="_blank">	
					<img src="<?php echo $rs['banner_url'];?>" <?php echo $dimension;?> border="0" />
				</a>
			<?php } ?>
		</div>
		<?php echo $widget_body;?>
	</div><br/>

<?php		
	} 		
}

function mbp_ba_widget_register(){
	register_sidebar_widget ('Banner Ads','mbp_ba_widget');
	register_widget_control('Banner Ads', 'mbp_ba_widget_control');
}

function mbp_ba_widget($args = array()) {
    global $table_prefix;
	$query = "SELECT 
						* FROM ".$table_prefix."banner 
					WHERE 
						banner_active=1 
						AND (banner_startdate=0 OR banner_startdate<=".time().") 
						AND (banner_enddate=0 OR banner_enddate>".time().") 
						AND banner_position='widget' 
					ORDER BY sort_fld ASC";
	$sql   = mysql_query($query);
	$no	   = mysql_num_rows($sql);
	if ($no>0) {		
		extract($args);
		$options = get_option('mbp_ba_widget_title');
		$title	 = ($options['mbp_ba_widget_title'] == '')?'':$options['mbp_ba_widget_title'];
		echo $before_widget . $before_title . $title . $after_title;
		mbp_ba_banner_widget();
		echo $after_widget;
	}	
}

function mbp_ba_widget_control() {
	$options = get_option('mbp_ba_widget_title');
	if ($_POST['mbp_ba_widget_title_submit']) {
		$options['mbp_ba_widget_title'] = $_POST['mbp_ba_widget_title'];
		update_option('mbp_ba_widget_title', $options);
	}
	
	$mbp_ba_widget_title = $options['mbp_ba_widget_title'];
?>
<p>
	<label>Title</label>
	<input size="30" type="text" name="mbp_ba_widget_title" value="<?php echo $mbp_ba_widget_title; ?>"/>
 	<input type="hidden" id="mbp_ba_widget_title_submit" name="mbp_ba_widget_title_submit"  value="1" />	
</p>
<?php } 
//banner image display 
function mbp_ba_banner_display() {
	global $table_prefix,$post;
	
	//tweak for js
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 0;
	if ($paged == 0) {
		$factor = 0;
	} else {
		$factor = $paged-1;
	}
	
	$limit1 =  $factor * 10;
	$limit2 =  $limit1 + 10;
	$query_wp = "SELECT 
							ID 
					 FROM 
					 		" . $table_prefix . "posts 
					 WHERE 
					 	post_type='post' 
						AND post_status='publish'
						ORDER BY ID DESC limit " . $limit1 . "," . $limit2;
		$sql_wp  = mysql_query($query_wp);
		while($rs_wp   = mysql_fetch_array($sql_wp)) {
			$arr_wp_postid[] = $rs_wp['ID'];
		}						
	
    $query = "SELECT 
					* FROM ".$table_prefix."banner 
			  WHERE 
			  		banner_active=1 
					AND (banner_startdate=0 OR banner_startdate<=".time().") 
					AND (banner_enddate=0 OR banner_enddate>".time().") 
					AND banner_position!='widget'";
	$sql   = mysql_query($query);
	$cnt   = 0;
	while($rs	   = mysql_fetch_array($sql)) {
		$post_id	 = substr($rs['banner_position'],5);
		$banner_size = $rs['banner_size'];
		$banner_link = ($rs['banner_clickurl'] == '')?"":"<a href=\"".$rs['banner_clickurl']."\" TARGET=\"_blank\"";
		if ($banner_size != '') {
			$banner_explode = explode("x",$banner_size);
			$width  = $banner_explode[0];
			$height = $banner_explode[1];
			@list($width_orig, $height_orig) = @getimagesize($rs['banner_url']);
		
			if ($width_orig < $width || $height_orig < $height) {
				$width  = $width_orig;
				$height = $height_orig; 
			} else {
				if ($width && ($width_orig < $height_orig)) {
					$width = ($height / $height_orig) * $width_orig;
				} else {
					$height = ($width / $width_orig) * $height_orig;
				}	
			}	
			$dimension = "width='" . $width . "' height='" . $height . "'";			
		} else {
			$dimension = "";			
		}		
		?>

	<?php
	if (@in_array($post_id,$arr_wp_postid)) {
		
	?>
		
	<div id="bannerdiv<?php echo $cnt;?>">	
		<?php if ($rs['banner_clickurl'] == '') {?>
			<img src="<?php echo $rs['banner_url'];?>" <?php echo $dimension;?> border="0" />
		<?php } else { ?>
			<a onclick="BannerClick('<?php echo $rs["banner_id"]?>')" href="<?php echo $rs['banner_clickurl']?>" target="_blank">	
				<img src="<?php echo $rs['banner_url'];?>" <?php echo $dimension;?> border="0" />
			</a>
		<?php } ?>		
	</div>
		
		<?php
		 	echo "<script>InsertElement('id','" . $rs['banner_position'] ."','bannerdiv" . $cnt."')</script>";
		}			
		 $cnt++;
	}								
}

/**
 * Add admin pages.
 */
function mbp_ba_add_pages() {
	add_options_page('Banner Ads', 'Banner Ads', 8, 'mbp_ba_options', 'mbp_ba_options');
}

function mbp_ba_options() {
	
	$mbp_ba_activate = get_option('mbp_ba_activate');
	$reg_msg = '';
	$mbp_ba_msg = '';
	$form_1 = 'mbp_ba_reg_form_1';
	$form_2 = 'mbp_ba_reg_form_2';
		// Activate the plugin if email already on list
	if ( trim($_GET['mbp_onlist']) == 1 ) {
		$mbp_ba_activate = 2;
		update_option('mbp_ba_activate', $mbp_ba_activate);
		$reg_msg = 'Thank you for registering the plugin. It has been activated'; 
	} 
	// If registration form is successfully submitted
	if ( ((trim($_GET['submit']) != '' && trim($_GET['from']) != '') || trim($_GET['submit_again']) != '') && $mbp_ba_activate != 2 ) { 
		update_option('mbp_ba_name', $_GET['name']);
		update_option('mbp_ba_email', $_GET['from']);
		$mbp_ba_activate = 1;
		update_option('mbp_ba_activate', $mbp_ba_activate);
	}
	if ( intval($mbp_ba_activate) == 0 ) { // First step of plugin registration
		global $userdata;
		mbp_baRegisterStep1($form_1,$userdata);
	} else if ( intval($mbp_ba_activate) == 1 ) { // Second step of plugin registration
		$name  = get_option('mbp_ba_name');
		$email = get_option('mbp_ba_email');
		mbp_baRegisterStep2($form_2,$name,$email);
	} else if ( intval($mbp_ba_activate) == 2 ) { // Options page
		if ( trim($reg_msg) != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$reg_msg.'</strong></p></div>';
		}			
	
	global $table_prefix;
//form submission
if ($_POST['Submit'] == 'Add Banner') {
	global $start_date,$end_date;
	$banner_url 		= $_POST['banner_url'];
	$click_url			= $_POST['click_url'];
	$banner_size		= $_POST['banner_size'];
	$banner_position	= ($_POST['banner_position'] == 'widget')?'widget' : $_POST['post_id'];
	$banner_enabled		= $_POST['banner_enabled'];
	
    if(!$_POST['startmonth'] 
		|| !$_POST['startday'] 
		|| !$_POST['startyear'] ? $start_date = 0 : $start_date = mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
    if(!$_POST['endmonth'] 
		|| !$_POST['endday'] 
		|| !$_POST['endyear'] ? $end_date = 0 : $end_date = mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));	
	
	$query = "INSERT INTO `" . $table_prefix . 'banner' . "`(`banner_clickurl`,
												`banner_startdate`,
												`banner_enddate`,
												`banner_active`,
												`banner_url`,
												`banner_size`,
												`banner_type`,
												`banner_position`
												)
				VALUES('$click_url',
					'$start_date', 
					'$end_date',
					'$banner_enabled',
					'$banner_url',
					'$banner_size',
					'$banner_type',
					'$banner_position'
					)";
	mysql_query($query);
	
	//for updating sort_fld
	$query_sort_fld = "UPDATE " . $table_prefix . "banner SET sort_fld='" . mysql_insert_id() . "' WHERE banner_id='" . mysql_insert_id() . "'";
	$sql_sort_fld   = mysql_query($query_sort_fld);		
	echo '<script type="text/javascript">';
	echo 'window.location.href="?page=mbp_ba_options&mess=add";';
	echo '</script>';		
}

if ($_POST['Submit'] == 'Edit Banner') {
    if(!$_POST['startmonth'] 
		|| !$_POST['startday'] 
		|| !$_POST['startyear'] ? $start_date = 0 : $start_date = mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
    if(!$_POST['endmonth'] 
		|| !$_POST['endday'] 
		|| !$_POST['endyear'] ? $end_date = 0 : $end_date = mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));	

	$banner_url 		= $_POST['banner_url'];
	$click_url			= $_POST['click_url'];
	$banner_size		= $_POST['banner_size'];
	$banner_position	= ($_POST['banner_position'] == 'widget')?'widget' : $_POST['post_id'];
	$banner_enabled		= $_POST['banner_enabled'];
	$banner_id			= $_POST['banner_id'];
	
	$query_update 		= "UPDATE 
								" . $table_prefix . "banner 
						    SET
								banner_url='" . $banner_url . "',
								banner_clickurl='" . $click_url . "',
								banner_size='" . $banner_size . "',
								banner_position='" . $banner_position . "',
								banner_active='" . $banner_enabled . "',
								banner_startdate='" . $start_date . "',
								banner_enddate='" . $end_date . "' 
							WHERE banner_id='" . $banner_id . "'";	
	mysql_query($query_update)or die(mysql_error());
	echo '<script type="text/javascript">';
	echo 'window.location.href="?page=mbp_ba_options&mess=edit";';
	echo '</script>';						
}
?>

<div class="wrap">	
<h2><?php echo MBP_BA_NAME.' '.MBP_BA_VERSION; ?></h2>

<strong>
		<img src="<?php echo MBP_BA_LIBPATH;?>images/how.gif" border="0" align="absmiddle" /> 
		<a href="http://wordpress.org/extend/plugins/banner-ads/other_notes/" target="_blank">How to use it</a>&nbsp;&nbsp;&nbsp;
		<img src="<?php echo MBP_BA_LIBPATH;?>images/commentimg.gif" border="0" align="absmiddle" /> 
		<a href="http://www.maxblogpress.com/forum/forumdisplay.php?f=37" target="_blank">Community</a>
		&nbsp;&nbsp;&nbsp;
		<img src="<?php echo MBP_BA_LIBPATH;?>images/helpimg.gif" border="0" align="absmiddle" /> 
		<a href="http://www.maxblogpress.com/revived-plugins/" target="_blank">View our revived plugins</a>
</strong>		
</br><br/></br><br/>
<script type="text/javascript">
function validateBannerForm() {
	var tomatch= /http:\/\/[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/;
 	var v = new RegExp();
    v.compile("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$"); 	
	
	if (document.getElementById('banner_url').value == '') {
		alert("Please enter the banner url");
		return false;
	}
	
	if (document.getElementById('banner_position').value == 0) {
		alert("Please select a banner position");
		return false;
	}
}

	function selectTag(id) {
		if (id == 'id') {
			document.getElementById('post_ids').style.display = 'block';
		} else {
			document.getElementById('post_ids').style.display = 'none';
		}

}
</script>
<?php
//delete operation
if ($_GET['action'] == 'delete') {
	$query = "DELETE FROM " . $table_prefix . "banner WHERE banner_id='" . $_GET['id'] . "'";
	$sql   = mysql_query($query);
	echo '<script type="text/javascript">';
	echo 'window.location.href="?page=mbp_ba_options&mess=delete";';
	echo '</script>';		
}

//reset hits
if ($_GET['action'] == 'reset') {
	$query = "UPDATE " . $table_prefix  . "banner SET banner_clicks='0' WHERE banner_id='" . $_GET['id'] . "'";
	$sql   = mysql_query($query);
	echo '<script type="text/javascript">';
	echo 'window.location.href="?page=mbp_ba_options";';
	echo '</script>';		
}
//default page listing 
 if (!$_GET['action']) { 
?>
<script type="text/javascript">
function confirmDelete(id) {
	var con = window.confirm("Are you sure to delete?");
	if (con == true) {
		window.location.href = "?page=mbp_ba_options&action=delete&id="+ id;
	} else {
		return false;
	}
}
</script>

<script type="text/javascript" src="<?php echo MBP_BA_LIBPATH . 'js/wz_tooltip.js'?>"></script>
<style type="text/css">
<!--
.style1 {
	color: #000;
	font-weight: bold;
	font-size:13px;
	background-color:#f1f1f1;
	height:37px;
}

#pagination {
	font-size:11px;
	margin:0;
	padding:10px 0;
	width:100%;
}
-->
</style>
<?php
if ($_GET['mess'] == 'add') {
	$message = 'Banner added';
} else if($_GET['mess'] == 'edit') {
	$message = 'Banner edited';
} else if($_GET['mess'] == 'delete') {
	$message = 'Banner deleted';
}
if ($_GET['mess']) {
?>
	<div class="updated"><?php echo $message;?></div>
<?php } ?>	
<table border="0" width="100%" bgcolor="#f1f1f1" style="border:1px solid #e5e5e5">
     <tr>
       <td colspan="7" style="padding:3px 3px 3px 3px; background-color:#f1f1f1" align="right">
	   <input type="button" value="Add New Banner Ad" onclick="window.location.href='?page=mbp_ba_options&action=add'" />
	   <input name="button2" type="button" onclick="window.location.href='?page=mbp_ba_options&amp;action=widget_order'" value="Rearrange Widget Ad  Position" /></td>
    </tr>
	  
	
     <tr >
	   <td width="4%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><span class="style1">S.no</span></td>
	    <td width="6%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><span class="style1">Banner </span></td>
	    <td width="26%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><span class="style1">Link URL</span></td>
       <td width="19%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center"><span class="style1">Banner Type</span></div></td>
       <td width="13%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center"><span class="style1">Status</span></div></td>
       <td width="18%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center" class="style1">Hits</div></td>
       <td width="14%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center"><span class="style1">Action</span></div></td>
     </tr>
	 <?php
	$max_banner  = 20;
	$page_banner = 0;
	
	if (isset($_GET['pagee'])) {
	  $page_banner = $_GET['pagee'];
	}
	$start_banner = $page_banner * $max_banner;	 
	 
	$query_Rs_banner = "SELECT *FROM " . $table_prefix . "banner";
	$query_limit_Rs_banner = sprintf("%s LIMIT %d, %d", $query_Rs_banner, $start_banner, $max_banner);
	
	$Rs_banner = mysql_query($query_limit_Rs_banner) or die("Error");
	
	if (isset($_GET['total_banner']) && ($_GET['total_banner']!='')) {
	  $total_banner = $_GET['total_banner'];
	} else {
	
	  $all_Rs_banner = mysql_query($query_Rs_banner);
	  $total_banner = mysql_num_rows($all_Rs_banner);
	}
	
	$totalPages = ceil($total_banner/$max_banner)-1;	 
	 
	 //for active/inactive test in front end
    $query_activate_test = "SELECT 
									banner_id
							FROM 
								".$table_prefix."banner 
							WHERE 
								banner_active=1 
								AND (banner_startdate=0 OR banner_startdate<=".time().") 
								AND (banner_enddate=0 OR banner_enddate>".time().")";	 
	$sql_activate_test = mysql_query($query_activate_test);
	while($rs_activate_test = mysql_fetch_array($sql_activate_test)) {
		$arr_activate_id[] = $rs_activate_test['banner_id'];
	}
	 
	 if($total_banner > 0 ){
	 $i = 1;
	 while($row_Rs_banner = mysql_fetch_array($Rs_banner)) {
	 	$banner_type 		= ($row_Rs_banner['banner_position'] == 'widget')?"Widget":"Post";
		
		if (@in_array($row_Rs_banner['banner_id'], $arr_activate_id)) {
			$banner_status = "Active";
			$status_color  = "green";
		} else {
			$banner_status = "InActive";
			$status_color  = "red";
		}
		
		$banner_size		= ($row_Rs_banner['banner_size'] == '')?"Not Specified":$row_Rs_banner['banner_size'];
		$banner_position	= ($row_Rs_banner['banner_position'] == 'widget')?"Widget": "Post " . substr($row_Rs_banner['banner_position'],5);

		if ($row_Rs_banner['banner_startdate'] > 0) {
			$tmp 	 		= getdate($row_Rs_banner['banner_startdate']);
			$startmonth  	= $tmp['mon'];
			$startday 	 	= $tmp['mday'];
			$startyear   	= $tmp['year'];	
		}
		
		if ($row_Rs_banner['banner_enddate'] > 0) {
			$tmp_end 		= getdate($row_Rs_banner['banner_enddate']);
			$endmonth  		= $tmp_end['mon'];
			$endday 	 	= $tmp_end['mday'];
			$endyear   		= $tmp_end['year'];		
		}				
		$tooltip_info = "Start Date:" . $startyear . "-" . $startmonth . "-" . $startday . "<br/>";
		$tooltip_info.= "End Date:" . $endyear . "-" . $endmonth . "-" . $endday . "<br/>";
		$tooltip_info.= "Banner Size:" . $banner_size . "<br/>";
		$tooltip_info.= "Banner Position:" . $banner_position . "<br/>";
		$tooltip_info.= "Banner Status:" . $banner_status . "<br/>";
		
		//for resize options
		$width  = 60;
		$height = 60;
		
		@list($width_orig, $height_orig) = @getimagesize($row_Rs_banner['banner_url']);
		
		if ($width_orig < $width || $height_orig < $height) {
			$width  = $width_orig;
			$height = $height_orig; 
		} else {
			if ($width && ($width_orig < $height_orig)) {
				$width = ($height / $height_orig) * $width_orig;
			} else {
				$height = ($width / $width_orig) * $height_orig;
			}	
		}		
	 ?>
     <tr >
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center"><?php echo $i; ?></div></td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1">
	   	<a onmouseover="Tip('<?php echo $tooltip_info; ?>');" onmouseout="UnTip();" href="#">	
			<img border="0" src="<?php echo $row_Rs_banner['banner_url'];?>" width="<?php echo $width;?>" height="<?php echo $height;?>" />	 </a>	   </td>
      
	   <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><?php echo $row_Rs_banner['banner_clickurl'];?></td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center"><?php echo $banner_type;?></div></td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1">
	   <div align="center">
	   	<?php if ($banner_status == 'Active') {?>
			<img src="<?php echo MBP_BA_LIBPATH .  'images/active.gif';?>" border="0" alt="Active" title="Active" />
		<?php } else if ($banner_status == 'InActive') { ?>
	   		<img src="<?php echo MBP_BA_LIBPATH .  'images/inactive.gif';?>" border="0" alt="InActive" title="InActive" />
	    <?php } ?>
		</div>
	   </td>
       <td style="background-color:#f1f1f1">
	   <div align="center"><?php echo $row_Rs_banner['banner_clicks'];?>&nbsp;&nbsp; <a href="?page=mbp_ba_options&action=reset&id=<?php echo $row_Rs_banner['banner_id']; ?>"  style='text-decoration:none;color: #006600'><img align="Reset Hits" title="Reset Hits" border="0" src="<?php echo MBP_BA_LIBPATH .  'images/reset.gif'?>" /></a>  </div>	
	   
	    </td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1">
	   <div align="center">
	    
	   <a href="?page=mbp_ba_options&action=edit&id=<?php echo $row_Rs_banner['banner_id']; ?>"  style='text-decoration:none;color: #006600'><img align="Edit" title="Edit" border="0" src="<?php echo MBP_BA_LIBPATH .  'images/edit.gif'?>" /></a>
	   &nbsp;&nbsp;&nbsp;
	    <a href="javascript:void(0);" onclick=" return confirmDelete('<?php echo $row_Rs_banner['banner_id']; ?>')" style='text-decoration:none;color:#CC0000'><img align="Delete" title="Delete" border="0" src="<?php echo MBP_BA_LIBPATH .  'images/delete.gif'?>" /></a> </div></td>
     </tr>
	 <?php $i++; } } else { ?>
     <tr >
       <td colspan="7" style="padding:3px 3px 3px 3px; background-color:#fff">
	   <div align="center">No Banner Yet</div>	   </td>
    </tr>
	 <?php } ?>
  </table>
<div id="pagination">
<?php
for($p=0; $p<=$totalPages; $p++)
{
	$page_no=$p+1;
	if($page_banner==$p)echo "<strong>Pages: </strong>"."<em>$page_no </em> ";
	else echo "<a href=\"?page=mbp_ba_options&pagee=$p&total_banner=$total_banner\">$page_no</a>";
}
?>
</div>  
<?php 
} // default listing page
?>

<?php
//banner add section panel
if ($_GET['action'] == 'add' || $_GET['action'] == 'edit') {
	global $table_prefix;
	$button_label = ($_GET['action'] == 'add')?'Add Banner':'Edit Banner';
if ($_GET['action'] == 'edit') {
	$query_banner_edit = "SELECT *FROM " .$table_prefix . "banner WHERE banner_id='" . $_GET['id'] . "'";
	$sql_banner_edit   = mysql_query($query_banner_edit);
	$rs_banner_edit	   = mysql_fetch_array($sql_banner_edit);
}
?>
<form name="add_banner" method="post" action="">
<input type="hidden" name="banner_id" value="<?php echo $_GET['id'];?>"/>
<table border="0" width="100%" bgcolor="#f1f1f1" style="border:1px solid #e5e5e5">
     <tr >
       <td colspan="2" style="padding:4px 4px 4px 4px;background-color:#f1f1f1; color:#0066CC"><div align="left">
         <label><span style="padding:3px 3px 3px 3px; background-color:#f1f1f1">
         <input name="button222" type="button" onclick="window.location.href='?page=mbp_ba_options'" value="HOME" />
         <!--<input name="button23" type="button" onclick="window.location.href='?page=mbp_ba_options&amp;action=widget_order'" value="Rearrange Widget Ad  Position" />-->
         </span></label>
       </div></td>
     </tr>
     <tr >
       <td colspan="2" style="padding:4px 4px 4px 4px;background-color:#f1f1f1; color:#0066CC"><strong>
	   
	   <?php echo ( $_GET['action'] == 'add'?'Add':'Edit') ?>   banner >> <font color="#CC3300"><?php if($msg){ echo $msg; } ?></font></strong></td>
      </tr>
     
     <tr>
       <td width="18%" style="padding:3px 3px 3px 3px; background-color:#fff"><strong>Banner URL:</strong></td>
       <td width="82%" style="padding:3px 3px 3px 3px; background-color:#fff"><label>
         <input name="banner_url" value="<?php echo $rs_banner_edit['banner_url'];?>" type="text" id="banner_url" size="50" />
       *</label></td>
     </tr>
     <tr>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><strong>Affiliate URL:</strong> </td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><input name="click_url" value="<?php echo $rs_banner_edit['banner_clickurl'];?>" type="text" id="click_url" size="50" /></td>
     </tr>
     <tr>
       <td style="padding:3px 3px 3px 3px; background-color:#fff"><span style="padding:3px 3px 3px 3px; background-color:#fff"><strong>Position:</strong></span></td>
       <td style="padding:3px 3px 3px 3px; background-color:#fff"><span style="padding:3px 3px 3px 3px; background-color:#fff">
         <select name="banner_position" id="banner_position" onchange="return selectTag(this.value);">
           <option value="0">-Select-</option>
           <option <?php if($rs_banner_edit['banner_position'] == 'widget') { echo 'selected';}?> value="widget">widget</option>
           <option <?php if(substr($rs_banner_edit['banner_position'],0,4) == 'post') { echo 'selected';}?> value="id">post</option>
          </select>
       </span>
	   
	   <?php 
	  	 $div_style = (substr($rs_banner_edit['banner_position'],0,4) == 'post')?'block':'none';
	   ?>
	   
	   <div id="post_ids" style="display:<?php echo $div_style;?>">
	   <?php 
			if ($_GET['action'] == 'add') {	   	
				$query_postid = "SELECT 
										CONCAT('post-',ID)as post_ids,
										CONCAT('Post',ID)as post_name 
								  FROM " . $table_prefix ."posts 
								  WHERE 
										post_type='post' 
										AND post_status='publish'
										AND ID NOT IN (SELECT SUBSTR(banner_position,6) FROM " . $table_prefix. "banner WHERE banner_position != 'widget')";
				$sql_postid	  = mysql_query($query_postid);
			} else {
			$query_postid = "SELECT 
									CONCAT('post-',ID)as post_ids,
									CONCAT('Post',ID)as post_name 
							  FROM ". $table_prefix ."posts 
							  WHERE 
									post_type='post' 
									AND post_status='publish'";
			$sql_postid	  = mysql_query($query_postid);				
			}
	   ?>
	   		<select name="post_id" id="post_id">
			<?php while($rs_postid = mysql_fetch_array($sql_postid)) {?>	
				<option <?php if ($rs_banner_edit['banner_position'] == $rs_postid['post_ids']) { echo 'selected';}?> value="<?php echo $rs_postid['post_ids'];?>"><?php echo $rs_postid['post_name'];?></option>
			<?php } ?>
			</select>
	   </div>	   </td>
     </tr>
     <tr>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><strong>Banner Size:</strong> </td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><label>
         <select name="banner_size" id="banner_size">
           <option value="">Select</option>
		   <option <?php if ($rs_banner_edit['banner_size'] == '480x60') { echo 'selected';}?> value="480x60">480x60</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '728x90') { echo 'selected';}?> value="728x90">728x90</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '234x60') { echo 'selected';}?> value="234x60">234x60</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '120x600') { echo 'selected';}?> value="120x600">120x600</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '120x60') { echo 'selected';}?> value="120x60">120x60</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '88x31') { echo 'selected';}?> value="88x31">88x31</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '122x80') { echo 'selected';}?> value="122x80">122x80</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '277x134') { echo 'selected';}?> value="277x134">277x134</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '208x102') { echo 'selected';}?> value="208x102">208x102</option>
           <option <?php if ($rs_banner_edit['banner_size'] == '104x51') { echo 'selected';}?> value="104x51">104x51</option>
         </select>
       </label></td>
     </tr>
     <tr>
       <td style="padding:3px 3px 3px 3px; background-color:#fff"><strong>Start Date:</strong> </td>
       <td style="padding:3px 3px 3px 3px; background-color:#fff">
        <?php
		if ($_GET['action'] == 'edit') {
			if ($rs_banner_edit['banner_startdate'] > 0) {
				$tmp 	 = getdate($rs_banner_edit['banner_startdate']);
				$tmp_end = getdate($rs_banner_edit['banner_enddate']);
				$startmonth  = $tmp['mon'];
				$startday 	 = $tmp['mday'];
				$startyear   = $tmp['year'];	
				
				$endmonth  = $tmp_end['mon'];
				$endday    = $tmp_end['mday'];
				$endyear   = $tmp_end['year'];							
			} else {
            	$startmonth = 0;
            	$startday   = 0;
            	$startyear  = 0;	
				
            	$endmonth = 0;
            	$endday   = 0;
            	$endyear  = 0;								
			}
		} else {
        	$tmp = getdate();
        	$startmonth = $tmp['mon'];
        	$startday   = $tmp['mday'];
        	$startyear  = $tmp['year'];			
		}
		?>Day: 
		 <select name="startday">
         <option></option>
		<?php  
			for($i=1;$i<32;$i++) { 	
		?>		
			<option <?php if ($i == $startday) { echo 'selected';}?> value="<?php echo $i;?>"><?php echo $i;?></option>
		<?php } ?>
		 </select>
         
		 Month: 
		 <select name="startmonth">
         <option></option>
		 <?php for($i=1;$i<13;$i++) { ?>		
				<option <?php if ($i == $startmonth) { echo 'selected';}?> value="<?php echo $i;?>"><?php echo $i;?></option>
		 <?php  
		 	}
		 ?>
		 </select>
Year:		 
         <select name="startyear">
         <option></option>
		 <?php for($i=2009;$i<=2011;$i++) { ?>
				<option <?php if ($i == $startyear) { echo 'selected';}?> value="<?php echo $i;?>">		<?php echo $i;?>				</option>		
		 <?php } ?>
		 </select>	    </td>
     </tr>
     <tr>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><strong>End Date:</strong> </td>
       <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1">
         Day: 
         <select name="endday">
		 <option></option>
			<?php  
				for($i=1;$i<32;$i++) { 	
			?>		
				<option <?php if ($i == $endday) { echo 'selected';}?> value="<?php echo $i;?>"><?php echo $i;?></option>
			<?php } ?>       
		 </select>
Month:         
<select name="endmonth">
		 <option></option>
			 <?php for($i=1;$i<13;$i++) { ?>		
					<option <?php if ($i == $endmonth) { echo 'selected';}?> value="<?php echo $i;?>"><?php echo $i;?></option>
			 <?php  
				}
			 ?>	 
         </select>
         Year: 
         <select name="endyear">
		 <option></option>
			 <?php for($i=2009;$i<=2011;$i++) { ?>
					<option <?php if ($i == $endyear) { echo 'selected';}?> value="<?php echo $i;?>">
						<?php echo $i;?>					</option>		
			 <?php } ?>	 
         </select>      </td>
     </tr>
     
     <tr>
       <td style="padding:3px 3px 3px 3px; background-color:#fff"><strong>Active:</strong></td>
       <td style="padding:3px 3px 3px 3px; background-color:#fff">
         
		 <input name="banner_enabled" type="radio" <?php if ($rs_banner_edit['banner_active'] == 1) { echo 'checked';}?> value="1" />
         <strong>Yes</strong> 
       <input name="banner_enabled" type="radio"  <?php if ($rs_banner_edit['banner_active'] == 0) { echo 'checked';}?> value="0" />
       <strong>No</strong></td>
     </tr>
     <tr>
      <td colspan="2" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><input name="Submit" type="submit" class="button" id="Submit" value="<?php echo $button_label;?>" onclick="return validateBannerForm();" /></td>
     </tr>
    </table>
</form>
<?php } //add section ?>

<?php 
if ($_GET['action'] == 'widget_order') {
$max_widget  = 100;
$page_widget = 0;

if (isset($_GET['pagee'])) {
  $page_widget = $_GET['pagee'];
}
$start_widget = $page_widget * $max_widget;
?>
<script type="text/javascript" src="<?php echo MBP_BA_LIBPATH . 'js/wz_tooltip.js'?>"></script>
<style type="text/css">
<!--
.style1 {
	color: #0066CC;
	font-weight: bold;
	background-color:#f1f1f1;
	height:33px;
}
<!--
#pagination {
	font-size:11px;
	margin:0;
	padding:10px 0;
	width:100%;
}
-->
</style>
<table border="0" width="100%" bgcolor="#f1f1f1" style="border:1px solid #e5e5e5">
  <tr>
    <td style="padding:3px 3px 3px 3px; background-color:#fff" align="right"><div align="left">
      <input name="button22" type="button" onclick="window.location.href='?page=mbp_ba_options'" value="Home" />
    </div></td>
    <td colspan="2" align="right" style="padding:3px 3px 3px 3px; background-color:#fff"><input name="button" type="button" onclick="window.location.href='?page=mbp_ba_options&amp;action=add'" value="Add New Banner Ad" /></td>
    </tr>

  <tr >
    <td colspan="3" style="padding:3px 3px 3px 3px; background-color:#f1f1f1" align="left">	 <font color="#0066CC"><strong>Widget Ordering >></strong>	 </font></td>
  </tr>

  <tr >
    <td width="6%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><span class="style1">S.no</span></td>
    <td width="28%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><span class="style1">Banner Url </span></td>
    <td width="20%" style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><span class="style1">Ordering</span></td>
    </tr>
  <?php
	$query_Rs_widget = "SELECT *FROM " . $table_prefix . "banner WHERE banner_position='widget' ORDER BY sort_fld ASC";
	$query_limit_Rs_widget = sprintf("%s LIMIT %d, %d", $query_Rs_widget, $start_widget, $max_widget);
	
	$Rs_widget = mysql_query($query_limit_Rs_widget) or die("Error");
	
	if (isset($_GET['total_widget']) && ($_GET['total_widget']!='')) {
	  $total_widget = $_GET['total_widget'];
	} else {
	
	  $all_Rs_widget = mysql_query($query_Rs_widget);
	  $total_widget = mysql_num_rows($all_Rs_widget);
	}
	
	$totalPages = ceil($total_widget/$max_widget)-1;
	 //sorting
	 if (isset($_GET['sort'])) {
	 	if ($_GET['sort'] == 'up') {
			$query_sort = "SELECT 
								banner_id as id, 
								sort_fld 
						  FROM 
						  		" . $table_prefix . "banner 
						   WHERE 
						   		sort_fld < " . $_GET['order'] . " 
						   AND banner_position='widget' 								
						   ORDER BY 
						   			sort_fld DESC";									
		} else {
			$query_sort = "SELECT 
								banner_id as id, 
								sort_fld FROM " . $table_prefix . "banner 
						   WHERE 
						   		sort_fld > " . $_GET['order'] . " 
								AND banner_position='widget' 
						   ORDER BY 
						   			sort_fld";
		}
		
		$sql_sort = mysql_query($query_sort);
		$rs_sort  = mysql_fetch_array($sql_sort);
			
		$query_sort2 = "UPDATE 
							" . $table_prefix . "banner 
					    SET 
							sort_fld = '" . $rs_sort['sort_fld'] . "' 
						WHERE 
							banner_id= '" . $_GET['id'] . "'";
							
		$query_sort3 = "UPDATE 
								" . $table_prefix . "banner 
						SET 
							sort_fld = '" . $_GET['order'] . "' 
						WHERE 
							banner_id = '" . $rs_sort['id'] . "'";
		
		$sql_sort2	 = mysql_query($query_sort2)or die(mysql_error());
		$sql_sort3	 = mysql_query($query_sort3)or die(mysql_error());
		echo '<script type="text/javascript">';
		echo 'window.location.href="?page=mbp_ba_options&action=widget_order";';
		echo '</script>';		
	 }
	 
	 if($total_widget > 0 ){
	 $i = 1;
	 while($row_Rs_widget = mysql_fetch_array($Rs_widget)) {
	 	$banner_type 		= ($row_Rs_widget['banner_position'] == 'widget')?"Widget":"Post";
		$banner_status 		= ($row_Rs_widget['banner_active'] == '0')?"InActive":"Active";
		$banner_size		= ($row_Rs_widget['banner_size'] == '')?"Not Specified":$row_Rs_widget['banner_size'];
		$banner_position	= ($row_Rs_widget['banner_position'] == 'widget')?"Widget": "Post " . substr($row_Rs_widget['banner_position'],5);

		if ($row_Rs_widget['banner_startdate'] > 0) {
			$tmp 	 		= getdate($row_Rs_widget['banner_startdate']);
			$startmonth  	= $tmp['mon'];
			$startday 	 	= $tmp['mday'];
			$startyear   	= $tmp['year'];	
		}
		
		if ($row_Rs_widget['banner_enddate'] > 0) {
			$tmp_end 		= getdate($row_Rs_widget['banner_enddate']);
			$endmonth  		= $tmp_end['mon'];
			$endday 	 	= $tmp_end['mday'];
			$endyear   		= $tmp_end['year'];		
		}		
		
		$tooltip_info = "Start Date:" . $startyear . "-" . $startmonth . "-" . $startday . "<br/>";
		$tooltip_info.= "End Date:" . $endyear . "-" . $endmonth . "-" . $endday . "<br/>";
		$tooltip_info.= "Banner Size:" . $banner_size . "<br/>";
		$tooltip_info.= "Banner Position:" . $banner_position . "<br/>";
		$tooltip_info.= "Banner Status:" . $banner_status . "<br/>";	 	
		//for resize options
		$width  = 60;
		$height = 60;
		
		@list($width_orig, $height_orig) = @getimagesize($row_Rs_widget['banner_url']);
		
		if ($width_orig < $width || $height_orig < $height) {
			$width  = $width_orig;
			$height = $height_orig; 
		} else {
			if ($width && ($width_orig < $height_orig)) {
				$width = ($height / $height_orig) * $width_orig;
			} else {
				$height = ($width / $width_orig) * $height_orig;
			}	
		}		
	 	
		//for hiding up and down arrow
		$query_max_min = "SELECT MIN(sort_fld) as min_value,MAX(sort_fld) as max_value FROM " . $table_prefix . "banner WHERE 1=1 AND banner_position='widget'";
		$sql_max_min   = mysql_query($query_max_min);
		$rs_max_min	   = mysql_fetch_array($sql_max_min);
	 ?>
  <tr >
    <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><div align="center"><?php echo $i; ?></div></td>
    <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1"><a onmouseover="Tip('<?php echo $tooltip_info; ?>');" onmouseout="UnTip();" href="#"><img width="<?php echo $width;?>" height="<?php echo $height;?>" border="0" src="<?php echo $row_Rs_widget['banner_url'];?>" /></a></td>
    <td style="padding:3px 3px 3px 3px; background-color:#f1f1f1">
	
	<?php if ($row_Rs_widget['sort_fld'] != $rs_max_min['min_value']) {?>
	<a href="?page=mbp_ba_options&id=<?php echo $row_Rs_widget['banner_id']?>&sort=up&order=<?php echo $row_Rs_widget['sort_fld'];?>&action=widget_order&pagee=<?=$_GET['pagee'];?>">
		<img src="<?php echo MBP_BA_LIBPATH;?>images/up_arrow.gif" border="0" width="8" height="10" />	</a>
<?php } ?>
	
	<?php if ($row_Rs_widget['sort_fld'] != $rs_max_min['max_value']) {?>
	<a href="?page=mbp_ba_options&id=<?php echo $row_Rs_widget['banner_id']?>&sort=down&order=<?php echo $row_Rs_widget['sort_fld'];?>&action=widget_order&pagee=<?=$_GET['pagee'];?>">
		<img src="<?php echo MBP_BA_LIBPATH;?>images/down_arrow.gif" width="8" height="10" border="0" />	</a>	
	<?php } ?>	  </td>
    </tr>
  <?php $i++; } } else { ?>
  <tr >
    <td colspan="3" style="padding:3px 3px 3px 3px; background-color:#fff"><div align="center">No Widget banner Yet</div></td>
  </tr>
  <?php } ?>
</table>
<div id="pagination">
<?php
for($p=0; $p<=$totalPages; $p++)
{
	$page_no=$p+1;
	if($page_widget==$p)echo "<strong>Pages: </strong>"."<em>$page_no </em> ";
	else echo "<a href=\"?page=mbp_ba_options&action=widget_order&pagee=$p&total_widget=$total_widget\">$page_no</a>";
}
?>

</div>
<?php } //order section 
?>
<br/><br/>
<div align="center" style="background-color:#f1f1f1; padding:5px 0px 5px 0px" >
<p align="center"><strong><?php echo MBP_BA_NAME.' '.MBP_BA_VERSION; ?> by <a href="http://www.maxblogpress.com" target="_blank">MaxBlogPress</a></strong></p>
<p align="center">This plugin is the result of <a href="http://www.maxblogpress.com/blog/219/maxblogpress-revived/" target="_blank">MaxBlogPress Revived</a> project.</p>
</div>

</div>
<?php 
	}// mbp_ba_options ends
}//registration

/**
 * Plugin registration form
 */
function mbp_baRegistrationForm($form_name, $submit_btn_txt='Register', $name, $email, $hide=0, $submit_again='') {
	$wp_url = get_bloginfo('wpurl');
	$wp_url = (strpos($wp_url,'http://') === false) ? get_bloginfo('siteurl') : $wp_url;
	$plugin_pg    = 'options-general.php';
	$thankyou_url = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'];
	$onlist_url   = $wp_url.'/wp-admin/'.$plugin_pg.'?page='.$_GET['page'].'&amp;mbp_onlist=1';
	if ( $hide == 1 ) $align_tbl = 'left';
	else $align_tbl = 'center';
	?>
	
	<?php if ( $submit_again != 1 ) { ?>
	<script><!--
	function trim(str){
		var n = str;
		while ( n.length>0 && n.charAt(0)==' ' ) 
			n = n.substring(1,n.length);
		while( n.length>0 && n.charAt(n.length-1)==' ' )	
			n = n.substring(0,n.length-1);
		return n;
	}
	function mbp_baValidateForm_0() {
		var name = document.<?php echo $form_name;?>.name;
		var email = document.<?php echo $form_name;?>.from;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		var err = ''
		if ( trim(name.value) == '' )
			err += '- Name Required\n';
		if ( reg.test(email.value) == false )
			err += '- Valid Email Required\n';
		if ( err != '' ) {
			alert(err);
			return false;
		}
		return true;
	}
	//-->
	</script>
	<?php } ?>
	<table align="<?php echo $align_tbl;?>">
	<form name="<?php echo $form_name;?>" method="post" action="http://www.aweber.com/scripts/addlead.pl" <?php if($submit_again!=1){;?>onsubmit="return mbp_baValidateForm_0()"<?php }?>>
	 <input type="hidden" name="unit" value="maxbp-activate">
	 <input type="hidden" name="redirect" value="<?php echo $thankyou_url;?>">
	 <input type="hidden" name="meta_redirect_onlist" value="<?php echo $onlist_url;?>">
	 <input type="hidden" name="meta_adtracking" value="mr-image-organizer">
	 <input type="hidden" name="meta_message" value="1">
	 <input type="hidden" name="meta_required" value="from,name">
	 <input type="hidden" name="meta_forward_vars" value="1">	
	 <?php if ( $submit_again == 1 ) { ?> 	
	 <input type="hidden" name="submit_again" value="1">
	 <?php } ?>		 
	 <?php if ( $hide == 1 ) { ?> 
	 <input type="hidden" name="name" value="<?php echo $name;?>">
	 <input type="hidden" name="from" value="<?php echo $email;?>">
	 <?php } else { ?>
	 <tr><td>Name: </td><td><input type="text" name="name" value="<?php echo $name;?>" size="25" maxlength="150" /></td></tr>
	 <tr><td>Email: </td><td><input type="text" name="from" value="<?php echo $email;?>" size="25" maxlength="150" /></td></tr>
	 <?php } ?>
	 <tr><td>&nbsp;</td><td><input type="submit" name="submit" value="<?php echo $submit_btn_txt;?>" class="button" /></td></tr>
	 </form>
	</table>

	<?php
}

/**
 * Register Plugin - Step 2
 */
function mbp_baRegisterStep2($form_name='frm2',$name,$email) {
	$msg = 'You have not clicked on the confirmation link yet. A confirmation email has been sent to you again. Please check your email and click on the confirmation link to activate the plugin.';
	if ( trim($_GET['submit_again']) != '' && $msg != '' ) {
		echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
	}
	?>
	<style type="text/css">
	table, tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_BA_NAME.' '.MBP_BA_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff; text-align:left;">
	  <tr><td align="center"><h3>Almost Done....</h3></td></tr>
	  <tr><td><h3>Step 1:</h3></td></tr>
	  <tr><td>A confirmation email has been sent to your email "<?php echo $email;?>". You must click on the link inside the email to activate the plugin.</td></tr>
	  <tr><td><strong>The confirmation email will look like:</strong><br /><img src="http://www.maxblogpress.com/images/activate-plugin-email.jpg" vspace="4" border="0" /></td></tr>
	  <tr><td>&nbsp;</td></tr>
	  <tr><td><h3>Step 2:</h3></td></tr>
	  <tr><td>Click on the button below to Verify and Activate the plugin.</td></tr>
	  <tr><td><?php mbp_baRegistrationForm($form_name.'_0','Verify and Activate',$name,$email,$hide=1,$submit_again=1);?></td></tr>
	 </table>
	 </td></tr></table><br />
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:1px solid #e3e3e3; padding:8px; background-color:#f1f1f1;">
	 <tr><td align="center">
	 <table width="650" cellpadding="5" cellspacing="1" style="border:1px solid #e9e9e9; padding:8px; background-color:#ffffff; text-align:left;">
	   <tr><td><h3>Troubleshooting</h3></td></tr>
	   <tr><td><strong>The confirmation email is not there in my inbox!</strong></td></tr>
	   <tr><td>Dont panic! CHECK THE JUNK, spam or bulk folder of your email.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>It's not there in the junk folder either.</strong></td></tr>
	   <tr><td>Sometimes the confirmation email takes time to arrive. Please be patient. WAIT FOR 6 HOURS AT MOST. The confirmation email should be there by then.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>6 hours and yet no sign of a confirmation email!</strong></td></tr>
	   <tr><td>Please register again from below:</td></tr>
	   <tr><td><?php mbp_baRegistrationForm($form_name,'Register Again',$name,$email,$hide=0,$submit_again=2);?></td></tr>
	   <tr><td><strong>Help! Still no confirmation email and I have already registered twice</strong></td></tr>
	   <tr><td>Okay, please register again from the form above using a DIFFERENT EMAIL ADDRESS this time.</td></tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr>
		 <td><strong>Why am I receiving an error similar to the one shown below?</strong><br />
			 <img src="http://www.maxblogpress.com/images/no-verification-error.jpg" border="0" vspace="8" /><br />
		   You get that kind of error when you click on &quot;Verify and Activate&quot; button or try to register again.<br />
		   <br />
		   This error means that you have already subscribed but have not yet clicked on the link inside confirmation email. In order to  avoid any spam complain we don't send repeated confirmation emails. If you have not recieved the confirmation email then you need to wait for 12 hours at least before requesting another confirmation email. </td>
	   </tr>
	   <tr><td>&nbsp;</td></tr>
	   <tr><td><strong>But I've still got problems.</strong></td></tr>
	   <tr><td>Stay calm. <strong><a href="http://www.maxblogpress.com/contact-us/" target="_blank">Contact us</a></strong> about it and we will get to you ASAP.</td></tr>
	 </table>
	 </td></tr></table>
	 </center>		
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_BA_NAME.' '.MBP_BA_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}

/**
 * Register Plugin - Step 1
 */
function mbp_baRegisterStep1($form_name='frm1',$userdata) {
	$name  = trim($userdata->first_name.' '.$userdata->last_name);
	$email = trim($userdata->user_email);
	?>
	<style type="text/css">
	tabled , tbody, tfoot, thead {
		padding: 8px;
	}
	tr, th, td {
		padding: 0 8px 0 8px;
	}
	</style>
	<div class="wrap"><h2> <?php echo MBP_BA_NAME.' '.MBP_BA_VERSION; ?></h2>
	 <center>
	 <table width="100%" cellpadding="3" cellspacing="1" style="border:2px solid #e3e3e3; padding: 8px; background-color:#f1f1f1;">
	  <tr><td align="center">
		<table width="548" align="center" cellpadding="3" cellspacing="1" style="border:1px solid #e9e9e9; padding: 8px; background-color:#ffffff;">
		  <tr><td align="center"><h3>Please register the plugin to activate it. (Registration is free)</h3></td></tr>
		  <tr><td align="left">In addition you'll receive complimentary subscription to MaxBlogPress Newsletter which will give you many tips and tricks to attract lots of visitors to your blog.</td></tr>
		  <tr><td align="center"><strong>Fill the form below to register the plugin:</strong></td></tr>
		  <tr><td align="center"><?php mbp_baRegistrationForm($form_name,'Register',$name,$email);?></td></tr>
		  <tr><td align="center"><font size="1">[ Your contact information will be handled with the strictest confidence <br />and will never be sold or shared with third parties ]</font></td></tr>
		</table>
	  </td></tr></table>
	 </center>
	<p style="text-align:center;margin-top:3em;"><strong><?php echo MBP_BA_NAME.' '.MBP_BA_VERSION; ?> by <a href="http://www.maxblogpress.com/" target="_blank" >MaxBlogPress</a></strong></p>
	</div>
	<?php
}
?>