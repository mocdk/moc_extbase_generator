<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

MOC_Autoload::addPlugin($_EXTKEY);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['extbase_generate'] = array("EXT:$_EXTKEY/bin/cli.generator.php", '_CLI_mocbase');
