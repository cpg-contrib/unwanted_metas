<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2005 Coppermine Dev Team
  v1.1 originaly written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.4.2
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

if (strpos($CONFIG['unwanted_metas'],"'")!==false) {
    unwanted_metas_upgrade();
}

$thisplugin->add_action('plugin_install','unwanted_metas_install');
$thisplugin->add_action('plugin_uninstall','unwanted_metas_uninstall');
if (isset($_POST['unwanted_metas'])) { //process post data from pluginmgr
    $thisplugin->add_action('page_start','unwanted_metas_page_start');
}


if (!isset($_GET['disable_unwanted_metas'])) { //to temporarily disable the plugin (per page)
    if (defined('INDEX_PHP')) {
        $thisplugin->add_filter('plugin_block','unwanted_metas_albs'); //index.php
    }

    if (!is_numeric($_GET['album']) && defined('THUMBNAILS_PHP')) {
    	$thisplugin->add_filter('post_breadcrumb','unwanted_metas_albs'); //thumbnails.php
    }

    if (isset($_GET['album']) && !is_numeric($_GET['album']) && $_GET['pos'] >= 0 && defined('DISPLAYIMAGE_PHP')) {
        $thisplugin->add_filter('thumb_caption_regular','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_lastcom','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_lastcomby','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_lastup','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_topn','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_toprated','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_lasthits','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_random','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_search','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_lastalb','unwanted_metas_displayimage'); //
        $thisplugin->add_filter('thumb_caption_favpics','unwanted_metas_displayimage'); //
    }
}

function unwanted_metas_filter($var='') {

	global $META_ALBUM_SET,$ALBUM_SET,$CONFIG;
    //$unwanted_metas=explode(",",$CONFIG['unwanted_metas']);
    //$unwanted_metas=implode("','",$unwanted_metas);
	$sql='AND aid NOT IN ('.$CONFIG['unwanted_metas'].')';
	if(strpos($META_ALBUM_SET,$sql)===false) {
	   $META_ALBUM_SET.= $sql;
       $ALBUM_SET.= $sql;
	}
    return $var;
}

function unwanted_metas_displayimage($rowset)
{
    global $CONFIG,$pic_data,$pic_count,$album_name;
    static $run_once;
    if (!$run_once) {
        $run_once=true;
        unwanted_metas_filter();
        $pos = isset($_GET['pos']) ? (int)$_GET['pos'] : 0;
        $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
        $cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
        $album = isset($_GET['album']) ? $_GET['album'] : '';
        if ($pos < 0 || $pid > 0) {
            $pic_data = get_pic_data($album, $pic_count, $album_name, -1, -1, false);
        } elseif (isset($_GET['pos'])) {
            $pic_data = get_pic_data($album, $pic_count, $album_name, $pos, 1, false);
        }
        return $pic_data;
    }
    return $rowset;
}

function unwanted_metas_page_start()
{
	global $CONFIG;
    $meta=array();
    foreach($_POST['unwanted_metas'] as $value) {
        $meta[]=(int)$value;
    }
    if (count($meta)) {
        $unwanted_metass="'".implode(",",$meta)."'";
        cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$unwanted_metass' WHERE name = 'unwanted_metas';");
        $CONFIG['unwanted_metas']=stripslashes($unwanted_metass);
    }

}

function unwanted_metas_upgrade()
{
	global $CONFIG;
    $unwanted_metas=str_replace("'","",$CONFIG['unwanted_metas']);
    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$unwanted_metas' WHERE name = 'unwanted_metas';");
    $CONFIG['unwanted_metas']=$unwanted_metas;
}

function unwanted_metas_albs($return) {

    static $run_once;
    if (!$run_once) {
        unwanted_metas_filter();
        $run_once=true;
    }
    return $return;
}

function unwanted_metas_install() {
    global $CONFIG;

    unwanted_metas_uninstall(); //makes sure the insert works if something has gone wrong in the past.

    $query = "INSERT INTO {$CONFIG['TABLE_CONFIG']} (name,value) VALUES ('unwanted_metas','') ;";
    $result = cpg_db_query($query);

    if ($result) return true;

}

function unwanted_metas_uninstall() {
    global $CONFIG;
    $query = "DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'unwanted_metas';";
    $result = cpg_db_query($query);
    return true;
}
?>