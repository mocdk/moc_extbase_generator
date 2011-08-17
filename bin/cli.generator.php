<?php
// Extbase autoloader :)
if (!class_exists('Tx_Extbase_Utility_ClassLoader')) {
	require(t3lib_extMgm::extPath('extbase') . 'Classes/Utility/ClassLoader.php');

	$classLoader = new Tx_Extbase_Utility_ClassLoader();
	spl_autoload_register(array($classLoader, 'loadClass'));
}

if (t3lib_extMgm::isLoaded('moc_helpers')) {
	require(t3lib_extMgm::extPath('moc_helpers') . 'Classes/Domain/Model/Abstract.php');
}

$args 	= $_SERVER['argv'];
$ext 	= $args[1];
$models = array_slice($args, 2);

if (empty($ext)) {
	printf('%s extbase_generate <extension> <model1, model2, model3>' . PHP_EOL, $args[0]);
	exit(PHP_EOL);
}

if (t3lib_extMgm::isLoaded($ext) === FALSE) {
	printf('Extension "%s" is not loaded!', $ext);
	exit(PHP_EOL);
}

try {
	$Generator = new MOC_Extbase_Generator($ext, $models);
	$Generator->process();
} catch(Exception $e) {
	echo PHP_EOL . $e->getMessage() . PHP_EOL . PHP_EOL;
	print_r($e);
}