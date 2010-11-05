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
		$columns = $this->getColumns();
		foreach ($columns as $column => $settings) {
			$this->keys[$column] = array_merge($settings, (array)$settings['extbase']);

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

	protected function getColumns() {
		$columns = $GLOBALS['TCA'][$this->table]['columns'];

		$ctrl = $GLOBALS['TCA'][$this->table]['ctrl'];

		if (!empty($ctrl['tstamp'])) {
			$columns[$ctrl['tstamp']] = array('var' => 'DateTime',  'desc' => 'Timestamp for last update to the record');
		}

		if (!empty($ctrl['crdate'])) {
			$columns[$ctrl['crdate']] = array('var' => 'DateTime', 'desc' => 'Timestamp for the creation of the record');
		}

		if (!empty($ctrl['cruser_id'])) {
			$columns[$ctrl['cruser_id']] = array('var' => 'Tx_Extbase_Domain_Model_FrontendUser', 'desc' => 'The UID of the user who created the record');
		}

		if (!empty($ctrl['delete'])) {
			$columns[$ctrl['delete']] = array('var' => 'boolean', 'desc' => 'Has the record been marked as deleted?');
		}

		if (!empty($ctrl['enablecolumns']['disabled'])) {
			$columns[$ctrl['enablecolumns']['disabled']] = array('var' => 'boolean', 'desc' => 'Has the record been marked as hidden?');
		}

		return $columns;
	}
}