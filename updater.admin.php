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
	showmenudiv($lang['updater']['available'],$lang['updater']['available_info'],'data/modules/updater/images/check.png','admin.php?module=updater&page=AVAIL',false);
	showmenudiv($lang['updater']['update'],$lang['updater']['update_info'],'data/modules/updater/images/update.png','admin.php?module=updater&page=UPDATE',false);
}

function updater_page_admin_REQ() {
	global $lang;
	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";

	//do requirements check here

	//check writable: data, files
	print("<b>Check folders</b> <br />");
	foreach (array('files', 'data', 'images', 'docs', 'data/inc', 'data/modules', 'data/trash', 'data/themes', 'data/themes/default', 'data/themes/oldstyle', 'data/settings', 'data/settings/langpref.php') as $check)
		check_writable($check);
	unset($check);
	print("	<br /><b> Check plugins </b><br />");

	//check availability libcurl
	if (extension_loaded('curl')){
		//installed
		?>
		<span>
			<img src="data/image/update-no.png" width="15" height="15" alt="<?php echo $lang['install']['good']; ?>" />
		</span>
		<span>&nbsp;/<?php echo $lang['updater']['curl_installed']; ?></span>
		<br />
		<?php
	
	} else {
		//not installed
		?>
		<span>
			<img src="data/image/error.png" width="15" height="15" alt="<?php echo $lang['install']['false']; ?>" />
		</span>
		<span>&nbsp;<?php echo $lang['updater']['curl_notinstalled']; ?></span>
		<br /> 
		<?php
	
	}
	print("	<br /> <b>Checks Complete </b><br /><br />");

	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";
}
 
function updater_page_admin_BACKUP() {
	global $lang;
	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";

	$backupfile = createSiteBackup();

	show_error($lang['updater']['backupcreated'],3);

	print("<a href=".$backupfile.">Download backup here</a>");

	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";

}

function updater_page_admin_AVAIL() {
	global $lang;
	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";

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

	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";
}
 
function updater_page_admin_UPDATE() {
	global $lang;
	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";

	//do update here
	//Backup install to /files
	createSiteBackup();

	$updateversion = checkUpdate();
	switch(check_update_version($updateversion)) {
	case 'no':
		show_error($lang['updater']['not_needed'],3);
		return;
	}

	//Download latest version

		$urlfile = 'https://github.com/pluck-cms/pluck/archive/'.$updateversion.'.tar.gz';
		$tmp_name = "files/pluck".$updateversion.".tar.gz";
		$tmp_name_tar = "files/pluck".$updateversion.".tar";
		$tmp_name_folder = "files/pluck-".$updateversion;

		copy($urlfile, $tmp_name);
	//Unpack latest version

	// decompress from gz
	$p = new PharData($tmp_name);
	$exts = explode('.', $tmp_name);
	array_shift($exts);
	array_pop($exts);
	$ext = implode('.', $exts);
	$p->decompress($ext);
	// unarchive from the tar
	$phar = new PharData($tmp_name_tar);
	$phar->extractTo('files');

	//After extraction: delete the tar.gz-file.
	unlink($tmp_name);
	unlink($tmp_name_tar);
	//Move Data folders over {image, inc, modules\albums, modules\blog, modules\contactform. modules\multitheme, modules\tinymce, modules\viewsite, modules\.htaccess, index.html. reset.css}
	
	rcopy($tmp_name_folder."/", ".");

	rrmdir($tmp_name_folder);

	echo "<p><a href=\"?module=updater\"><<< Back</a></p>";
}

function createSiteBackup(){
	global $lang;
	try
	{
		$date = date("Y-M-d");
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
		curl_close($geturl);
		// Find latest release
		//            <span class\=\"css-truncate-target\" style="max-width: 125px">4.7.11</span>
		preg_match('/\<span class\=\"css-truncate-target\" style\=\"max-width: 125px\"\>(.*)\<\/span\>/', $response, $match);
		// Current latest release string
		$update_available = strip_tags($match[0]);
	
		// Remove v char if we are using normal releases
		$update_available = str_replace('v', '', $update_available);
		

		return $update_available;

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
