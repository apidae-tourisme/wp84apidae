<?php

class WP84ApidaeReqAPI {
	/**
	 * retourne le détail d'un objet touristique
	 *
	 * @param int $id identifiant de l'objet
	 * @param array $fields champs retournés
	 * @param string $locale langues demandées
	 * @param string $bypass paramètre supplémentaires (prioritaires)
	 *
	 * @return string JSON
	 */
	public static function getOBT( $id, $fields, $locale, $bypass ) {
		$qover = array();
		if ( $bypass != '' ) {
			parse_str( $bypass, $qover );
		}
		$basepay = json_decode( get_option( 'wp84apidae_params', json_encode( array() ) ), true );
		$aRK     = array( 'idproj' => 'projetId', 'apikey' => 'apiKey' );
		$query   = array();
		foreach ( $basepay as $ky => $vl ) {
			if ( in_array( $ky, array_keys( $aRK ) ) ) {
				$query[ $aRK[ $ky ] ] = $vl;
			} else {
				$query[ $ky ] = $vl;
			}
		}
		if ( $fields != '' ) {
			$query['responseFields'] = $fields;
		}
		$query['locales'] = $locale;
		$mquery           = array_merge( $query, $qover );
		$url              = sprintf( 'https://api.apidae-tourisme.com/api/v002/objet-touristique/get-by-id/%d/?', $id ) . http_build_query( $mquery );
		$md               = md5( $url );
		$output           = self::getCache( $md );
		if ( $output === false ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			$isValid = ! curl_errno( $ch );
			$output  = curl_exec( $ch );
			curl_close( $ch );
			self::setCache( $md, $output );
		} else {
			$isValid = true;
		}
		$ret = false;
		if ( $isValid === true ) {
			$ret = $output;
		}

		return $ret;
	}

	/**
	 * retourne une chaine de caractère aléatoire de 8 caractères de longueur dans les chiffres et lettres minuscules
	 * @return string
	 */
	public static function genRandomSeed() {
		$sAR  = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$sRet = '';
		for ( $i = 0; $i < 9; $i ++ ) {
			$sRet .= $sAR[ rand( 0, 35 ) ];
		}

		return $sRet;
	}

	/**
	 * exécute une requête de recherche Apidae
	 *
	 * @param int $cnt nombre de résutats
	 * @param array $basepay tableau de paramètres
	 * @param int $first indice du premier résultat à retourner
	 *
	 * @return array nombre de résultats, string json des résultats
	 */
	public static function doReq( $cnt, $basepay, $first = 0 ) {
		$aRK     = array( 'idproj' => 'projetId', 'apikey' => 'apiKey' );
		$payload = array();
		foreach ( $basepay as $ky => $vl ) {
			if ( in_array( $ky, array_keys( $aRK ) ) ) {
				$payload[ $aRK[ $ky ] ] = $vl;
			} else {
				$payload[ $ky ] = $vl;
			}
		}
		$payload['count'] = $cnt;
		$payload['first'] = $first;
		if ( array_key_exists( 'order', $payload ) && $payload['order'] === 'RANDOM' ) {
			if ( array_key_exists( 'WP84randomSeed', $_SESSION ) ) {
				$payload['randomSeed'] = $_SESSION['WP84randomSeed'];
			} else {
				$seed                       = self::genRandomSeed();
				$_SESSION['WP84randomSeed'] = $seed;
				$payload['randomSeed']      = $seed;
			}
		}
		$query   = array( 'query' => json_encode( $payload ) );
		$url     = 'https://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques?' . http_build_query( $query );
		$md      = md5( $url );
		$cache  = self::getCache( $md );
		$isValid = true;
		$rep = false;
		if ( $cache === false ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			$isValid = ! curl_errno( $ch );
			$rep  = curl_exec( $ch );
			curl_close( $ch );
			$rep = json_decode( $rep, true );
		}

		if ( $isValid === true ) {
			if ( is_array( $rep ) ) {
				if ( $cache === false ) {
					self::setCache( $md, $rep);
				}
				$numFound = array_key_exists( 'numFound', $rep ) ? intval( $rep['numFound'] ) : 0;
				$ret      = ( $numFound > 0 ) ? $rep['objetsTouristiques'] : array();

				//$nbPages= $numFound>0?ceil($numFound/$cnt):0;
				return array( $numFound, $ret );
			} else {
				return array( 0, false );
			}
		} else {
			return array( 0, false );
		}
	}

	/**
	 * récupère le contenu du cache s'il existe et s'il n'est pas expiré
	 *
	 * @param string $md
	 *
	 * @return boolean
	 */
	static public function getCache( $md ) {
		return get_transient( 'wp84apidae_' . $md );
	}

	/**
	 * détermine le contenu d'un fichier de cache
	 *
	 * @param string $md
	 * @param string $content
	 *
	 * @return boolean
	 */
	static public function setCache( $md, $content ) {
		$iCache = get_option( 'wp84apidae_dureecache', 15 );
		if ( $iCache == 0 ) {
			return false;
		} else {
			return set_transient( 'wp84apidae_' . $md, $content, $iCache * 60 );
		}
	}

	/**
	 * vide le cache des fichiers arrivés à expiration
	 */
	static public function purgeCache() {
		delete_expired_transients( true );
	}

	/**
	 * vide le cache
	 */
	static public function emptyCache() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wp84apidae_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wp84apidae_' ) . '%'
		) );
	}
}
