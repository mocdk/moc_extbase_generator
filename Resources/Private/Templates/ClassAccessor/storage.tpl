/**
 * Setter for ###KEY###
 *
 * @param Tx_Extbase_Persistence_ObjectStorage $###KEY_SINGULAR_LOWER### An Object Storage containing ###VAR### instances
 * @return ###SELF###
 */
public function set###KEY_PLURAL###(Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL_LOWER###) {
	###THIS###->###KEY_LOWER### = $###KEY_PLURAL_LOWER###;
	return ###THIS###;
}

/**
 * Check if a ###KEY_SINGULAR_LOWER### exists in the storage
 *
 * @return boolean
 */
public function has###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER###) {
    return true === $this->###KEY_LOWER###->contains($###KEY_SINGULAR_LOWER###);
}

/**
 * Adds a ###KEY_SINGULAR_LOWER###
 *
 * @param ###VAR### $###KEY_SINGULAR_LOWER###
 * @return ###SELF###
 */
public function add###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER###) {
	###THIS###->###KEY_LOWER###->attach($###KEY_SINGULAR_LOWER###);
	return ###THIS###;
}

/**
 * Removes a ###KEY_SINGULAR_LOWER###
 *
 * @param ###VAR### $###KEY_SINGULAR_LOWER###
 * @return ###SELF###
 */
public function remove###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER###) {
	###THIS###->###KEY_LOWER###->detatch($###KEY_SINGULAR_LOWER###);
	return ###THIS###;
}

/**
 * Remove all ###KEY_PLURAL_LOWER###
 *
 * @return ###SELF###
 */
public function removeAll###KEY_PLURAL###() {
	###THIS###->###KEY_LOWER### = new Tx_Extbase_Persistence_ObjectStorage();
	return ###THIS###;
}

/**
 * Returns the ###KEY_PLURAL_LOWER### ###VAR###
 *
 * @return Tx_Extbase_Persistence_ObjectStorage<###VAR###> An Tx_Extbase_Persistence_ObjectStorage holding instances of ###VAR###
 */
public function get###CAMEL_CASE###() {
	return ###THIS###->###KEY_LOWER###;
}