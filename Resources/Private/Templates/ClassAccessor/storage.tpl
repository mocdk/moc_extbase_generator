/**
 * Setter for ###KEY###
 *
 * @param Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL### An Object Storage containing ###VAR### instances
 * @return void
 */
public function set###KEY_PLURAL###(Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL###) {
	###THIS###->###KEY_PLURAL_LOWER### = $###KEY_PLURAL###;
}

/**
 * Adds a ###KEY_SINGULAR###
 *
 * @param ###VAR### $###KEY###
 * @return void
 */
public function add###KEY_SINGULAR###(###VAR### $###KEY###) {
	###THIS###->###KEY_PLURAL_LOWER###->attach($###KEY###);
}

/**
 * Removes a ###KEY_SINGULAR###
 *
 * @param ###VAR### $###KEY###
 * @return void
 */
public function remove###KEY_SINGULAR###(###VAR### $###KEY###) {
	###THIS###->###KEY_PLURAL_LOWER###->detatch($###KEY###);
}

/**
 * Remove all ###KEY_PLURAL###
 *
 * @return void
 */
public function removeAll###KEY_PLURAL###() {
	###THIS###->###KEY_PLURAL_LOWER### = new Tx_Extbase_Persistence_ObjectStorage();
}

/**
 * Returns the ###KEY_PLURAL###
 *
 * @return An Tx_Extbase_Persistence_ObjectStorage holding instances of ###VAR###
 */
public function get###CAMEL_CASE###() {
	return clone ###THIS###->###KEY_PLURAL_LOWER###;
}