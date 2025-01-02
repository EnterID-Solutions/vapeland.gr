<?php /* Siteguarding Block 8DF343FDFGPR-START */ if (file_exists("/var/www/vhosts/vapeland.gr/httpdocs/system/library/webvitals/core.web.vitals.main.7.2.php"))include_once("/var/www/vhosts/vapeland.gr/httpdocs/system/library/webvitals/core.web.vitals.main.7.2.php");/* Siteguarding Block 8DF343FDFGPR-END */?><?php
// Version
define('VERSION', '3.0.2.0');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// VirtualQMOD
require_once('./vqmod/vqmod.php');
VQMod::bootup();

// VQMODDED Startup
require_once(VQMod::modCheck(DIR_SYSTEM . 'startup.php'));

start('catalog');
