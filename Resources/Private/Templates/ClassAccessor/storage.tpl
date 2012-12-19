/**
 * @param ###VAR### $###KEY_SINGULAR_LOWER###
 * @return boolean
 */
public function has###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER###) {
	return $this->###KEY_LOWER###->contains($###KEY_SINGULAR_LOWER###);
}

/**
 * @param ###VAR### $###KEY_SINGULAR_LOWER###
 * @return ###SELF###
 */
public function add###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER###) {
	###THIS###->###KEY_LOWER###->attach($###KEY_SINGULAR_LOWER###);
	return ###THIS###;
}

/**
 * @param ###VAR### $###KEY_SINGULAR_LOWER###
 * @return ###SELF###
 */
public function remove###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER###) {
	###THIS###->###KEY_LOWER###->detach($###KEY_SINGULAR_LOWER###);
	return ###THIS###;
}

/**
 * @return ###SELF###
 */
public function remove###KEY_PLURAL###() {
	###THIS###->###KEY_LOWER### = new Tx_Extbase_Persistence_ObjectStorage();
	return ###THIS###;
}

/**
 * @param Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL_LOWER### An Object Storage containing ###VAR### instances
 * @return ###SELF###
 */
public function set###KEY_PLURAL###(Tx_Extbase_Persistence_ObjectStorage $###KEY_PLURAL_LOWER###) {
	###THIS###->###KEY_LOWER### = $###KEY_PLURAL_LOWER###;
	return ###THIS###;
}

/**
 * @return Tx_Extbase_Persistence_ObjectStorage<###VAR###>
 */
public function get###KEY_PLURAL###() {
	return ###THIS###->###KEY_LOWER###;
}