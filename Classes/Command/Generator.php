<?php
if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

class Tx_MocExtbaseGenerator_Command_Generator extends t3lib_cli {

	/**
	 * CLI arguments
	 *
	 * @var array
	 */
	protected $argv;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var string
	 */
	protected $models;

	/**
	 * @var string
	 */
	protected $extensionPath;

	/**
	 * @var string
	 */
	protected $templatePath;

	/**
	 * @var string
	 */
	protected $modelPath;

	/**
	 * @var string
	 */
	protected $baseModelPath;

	/**
	 * @var string
	 */
	protected $repositoryPath;

	/**
	 * @var string
	 */
	protected $model;
	/**
	 * @var array
	 */
	protected $output = array();

	/**
	 * @var array
	 */
	protected $defaultTemplateValues = array(
		'dataType' => 'default',
		'var' => 'string',
		'default' => NULL,
		'annotations' => array()
	);

	/**
	 * @var boolean
	 */
	protected $buildConstructor = FALSE;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::t3lib_cli();

		$this->cli_help = array(
			'name' => 'MOC Extbase Generator',
			'options' => '',
			'description' => 'Generate Extbase models & repositories from TCA',
			'examples' => 'typo3/cli_dispatch.phpsh moc_extbase_generator <extension> <model1, model2, model3>',
			'author' => 'Aske Ertmann <aske@moc.net> (c) 2012'
		);

		$this->cli_options = array(array('<extension>'), array('<model1, model2, model3>'));

		$this->templatePath = t3lib_extMgm::extPath('moc_extbase_generator') . 'Resources/Private/Templates/';
	}

	/**
	 * Main function call
	 *
	 * @param array $argv
	 * @return void
	 */
	public function cli_main(array $argv) {
		if (isset($argv[1]) === TRUE) {
			$extension = $argv[1];
			if (t3lib_extMgm::isLoaded($extension) === FALSE) {
				$this->cli_echo(sprintf('Extension "%s" is not loaded!', $extension));
				exit(PHP_EOL);
			}

			$this->extension = $extension;
			$this->models = array_map('strtolower', array_slice($argv, 2));
			$this->extensionPath = t3lib_extMgm::extPath($extension);
			$this->modelPath = $this->extensionPath . 'Classes/Domain/Model/';
			$this->baseModelPath = $this->extensionPath . 'Classes/Domain/Model/Base/';
			$this->repositoryPath = $this->extensionPath . 'Classes/Domain/Repository/';

			foreach (array($this->modelPath, $this->baseModelPath, $this->repositoryPath) as $path) {
				if (!is_dir($path)) {
					mkdir($path, 0777, TRUE);
				}
			}

			try {
				$this->process();
			} catch(Exception $e) {
				$this->cli_echo(PHP_EOL . $e->getMessage() . PHP_EOL . PHP_EOL);
			}
		} else {
			$this->cli_help();
		}
	}

	/**
	 * @return void
	 */
	public function process() {
		$tablePrefix = 'tx_' . str_replace('_', '', $this->extension);
		foreach (array_keys($GLOBALS['TCA']) as $table) {
			if (substr($table, 0, strlen($tablePrefix)) === $tablePrefix) {
				$model = ucfirst(str_replace(array($tablePrefix . '_', 'domain_model_'), '', $table));
				$this->className = sprintf('Tx_%s_Domain_Model_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $model);
				if (!empty($this->models) && FALSE === array_search(strtolower($model), $this->models)) {
					printf('Skipping model: %s' . PHP_EOL, $model);
					continue;
				}
				printf('Processing model: %s' . PHP_EOL, $model);

				$this->model = $model;

				t3lib_div::loadTCA($table);
				$this->createTemplate($GLOBALS['TCA'][$table]);
			}
		}
	}

	/**
	 * @param array $TCA
	 * @return void
	 */
	protected function createTemplate(array $TCA) {
		$this->reset();

		$columns = $TCA['columns'];
		$columns = $this->preprocessColumns($columns);

		$this->buildClassProperties($columns);
		if ($this->buildConstructor) {
			$this->buildConstructor($columns);
		}

		$this->buildClassAccessors($columns);

		$this->writeModelBaseClass();
		$this->writeModelClass();
		$this->writeRepositoryClass();
	}

	/**
	 * @return void
	 */
	protected function writeModelBaseClass() {
		$className = sprintf('Tx_%s_Domain_Model_Base_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->model);
		$extendsClassName = t3lib_extMgm::isLoaded('moc_helpers') ? 'Tx_MocHelpers_Domain_Model_Abstract' : 'Tx_Extbase_DomainObject_AbstractEntity';

		$output = array();
		$output[] = '<?php';
		$output[] = 'abstract class ' . $className . ' extends ' . $extendsClassName . ' {' . PHP_EOL;
		$output[] = $this->pad(1, join($this->output['ClassProperties'], PHP_EOL . PHP_EOL));
		$output[] = '';

//		So far, dependency injection into domain models are not supported when unserializing models (See bug 11311 in forge)
//		$output[] = '	/**';
//	 	$output[] = '	 * Injector for Extbase ObjectManager';
//	 	$output[] = '	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager';
//	 	$output[] = '	 */';
//	 	$output[] = '	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {';
//		$output[] = '		$this->objectManager = $objectManager;';
//		$output[] = '	}';
//		$output[] = '';

		// Disabled for now
		if ($this->buildConstructor && (count($this->output['ClassConstructor']) > 0)) {
			$output[] = '	/**';
			$output[] = '	 * @return void';
			$output[] = '	 */';
			$output[] = '	public function initializeObject() {';
			$output[] = $this->pad(2, join($this->output['ClassConstructor'], PHP_EOL));
			$output[] = '	}' . PHP_EOL;
		}
		$output[] = $this->pad(1, join($this->output['ClassAccessors'], PHP_EOL . PHP_EOL));
		$output[] = PHP_EOL . '}';

		$targetFile = $this->baseModelPath . $this->model . '.php';
		file_put_contents($targetFile, join($output, PHP_EOL));
	}

	/**
	 * @return void
	 */
	protected function writeModelClass() {
		$targetFile = $this->modelPath . $this->model . '.php';
		if (file_exists($targetFile) === FALSE) {
			$className = sprintf('Tx_%s_Domain_Model_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->model);
			$extendsClassName = sprintf('Tx_%s_Domain_Model_Base_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->model);
			$output = array(
				'<?php',
				'class ' . $className . ' extends ' . $extendsClassName . ' {',
				'}'
			);
			file_put_contents($targetFile, join($output, PHP_EOL));
		}
	}

	/**
	 * @return void
	 */
	protected function writeRepositoryClass() {
		$targetFile = $this->repositoryPath . $this->model . 'Repository.php';
		if (file_exists($targetFile) === FALSE) {
			$className = sprintf('Tx_%s_Domain_Repository_%sRepository', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->model);
			$extendsClassName = t3lib_extMgm::isLoaded('moc_helpers') ? 'Tx_MocHelpers_Domain_Repository_MocRepository' : 'Tx_Extbase_Persistence_Repository';
			$output = array(
				'<?php',
				'class ' . $className . ' extends ' . $extendsClassName . ' {',
				'}'
			);
			file_put_contents($targetFile, join($output, PHP_EOL));
		}
	}

	/**
	 * @param integer $size
	 * @param string $string
	 * @return string
	 */
	protected function pad($size, $string) {
		$this->padSize = $size;
		$lines = explode(PHP_EOL, $string);
		$lines = array_map(array($this, '_pad'), $lines);
		return join($lines, PHP_EOL);
	}

	/**
	 * @param string $a
	 * @return string
	 */
	protected function _pad($a) {
		if (empty($a)) {
			return '';
		}
		return str_repeat(chr(9), $this->padSize) . $a;
	}

	/**
	 * @return void
	 */
	protected function reset() {
		$this->output = array();
		$this->buildConstructor = FALSE;
	}

	/**
	 * @param array $config
	 * @return array
	 */
	protected function getKeyMarkers(array $config) {
		$key = $config['key'];

		$replace = array();
		$replace['###KEY###'] 					= Tx_MocExtbaseGenerator_Utility_Inflector::variable($key);
		$replace['###KEY_UPPER###'] 			= ucfirst(Tx_MocExtbaseGenerator_Utility_Inflector::variable($key));
		$replace['###KEY_PLURAL###'] 			= Tx_MocExtbaseGenerator_Utility_Inflector::camelize(Tx_MocExtbaseGenerator_Utility_Inflector::pluralize($key));
		$replace['###KEY_SINGULAR###']			= Tx_MocExtbaseGenerator_Utility_Inflector::camelize(Tx_MocExtbaseGenerator_Utility_Inflector::singularize($key));

		$replace['###KEY_LOWER###']				= Tx_MocExtbaseGenerator_Utility_Inflector::variable($key);
		$replace['###KEY_PLURAL_LOWER###']		= Tx_MocExtbaseGenerator_Utility_Inflector::variable(Tx_MocExtbaseGenerator_Utility_Inflector::pluralize($key));
		$replace['###KEY_SINGULAR_LOWER###'] 	= Tx_MocExtbaseGenerator_Utility_Inflector::variable(Tx_MocExtbaseGenerator_Utility_Inflector::singularize($key));

		$replace['###CAMEL_CASE###'] 			= Tx_MocExtbaseGenerator_Utility_Inflector::camelize(Tx_MocExtbaseGenerator_Utility_Inflector::variable($key));
		$replace['###THIS###']	 				= '$this';

		$replace['###DEFAULT_VALUE###'] 		= $config['default'];
		$replace['###VAR###']					= $config['var'];

		$replace['###SELF###']					= $this->className;
		$replace['###ANNOTATIONS###']			= '';

		if ($config['annotations'] !== array()) {
			foreach ($config['annotations'] as $annotation) {
				$replace['###ANNOTATIONS###'] .= sprintf(PHP_EOL . ' * @%s', $annotation);
			}
		}

		if (!empty($config['validations'])) {
			foreach ($config['validations'] as $rule => $args) {
				$replace['###ANNOTATIONS###'] .= sprintf(PHP_EOL . ' * @validate %s%s', $rule, $this->getValidationParams($args, $rule, $key));
			}
		}

		if (!empty($replace['###ANNOTATIONS####'])) {
			$replace['###ANNOTATIONS####'] = PHP_EOL . ' *' . $replace['###ANNOTATIONS###'];
		}

		return $replace;
	}

	/**
	 * @param array $args
	 * @param string $rule
	 * @param string $key
	 * @return array
	 */
	protected function getValidationParams(array $args, $rule = NULL, $key = NULL) {
		if (empty($args)) {
			return '';
		}
		if (is_string($args)) {
			return sprintf('(%s)', $args);
		}
		if (is_array($args)) {
			$rules = '';
			foreach ($args as $k => $v) {
				$rules .= sprintf('%s=%s,', $k, $v);
			}
			return sprintf('(%s)', trim($rules, ','));
		}
		throw new Exception(sprintf('Invalid validation params for rule %s in key %s in object %s', $rule, $key, $this->className));
	}

	/**
	 * @param array $columns
	 * @return void
	 */
	protected function buildClassProperties(array $columns) {
		foreach ($columns as $column => $config) {
			$replace = $this->getKeyMarkers($config);
			$def = $this->loadClassPropertyTemplate($config['dataType']);
			$this->output['ClassProperties'][$column] = $this->applyMarkers($def, $replace);
		}
	}

	/**
	 * @param array $columns
	 * @return void
	 */
	protected function buildClassAccessors(array $columns) {
		foreach ($columns as $column => $config) {
			if (($config['dataType'] !== 'storage') && (stristr($config['var'], '_Domain_Model_') !== FALSE)) {
				$config['dataType'] = 'object';
			}
			$replace = $this->getKeyMarkers($config);
			$def = $this->loadClassAccessorTemplate($config['dataType']);
			$this->output['ClassAccessors'][$column] = $this->applyMarkers($def, $replace);
		}
	}

	/**
	 * @param array $columns
	 * @return void
	 */
	protected function buildConstructor(array $columns) {
		foreach ($columns as $column => $config) {
			// No need for constructor on normal strings or oneToOne relations or if initialize is FALSE
			if (($config['dataType'] === 'default') || ($config['dataType'] === 'object') || ($config['initialize'] === FALSE)) {
				continue;
			}

			// Avoid endless loop
			if (($config['dataType'] !== 'storage') && ($config['dataType'] !== 'DateTime')) {
				continue;
			}

			$replace = $this->getKeyMarkers($config);

			if ($config['var'] === 'DateTime') {
				$def = $this->loadClassConstructorTemplate('DateTime');
			} else {
				$def = $this->loadClassConstructorTemplate($config['dataType']);
			}

			$this->output['ClassConstructor'][$column] = $this->applyMarkers($def, $replace);
		}
	}

	/**
	 * @param array $columns
	 * @return void
	 */
	protected function preprocessColumns(array $columns) {
		foreach ($columns as $column => &$config) {
			if (isset($config['extbase'])) {
				$config = array_merge($this->defaultTemplateValues, $config['config'], $config['extbase']);
			} else {
				$config = array_merge($this->defaultTemplateValues, $config['config']);
			}

			if (in_array($config['type'], array('select', 'group', 'inline', 'storage')) && $config['maxitems'] > 1) {
				$config['dataType'] = 'storage';
			}

			$dateTime = in_array($config['eval'], array('date', 'datetime', 'time', 'timesec', 'year'));

			if ($config['dataType'] === 'storage' || $dateTime === TRUE) {
				$this->buildConstructor = TRUE;
			}

			if ($dateTime === TRUE) {
				$config['var'] = 'DateTime';
			}

			if (empty($config['key'])) {
				$config['key'] = $column;
			}

			if (stripos($config['key'], '_') !== FALSE) {
				$fixedKey = Tx_MocExtbaseGenerator_Utility_Inflector::variable($config['key']);
				$config['key'] = $fixedKey;
			}

			if (isset($config['foreign_class'])) {
				$config['var'] = $config['foreign_class'];
			}

			if ($config['type'] === 'check') {
				$config['var'] = 'boolean';
			}
		}

		return $columns;
	}

	/**
	 * @param string $template
	 * @param array $markers
	 * @return string
	 */
	protected function applyMarkers($template, array $markers) {
		return str_replace(array_keys($markers), array_values($markers), $template);
	}

	/**
	 * @param string $type
	 * @return string
	 */
	protected function loadClassPropertyTemplate($type) {
		return $this->loadTemplate('ClassProperty', $type);
	}

	/**
	 * @param string $type
	 * @return string
	 */
	protected function loadClassConstructorTemplate($type) {
		return $this->loadTemplate('ClassConstructor', $type);
	}

	/**
	 * @param string $type
	 * @return string
	 */
	protected function loadClassAccessorTemplate($type) {
		return $this->loadTemplate('ClassAccessor', $type);
	}

	/**
	 * @param string $kind
	 * @param string $type
	 * @return string
	 */
	protected function loadTemplate($kind, $type) {
		$filename = $this->templatePath . $kind . DIRECTORY_SEPARATOR . $type . '.tpl';
		if (!is_file($filename)) {
			throw new Exception(sprintf('Template file %s does not exists', $filename));
		}
		return file_get_contents($filename);
	}

}

require_once(t3lib_extMgm::extPath('moc_extbase_generator') . 'Classes/Utility/Inflector.php');
$cliObj = t3lib_div::makeInstance('Tx_MocExtbaseGenerator_Command_Generator');
$cliObj->cli_main($_SERVER['argv']);
?>