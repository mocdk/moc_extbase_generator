<?php
class MOC_Extbase_Generator {

	protected $extension;

	protected $models;

	protected $extensionPath;

	protected $templatePath;

	protected $outputPath;

	protected $configPath;

	protected $defaultTemplateValues = array(
		'type' 		=> 'default',
		'var' 		=> 'string',
		'default' 	=> 'null',
		'annotations' => array(

		)
	);

	protected $buildConstructor = false;

	protected $output = array();

	public function __construct($extension, $models = array()) {
		$this->extension 		= $extension;
		$this->models			= $models;
		$this->extensionPath 	= t3lib_extMgm::extPath($extension);
		$this->modelPath 		= $this->extensionPath . 'Classes/Domain/Model/';
		$this->outputPath 		= $this->extensionPath . 'Classes/Domain/Model/Base/';
		$this->configPath 		= $this->extensionPath . 'Classes/Domain/Configuration/';

		$this->templatePath 	= t3lib_extMgm::extPath('moc_extbase_generator') . 'Resources/Private/Templates/';

		if (!is_dir($this->outputPath)) {
			mkdir($this->outputPath, 0777, true);
		}

		if (!is_dir($this->configPath)) {
			throw new MOC_Exception(sprintf('Configuration folder %s does not exists', $this->configPath));
		}

		if (!empty($this->models) && !is_array($this->models)) {
			throw new MOC_Exception('Model list is not an array!');
		}

		$this->models = array_map('strtolower', $this->models);
	}

	public function process() {
		$files = glob(sprintf('%s*.php', $this->configPath));
		if (empty($files)) {
			throw new MOC_Exception(sprintf('No configuration classes found in %s', $this->configPath));
		}

		foreach ($files as $filepath) {
			$file = basename($filepath, '.php');
			$className = sprintf('Tx_%s_Domain_Configuration_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $file);
			$this->className = sprintf('Tx_%s_Domain_Model_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $file);
			if (!empty($this->models) && false === array_search(strtolower($file), $this->models)) {
				printf('Skipping model: %s' . PHP_EOL, $className);
				continue;
			}
			printf('Processing model: %s' . PHP_EOL, $className);

			$this->currentFile = $file;

			require_once $filepath;

			if (!class_exists($className)) {
				throw new MOC_Exception(sprintf('File %s did not define class %s as expected', $filepath, $className));
			}

			$ModelConfigurationInstance = new $className();
			$this->createTemplate($ModelConfigurationInstance);
		}
	}

	protected function createTemplate($ModelConfigurationInstance) {
		$this->reset();

		$keys = $ModelConfigurationInstance->getKeys();
		$keys = $this->preprocessKeys($keys);

		$this->buildClassProperties($keys);

		if ($this->buildConstructor) {
			$this->buildConstructor($keys);
		}



		$this->buildClassAccessors($keys);

		$this->writeModelBaseClass();
		$this->writeModelClass();
	}

	protected function writeModelBaseClass() {
		$className = sprintf('Tx_%s_Domain_Model_Base_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->currentFile);
		$extendsClassName = class_exists('Tx_MocHelpers_Domain_Model_Abstract') ? 'Tx_MocHelpers_Domain_Model_Abstract' : 'Tx_Extbase_DomainObject_AbstractEntity';

		$output = array();
		$output[] = '<?php';
		$output[] = '/**';
		$output[] = ' * Generated on ' . date('r');
		$output[] = ' */';
		$output[] = 'abstract class ' . $className . ' extends ' . $extendsClassName . ' {' . PHP_EOL;
		$output[] = $this->pad(1, join($this->output['ClassProperties'], PHP_EOL . PHP_EOL));
		$output[] = '';

//		So far, dependency injection into dmain models are not supported in fomai models  (See bug 11311 in forge)
//		$output[] = '	/**';
//	 	$output[] = '	 * Injector for Extbase ObjectManager';
//	 	$output[] = '	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager';
//	 	$output[] = '	 */';
//	 	$output[] = '	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {';
//		$output[] = '		$this->objectManager = $objectManager;';
//		$output[] = '	}';
//		$output[] = '';

		// Disabled for now
		if ($this->buildConstructor) {
			$output[] = '	/**';
			$output[] = '	 * Object initializer';
			$output[] = '	 *';
			$output[] = '	 * @return void';
			$output[] = '	 */';
			$output[] = '	public function initializeObject() {';
			$output[] = $this->pad(2, join($this->output['ClassConstructor'], PHP_EOL));
			$output[] = '	}' . PHP_EOL;
		}
		$output[] = $this->pad(1, join($this->output['ClassAccessors'], PHP_EOL . PHP_EOL));
		$output[] = PHP_EOL . '}';

		$targetFile = $this->outputPath . $this->currentFile . '.php';
		file_put_contents($targetFile, join($output, PHP_EOL));
	}

	protected function writeModelClass() {
		$targetFile = $this->modelPath . $this->currentFile . '.php';
		if (file_exists($targetFile) === FALSE) {
			$className = sprintf('Tx_%s_Domain_Model_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->currentFile);
			$extendsClassName = sprintf('Tx_%s_Domain_Model_Base_%s', t3lib_div::underscoredToUpperCamelCase($this->extension), $this->currentFile);
			$output = array(
				'<?php',
				'class ' . $className . ' extends ' . $extendsClassName . ' {',
				'}'
			);
			file_put_contents($targetFile, join($output, PHP_EOL));
		}
	}

	protected function pad($size, $string) {
		$this->padSize = $size;
		$lines = split(PHP_EOL, $string);
		$lines = array_map(array($this, '_pad'), $lines);
		return join($lines, PHP_EOL);
	}

	protected function _pad($a) {
		if (empty($a)) {
			return '';
		}
		return str_repeat(chr(9), $this->padSize) . $a;
	}

	protected function reset() {
		$this->output = array();
		$this->buildConstructor = false;
	}

	protected function getKeyMarkers($values) {
		$key = $values['key'];

		$replace = array();
		$replace['###KEY###'] 					= MOC_Inflector::variable($key);
		$replace['###KEY_UPPER###'] 			= ucfirst(MOC_Inflector::variable($key));
		$replace['###KEY_PLURAL###'] 			= MOC_Inflector::camelize(MOC_Inflector::pluralize($key));
		$replace['###KEY_SINGULAR###']			= MOC_Inflector::camelize(MOC_Inflector::singularize($key));

		$replace['###KEY_LOWER###']				= MOC_Inflector::variable($key);
		$replace['###KEY_PLURAL_LOWER###']		= MOC_Inflector::variable(MOC_Inflector::pluralize($key));
		$replace['###KEY_SINGULAR_LOWER###'] 	= MOC_Inflector::variable(MOC_Inflector::singularize($key));

		$replace['###CAMEL_CASE###'] 			= MOC_Inflector::camelize(MOC_Inflector::variable($key));
		$replace['###THIS###']	 				= '$this';

		$replace['###DEFAULT_VALUE###'] 		= $values['default'];
		$replace['###VAR###']					= $values['var'];

		$replace['###SELF###']					= $this->className;
		$replace['###ANNOTATIONS###']			= '';

		if (!empty($values['annotations'])) {
			foreach ($values['annotations'] as $annotation) {
				$replace['###ANNOTATIONS###'] .= sprintf('@%s' . PHP_EOL . ' * ', $annotation);
			}
		}

		if (!empty($values['validations'])) {
			$validationRules = MOC_Array::normalize($values['validations']);
			foreach ($validationRules as $rule => $args) {
				$replace['###ANNOTATIONS###'] .= sprintf(PHP_EOL . ' * @validate %s%s', $rule, $this->getValidationParams($args, $rule, $key));
			}
		}

		if (!empty($replace['###ANNOTATIONS####'])) {
			$replace['###ANNOTATIONS####'] = PHP_EOL . ' *' . PHP_EOL . ' *' . $replace['###ANNOTATIONS###'];
			$replace['###ANNOTATIONS####'] = PHP_EOL . ' *' . PHP_EOL . ' *' . $replace['###ANNOTATIONS###'];
			$replace['###ANNOTATIONS####'] = PHP_EOL . ' *' . PHP_EOL . ' *' . $replace['###ANNOTATIONS###'];
		}

		return $replace;
	}

	protected function getValidationParams($args, $rule = null, $key = null) {
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
		throw new MOC_Exception(sprintf('Invalid validation params for rule %s in key %s in object %s', $rule, $key, $this->className));
	}

	protected function buildClassProperties($keys) {
		foreach ($keys as $key => $values) {
			$replace = $this->getKeyMarkers($values);
			$def = $this->loadClassPropertyTemplate($values['type']);
			$this->output['ClassProperties'][$key] = $this->applyMarkers($def, $replace);
		}
	}

	protected function buildClassAccessors($keys) {
		foreach($keys as $key => $values) {
			$replace = $this->getKeyMarkers($values);
			$def     = $this->loadClassAccessorTemplate($values['type']);
			$this->output['ClassAccessors'][$key] = $this->applyMarkers($def, $replace);
		}
	}


	protected function buildConstructor($keys) {
		foreach($keys as $key => $values) {
			// No need for constructor on normal strings
			if ($values['type'] === 'default') {
				continue;
			}

			// Avoid endless loop
			if ($values['var'] === $this->className) {
				continue;
			}
			$replace = $this->getKeyMarkers($values);
			if($values['var'] === 'DateTime') {
				$def 	 = $this->loadClassConstructorTemplate('DateTime');
			} else {
				$def 	 = $this->loadClassConstructorTemplate($values['type']);
			}
			$this->output['ClassConstructor'][$key] = $this->applyMarkers($def, $replace);
		}
	}

	protected function preprocessKeys($keys) {
		foreach ($keys as $key => &$values) {
			$values = array_merge($this->defaultTemplateValues, $values);

			if (($values['type'] == 'storage') || ($values['var'] == 'DateTime')) {
				$this->buildConstructor = true;
			}

			if (empty($values['key'])) {
				$values['key'] = $key;
			}

			if (stripos($values['key'], '_') !== false) {
				$fixed_key = MOC_Inflector::variable($values['key']);
				$values['key'] = $fixed_key;
			}

			// Make sure to switch to object template if it's a class
			if (class_exists($values['var']) && empty($values['extbase']['type'])) {
				$values['type'] = 'object';
			}
		}

		return $keys;
	}

	protected function applyMarkers($template, $markers) {
		return str_replace(array_keys($markers), array_values($markers), $template);
	}

	protected function loadClassPropertyTemplate($type) {
		return $this->loadTemplate('ClassProperty', $type);
	}

	protected function loadClassConstructorTemplate($type) {
		return  $this->loadTemplate('ClassConstructor', $type);
	}

	protected function loadClassAccessorTemplate($type) {
		return $this->loadTemplate('ClassAccessor', $type);
	}
	protected function loadTemplate($kind, $type) {
		$filename = $this->templatePath . $kind . DIRECTORY_SEPARATOR . $type . '.tpl';
		if (!is_file($filename)) {
			throw new MOC_Exception(sprintf('Template file %s does not exists', $filename));
		}

		return file_get_contents($filename);
	}
}
