<?php
class MOC_Extbase_Generator_TCA extends MOC_Extbase_Generator_Configuration {
	public function getKeys() {
		if (empty($this->table)) {
			throw new MOC_Exception('Undefined table setting for generator configuration object');
		}
		if (empty($GLOBALS['TCA'][$this->table])) {
			throw new MOC_Exception(sprintf('No TCA configuration for table %s available', $this->table));
		}

		t3lib_div::loadTCA($this->table);

		$this->generateKeys();
		return $this->keys;
	}

	protected function generateKeys() {
		foreach ($GLOBALS['TCA'][$this->table]['columns'] as $column => $settings) {
			$this->keys[$column] = (array)$settings['extbase'];

			if (empty($this->keys[$column]['var'])) {
				$var = $this->guessVar($settings);
				if (!empty($var)) {
					$this->keys[$column]['var'] = $var;
				}
			}
		}
	}

	protected function guessVar($settings) {
		switch ($settings['config']['type']) {
			case 'check' :
				return 'boolean';
		}
	}

	protected function translate($label) {
		return trim($GLOBALS['LANG']->sL($label), ':');
	}
}