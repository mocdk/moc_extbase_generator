<?php
// Extbase autoloader :)
if (!class_exists('Tx_Extbase_Utility_ClassLoader')) {
	require(t3lib_extmgm::extPath('extbase') . 'Classes/Utility/ClassLoader.php');

	$classLoader = new Tx_Extbase_Utility_ClassLoader();
	spl_autoload_register(array($classLoader, 'loadClass'));
}

$args 	= $_SERVER['argv'];
$ext 	= $args[1];
$models = array_slice($args, 2);

if (empty($ext)) {
	printf("%s extbase_generate <extension> <model1, model2, model3>\n", $args[0]);
	exit(1);
}

$Generator = new MOC_Extbase_Generator($ext, $models);
$Generator->process();