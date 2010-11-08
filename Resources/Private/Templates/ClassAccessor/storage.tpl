/**
 * Setter for ###KEY###
 *
 * @param Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL### An Object Storage containing ###VAR### instances
 * @return ###SELF###
 */
public function set###KEY_PLURAL###(Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL_LOWER###) {
	###THIS###->###KEY_PLURAL_LOWER### = $###KEY_PLURAL_LOWER###;
	return ###THIS###;
}

/**
 * Check if a ###KEY_SINGULAR### exists in the storage
 *
 * @return boolean
 */
public function has###KEY_SINGULAR###(###VAR### $###KEY###) {
    return $this->###KEY_PLURAL_LOWER###->offsetExists($###KEY###);
}

/**
 * Adds a ###KEY_SINGULAR###
 *
 * @param ###VAR### $###KEY###
 * @return ###SELF###
 */
public function add###KEY_SINGULAR###(###VAR### $###KEY###) {
	###THIS###->###KEY_PLURAL_LOWER###->attach($###KEY###);
	return ###THIS###;
}

/**
 * Removes a ###KEY_SINGULAR###
 *
 * @param ###VAR### $###KEY###
 * @return ###SELF###
 */
public function remove###KEY_SINGULAR###(###VAR### $###KEY###) {
	###THIS###->###KEY_PLURAL_LOWER###->detatch($###KEY###);
	return ###THIS###;
}

/**
 * Remove all ###KEY_PLURAL###
 *
 * @return ###SELF###
 */
public function removeAll###KEY_PLURAL###() {
	###THIS###->###KEY_PLURAL_LOWER### = new Tx_Extbase_Persistence_ObjectStorage();
	return ###THIS###;
}

/**
 * Returns the ###KEY_PLURAL###
 *
 * @return ###VAR### An Tx_Extbase_Persistence_ObjectStorage holding instances of ###VAR###
 */
public function get###CAMEL_CASE###() {
	return ###THIS###->###KEY_PLURAL_LOWER###;
}