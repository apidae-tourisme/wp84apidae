<?php
class WP84ApidaeAdmin{
    private static $initiated = false;
    protected static $nonce;
    public static function init() {
            if ( ! self::$initiated ) {
                    self::init_hooks();
            }
    }

    /**
     * creation de l'entrée de menu admin et ecoute sur les retours AJAX
     */
    public static function init_hooks() {
        self::$initiated = true;
        self::$nonce = wp_create_nonce( 'wp84adminnonce' );
            add_action( 'admin_menu', array('WP84ApidaeAdmin','admin_menu') );
            add_action( 'wp_ajax_setparams', array('WP84ApidaeAdmin','setparams') );
            add_action( 'wp_ajax_getlist', array('WP84ApidaeAdmin','getlist') );
            add_action( 'wp_ajax_getlisttemplate', array('WP84ApidaeAdmin','getlisttemplate') );
            add_action( 'wp_ajax_getlistmoteur', array('WP84ApidaeAdmin','getlistmoteur') );
            add_action( 'wp_ajax_getlistdetail', array('WP84ApidaeAdmin','getlistdetail') );
            add_action( 'wp_ajax_getall4short', array('WP84ApidaeAdmin','getall4short') );
            add_action( 'wp_ajax_setlist', array('WP84ApidaeAdmin','setlist') );
            add_action( 'wp_ajax_setlistTemplate', array('WP84ApidaeAdmin','setlistTemplate') );
            add_action( 'wp_ajax_setmoteur', array('WP84ApidaeAdmin','setmoteur') );
            add_action( 'wp_ajax_setdetail', array('WP84ApidaeAdmin','setdetail') );
            add_action( 'wp_ajax_getdoc', array('WP84ApidaeAdmin','getdoc') );
            add_action( 'wp_ajax_getobt', array('WP84ApidaeAdmin','getobt') );
            add_action( 'wp_ajax_resjpath', array('WP84ApidaeAdmin','resjpath') );
            add_action( 'wp_ajax_razcache', array('WP84ApidaeAdmin','razcache') );
    }
    /**
     * ajout de l'entrée de menu et appel à l'ajout des scripts
     */
    public static function admin_menu(){
        add_action('admin_enqueue_scripts', array('WP84ApidaeAdmin','corejs'));
        add_menu_page( 'Options Apidae Wordpress', 'Apidae Wordpress', 'manage_options', 'ws84apidae', array('WP84ApidaeAdmin','plugin_admin_display') );
    }
    /**
     * ajout des javascripts et css + variables d'environnement js
     */
    public static function corejs($hook){
        if(substr_compare($hook, 'ws84apidae', strlen($hook)-strlen('ws84apidae'), strlen('ws84apidae')) !== 0){
            return;
        }
        wp_enqueue_style('wp84apidaecsscore', plugins_url('../css/core.css',__FILE__));
        wp_enqueue_script('wp84apidaeangular', plugins_url('../js/angular.js',__FILE__));
        wp_enqueue_script('wp84apidaeangularmessages', plugins_url('../js/angular-messages.min.js',__FILE__), array('wp84apidaeangular') );
        wp_enqueue_script('wp84apidaeangularsanitize', plugins_url('../js/angular-sanitize.min.js',__FILE__), array('wp84apidaeangular') );

        wp_enqueue_script('wp84apidaejscore', plugins_url('../js/core.js',__FILE__));
        
        wp_localize_script( 'wp84apidaejscore', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) , 'nonce' => self::$nonce, 'base_params' => get_option('wp84apidae_params', json_encode(array())), 'dureecache'=> get_option('wp84apidae_dureecache',15) ) );
    }
    /**
     * fonction AJAX d'enregistrement clé API + id de projet
     */
    public static function setparams(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $sMessage = 'erreur d\enregistrement...';
            
            if (preg_match('/^[0-9]+$/',$_POST['idproj']) === 1 and preg_match('/^[a-zA-Z0-9]+$/',$_POST['apikey']) === 1){
                $sStore = json_encode(array('idproj'=>$_POST['idproj'],'apikey'=>$_POST['apikey']));
                update_option('wp84apidae_params',$sStore);
                if(array_key_exists('dureecache', $_POST) && filter_var($_POST['dureecache'], FILTER_VALIDATE_INT) !== false){
                    update_option('wp84apidae_dureecache',$_POST['dureecache']);
                }
                $sMessage = 'la configuration a été mise à jour';
            }
            echo json_encode(array('message'=>$sMessage));
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  des listes
     */
    public static function getlist(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $sMessage = 'pas de liste créée...';
            //print_r($resultats);
            $resultats=WP84Apidae::getlist();
            $cnt = count($resultats);
            if($cnt>0){
                $sMessage = 'listes existantes :';
            }
            echo json_encode(array('message'=>$sMessage,'res'=> $resultats,'cnt'=>$cnt));
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  des templates de listes
     */
    public static function getlisttemplate(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $sMessage = 'pas de template créé...';
            //print_r($resultats);
            $resultats=WP84Apidae::getlisttemplate();
            $cnt = count($resultats);
            if($cnt>0){
                $sMessage = 'templates existants :';
            }
            echo json_encode(array('message'=>$sMessage,'res'=> $resultats,'cnt'=>$cnt));
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  des moteurs
     */
    public static function getlistmoteur(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $sMessage = 'pas de moteur créé...';
            //print_r($resultats);
            $resultats=WP84Apidae::getlistmoteur();
            $cnt = count($resultats);
            if($cnt>0){
                $sMessage = 'moteurs existants :';
            }
            echo json_encode(array('message'=>$sMessage,'res'=> $resultats,'cnt'=>$cnt));
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  des templates de detail
     */
    public static function getlistdetail(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $sMessage = 'pas de template de détail créé...';
            //print_r($resultats);
            $resultats=WP84Apidae::getlistdetail();
            $cnt = count($resultats);
            if($cnt>0){
                $sMessage = 'templates de detail existants :';
            }
            echo json_encode(array('message'=>$sMessage,'res'=> $resultats,'cnt'=>$cnt));
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  de la doc
     */
    public static function getdoc(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
                $sDoc='';
            if(array_key_exists('docpage', $_POST)){
                $sDoc.= file_get_contents(__DIR__.'/../doc/'.$_POST['docpage'].'.html');
            }else{
                for($i=1;$i<10;$i++){
                    $sDoc.= file_get_contents(__DIR__.'/../doc/'.$i.'.html');
                }
            }
            echo $sDoc;
            wp_die();
        }
    }
    /**
     * fonction AJAX : test jpath
     */
    public static function resjpath(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            if(array_key_exists('obttemp', $_SESSION)){
                echo WP84ApidaeTemplate::testJSONPath($_SESSION['obttemp'], stripslashes($_POST['jpath']));
            }else{
                echo 'erreur de session... aucun objet touristique chargé, veuillez réessayer de charger un objet...';
            }
            wp_die();
        }
    }
    /**
     * fonction AJAX : effacer le cache
     */
    public static function razcache(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            WP84ApidaeReqAPI::emptyCache();
            echo 'ok';
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  d'un objet touristique
     */
    public static function getobt(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $ret=WP84ApidaeReqAPI::getOBT($_POST['idapidae'], '@all', 'fr,en', $_POST['overload']);
            if(strpos($ret, 'OBJET_TOURISTIQUE_NOT_FOUND')=== false){
                $_SESSION['obttemp']=$ret;
                echo $ret;
            }else{
                echo 'notfound';
            }
            wp_die();
        }
    }
    /**
     * fonction AJAX : récupération  des templates de detail
     */
    public static function getall4short(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            // This nonce is not valid.
            wp_die( 'Security check' ); 
        }else{
            $aMessage=array();
            $aRes=array('moteur'=> array(),'liste'=> array(),'tliste'=> array(),'detail'=> array());
            $bProcess=true;
            
            $resultats=WP84Apidae::getlistall();
            $aConfR=array(
                'detail'=>array('msg'=>'pas de template de détail créé...','required'=>true)
                ,'tlist'=>array('msg'=>'pas de template de liste créé...','required'=>true)
                ,'list'=>array('msg'=>'pas de liste créée...','required'=>true)
                ,'moteur'=>array('msg'=>'pas de moteur créé...','required'=>false)
                );
            foreach($resultats as $res){
                $aRes[$res['typeconf']][]=$res;
            }
            foreach(array_keys($aRes) as $sK){
                if(count($aRes[$sK])===0){
                    if($sK !== 'moteur')$bProcess=false;
                    $aMessage[]=$aConfR[$sK];
                }
            }
            echo json_encode(array('message'=>implode(' ',$aMessage),'res'=> ($bProcess===true)?$aRes:array(),'haserror'=>!$bProcess));
            wp_die();
        }
    }
    /**
     * fonction AJAX : actions sur les listes (ajout, modif, suppression)
     */
    public static function setlist(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            wp_die( 'Security check' ); 
        }else{
            global $wpdb;
            if(array_key_exists('nameid',$_POST) && $_POST['nameid']===''){
                //nouvelle liste
                $wpdb->insert("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>wp_strip_all_tags($_POST['desc']),'typeconf'=>'liste'),array('%s','%s','%s'));
                $sMessage = 'liste ajoutée, mise à jour en cours...';
            }
            if(array_key_exists('nameid',$_POST) && preg_match('/^liste[0-9]+$/',$_POST['nameid'])===1){
                //mets a jour
                $wpdb->update("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>$_POST['desc']),array('id'=>str_replace('liste','',$_POST['nameid'])),array('%s','%s'),array('%s'));
                $sMessage = 'liste modifiée, mise à jour en cours...';
            }
            if(array_key_exists('delid',$_POST) && preg_match('/^liste[0-9]+$/',$_POST['delid'])===1){
                //supprimer
                $delid=str_replace('liste','',$_POST['delid']);
                $wpdb->delete("{$wpdb->prefix}wp84apidaeplugin",array('id'=>$delid),array('%d'));
                $sMessage = 'liste supprimée, mise à jour en cours...';
            }
            echo json_encode(array('message'=>$sMessage));
            wp_die();
        }
    }
    /**
     * fonction AJAX : actions sur les templates de listes (ajout, modif, suppression)
     */
    public static function setlistTemplate(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            wp_die( 'Security check' ); 
        }else{
            global $wpdb;
            if(array_key_exists('nameid',$_POST) && $_POST['nameid']===''){
                //nouvelle liste
                $wpdb->insert("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>wp_strip_all_tags($_POST['desc']),'typeconf'=>'tliste'),array('%s','%s','%s'));
                $sMessage = 'template de liste ajouté, mise à jour en cours...';
            }
            if(array_key_exists('nameid',$_POST) && preg_match('/^tliste[0-9]+$/',$_POST['nameid'])===1){
                //met a jour
                $wpdb->update("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>$_POST['desc']),array('id'=>str_replace('tliste','',$_POST['nameid'])),array('%s','%s'),array('%s'));
                $sMessage = 'template de liste modifié, mise à jour en cours...';
            }
            if(array_key_exists('delid',$_POST) && preg_match('/^tliste[0-9]+$/',$_POST['delid'])===1){
                //supprimer
                $delid=str_replace('tliste','',$_POST['delid']);
                $wpdb->delete("{$wpdb->prefix}wp84apidaeplugin",array('id'=>$delid),array('%d'));
                $sMessage = 'template de liste supprimé, mise à jour en cours...';
            }
            echo json_encode(array('message'=>$sMessage));
            wp_die();
        }
    }
    /**
     * fonction AJAX : actions sur les moteurs (ajout, modif, suppression)
     */
    public static function setmoteur(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            wp_die( 'Security check' ); 
        }else{
            global $wpdb;
            if(array_key_exists('nameid',$_POST) && $_POST['nameid']===''){
                //nouvelle liste
                $wpdb->insert("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>wp_strip_all_tags($_POST['desc']),'typeconf'=>'moteur'),array('%s','%s','%s'));
                $sMessage = 'moteur ajouté, mise à jour en cours...';
            }
            if(array_key_exists('nameid',$_POST) && preg_match('/^moteur[0-9]+$/',$_POST['nameid'])===1){
                //met a jour
                $wpdb->update("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>$_POST['desc']),array('id'=>str_replace('moteur','',$_POST['nameid'])),array('%s','%s'),array('%s'));
                $sMessage = 'moteur modifié, mise à jour en cours...';
            }
            if(array_key_exists('delid',$_POST) && preg_match('/^moteur[0-9]+$/',$_POST['delid'])===1){
                //supprimer
                $delid=str_replace('moteur','',$_POST['delid']);
                $wpdb->delete("{$wpdb->prefix}wp84apidaeplugin",array('id'=>$delid),array('%d'));
                $sMessage = 'moteur supprimé, mise à jour en cours...';
            }
            echo json_encode(array('message'=>$sMessage));
            wp_die();
        }
    }
    /**
     * fonction AJAX : actions sur les templates de détail (ajout, modif, suppression)
     */
    public static function setdetail(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'wp84adminnonce' ) && is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX  ) {
            wp_die( 'Security check' ); 
        }else{
            global $wpdb;
            if(array_key_exists('nameid',$_POST) && $_POST['nameid']===''){
                //nouvelle liste
                $wpdb->insert("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>wp_strip_all_tags($_POST['desc']),'typeconf'=>'detail'),array('%s','%s','%s'));
                $sMessage = 'template de détail ajouté, mise à jour en cours...';
            }
            if(array_key_exists('nameid',$_POST) && preg_match('/^detail[0-9]+$/',$_POST['nameid'])===1){
                //met a jour
                $wpdb->update("{$wpdb->prefix}wp84apidaeplugin",array('confvalue'=>$_POST['confvalue'],'descript'=>$_POST['desc']),array('id'=>str_replace('detail','',$_POST['nameid'])),array('%s','%s'),array('%s'));
                $sMessage = 'template de détail modifié, mise à jour en cours...';
            }
            if(array_key_exists('delid',$_POST) && preg_match('/^detail[0-9]+$/',$_POST['delid'])===1){
                //supprimer
                $delid=str_replace('detail','',$_POST['delid']);
                $wpdb->delete("{$wpdb->prefix}wp84apidaeplugin",array('id'=>$delid),array('%d'));
                $sMessage = 'template de détail supprimé, mise à jour en cours...';
            }
            echo json_encode(array('message'=>$sMessage));
            wp_die();
        }
    }
    /**
     * Affichage de l'interface
     */
    public static function plugin_admin_display(){
            if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
<h1>Paramètres du module Worpdress Apidae</h1>
<div ng-app="wp84Apidae" class="ws84apidae-cnt" ng-controller="wp84ApidaeCtrl">
    <ul class="ws84setting-menu">
        <li><button ng-click="tselect('config')">Configuration</button></li>
        <li><button ng-click="tselect('listes')">Listes</button></li>
        <li><button ng-click="tselect('templates_liste')">Templates de liste</button></li>
        <li><button ng-click="tselect('templates_detail')">Templates de détail</button></li>
        <li><button ng-click="tselect('moteurs')">Moteurs</button></li>
        <li><button ng-click="tselect('short_tag')">Fabrique à shortcode</button></li>
        <li><button ng-click="tselect('jpath')">JSONPath test</button></li>
        <li><button ng-click="tselect('doc')">Documentation</button></li>
    </ul>
    <div ng-show="tabSelected === 'config'" ng-controller="wp84ApidaeConfig">
        <h2>Configuration :</h2>
        <form novalidate name="wp84configform">
        <p><label>Id du projet : <input type="text" name="idproj" required ng-pattern="/^[0-9]+$/" ng-trim="false" ng-model="idproj"/></label><span ng-messages="wp84configform.idproj.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des chiffres</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
        <p><label>Clé API du projet : <input type="text" name="apikey" required  ng-pattern="/^[a-zA-Z0-9]+$/" ng-trim="false" ng-model="apikey"/></label><span ng-messages="wp84configform.apikey.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : la clé d'API semble invalide</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
        <p><label>Durée du cache Apidae (en minutes, 0 = pas de cache) : <input type="text" name="dureecache" ng-pattern="/^[0-9]+$/" ng-trim="false" ng-model="dureecache"/></label><span ng-messages="wp84configform.dureecache.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquement des chiffres</span></span><button ng-click="raz()">Effacer le cache</button></p>
        <p>{{configStatus}}</p>
        <p><button ng-click="saveit()">Sauvegarder la configuration</button></p>
        </form>
        <p><strong>Documentation:</strong></p>
        <div class="wp84lidoc" ng-bind-html="doc"></div>
    </div>
    <div ng-show="tabSelected === 'listes'" ng-controller="wp84ApidaeListes">
        <span ng-show="listready" class="wp84lft"><button ng-click="addlist()">Ajouter une liste</button></span><h2>Listes :</h2>
        <div ng-show="!listadd">
        <p>{{listemsg}}</p>
        <p ng-repeat="lst in listresarray track by lst.nameid">
            {{lst.descript}}<button ng-click="modlist(lst.nameid)">modifier</button><button ng-confirm-click="Attention, voulez-vous réellement supprimer cette liste ? Veuillez vérifier qu'elle ne soit plus utilisée." confirmed-click="suplist(lst.nameid)">supprimer</button>
        </p>
        </div>
        <form novalidate name="wp84listeform" ng-show="listadd" class="wp84form">
            <p><small class="wp84encart">{{listmodaddlabel}}</small></p>
            <p>{{listaddmodstatus}}</p>
            <p><label>Id de la sélection : <input type="text" name="idselect" required ng-pattern="/^[0-9]+$/" ng-trim="false" ng-model="idselect"/></label><span ng-messages="wp84listeform.idselect.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des chiffres</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
            <p><label>Nom : <input type="text" name="listdesc" required maxlength="30" ng-pattern="/^[a-z0-9 ]+$/" ng-model="listdesc"/></label><span ng-messages="wp84listeform.listdesc.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des minuscules, aucun caractère spécial (accent, apostrophe, ponctuation, etc...)</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
            <p><label for="selectorder">Mode de tri : </label>
    <select name="selectorder" id="selectorder"
      ng-options="option.name for option in listselectdata.availableOptions track by option.id"
      ng-model="listselectdata.selectedOption"></select><label><input type="checkbox" ng-model="listasc">Tri inversé</label></p>
      <p>
    <label for="selectlangue">Langue : </label>
    <select name="selectlangue" id="selectorder"
      ng-options="option.name for option in listelangue.availableOptions track by option.id"
      ng-model="listelangue.selectedOption"></select>
      </p>

            <p><label><input type="checkbox" ng-model="advancevisible">Critères avancés, à n'utiliser que si vous êtes sûr de vous (<a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/format-des-recherches" target="_blank">voir la documentation technique</a>)</label></p>
            <div class="wp84det" ng-show="advancevisible">
                <p><label>Champs retournés, séparés par des virgules (ex : id,nom,informations laisser vide pour <a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/filtrage-des-donnees#comportement-defaut" target="_blank">les champs par défaut</a>) :<br/><input type="text" name="fieldlist" ng-pattern="/^(?:[@a-zA-Z\.]+,?)*[^,]$/" ng-trim="false" ng-model="fieldlist"/></label><span ng-messages="wp84listeform.fieldlist.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : le nom des champs sans espace séparés par des virgules, ne pas finir par une virgule.</span></span></p>
                <p class="wp84errorfield">Pour le confort de tous, merci de paramétrer correctement le filtrage des champs !!<br />Cela peut causer des problèmes de performance pour l'ensemble du réseau Apidae !!<br /> Ces champs ne sont retournés que pour l'affichage des listes, pour le détail plus de champs sont disponibles,<br />merci donc de ne garder que ce qui est réellement nécessaire.</p>
                <p><label>Configuration suppémentaire en JSON (ex: {"territoireIds":[ 95938, 156922 ]}), attention cette configuration est prioritaire :<br/><textarea row="4" cols="100" type="text" name="listaddconfig"  ng-model="listaddconfig"></textarea></label><span ng-show="jsoninvalid" class="wp84errorfield">Erreur : le JSON est invalide !</span></p>
                <button ng-click="genreq()">voir le JSON de la requête</button>
                <p>{{requeteliste}}</p>
            </div>
            <p><button ng-click="savelist()">{{listvalidtext}}</button><button ng-click="undoaddmod()">Annuler</button></p>
        </form>
        <h3 id="detaildoc">Documentation pour les listes</h3>
        <div class="wp84lidoc" ng-bind-html="doc"></div>
    </div>
    <div ng-show="tabSelected === 'moteurs'" ng-controller="wp84ApidaeMoteur">
        <span ng-show="moteurready" class="wp84lft"><button ng-click="addmoteur()">Ajouter un moteur</button></span><h2>Moteurs :</h2>
        <div ng-show="!moteuradd">
        <p>{{moteurmsg}}</p>
        <p ng-repeat="lst in moteurresarray track by lst.nameid">
            {{lst.descript}}<button ng-click="modmoteur(lst.nameid)">modifier</button><button ng-confirm-click="Attention, voulez-vous réellement supprimer cette liste ? Veuillez vérifier qu'elle ne soit plus utilisée." confirmed-click="supmoteur(lst.nameid)">supprimer</button>
        </p>
        </div>
        <form novalidate name="wp84moteurform" ng-show="moteuradd" class="wp84form">
            <p><small class="wp84encart">{{moteurmodaddlabel}}</small></p>
            <p>{{moteuraddmodstatus}}</p>
            <p><label>Nom : <input type="text" name="moteurdesc" required maxlength="30" ng-pattern="/^[a-z0-9 ]+$/" ng-model="moteurdesc"/></label><span ng-messages="wp84moteurform.moteurdesc.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des minuscules, aucun caractère spécial (accent, apostrophe, ponctuation, etc...)</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
            <p><label>Code du template (HTML + code spécifique <a href="#moteurdoc">voir la documentation</a>) :<br/><textarea row="4" cols="100" type="text" name="moteurconfig"  ng-model="moteurconfig"></textarea></label></p>
            <p><button ng-click="savemoteur()">{{moteurvalidtext}}</button><button ng-click="undoaddmodmoteur()">Annuler</button></p>
            <h3 id="moteurdoc">Documentation pour créer un moteur</h3>
            <div class="wp84lidoc" ng-bind-html="doc" ng-bind-html="doc"></div>
        </form>
    </div>
    <div ng-show="tabSelected === 'templates_liste'" ng-controller="wp84ApidaeTemplatesListe">
        <span ng-show="templatesready" class="wp84lft"><button ng-click="addtemplate()">Ajouter un template</button></span><h2>Templates de liste :</h2>
        <div ng-show="!templateadd">
            <p>{{templatesmsg}}</p>
            <p ng-repeat="lst in templatesresarray track by lst.nameid">
                {{lst.descript}}<button ng-click="modtemplate(lst.nameid)">modifier</button><button ng-confirm-click="Attention, voulez-vous réellement supprimer ce template ? Veuillez vérifier qu'il ne soit plus utilisé." confirmed-click="suptemplate(lst.nameid)">supprimer</button>
            </p>
        </div>
            <form novalidate name="wp84templatelisteform" class="wp84form" ng-show="templateadd">
            <p><small class="wp84encart">{{ltempmodaddlabel}}</small></p>
            <p>{{tlistaddmodstatus}}</p>
            <p><label>Nom : <input type="text" name="templatedesc" required maxlength="30" ng-pattern="/^[a-z0-9 ]+$/" ng-model="templatedesc"/></label><span ng-messages="wp84templatelisteform.templatedesc.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des minuscules, aucun caractère spécial (accent, apostrophe, ponctuation, etc...)</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
            <p><label>Entête (code html uniquement : attention le code n'est pas vérifié veuillez fournir un code correct) :<br/><textarea row="4" cols="100" type="text" name="tlisthead"  ng-model="tlisthead"></textarea></label></p>
            <p><label>Corps de liste (template qui se répète à chaque nouvel élément de liste : html plus language de templating spécifique <a href="#templatedoc">voir la documentation</a>) :<br/><textarea row="4" cols="100" type="text" name="tlistcontent"  ng-model="tlistcontent"></textarea></label></p>
            <p><label for="tlistfooter">Pied</label> (code html uniquement avec template de pagination :<br/>
                <span ng-non-bindable="ng-non-bindable">entre {{}} chaque élément sera séparé par des ||| premier élément template de page précédente [link] sera le lien, puis élément de template suivant, code qui précède la pagination, code de template par page (sauf page en cours) [link] est toujours le lien et [nbpage] le numéro de la page, code de template pour page en cours et enfin le code de fin de pagination.<br/>
                    ATTENTION : ce code là sera dans un div identifié<br/>
                {{&lt;a href="[link]" title="aller à la page précédente"&gt;&lt;&lt;&lt;/a&gt;|||&lt;a href="[link]" title="aller à la page suivante"&gt;&gt;&gt;&lt;/a&gt;|||&lt;ul&gt;|||&lt;li&gt;&lt;a href="[link]" title="aller à la page [nbpage]"&gt;[nbpage]&lt;/a&gt;&lt;/li&gt;|||&lt;li&gt;[nbpage]&lt;/li&gt;|||&lt;/ul&gt;}}</span>
                    <textarea row="4" cols="100" type="text" id="tlistfooter" name="tlistfooter"  ng-model="tlistfooter"></textarea>
            </p>
            <p><button ng-click="savetemplate()">{{templatevalidtext}}</button><button ng-click="undoaddmodtemplate()">Annuler</button></p>
            <h3 id="templatedoc">Documentation pour créer un template de liste</h3>
            <div class="wp84lidoc" ng-bind-html="doc" ng-bind-html="doc"></div>
            </form>
    </div>
    <div ng-show="tabSelected === 'templates_detail'" ng-controller="wp84ApidaeDetail">
        <span ng-show="detailready" class="wp84lft"><button ng-click="adddetail()">Ajouter un template de détail</button></span><h2>Template de détail :</h2>
        <div ng-show="!detailadd">
        <p>{{detailmsg}}</p>
        <p ng-repeat="lst in detailresarray track by lst.nameid">
            {{lst.descript}}<button ng-click="moddetail(lst.nameid)">modifier</button><button ng-confirm-click="Attention, voulez-vous réellement supprimer ce template de détail ? Veuillez vérifier qu'il ne soit plus utilisé." confirmed-click="supdetail(lst.nameid)">supprimer</button>
        </p>
        </div>
        <form novalidate name="wp84detailform" ng-show="detailadd" class="wp84form">
            <p><small class="wp84encart">{{detailmodaddlabel}}</small></p>
            <p>{{detailaddmodstatus}}</p>
            <p><label>Nom : <input type="text" name="detaildesc" required maxlength="30" ng-pattern="/^[a-z0-9 ]+$/" ng-model="detaildesc"/></label><span ng-messages="wp84detailform.detaildesc.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des minuscules, aucun caractère spécial (accent, apostrophe, ponctuation, etc...)</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
            <p><label>Champs retournés, séparés par des virgules (ex : id,nom,informations laisser vide pour <a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/filtrage-des-donnees#comportement-defaut" target="_blank">les champs par défaut</a>) :<br/><input type="text" name="fieldlist" ng-pattern="/^(?:[@a-zA-Z\.]+,?)*[^,]$/" ng-trim="false" ng-model="fieldlist"/></label><span ng-messages="wp84detailform.fieldlist.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : le nom des champs sans espace séparés par des virgules, ne pas finir par une virgule.</span></span></p>
            <p>
          <label for="selectlangue">Langue : </label>
          <select name="selectlangue" id="selectorder"
            ng-options="option.name for option in detaillistelangue.availableOptions track by option.id"
            ng-model="detaillistelangue.selectedOption"></select>
            </p>
            <p><label><input type="checkbox" ng-model="detailavance">Avancé (by-pass des variables envoyées en get)</label></p>
            <p ng-show="detailavance">
                Ne renseignez que si vous savez ce que vous faites, une erreur bloquera l'affichage des pages de détail !<br/>Veuillez entrer ce qui va supplanter les <a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/liste-des-services/v002objet-touristiqueget-by-id" target="_blank">variables envoyées en get</a> ex. : responseFields=id,nom&locales=fr,en<br/>
                <input type="text" name="detailoverload" ng-pattern="/^[@a-zA-Z0-9\.=,&]+$/" ng-trim="false" id="detailoverload"  ng-model="detailoverload"/><span ng-messages="wp84detailform.detailoverload.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : pas d'espaces ni de caractères spéciaux, le nom des champs et leurs valeurs doivent être précisés comme dans l'exemple</span></span>
            </p>
            <p><label>Balise Title (ATTENTION : PAS DE HTML) : <input type="text" name="detailmetatitle" ng-model="detailmetatitle" /></label></p>
            <p><label>fichiers css additionnels, sépararés par des virgules : <input type="text" name="detailcss" ng-pattern="/^[A-Za-z0-9\:\/\.\,\-_]+$/" ng-trim="false" ng-model="detailcss" /></label><span ng-messages="wp84detailform.detailcss.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des urls ou noms de fichiers, aucun caractère spécial (accent, apostrophe, etc...) séparés par des virgules</span></span></p>
            <p><label>fichiers javascript additionnels, sépararés par des virgules <input type="text" name="detailjs"  ng-pattern="/^[A-Za-z0-9\:\/\.\,\-_]+$/" ng-trim="false" ng-model="detailjs" /></label><span ng-messages="wp84detailform.detailjs.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des urls ou noms de fichiers, aucun caractère spécial (accent, apostrophe, etc...) séparés par des virgules</span></span></p>
            <p><label>Code du template (HTML + code spécifique <a href="#detaildoc">voir la documentation</a>) :<br/><textarea row="4" cols="100" type="text" name="detailconfig"  ng-model="detailconfig"></textarea></label></p>
            <p><button ng-click="savedetail()">{{detailvalidtext}}</button><button ng-click="undoaddmoddetail()">Annuler</button></p>
            <h3 id="detaildoc">Documentation pour créer un detail</h3>
            <div class="wp84lidoc" ng-bind-html="doc"></div>
        </form>
    </div>
    <div ng-show="tabSelected === 'short_tag'" ng-controller="wp84ApidaeShortTag">
        <h2>Fabrique à shortcode</h2>
        <p>Ce shortcode est à ajouter dans une page pour afficher une liste paginée ou non. A minima, merci de sélectionner une liste, un template de liste et un template de détail.</p>
        <p>{{allmsg}}</p>
        <form novalidate name="wp84tagform" ng-show="formdisplay" class="wp84form">
            <p>
                <label for="selectlist">Liste : </label>
                <select name="selectlist" id="selectlist"
                  ng-options="option.descript for option in liste track by option.id"
                  ng-model="selectedliste" required ng-change="delshort()"><option value="">-- choisir la liste --</option></select>
                <label for="selecttlist" required>Template de liste : </label>
                <select name="selecttlist" id="selecttlist"
                  ng-options="option.descript for option in tliste track by option.id"
                  ng-model="selectedtliste" required ng-change="delshort()"><option value="">-- choisir le template de liste --</option></select>
                <label for="selectdetail">Template de détail : </label>
                <select name="selectdetail" id="selectdetail"
                  ng-options="option.descript for option in detail track by option.id"
                  ng-model="selecteddetail" required ng-change="delshort()"><option value="">-- choisir le template de détail --</option></select>
                <label for="selectmoteur">Moteur de recherche (optionnel) : </label>
                <select name="selectmoteur" id="selectmoteur"
                  ng-options="option.descript for option in moteur track by option.id"
                  ng-model="selectedmoteur" ng-change="delshort()"><option value="">-- choisir le moteur --</option></select>
            </p>
            <p>
                <label><input type="checkbox" ng-model="paged" ng-change="delshort()"/>liste paginée (attention: une seule par page)</label>, 
                <label>nombre de réponses (20 par défault si vide) : <input ng-change="delshort()" type="text" name="inb" ng-pattern="/^(?:[1-9]|[1-4][0-9]|50)$/" ng-trim="false" ng-model="inb" /></label><span ng-messages="wp84tagform.inb.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : une réponse de 1 à 50 est requise</span></span>
            </p>
            <p><label>fichiers css additionnels, sépararés par des virgules : <input ng-change="delshort()" type="text" name="listecss" ng-pattern="/^[A-Za-z0-9\:\/\.\,\-_]+$/" ng-trim="false" ng-model="listecss" /></label><span ng-messages="wp84detailform.detailcss.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des urls ou noms de fichiers, aucun caractère spécial (accent, apostrophe, etc...) séparés par des virgules</span></span></p>
            <p><label>fichiers javascript additionnels, sépararés par des virgules <input ng-change="delshort()" type="text" name="listejs"  ng-pattern="/^[A-Za-z0-9\:\/\.\,\-_]+$/" ng-trim="false" ng-model="listejs" /></label><span ng-messages="wp84detailform.detailjs.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des urls ou noms de fichiers, aucun caractère spécial (accent, apostrophe, etc...) séparés par des virgules</span></span></p>
            <p><button ng-click="generateshort()">Générer le short tag</button></p>
            <p>{{shorttag}}</p>
        </form>
        <p><strong>Documentation</strong></p>
        <div class="wp84lidoc" ng-bind-html="doc"></div>
    </div>
    <div ng-show="tabSelected === 'jpath'" ng-controller="wp84ApidaeJPath">
        <h2>Tester vos JSONPath</h2>
        <form novalidate name="wp84jpathform" class="wp84form">
            <p>Etape 1 : charger un objet touristique Apidae, veuillez donner son identifiant :</p>
            <p><label>Identifiant : <input type="text" name="idapidae" required maxlength="30" ng-pattern="/^[0-9]+$/" ng-model="idapidae"  ng-trim="false"/></label><span ng-messages="wp84jpathform.idapidae.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : uniquemement des chiffres</span><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
            <label><input type="checkbox" ng-model="jpavance"/>options avancées</label>
            <p ng-show="jpavance">
                Ne renseignez que si vous savez ce que vous faites<br/>Veuillez entrer ce qui va supplanter les <a href="http://dev.apidae-tourisme.com/fr/documentation-technique/v2/api-de-diffusion/liste-des-services/v002objet-touristiqueget-by-id" target="_blank">variables envoyées en get pour la récupération de la donnée de l'objet</a> ex. : responseFields=id,nom&locales=fr,en<br/>
                <input type="text" name="detailoverload" ng-pattern="/^[@a-zA-Z0-9\.=,&]+$/" ng-trim="false" id="detailoverload"  ng-model="detailoverload"/><span ng-messages="wp84detailform.detailoverload.$error"><span ng-message="pattern" class="wp84errorfield">Erreur : pas d'espaces ni de caractères spéciaux, le nom des champs et leurs valeurs doivent être précisés comme dans l'exemple</span></span>
            </p>
            <div style="overflow:hidden;">
                <p><button ng-show="wp84jpathform.idapidae.$valid" ng-click="loadOBT()">Charger l'objet touristique</button></p>
                <p ng-show="jpathprocess">Etape 2 : renseignez le JSONPath à tester et vérifier le résultat :</p>
                <pre class="wp84apidae_json">{{jsonapidae | json}}</pre>

                <div ng-show="jpathprocess">
                    <p>Attention par défaut ce testeur récupère tous les noeuds d'information d'un objet, si vous utilisez un noeud particulier, veuillez vérifier que vous le récupérez bien dans vos configurations de listes ou template de détail :</p>
                    <p><label>JSONPath à tester : <input type="text" name="jsonpath" required ng-model="jsonpath"/></label><span ng-messages="wp84jpathform.jsonpath.$error"><span ng-message="required" class="wp84errorfield">Champ requis</span></span></p>
                    <div ng-show="wp84jpathform.jsonpath.$valid">
                        <p><button ng-click="loadJPath()">Tester le JSONPath</button> (pour être utilisable votre résultat doit retourner un tableau à une dimension ne contenant que des chaines de type texte ou numérique <br/>(ex: ["reponse 1", "reponse 2", "reponse 3"]). Il peut bien sûr n'y avoir qu'une seule réponse.</p>
                        <p class="wp84errorfield">{{jsonpathres}}</p>
                    </div>
                </div>
            </div>
            <p><strong>liste de JSONPath</strong></p>
            <div class="wp84lidoc" ng-bind-html="doc"></div>
        </form>
    </div>
    <div ng-show="tabSelected === 'doc'" ng-controller="wp84ApidaeDoc">
        <h2>Documentation</h2>
        <p><a href="<?php echo plugins_url('../',__FILE__); ?>doc/doc.pdf" target="_blank">Téléchargez la documentation en PDF</a></p>
        <div ng-bind-html="doc"></div>
    </div>
</div>
<div>Plugin développé par © Michel CHOUROT / <a href="http://vaucluseprovence-attractivite.com/" target="_blank">Vaucluse Provence Attractivité</a></div>
        <?php
    }
}