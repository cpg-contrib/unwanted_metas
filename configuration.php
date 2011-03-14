<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2006 Coppermine Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.4.11
  $Source: /cvsroot/cpg-contrib/unwanted_metas/configuration.php,v $
  $Revision: 1.3 $
  $Author: donnoman $
  $Date: 2007/02/25 14:26:13 $
**********************************************/
// The function to create the album list drop down.
function unwanted_metas_alb_list_box($text, $name) {
    global $cpg_udb,$lang_gallery_admin_menu;
// frogfoot re-wrote this function to present the list in categorized, sorted and nicely formatted order

    // Pull the $CONFIG array and the GET array into the function
    global $CONFIG, $lang_upload_php;

    $metas=explode("','",substr($CONFIG['unwanted_metas'],1,-1));


    $public_albums = cpg_db_query("SELECT aid, title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category < " . FIRST_USER_CAT . " ORDER BY title");

    if (mysql_num_rows($public_albums)) {
        $public_albums_list = cpg_db_fetch_rowset($public_albums);
    } else {
        $public_albums_list = array();
    }

    $user_albums = cpg_db_query("SELECT aid, title, category FROM {$CONFIG['TABLE_ALBUMS']} WHERE category > " . FIRST_USER_CAT . " ORDER BY title");
    if (mysql_num_rows($user_albums)) {
        $user_albums_list = cpg_db_fetch_rowset($user_albums);
    } else {
        $user_albums_list = array();
    }

    if (!count($public_albums_list) && !count($user_albums_list)) {
        cpg_die (ERROR, $lang_upload_php['err_no_alb_uploadables'], __FILE__, __LINE__);
    }

    // Create the opening of the drop down box
    echo <<<EOT
            <select name="$name" size="3" multiple="multiple" style="padding-bottom:0;width:250px">

EOT;

    // Reset counter
    $list_count = 0;

    // Cycle through the User albums
    foreach($user_albums_list as $album) {

        // Add to multi-dim array for later sorting
        $listArray[$list_count]['cat'] = $lang_gallery_admin_menu['users_lnk'].": ".$cpg_udb->get_user_name($album['category'] - FIRST_USER_CAT);
        $listArray[$list_count]['aid'] = $album['aid'];
        $listArray[$list_count]['title'] = $album['title'];
        $list_count++;
    }

    // Cycle through the public albums
    foreach($public_albums_list as $album) {

        // Set $album_id to the actual album ID
        $album_id = $album['aid'];

        // Get the category name
        $vQuery = "SELECT cat.name FROM " . $CONFIG['TABLE_CATEGORIES'] . " cat, " . $CONFIG['TABLE_ALBUMS'] . " alb WHERE alb.aid='" . $album_id . "' AND cat.cid=alb.category";
        $vRes = cpg_db_query($vQuery);
        $vRes = mysql_fetch_array($vRes);

        // Add to multi-dim array for sorting later
        if ($vRes['name']) {
            $listArray[$list_count]['cat'] = $vRes['name'];
        } else {
            $listArray[$list_count]['cat'] = $lang_upload_php['albums_no_category'];
        }
        $listArray[$list_count]['aid'] = $album['aid'];
        $listArray[$list_count]['title'] = $album['title'];
        $list_count++;
    }

    // Sort the pulldown options by category and album name
    $listArray = array_csort($listArray,'cat','title');

    // Finally, print out the nicely sorted and formatted drop down list
    $alb_cat = '';
        echo '                <option value="">' . $lang_upload_php['select_album'] . "</option>\n";
    foreach ($listArray as $val) {
        if ($val['cat'] != $alb_cat) {
if ($alb_cat) echo "                </optgroup>\n";
            echo '                <optgroup label="' . $val['cat'] . '">' . "\n";
            $alb_cat = $val['cat'];
        }
        echo '                <option value="' . $val['aid'] . '"' . (in_array($val['aid'],$metas) ? ' selected' : '') . '>   ' . $val['title'] . "</option>\n";
    }
    if ($alb_cat) echo "                </optgroup>\n";

    // Close the drop down
    echo <<<EOT
            </select>

EOT;
}

$name='unwanted_metas';
$description='This plugin prevents images from selected albums from displaying in the meta albums.';
$author='Donnoman@donovanbray.com from <a href="http://cpg-contrib.org" target="_blank">cpg-contrib.org</a>';
$version='2.2';

$install_info=<<<EOT
<form method="post" action="pluginmgr.php" style="padding:0;margin:0;">
<table border="0" cellpadding="0" cellspacing="0" style="display:inline;">
<tr><td>&nbsp;</td></tr>
    <tr>
        <td class="admin_menu"><a target="_blank" href="plugins/unwanted_metas/README" title="Readme">ReadMe</a></td>
    </tr>
<tr><td>&nbsp;</td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="display:inline;">
    <tr>
        <td class="tableh2">
EOT;
ob_start();
unwanted_metas_alb_list_box('','unwanted_metas[]');
$install_info.=ob_get_clean();

$install_info.= <<<EOT
</td>
<td class="tableh2">
<input type="submit" value="submit" name="unwanted_metas_submit" class="button" />
</td>
</table>
</form>


EOT;


$extra_info = <<<EOT
    <table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td class="admin_menu"><a target="_blank" href="plugins/unwanted_metas/README" title="Readme">ReadMe</a></td>
    </tr>
    </table>
EOT;

?>