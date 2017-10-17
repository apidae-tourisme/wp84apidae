<?php
/**
 * Classe principale du plugin, methodes statiques
 */
class WP84Apidae{
    /**
     * Fonction outil pour effectuer des recherches d'existence de cles multiples dans un tableau
     * @param type $keys
     * @param type $search_r
     * @return boolean
     */
    protected static function array_key_exists_r($keys, $search_r) {
        $keys_r = explode('|',$keys);
        foreach($keys_r as $key){
            if(!array_key_exists($key,$search_r)){
                return false; 
            }
        }
        return true;
    }
/**
 * Fonction outil pour vérifier une date (format + date réelle)
 *
 * @param string $date valide si en format "YYYY-MM-DD"
 *
 * @return bool
 */
    public static function checkDateFormat($date)
{
  // match the format of the date
  if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts))
  {
    // check whether the date is valid or not
    if (checkdate($parts[2],$parts[3],$parts[1])) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}
    /**
     * Activation du plugin et creation de la table de configuration necessaire
     * @global type $wpdb
     */
    public static function plugin_activation (){
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . "wp84apidaeplugin";
        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          confvalue text NOT NULL,
          descript text NOT NULL,
          typeconf varchar(10) NOT NULL,
          PRIMARY KEY  (id),
          KEY typeconf_idx (typeconf(10))
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        if (! wp_next_scheduled ( 'wp84apidae_cacheclear' )) {
            wp_schedule_event(time(), 'hourly', 'wp84apidae_cacheclear');
        }
        if(!mkdir(WP84APIDAE_PLUGIN_DIR.DIRECTORY_SEPARATOR.'tmp',0755)){
            set_transient( 'wp84apidae_msg_status', 'dir_tmp_error', 5 );
        }else{
            if ( version_compare( $GLOBALS['wp_version'], '4.7', '>=') && version_compare(phpversion(), '5.6.0', '>=') ){
                set_transient( 'wp84apidae_msg_status', 'install_success', 5 );
            }else{
                set_transient( 'wp84apidae_msg_status', 'version_doubt', 5 );
            }
            
        }

    }
    /**
     * Notices admin sur l'install du plugin
     */
    public static function do_admin_notice() {
        if( get_transient( 'wp84apidae_msg_status' ) === 'dir_tmp_error' ){
        ?>
        <div class="notice notice-error is-dismissible" >
            <p>Le répertoire de cache n'a pas pu être créé !! Le plugin ne pourra pas utiliser de cache pour les requêtes apidae, vous ne disposez pas des droits suffisant pour créer un dossier tmp dans le répertoire du plugin.</p>
        </div>
        <?php
        }elseif(get_transient( 'wp84apidae_msg_status' ) === 'install_success' ){
        ?>
        <div class="notice notice-success is-dismissible" >
            <p>Le plugin Wordpress Apidae a été installé avec succès !</p>
        </div>
        <?php
        }elseif(get_transient( 'wp84apidae_msg_status' ) === 'version_doubt' ){
        ?>
        <div class="notice notice-warning is-dismissible" >
            <p>Ce plugin a été développé sur php 5.6 et Wordpress 4.7. Votre version de php et/ou Wordpress semble antérieure, le plugin pourrait ne pas fonctionner correctement...</p>
            <?php
            echo version_compare( $GLOBALS['wp_version'], '4.7', '>=')?'wordpress version suffisante':'wordpress version insuffisante<br/>';
            echo version_compare(phpversion(), '5.6.0', '>=')?', php version suffisante':', php version insuffisante';
            ?>
        </div>
        <?php
        }
        
        delete_transient( 'wp84apidae_msg_status' );
    }

    /**
     * methode d'initialisation
     */
    public static function init(){
        add_shortcode( 'apidaelist', array( 'WP84Apidae', 'apidaelist_shorttag' ) );
        add_filter( 'query_vars', array( 'WP84Apidae','add_query_vars_filter') );
        add_filter('the_posts',array( 'WP84Apidae','fakepage_WP84_detect'),-10);
    }
    /**
     * ajout de variable(s) d'url query supplementaire(s) pour les pages de liste
     * @param array $vars
     * @return string
     */
    public static function add_query_vars_filter ($vars){
        $vars[] = "apisearch";
        $vars[] = "datedebut";
        $vars[] = "datefin";
        return $vars;
    }
    /**
     * Ajout des règles de rewrite avec flush_rules si les donnees ne sont pas en base, plus demarrage de session.
     * @global type $wp_rewrite
     */
    public static function add_wp84_rewrite(){
        global $wp_rewrite;
        $sFakePageUrl='index.php?pagename=apiref&typeoi=$matches[1]&commune=$matches[2]&nom=$matches[3]&templatedetailid=$matches[5]&oid=$matches[4]';
        add_rewrite_tag('%templatedetailid%','([^&]+)');
        add_rewrite_tag('%oid%','([^&]+)');
        add_rewrite_tag('%typeoi%','([^&]+)');
        add_rewrite_tag('%commune%','([^&]+)');
        add_rewrite_tag('%nom%','([^&]+)');
        $rule='^apiref/([^/]+)/([^/]+)/([^/]+)/([0-9]+)-([0-9]+)';
        add_rewrite_rule($rule,$sFakePageUrl,'top');
        $rules = get_option( 'rewrite_rules' );
        if ( ! isset( $rules[$rule] ) ) { 
            $wp_rewrite->flush_rules();
        }
        if( !session_id() ){
          session_start();
        }
    }
    
    /**
     * desactivation du plugin
     * @global type $wpdb
     */
    public static function plugin_deactivation (){
        global $wpdb;
        delete_option( 'wp84apidae_params' );
        delete_option( 'wp84apidae_dureecache' );
        $table_name = $wpdb->prefix . "wp84apidaeplugin";
        $sql = "DROP TABLE $table_name;";
        $wpdb->query($sql);
        wp_clear_scheduled_hook('wp84apidae_cacheclear');

        if(is_dir(WP84APIDAE_PLUGIN_DIR.'/tmp')){
            WP84ApidaeReqAPI::emptyCache();
            rmdir(WP84APIDAE_PLUGIN_DIR.'/tmp');
        }
        //soft flush le plugin ne modifie pas le .htaccess
        flush_rewrite_rules(false);
    }
    /**
     * retourne la liste des configurations de listes
     * @global type $wpdb
     * @param type $id
     * @return array
     */
    public static function getlist($id=''){
        global $wpdb;
        $add='';
        if($id !=='' && filter_var($id, FILTER_VALIDATE_INT)){
            $add=' AND id='.$id;
        }
        $resultats = $wpdb->get_results("SELECT CONCAT('liste',id) as nameid,descript,confvalue FROM {$wpdb->prefix}wp84apidaeplugin WHERE typeconf='liste'$add ORDER BY id ASC", ARRAY_A) ;
        return stripslashes_deep($resultats);
    }
    /**
     * retourne la liste des templates de listes
     * @global type $wpdb
     * @param type $id
     * @return array
     */
    public static function getlisttemplate($id=''){
        global $wpdb;
        $add='';
        if($id !=='' && filter_var($id, FILTER_VALIDATE_INT)){
            $add=' AND id='.$id;
        }
        $resultats = $wpdb->get_results("SELECT CONCAT('tliste',id) as nameid,descript,confvalue FROM {$wpdb->prefix}wp84apidaeplugin WHERE typeconf='tliste'$add ORDER BY id ASC", ARRAY_A) ;
        return stripslashes_deep($resultats);
    }
    /**
     * retourne la liste des moteurs
     * @global type $wpdb
     * @param type $id
     * @return array
     */
    public static function getlistmoteur($id=''){
        global $wpdb;
        $add='';
        if($id !=='' && filter_var($id, FILTER_VALIDATE_INT)){
            $add=' AND id='.$id;
        }
        $resultats = $wpdb->get_results("SELECT CONCAT('moteur',id) as nameid,descript,confvalue FROM {$wpdb->prefix}wp84apidaeplugin WHERE typeconf='moteur'$add ORDER BY id ASC", ARRAY_A) ;
        return stripslashes_deep($resultats);
    }
    /**
     * retourne la liste des template de detail
     * @global type $wpdb
     * @param type $id
     * @return array
     */
    public static function getlistdetail($id=''){
        global $wpdb;
        $add='';
        if($id !=='' && filter_var($id, FILTER_VALIDATE_INT)){
            $add=' AND id='.$id;
        }
        $resultats = $wpdb->get_results("SELECT CONCAT('detail',id) as nameid,descript,confvalue FROM {$wpdb->prefix}wp84apidaeplugin WHERE typeconf='detail'$add ORDER BY id ASC", ARRAY_A) ;
        return stripslashes_deep($resultats);
    }
    /**
     * nettoyage des éléments périmés en cache
     */
    public static function do_clear_cache(){
        WP84ApidaeReqAPI::purgeCache();
    }
    /**
     * retourne la liste des éléments en base sans le détail
     * @global type $wpdb
     * @return array
     */
    public static function getlistall(){
        global $wpdb;
        $resultats = $wpdb->get_results("SELECT typeconf, id, CONCAT(typeconf,id) as nameid,descript FROM {$wpdb->prefix}wp84apidaeplugin ORDER BY typeconf ASC", ARRAY_A) ;
        return stripslashes_deep($resultats);
    }
    
    
    /**
     * initialise le shortag pour l'affichage des listes
     * @param type $atts
     * @return string
     */
    public static function apidaelist_shorttag($atts){
        if(array_key_exists('list', $atts) && filter_var($atts['list'], FILTER_VALIDATE_INT)&& array_key_exists('detail', $atts) && array_key_exists('templist', $atts) && filter_var($atts['detail'], FILTER_VALIDATE_INT) && filter_var($atts['templist'], FILTER_VALIDATE_INT)){
            $basepar=get_option('wp84apidae_params',array());
            $listpar=self::getlist($atts['list']);
            $iBnb=array_key_exists('nb', $atts)?$atts['nb']:20;
            $tlistpar=self::getlisttemplate($atts['templist']);
            $aDatePars=array();
            if(count($listpar) > 0 && count($tlistpar)>0){
                $iCurrPage = get_query_var('page',1);
                $iCurrPage = $iCurrPage==0?1:$iCurrPage;
                $sMoteur='';
                $oObj=null;
                $aParams=get_query_var('apisearch','')!=''?explode('/',get_query_var('apisearch','')):array();
                $sDDebut=get_query_var('datedebut','');
                $sDFin=get_query_var('datefin','');
                $aQA=(count($aParams)>0)?array('apisearch'=>implode('/',$aParams)):array();
                if($sDDebut!=='' && self::checkDateFormat($sDDebut)){
                    $aQA['datedebut']=$sDDebut;
                    $aDatePars['dateDebut']=$sDDebut;
                }
                if($sDFin!=='' && self::checkDateFormat($sDFin)){
                    $aQA['datefin']=$sDFin;
                    $aDatePars['dateFin']=$sDFin;
                }
                if(array_key_exists('paged', $atts)){
                    $sCnbPage=($iCurrPage>1)?$iCurrPage.'/':'';
                    $_SESSION['wp84apidae_url_list'] = count($aQA)>0?add_query_arg($aQA,get_page_link().$sCnbPage):get_page_link().$sCnbPage;
                }
                if(array_key_exists('paged', $atts) && array_key_exists('moteur', $atts)  && filter_var($atts['moteur'], FILTER_VALIDATE_INT)){
                    $aMoteur=self::getlistmoteur($atts['moteur']);
                    if(count($aMoteur) > 0 ){
                        list($oObj,$sMoteur)=WP84ApidaeTemplate::templateMoteur($aMoteur[0]['confvalue'], $aParams);
                    }
                }
                $parbase=json_decode($listpar[0]['confvalue'],true);
                if($iCurrPage>1 && array_key_exists('paged', $atts)){
                    $first=intval($iCurrPage-1)*$iBnb;
                }else{
                    $first=0;
                }
                if($oObj!==null){
                    $aCQ=array();

                    if(array_key_exists('criteresQuery',$parbase)){
                        $aCQ[]=$parbase['criteresQuery'];
                        unset($parbase['criteresQuery']);
                    }
                    $aTmpQr= $parbase;
                    foreach($aParams as $sParam){
                        $aTMQR=json_decode('{'.$oObj['moteur'][$sParam]['code'].'}',true);

                        if(array_key_exists('criteresQuery',$aTMQR)){
                            $aCQ[]=$aTMQR['criteresQuery'];
                            unset($aTMQR['criteresQuery']);
                        }
                        $aTmpQr=array_merge($aTmpQr,$aTMQR);
                    }
                    if(count($aCQ)>0){
                        $aTmpQr['criteresQuery']=implode(' ',$aCQ);
                    }
                    $payload= array_merge(json_decode($basepar,true),$aTmpQr);
                }else{
                    $payload= array_merge(json_decode($basepar,true),$parbase);
                }
                $payload= array_merge($payload,$aDatePars);
                list($nbOBT,$res)=WP84ApidaeReqAPI::doReq($iBnb,$payload,$first);
                $tconf=json_decode($tlistpar[0]['confvalue'],true);
                $content=array_key_exists('paged', $atts)?'<div id="wp84apidae-list">':'';
                foreach($res as $rs){
                    $content.=str_replace('[detailid]',$atts['detail'],WP84ApidaeTemplate::renderTemplate($rs, $tconf['content']));
                }
                $iMaxP=$nbOBT>0?ceil($nbOBT/$iBnb):0;
                    $sHeader= WP84ApidaeTemplate::templateHeadFoot($tconf['header'], $iMaxP, $iCurrPage, $aParams,array_key_exists('paged', $atts));             
                    $sFooter='<div id="wp84apidae-pages">';
                    $sFooter.= WP84ApidaeTemplate::templateHeadFoot($tconf['footer'], $iMaxP, $iCurrPage, $aParams,array_key_exists('paged', $atts));
                    $sFooter.='</div>';
                $content.=array_key_exists('paged', $atts)?'</div>':'';
                $ret = str_replace(array('[count]','[moteur]'),array($nbOBT,$sMoteur),$sHeader).$content.str_replace(array('[count]'),array($nbOBT),$sFooter);
                //js - css
                if(array_key_exists('css', $atts)){
                    foreach (explode(',',$atts['css']) as $k=>$cssfl){
                        wp_enqueue_style('wp84apidaecsstag'.$k, (strpos($cssfl,'http://')===0 || strpos($cssfl,'https://')===0 || strpos($cssfl,'//')===0)?$cssfl:'/'.$cssfl);
                    }
                }
                if(array_key_exists('js', $atts)){ 
                    foreach (explode(',',$atts['js']) as  $k=>$jsfl){
                        wp_enqueue_script('wp84apidaejstag'.$k, (strpos($jsfl,'http://')===0 || strpos($jsfl,'https://')===0 || strpos($jsfl,'//')===0)?$jsfl:'/'.$jsfl);
                    }
                }
            }else{
                $ret='';
            }
            
        }else{
            $ret='';
        }
        return $ret;
    }
    /**
     * Detection de la fausse page pour creer un fausse page de detail d'objet touristique en utilisant le template wordpress en cours
     * @global type $wp
     * @global type $wp_query
     * @global boolean $fakepage_WP84_detect
     * @param type $posts
     * @return \stdClass
     */
    public static function fakepage_WP84_detect($posts){
    global $wp;
    global $wp_query;
    global $fakepage_WP84_detect; // used to stop double loading
        $fakepage_WP84_url = 'apiref'; // URL of the fake page
    if ( !$fakepage_WP84_detect && (is_array($wp->query_vars) && self::array_key_exists_r('pagename|templatedetailid|oid|typeoi|commune|nom',$wp->query_vars) && $wp->query_vars['pagename'] == $fakepage_WP84_url) ) {
        // stop interferring with other $posts arrays on this page (only works if the sidebar is rendered *after* the main page)
        $fakepage_WP84_detect = true;
        //charger template
        $aTemplateDetail=self::getlistdetail($wp->query_vars['templatedetailid'],array());
        $aTemplateJSON=count($aTemplateDetail)===1?json_decode($aTemplateDetail[0]['confvalue'],true):false;
        if ($aTemplateJSON !== false){
            //charger OBT
            $oJSON=WP84ApidaeReqAPI::getOBT($wp->query_vars['oid'],$aTemplateJSON['fields'],$aTemplateJSON['locales'],$aTemplateJSON['overload']);
            if($oJSON!==false){
                // create a fake virtual page
                //js - css
                if(array_key_exists('css', $aTemplateJSON)){
                    foreach (explode(',',$aTemplateJSON['css']) as $k=>$cssfl){
                        wp_enqueue_style('wp84apidaecssdetailtag'.$k, (strpos($cssfl,'http://')===0 || strpos($cssfl,'https://')===0 || strpos($cssfl,'//')===0)?$cssfl:'/'.$cssfl);
                    }
                }
                if(array_key_exists('js', $aTemplateJSON)){  
                    foreach (explode(',',$aTemplateJSON['js']) as  $k=>$jsfl){
                        wp_enqueue_script('wp84apidaejsdetailtag'.$k, (strpos($jsfl,'http://')===0 || strpos($jsfl,'https://')===0 || strpos($jsfl,'//')===0)?$jsfl:'/'.$jsfl);
                    }
                }
                $post = new stdClass;
                $post->post_author = 1;
                $post->post_name = $fakepage_WP84_url;
                $post->guid = get_bloginfo('wpurl') . '/' . $fakepage_WP84_url;
                $post->post_title = WP84ApidaeTemplate::renderTemplate($oJSON,$aTemplateJSON['title']);
                //$post->post_content = fakepage_chat_render();
                $post->post_content = WP84ApidaeTemplate::renderTemplate($oJSON,$aTemplateJSON['code']);
                $post->ID = -1;
                $post->post_type = 'page';
                $post->post_parent = 0;
                $post->post_status = 'static';
                $post->comment_status = 'closed';
                $post->ping_status = 'open';
                $post->comment_count = 0;
                $post->post_date = current_time('mysql');
                $post->post_date_gmt = current_time('mysql', 1);
                $posts=NULL;
                $posts[]=$post;
                // make wpQuery believe this is a real page too
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->is_home = false;
                $wp_query->is_archive = false;
                $wp_query->is_category = false;
                unset($wp_query->query["error"]);
                $wp_query->query_vars["error"]="";
                $wp_query->is_404=false;
            }
        }
    }
    if(!(is_array($wp->query_vars) && self::array_key_exists_r('pagename|templatedetailid|oid|typeoi|commune|nom',$wp->query_vars) && $wp->query_vars['pagename'] == $fakepage_WP84_url) ){
        unset($_SESSION['wp84apidae_url_list']);
    }

    return $posts;
    }
}