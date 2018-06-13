<?php
/**
 * Json manifest for cache busting.
 *
 * @package BootstrapFast
 */

/**
 * Updated assetloader.
 *
 * @param filename $filename text Gets the namifest filename.
 *
 * @return filename Returns the filename of a compiled asset is present otherwise it will return the normal filename for cache busting purposes.
 */
class JsonManifest {
	/**
	 * Manifest file location.
	 *
	 * @var $manifest Manifest file location.
	 */
	private $manifest;

	/**
	 * Constructor.
	 *
	 * @param [type] $manifest_path Path of manifest.
	 */
	public function __construct( $manifest_path ) {

		// TODO This always returns false, future rev might be needing to revise to do cache bustin.
		if ( file_exists( $manifest_path ) ) {
			$this->manifest = json_decode( wpcom_vip_file_get_contents( $manifest_path ), true );
		} else {
			$this->manifest = [];
		}
	}

	/**
	 * Get the manifest location.
	 *
	 * @return array manifest file array.
	 */
	public function get() {
		return $this->manifest;
	}

	/**
	 * [getPath description]
	 *
	 * @param  string $key     [description].
	 * @param  [type] $default [description].
	 * @return [type]          [description].
	 */
	public function get_path( $key = '', $default = null ) {
		$collection = $this->manifest;
		if ( is_null( $key ) ) {
			return $collection;
		}
		if ( isset( $collection[ $key ] ) ) {
			return $collection[ $key ];
		}
		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! isset( $collection[ $segment ] ) ) {
				return $default;
			} else {
				$collection = $collection[ $segment ];
			}
		}
		return $collection;
	}
}
