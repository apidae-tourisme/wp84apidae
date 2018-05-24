<?php
class WP84ApidaeReqAPI{
    /**
     * retourne le détail d'un objet touristique
     * @param type $id identifiant de l'objet
     * @param type $fields champs retournés
     * @param type $locale langues demandées
     * @param type $bypass paramètre supplémentaires (prioritaires)
     * @return type string JSON 
     */
    public static function getOBT($id,$fields,$locale,$bypass){
        $qover=array();
        if($bypass != ''){
            parse_str($bypass,$qover);
        }
        $basepay= json_decode(get_option('wp84apidae_params', json_encode(array())),true);
            $aRK=array('idproj'=>'projetId','apikey'=>'apiKey');
            $query=array();
            foreach ($basepay as $ky=>$vl){
                if(in_array($ky,array_keys($aRK))){
                    $query[$aRK[$ky]]=$vl;
                }else{
                    $query[$ky]=$vl;
                }
            }
        if($fields!=''){
            $query['responseFields']=$fields;
        }
        $query['locales']=$locale;
        $mquery= array_merge($query, $qover);
        $url=sprintf('http://api.apidae-tourisme.com/api/v002/objet-touristique/get-by-id/%d/?',$id).http_build_query($mquery);  
        $md = md5($url);
        $output =self::getCache($md);
        if ($output === false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,5);
        $isValid=!curl_errno($ch);
        $output = curl_exec($ch);
        curl_close($ch);
        self::setCache($md, $output);
        }else{
            $isValid=true;
        }
        $ret=false;
        if($isValid===true){
            $ret = $output;
        }
        return $ret;
    }
    /**
     * retourne une cahine de caractère aléatoire de 8 caractères de longueur dans les chiffres et lettres minuscules
     * @return string
     */
    public static function genRandomSeed(){
        $sAR='abcdefghijklmnopqrstuvwxyz0123456789';
        $sRet='';
        for($i=0;$i<9;$i++){
            $sRet.= $sAR[rand(0, 35)];
        }
        return $sRet;
    }
    /**
     * exécute une requête de recherche Apidae
     * @param type $cnt nombre de résutats
     * @param type $basepay tableau de paramètres
     * @param type $first indice du premier résultat à retourner
     * @return array nombre de résultats, string json des résultats
     */
    public static function doReq($cnt,$basepay,$first=0){

            $aRK=array('idproj'=>'projetId','apikey'=>'apiKey');
            $payload=array();
            foreach ($basepay as $ky=>$vl){
                if(in_array($ky,array_keys($aRK))){
                    $payload[$aRK[$ky]]=$vl;
                }else{
                    $payload[$ky]=$vl;
                }
            }
            $payload['count']=$cnt;
            $payload['first']=$first;
            if(array_key_exists('order', $payload) && $payload['order']==='RANDOM'){
                if(array_key_exists('WP84randomSeed', $_SESSION)){
                    $payload['randomSeed']=$_SESSION['WP84randomSeed'];
                }else{
                    $seed = self::genRandomSeed();
                    $_SESSION['WP84randomSeed']=$seed;
                    $payload['randomSeed']=$seed;
                }
            }
            $query=array('query'=>json_encode($payload));
            $url='http://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques?'.http_build_query($query);
            $md = md5($url);
            $output =self::getCache($md);
            if($output===false){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url); 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT,5);
                $isValid=!curl_errno($ch);
                $output = curl_exec($ch);
                curl_close($ch);
                self::setCache($md, $output);
            }else{
                $isValid=true;
            }
            if($isValid===true){
                $rep=json_decode($output,true);
                if(is_array($rep)){
                $numFound= array_key_exists('numFound', $rep)?intval($rep['numFound']):0;
                $ret=($numFound>0)?$rep['objetsTouristiques']:array();              
                //$nbPages= $numFound>0?ceil($numFound/$cnt):0;
                return array($numFound,$ret);
                }else{
                    return array(0,false);
                }

            }else{
                return array(0,false);
            }
        }
        /**
         * récupère le contenu d'un fichier de cache s'il existe et s'il n'est pas expiré
         * @param type $md
         * @return boolean
         */
    static public function getCache($md){
        return get_transient('wp84apidae_'.$md);
    }
    /**
     * détermine le contenu d'un fichier de cache, supprime les fichiers expirés si wp_cron est désactivé
     * @param type $md
     * @param type $content
     * @return boolean
     */
    static public function setCache($md,$content){
        $iCache=get_option('wp84apidae_dureecache',15);
        if($iCache==0){
            return false;
        }else{
            return set_transient('wp84apidae_'.$md, $content, $iCache);
        }
    }
    /**
     * vide le cache des fichiers arrivés à expiration
     */
    static public function purgeCache(){
        delete_expired_transients(true);
    }
    /**
     * vide le cache
     */
    static public function emptyCache(){
        global $wpdb;

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_wp84apidae_' ) . '%'
		) );
    }
}
