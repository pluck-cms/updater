<?php

function updater_info() {
	global $lang;
	$module_info = array(
		'name'          => $lang['updater']['name'],
		'intro'         => $lang['updater']['intro'],
		'version'       => '0.1',
		'author'        => $lang['updater']['author'],
		'website'       => 'http://xobit.nl',
		'icon'          => 'images/style-edit.png',
		'compatibility' => '4.7'
	);
	return $module_info;
}
 
?>