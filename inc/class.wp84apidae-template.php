<?php
require_once WP84APIDAE_PLUGIN_INC . 'Skyscanner/JsonPath/JsonObject.php';
require_once WP84APIDAE_PLUGIN_INC . 'Skyscanner/JsonPath/InvalidJsonException.php';
require_once WP84APIDAE_PLUGIN_INC . 'Skyscanner/JsonPath/InvalidJsonPathException.php';
require_once WP84APIDAE_PLUGIN_INC . 'Skyscanner/Utilities/ArraySlice.php';

use JsonPath\JsonObject;

function toUrl( $string ) {
	return urlencode( esc_attr( str_replace( ' ', '-', sanitize_title( $string ) ) ) );
}

class WP84ApidaeTemplate {
	/**
	 * retourne le résultat HTML d'un template en fonction d'un JSON
	 *
	 * @param string $jsond JSON source
	 * @param string $sTxt template
	 *
	 * @return string
	 * @throws \JsonPath\InvalidJsonException
	 */
	public static function renderTemplate( $jsond, $sTxt ) {
		$oJS = new JsonObject( $jsond );

		return preg_replace_callback( '/{{([^}]+)}}/', function ( $matches ) use ( $oJS ) {
			$sR = '';
			if ( strpos( $matches[1], 'foreach' ) === 0 ) {
				$inputs = explode( '|||', $matches[1] );
				if ( count( $inputs ) === 3 ) {
					$aJP        = explode( ';;', $inputs[1] );
					$iCols      = count( $aJP );
					$aR         = array();
					$samelength = true;
					$lastlength = false;
					foreach ( $aJP as $k => $sJP ) {
						$func = explode( ':', $sJP );
						$iC   = count( $func );
						if ( $iC > 1 ) {
							$sJP = $func[ $iC - 1 ];
							unset( $func[ $iC - 1 ] );
							try {
								$aTV = $oJS->get( $sJP );
								foreach ( $func as $f ) {
									if ( ! isset( $aR[ $k ] ) ) {
										$aR[ $k ] = $aTV;
									}
									switch ( $f ) {
										case 'tourl':
											$aR[ $k ] = ( is_array( $aR[ $k ] ) && count( $aR[ $k ] ) > 0 ) ? array_map( 'toUrl', $aR[ $k ] ) : array();
											break;
										case 'tohttps':
											$aR[ $k ] = ( is_array( $aR[ $k ] ) && count( $aR[ $k ] ) > 0 ) ? array_map( function ( $str ) {
												return str_replace( 'http://', 'https://', $str );
											}, $aR[ $k ] ) : array();
											break;
										case 'nl2br':
										case 'addslashes':
										case 'stripslashes':
										case 'strtolower':
										case 'strtoupper':
										case 'esc_attr':
											$aR[ $k ] = ( is_array( $aR[ $k ] ) && count( $aR[ $k ] ) > 0 ) ? array_map( $f, $aR[ $k ] ) : array();
											break;
									}
								}
							} catch ( Exception $e ) {
								$aR[ $k ] = array();
							}

						} else {
							try {
								$aR[ $k ] = $oJS->get( $sJP );
							} catch ( Exception $e ) {
								$aR[ $k ] = array();
							}
						}
						$iCnt = count( $aR[ $k ] );
						if ( $lastlength !== false ) {
							if ( $lastlength != $iCnt ) {
								$samelength = false;
								break;
							} else {
								$lastlength = $iCnt;
							}
						} else {
							$lastlength = $iCnt;
						}
					}

					if ( $samelength === true and count( $aR ) > 0 and array_key_exists( 0, $aR ) and count( $aR[0] ) > 0 ) {

						$sR = '';
						for ( $d = 0; $d < count( $aR[0] ); $d ++ ) {
							$sTempR = $inputs[2];
							for ( $c = 0; $c < $iCols; $c ++ ) {
								$iK     = ( $iCols == 1 ) ? '' : $c;
								$sTempR = str_replace( '[val' . $iK . ']', $aR[ $c ][ $d ], $sTempR );
							}
							$sTempR = str_replace( '[count]', $d + 1, $sTempR );
							if ( $c > 0 ) {
								$sR .= $sTempR;
							}
						}
					}
				}
			} elseif ( strpos( $matches[1], 'forif' ) === 0 ) {
				$inputs = explode( '|||', $matches[1] );
				if ( count( $inputs ) === 3 ) {
					$aJP        = explode( ';;', $inputs[1] );
					$iCols      = count( $aJP );
					$aR         = array();
					$samelength = true;
					$lastlength = false;
					foreach ( $aJP as $k => $sJP ) {
						$func = explode( ':', $sJP );
						$iC   = count( $func );
						if ( $iC > 1 ) {
							$sJP = $func[ $iC - 1 ];
							unset( $func[ $iC - 1 ] );
							try {
								$aTV = $oJS->get( $sJP );
								foreach ( $func as $f ) {
									if ( ! isset( $aR[ $k ] ) ) {
										$aR[ $k ] = $aTV;
									}
									switch ( $f ) {
										case 'tourl':
											$aR[ $k ] = ( is_array( $aR[ $k ] ) && count( $aR[ $k ] ) > 0 ) ? array_map( 'toUrl', $aR[ $k ] ) : array();
											break;
										case 'tohttps':
											$aR[ $k ] = ( is_array( $aR[ $k ] ) && count( $aR[ $k ] ) > 0 ) ? array_map( function ( $str ) {
												return str_replace( 'http://', 'https://', $str );
											}, $aR[ $k ] ) : array();
											break;
										case 'nl2br':
										case 'addslashes':
										case 'stripslashes':
										case 'strtolower':
										case 'strtoupper':
										case 'esc_attr':
											$aR[ $k ] = ( is_array( $aR[ $k ] ) && count( $aR[ $k ] ) > 0 ) ? array_map( $f, $aR[ $k ] ) : array();
											break;
									}

								}
							} catch ( Exception $e ) {
								$aR[ $k ] = array();
							}
						} else {
							try {
								$aR[ $k ] = $oJS->get( $sJP );
							} catch ( Exception $e ) {
								$aR[ $k ] = array();
							}
						}
						$iCnt = count( $aR[ $k ] );
						if ( $lastlength !== false ) {
							if ( $lastlength != $iCnt ) {
								$samelength = false;
								break;
							} else {
								$lastlength = $iCnt;
							}
						} else {
							$lastlength = $iCnt;
						}
					}

					if ( $samelength === true and count( $aR ) > 0 and array_key_exists( 0, $aR ) and count( $aR[0] ) > 0 ) {
						$sR   = '';
						$aTmp = explode( ';;', $inputs[2] );
						$aEls = array();
						foreach ( $aTmp as $aTms ) {
							$aTsm = explode( '%%', $aTms );
							if ( count( $aTsm ) == 2 ) {
								$aEls[ $aTsm[0] ] = $aTsm[1];
							}
						}
						for ( $d = 0; $d < count( $aR[0] ); $d ++ ) {
							if ( array_key_exists( $aR[0][ $d ], $aEls ) ) {
								$sTempR = $aEls[ $aR[0][ $d ] ];
								for ( $c = 0; $c < $iCols; $c ++ ) {
									$iK     = ( $iCols == 1 ) ? '' : $c;
									$sTempR = str_replace( '[val' . $iK . ']', $aR[ $c ][ $d ], $sTempR );
								}
								$sTempR = str_replace( '[count]', $d + 1, $sTempR );
								if ( $c > 0 ) {
									$sR .= $sTempR;
								}
							} else {
								if ( array_key_exists( 'else', $aEls ) ) {
									$sTempR = $aEls['else'];
									for ( $c = 0; $c < $iCols; $c ++ ) {
										$iK     = ( $iCols == 1 ) ? '' : $c;
										$sTempR = str_replace( '[val' . $iK . ']', $aR[ $c ][ $d ], $sTempR );
									}
									$sTempR = str_replace( '[count]', $d + 1, $sTempR );
									if ( $c > 0 ) {
										$sR .= $sTempR;
									}
								}
							}
						}
					}
				}
			} elseif ( strpos( $matches[1], 'returnlink' ) === 0 ) {
				$inputs = explode( '|||', $matches[1] );
				if ( count( $inputs ) === 3 ) {
					if ( array_key_exists( 'wp84apidae_url_list', $_SESSION ) ) {
						$sR = str_replace( '[retlink]', $_SESSION['wp84apidae_url_list'], $inputs[1] );
					} else {
						$sR = $inputs[2];
					}
				}
			} else {
				$func = explode( ':', $matches[1] );
				$iC   = count( $func );
				if ( $iC > 1 ) {
					$sJP = $func[ $iC - 1 ];
					unset( $func[ $iC - 1 ] );
					try {
						$aTV = $oJS->get( $sJP );
						foreach ( $func as $i => $f ) {
							if ( empty( $sR ) ) {
								$sR = $aTV[0];
							}
							switch ( $f ) {
								case 'tourl':
									$sR = toUrl( $sR );
									break;
								case 'tohttps':
									$sR = str_replace( 'http://', 'https://', $sR );
									break;
								case 'nl2br':
								case 'addslashes':
								case 'stripslashes':
								case 'strtolower':
								case 'strtoupper':
								case 'esc_attr':
									$sR = call_user_func( $f, $sR );
									break;
								case 'maxlength':
									$sR = mb_substr( $sR, 0, $func[ $i + 1 ] );
									break;
							}


						}
					} catch ( Exception $e ) {
						$sR = '';
					}
				} else {
					try {
						$sR = $oJS->get( $matches[1] )[0];
					} catch ( Exception $e ) {
						$sR = '';
					}
				}
			}

			return $sR;
		}, $sTxt );
	}

	/**
	 * Retourne un résultat HTML d'un template de header ou footer
	 *
	 * @param string $sTxt template
	 * @param int $iMaxP nombre de pages maxi
	 * @param int $iCurrPage numero de la page en cours
	 * @param array $aParams paramètres de recherche url
	 * @param bool $bPaged si c'est pour une liste paginee
	 *
	 * @return string
	 */
	public static function templateHeadFoot( $sTxt, $iMaxP, $iCurrPage, $aParams, $bPaged ) {
		return preg_replace_callback( '/{{([^}]+)}}/', function ( $matches ) use ( $iMaxP, $iCurrPage, $aParams, $bPaged ) {
			if ( $bPaged === true && $iMaxP > 1 ) {

				$inputs = explode( '|||', $matches[1] );

				if ( count( $inputs ) == 6 ) {
					$ret = '';
					if ( $iCurrPage > 1 ) {
						$iBefP = ( $iCurrPage - 1 );
						$sBP   = ( $iBefP == 1 ) ? '' : $iBefP . '/';

						$sDDebut = get_query_var( 'datedebut', '' );
						$sDFin   = get_query_var( 'datefin', '' );
						$aQA     = ( count( $aParams ) > 0 ) ? array( 'apicritere' => implode( '/', $aParams ) ) : array();
						if ( $sDDebut !== '' && WP84Apidae::checkDateFormat( $sDDebut ) ) {
							$aQA['datedebut'] = $sDDebut;
						}
						if ( $sDFin !== '' && WP84Apidae::checkDateFormat( $sDFin ) ) {
							$aQA['datefin'] = $sDFin;
						}
						$lnk = count( $aQA ) > 0 ? add_query_arg( $aQA, get_page_link() . $sBP ) : get_page_link() . $sBP;
						$ret .= str_replace( '[link]', $lnk, $inputs[0] );
					}
					$ret .= $inputs[2];
					for ( $i = 1; $i <= $iMaxP; $i ++ ) {
						$sAddPg  = $i == 1 ? '' : "$i/";
						$sDDebut = get_query_var( 'datedebut', '' );
						$sDFin   = get_query_var( 'datefin', '' );
						$aQA     = ( count( $aParams ) > 0 ) ? array( 'apicritere' => implode( '/', $aParams ) ) : array();
						if ( $sDDebut !== '' && WP84Apidae::checkDateFormat( $sDDebut ) ) {
							$aQA['datedebut'] = $sDDebut;
						}
						if ( $sDFin !== '' && WP84Apidae::checkDateFormat( $sDFin ) ) {
							$aQA['datefin'] = $sDFin;
						}
						$sLnk = count( $aQA ) > 0 ? add_query_arg( $aQA, get_page_link() . $sAddPg ) : get_page_link() . $sAddPg;
						if ( $i == $iCurrPage ) {
							$ret .= str_replace( array( '[link]', '[nbpage]' ), array( $sLnk, $i ), $inputs[4] );
						} else {
							$ret .= str_replace( array( '[link]', '[nbpage]' ), array( $sLnk, $i ), $inputs[3] );
						}
					}
					$ret .= $inputs[5];
					if ( $iCurrPage < $iMaxP ) {
						$iNxtP = ( $iCurrPage + 1 );
						$sBP   = ( $iNxtP == 1 ) ? '' : $iNxtP . '/';


						$sDDebut = get_query_var( 'datedebut', '' );
						$sDFin   = get_query_var( 'datefin', '' );
						$aQA     = ( count( $aParams ) > 0 ) ? array( 'apicritere' => implode( '/', $aParams ) ) : array();
						if ( $sDDebut !== '' && WP84Apidae::checkDateFormat( $sDDebut ) ) {
							$aQA['datedebut'] = $sDDebut;
						}
						if ( $sDFin !== '' && WP84Apidae::checkDateFormat( $sDFin ) ) {
							$aQA['datefin'] = $sDFin;
						}

						$lnk = count( $aQA ) > 0 ? add_query_arg( $aQA, get_page_link() . $sBP ) : get_page_link() . $sBP;
						$ret .= str_replace( '[link]', $lnk, $inputs[1] );
					}

					return $ret;
				} else {
					return '';
				}
			} else {
				return '';
			}
		}, $sTxt, 1 );
	}

	/**
	 * retourne le HTML du moteur de recherche
	 *
	 * @param string $sTxt template
	 * @param array $aParms paramètres de recherche url
	 *
	 * @return array
	 */
	public static function templateMoteur( $sTxt, $aParms ) {
		$oUse          = array( 'moteur' => array(), 'categorie' => array() );
		$sR            = $sTxt;
		$iNbRs         = preg_match_all( '/{{([^}]+)}}/', $sTxt, $matches, PREG_PATTERN_ORDER );
		$aToReplace    = array();
		$aToBeReplaced = array();
		if ( $iNbRs !== false && $iNbRs > 0 ) {
			for ( $i = 0; $i < $iNbRs; $i ++ ) {
				$inputs = explode( '|||', $matches[1][ $i ] );

				if ( count( $inputs ) > 1 && $inputs[0] === 'critere' && count( $inputs ) === 7 ) {
					$oUse['moteur'][ $inputs[3] ] = array(
						'label'     => $inputs[2],
						'code'      => $inputs[4],
						'categorie' => $inputs[1]
					);
					if ( ! array_key_exists( $inputs[1], $oUse['categorie'] ) ) {
						$oUse['categorie'][ $inputs[1] ] = array();
					}
					$oUse['categorie'][ $inputs[1] ][] = $inputs[3];
				}
			}
			for ( $i = 0; $i < $iNbRs; $i ++ ) {
				$inputs = explode( '|||', $matches[1][ $i ] );
				if ( count( $inputs ) > 1 && $inputs[0] === 'recap' && count( $inputs ) === 2 ) {
					$srRmLinks = '';
					foreach ( $aParms as $sParams ) {
						$aTmpPars = $aParms;
						if ( ( $key = array_search( $sParams, $aTmpPars ) ) !== false ) {
							unset( $aTmpPars[ $key ] );
						}
						$sDDebut = get_query_var( 'datedebut', '' );
						$sDFin   = get_query_var( 'datefin', '' );
						$aQA     = ( count( $aTmpPars ) > 0 ) ? array( 'apicritere' => implode( '/', $aTmpPars ) ) : array();
						if ( $sDDebut !== '' && WP84Apidae::checkDateFormat( $sDDebut ) ) {
							$aQA['datedebut'] = $sDDebut;
						}
						if ( $sDFin !== '' && WP84Apidae::checkDateFormat( $sDFin ) ) {
							$aQA['datefin'] = $sDFin;
						}
						$srRmLinks .= str_replace( array(
							'[rmlink]',
							'[label]'
						), array(
							count( $aQA ) > 0 ? add_query_arg( $aQA, get_page_link() ) : get_page_link(),
							$oUse['moteur'][ $sParams ]['label']
						), $inputs[1] );
					}
					$aToReplace[]    = $matches[0][ $i ];
					$aToBeReplaced[] = $srRmLinks;
				} elseif ( count( $inputs ) > 1 && $inputs[0] === 'tout' && count( $inputs ) === 4 ) {
					$bRemoveLink = true;
					if ( empty( get_query_var( 'apicritere' ) ) ) {
						$bRemoveLink = false;
					}
					$aToReplace[]    = $matches[0][ $i ];
					$aToBeReplaced[] = str_replace( array(
						'[link]',
						'[label]'
					), array(
						remove_query_arg( array( 'apicritere' ) ),
						$inputs[1]
					), $bRemoveLink === true ? $inputs[2] : $inputs[3] );
				} elseif ( count( $inputs ) > 1 && $inputs[0] === 'critere' && count( $inputs ) === 7 ) {
					//lien pour enlever le parametre si déjà sélectionné
					$aDf         = array_intersect( $oUse['categorie'][ $oUse['moteur'][ $inputs[3] ]['categorie'] ], $aParms );
					$bRemoveLink = false;
					if ( in_array( $inputs[3], $aParms ) ) {
						$aTmpPars = $aParms;
						if ( ( $key = array_search( $inputs[3], $aTmpPars ) ) !== false ) {
							unset( $aTmpPars[ $key ] );
						}
						$bRemoveLink = true;
					} elseif ( count( $aDf ) > 0 ) {
						//enleve le parametre de la même categorie en faisant la différence des tableaux
						$aTmpPars = array_diff( $aParms, $aDf );
						//ajout du paramètre
						$aTmpPars[] = $inputs[3];
					} else {
						//nelle catégorie ajoute
						$aTmpPars   = $aParms;
						$aTmpPars[] = $inputs[3];
					}
					$aToReplace[] = $matches[0][ $i ];


					$sDDebut = get_query_var( 'datedebut', '' );
					$sDFin   = get_query_var( 'datefin', '' );
					$aQA     = ( count( $aTmpPars ) > 0 ) ? array( 'apicritere' => implode( '/', $aTmpPars ) ) : array();
					if ( $sDDebut !== '' && WP84Apidae::checkDateFormat( $sDDebut ) ) {
						$aQA['datedebut'] = $sDDebut;
					}
					if ( $sDFin !== '' && WP84Apidae::checkDateFormat( $sDFin ) ) {
						$aQA['datefin'] = $sDFin;
					}
					$aToBeReplaced[] = str_replace( array(
						'[link]',
						'[label]'
					), array(
						count( $aQA ) > 0 ? add_query_arg( $aQA, get_page_link() ) : get_page_link(),
						$inputs[2]
					), $bRemoveLink === true ? $inputs[6] : $inputs[5] );
				}
			}
			$sR = str_replace( $aToReplace, $aToBeReplaced, $sTxt );
		}


		return array( $oUse, $sR );
	}

	/**
	 * retourne le résultat d'un JSONPath en fonction d'un JSON
	 *
	 * @param string $jsond source JSON
	 * @param string $sJpath JSONPath
	 *
	 * @return string
	 */
	public static function testJSONPath( $jsond, $sJpath ) {
		try {
			$oJS      = new JsonObject( $jsond );
			$bProcess = true;
		} catch ( Exception $e ) {
			$aR       = 'erreur...';
			$bProcess = false;
		}
		if ( $bProcess === true ) {
			try {
				$aRT = $oJS->get( $sJpath );
				$aR  = json_encode( $aRT );
			} catch ( Exception $e ) {
				$aR = 'erreur...';
			}
		} else {
			$aR = 'json source invalide...';
		}

		return $aR;
	}
}
