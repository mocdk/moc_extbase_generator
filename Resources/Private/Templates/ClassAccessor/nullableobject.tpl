/**
 * @param ###VAR### $###KEY###
 * @return ###SELF###
 */
public function set###KEY_SINGULAR###(###VAR### $###KEY_SINGULAR_LOWER### = NULL) {
	###THIS###->###KEY_LOWER### = $###KEY_SINGULAR_LOWER###;
	return ###THIS###;
}

/**
 * @return ###VAR###
 */
public function get###KEY_SINGULAR###() {
	return ###THIS###->###KEY_LOWER###;
}