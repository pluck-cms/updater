<?php

function updater_info() {
	global $lang;
	$module_info = array(
		'name'          => $lang['updater']['name'],
		'intro'         => $lang['updater']['intro'],
		'version'       => '0.1',
		'author'        => 'Bas Steelooper',
		'website'       => 'http://xobit.nl',
		'icon'          => 'images/update.jpg',
		'compatibility' => '4.7'
	);
	return $module_info;
}
 
?>