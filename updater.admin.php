<?php
//This is a module for pluck, an opensource content management system
//Website: http://www.pluck-cms.org

//Make sure the file isn't accessed directly
defined('IN_PLUCK') or exit('Access denied!');

$version = "";

function updater_pages_admin() {
	global $lang;

	$module_page_admin[] = array(
		'func'  => 'Main',
		'title' => $lang['updater']['main']
	);
	$module_page_admin[] = array(
		'func'  => 'REQ',
		'title' => $lang['updater']['requirements']
	);
	$module_page_admin[] = array(
		'func'  => 'AVAIL',
		'title' => $lang['updater']['available']
	);
	
	$module_page_admin[] = array(
		'func'  => 'UPDATE',
		'title' => $lang['updater']['update']
	);
	
	$module_page_admin[] = array(
		'func'  => 'BACKUP',
		'title' => $lang['updater']['backup']
	);
	
	return $module_page_admin;
}

function updater_page_admin_Main() {
	global $lang;

	showmenudiv($lang['updater']['requirements'],$lang['updater']['requirements_info'],'data/modules/updater/images/requirements.jpg','admin.php?module=updater&page=REQ',false);
	showmenudiv($lang['updater']['backup'],$lang['updater']['backup_info'],'data/modules/updater/images/backup.png','admin.php?module=updater&page=BACKUP',false);
	showmenudiv($lang['updater']['backupfiles'],$lang['updater']['backupfiles_info'],'data/image/file.png','admin.php?action=files',false);
	showmenudiv($lang['updater']['available'],$lang['updater']['available_info'],'data/modules/updater/images/check.png','admin.php?module=updater&page=AVAIL',false);
	showmenudiv($lang['updater']['update'],$lang['updater']['update_info'],'data/modules/updater/images/update.jpg','admin.php?module=updater&page=UPDATE',false);
}

function updater_page_admin_REQ() {
	global $lang;
	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php

	//do requirements check here

	//check writable: data, files
	echo '<b>'.$lang['updater']['check_folders'].'</b><br />';
	foreach (array('files', 'data', 'images', 'docs', 'data/inc', 'data/modules', 'data/trash', 'data/themes', 'data/themes/default', 'data/themes/oldstyle', 'data/settings', 'data/settings/langpref.php') as $check)
		check_writable($check);
	unset($check);
	echo '<br /><b>'.$lang['updater']['check_plugins'].'</b><br />';

	//check availability libcurl
	if (extension_loaded('curl')){
		//installed
		?>
		<span>
			<img src="data/image/update-no.png" width="15" height="15" alt="" />
		</span>
		<span>&nbsp;<?php echo $lang['updater']['curl_installed']; ?></span>
		<br />
		<?php
	
	} else {
		//not installed
		?>
		<span>
			<img src="data/image/error.png" width="15" height="15" alt="" />
		</span>
		<span>&nbsp;<?php echo $lang['updater']['curl_notinstalled']; ?></span>
		<br /> 
		<?php
	
	}
	echo '<br /><b>'.$lang['updater']['checks_complete'].'</b><br /><br />';

	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php
}
 
function updater_page_admin_BACKUP() {
	global $lang;
	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php

	$backupfile = createSiteBackup();

	show_error($lang['updater']['backupcreated'],3);

	echo '<a href='.$backupfile.'>'.$lang['updater']['download_backup'].'</a>';
	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php

}

function updater_page_admin_AVAIL() {
	global $lang;
	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php

	if (extension_loaded('curl')){
		//installed

		$updateversion = checkUpdate();
		switch(check_update_version($updateversion)) {
		case 'yes':
			show_error($lang['updater']['yes'],1);
			break;
		case 'urgent':
			show_error($lang['updater']['urgent'],1);
			break;
		case 'no':
			show_error($lang['updater']['no'],3);
			break;
		}
	} else {
		//not installed
		show_error($lang['updater']['curl_notinstalled'],1);
	
	}

	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php
}
 
function updater_page_admin_UPDATE() {
	global $lang;
	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php
	//do update here

	$updateversion = checkUpdate();
	switch(check_update_version($updateversion)) {
	case 'no':
		show_error($lang['updater']['not_needed'],3);
		return;
	}
	//Backup install to /files
		createSiteBackup();

	//Download latest version

		$urlfile = 'https://github.com/pluck-cms/pluck/archive/'.$updateversion.'.tar.gz';
		$tmp_name = "files/pluck".$updateversion.".tar.gz";
		$tmp_name_tar = "files/pluck".$updateversion.".tar";
		$tmp_name_folder = "files/pluck-".$updateversion;

		copy($urlfile, $tmp_name);
	//Unpack latest version

	// decompress from gz
	$p = new PharData($tmp_name);
	$p->decompress();
	// unarchive from the tar
	$phar = new PharData($tmp_name_tar);
	$phar->extractTo('files');

	//After extraction: delete the tar.gz-file.
	unlink($tmp_name);
	unlink($tmp_name_tar);
	//Move Data folders over {image, inc, modules\albums, modules\blog, modules\contactform. modules\multitheme, modules\tinymce, modules\viewsite, modules\.htaccess, index.html. reset.css}
	
	rcopy($tmp_name_folder."/", ".");

	rrmdir($tmp_name_folder);

	?>
	<p><a href="?module=updater">&lt;&lt;&lt; <?php echo $lang['general']['back']; ?></a></p>
	<?php
}

function createSiteBackup(){
	global $lang;
	try
	{
		$date = date("Y-M-d-H-i-s");
		
		$tarfilename = 'files/website-backup-'.$date.'.tar';
		
		$a = new PharData($tarfilename);

		// ADD FILES TO archive.tar FILE
		$a->buildFromDirectory('.');

		// COMPRESS archive.tar FILE. COMPRESSED FILE WILL BE archive.tar.gz
		$a->compress(Phar::GZ);

		// NOTE THAT BOTH FILES WILL EXISTS. SO IF YOU WANT YOU CAN UNLINK archive.tar
		unlink($tarfilename);

	} 
	catch (Exception $e) 
	{
		echo "Exception : " . $e;
	}
	return $tarfilename.".gz";
}

function checkUpdate(){
		//do update check here
		$url = 'https://github.com/pluck-cms/pluck/releases/latest';

		// Initialize session and set URL.
		$geturl = curl_init();
		curl_setopt($geturl, CURLOPT_URL, $url);
		// Dont check ssl certifical
		curl_setopt($geturl, CURLOPT_SSL_VERIFYPEER, false);
		// Go redirect
		curl_setopt($geturl, CURLOPT_FOLLOWLOCATION, true);
		// Return data
		curl_setopt($geturl, CURLOPT_RETURNTRANSFER, true);
			
		// Get the response and close the channel.
		$response = curl_exec($geturl);
		
		$finalurl = curl_getinfo($geturl, CURLINFO_EFFECTIVE_URL);

		// Parse the URL to get the path
		$path = parse_url($finalurl, PHP_URL_PATH);

		// Split the path into segments
		$segments = explode('/', rtrim($path, '/'));

		// Get the last segment
		$lastPart = end($segments);

		curl_close($geturl);

		return $lastPart;

}

   // Function to remove folders and files 
   function rrmdir($dir) {
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file)
			if ($file != "." && $file != "..") rrmdir("$dir/$file");
		rmdir($dir);
	}
	else if (file_exists($dir)) unlink($dir);
}

// Function to Copy folders and files       
function rcopy($src, $dst) {
//	if (file_exists ( $dst ))
//		rrmdir ( $dst );
	if (is_dir ( $src ) && !file_exists ( $dst )) {
		mkdir ( $dst );
	}
	
	if(is_dir($src)){
		$files = scandir ( $src );
		foreach ( $files as $file )
			if ($file != "." && $file != "..")
				rcopy ( "$src/$file", "$dst/$file" );
	} else if (file_exists ( $src ))
		copy ( $src, $dst );
}
