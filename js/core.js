//wordpress ajax-admin ne supporte pas les réponse application/json il faut poster la réponse en form-encoded
var basereq={
    method:'POST'
    ,url:ajax_object.ajax_url
    ,headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    ,transformRequest: function(obj) {
        var str = [];
        for(var p in obj)
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
        return str.join("&");
    }
};
angular.module('wp84Apidae',['ngMessages','ngSanitize'])
    .controller('wp84ApidaeCtrl', ['$scope','dispctrl',function($scope,dispctrl) {
        $scope.tabSelected='config';

        $scope.tselect= function(tx){
            $scope.tabSelected = tx;
            dispctrl.notify();
        };
    }])
/*
 * Controller de la config
 */
    .controller('wp84ApidaeConfig', ['$scope','$http','dispctrl', function($scope, $http, dispctrl) {
            $scope.base_params=JSON.parse(ajax_object.base_params);
            $scope.dureecache=ajax_object.dureecache;
            if(typeof $scope.base_params.idproj !== 'undefined' && typeof $scope.base_params.apikey !== 'undefined'){

                $scope.idproj=$scope.base_params.idproj;
                $scope.apikey=$scope.base_params.apikey;
            }else{
                $scope.idproj='';
                $scope.apikey='';
            }
            dispctrl.setConf({apiKey:$scope.apikey, projetId: $scope.idproj});
            $scope.configStatus='';
            var req=basereq;
            req.data={action:'getdoc','docpage':2,nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.doc=response.data;
            });
            $scope.saveit=function(){
                if($scope.wp84configform.idproj.$valid && $scope.wp84configform.apikey.$valid && $scope.wp84configform.dureecache.$valid){
                    var req= basereq;
                    req.data={action:'setparams',apikey:$scope.apikey,idproj:$scope.idproj,nonce:ajax_object.nonce};
                    if ($scope.dureecache !== ''){
                        req.data.dureecache=$scope.dureecache;
                    }
                    $http(req).then(function(response) {
                        dispctrl.setConf({apiKey:$scope.apikey, projetId: $scope.idproj});
                        $scope.configStatus = response.data.message;
                    });
                    setTimeout(function(){
                        $scope.$apply(function () {$scope.configStatus='';});
                    },2000);
                }else{
                    $scope.configStatus='la configuration est invalide, sauvegarde impossible...';
                }

                setTimeout(function(){
                    $scope.$apply(function () {$scope.configStatus='';});
                },3000);
            };
            $scope.raz=function(){
                var req= basereq;
                req.data={action:'razcache',nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.configStatus = 'cache effacé';
                    });
                    setTimeout(function(){
                        $scope.$apply(function () {$scope.configStatus='';});
                    },2000);
            };
    }])
/**
 * Controller des listes
 */
    .controller('wp84ApidaeListes', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
        $scope.listemsg='chargement en cours';
        $scope.listaddconfig ='';
        $scope.listadd=false;
        $scope.listready=false;
        $scope.listresarray=[];
        $scope.listasc=false;
        $scope.listselectdata = {
         selectedOption: {id: '', name: 'Aucun'},
         availableOptions: [
           {id: '', name: 'Aucun'},
           {id: 'NOM', name: 'Nom'},
           {id: 'ID', name: 'Id'},
           {id: 'RANDOM', name: 'Random'},
           {id: 'DATE_OUVERTURE', name: 'Date d\'ouverture'}
         ]
        };
        $scope.listelangue = {
         selectedOption: {id: 'fr', name: 'Français'},
         availableOptions: [
           {id: 'fr', name: 'Français'},
           {id: 'en', name: 'Anglais'},
           {id: 'de', name: 'Allemand'},
           {id: 'nl', name: 'Hollandais'},
           {id: 'it', name: 'Italien'},
           {id: 'es', name: 'Espagnol'},
           {id: 'ru', name: 'Russe'},
           {id: 'zh', name: 'Chinois'},
           {id: 'pt-br', name: 'Portugais (Brésil)'}
         ]
        };

        $scope.isJSONString=function(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
        }
        /*
         * Annuler la modification
         */
        $scope.undoaddmod = function(){
            $scope.listadd=false;
            $scope.listready=true;
        };
        /*
         * Ajout d'une liste
         */
        $scope.addlist=function(){
            $scope.listready=false;
            $scope.listadd=true;
            $scope.listselectdata.selectedOption={id: '', name: 'Aucun'};
            $scope.listelangue.selectedOption= {id: 'fr', name: 'Français'};
            $scope.listvalidtext='Ajouter la liste';
            $scope.fieldlist='';
            $scope.listdesc='';
            $scope.idselect='';
            $scope.listmodaddid='';
            $scope.listaddconfig='';
            $scope.listmodaddlabel='nouvelle liste';
            $scope.listasc=false;
            $scope.advancevisible=false;
            $scope.jsoninvalid=false;
        };
        /*
         * Modification d'une liste
         */
        $scope.modlist=function(nid){
            $scope.listready=false;
            var row = $filter('filter')($scope.listresarray, {'nameid':nid})[0];
            var vjson = JSON.parse(row.confvalue);
            $scope.idselect=vjson.selectionIds[0];
            if(vjson.selectionIds.length===1){
                //delete si pas de selection custom
                delete vjson.selectionIds;
            }
            $scope.listelangue.selectedOption=$filter('filter')($scope.listelangue.availableOptions,{'id':vjson.locales[0]})[0];
            if(vjson.locales.length===1){
                //delete si pas de locales custom
                delete vjson.locales;
            }
            $scope.listadd=true;

            $scope.listvalidtext='Modifier la liste';
            if(typeof vjson.responseFields !== 'undefined'){
                $scope.fieldlist=vjson.responseFields.join(',');
                delete vjson.responseFields;
            }else{
                $scope.fieldlist='';
            }
            if(typeof vjson.order !== 'undefined'){
                $scope.listselectdata.selectedOption=$filter('filter')($scope.listselectdata.availableOptions,{'id':vjson.order})[0];
                $scope.listasc=!vjson.asc;
                delete vjson.order;
                delete vjson.asc;
            }else{
                $scope.listselectdata.selectedOption={id: '', name: 'Aucun'};
            }
            
            
            $scope.listdesc=row.descript;
            $scope.listmodaddid=nid;
            //vérifie s'il reste des éléments dans le JSON de config après en avoir retiré les éléments basiques (cf les delete plus haut) et l'affiche
            if(angular.equals({}, vjson)){
                $scope.listaddconfig='';
            }else{
                $scope.listaddconfig=JSON.stringify(vjson);
            }
            
            $scope.listmodaddlabel=row.descript;
            $scope.advancevisible=false;
            $scope.jsoninvalid=false;
        };
        /*
         * Suppression d'une liste
         */
        $scope.suplist=function(nid){
                $scope.listresarray=[];    
                var req= basereq;
                req.data={action:'setlist',delid:nid,nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.listemsg = response.data.message;
                    $scope.listadd=false;
                    $scope.listready=false;
                    setTimeout(function(){
                        $scope.refreshstatelist();
                    },1500);
                });
        };
        /*
         * Génération de la requete JSON de la liste pour enregistrement, pour faire une requête apidae il manquera la clé d'api et l'id projet qui sont saisis dans la config
         */
        $scope._genreq=function(){
            var vjson = $scope.isJSONString($scope.listaddconfig);
            var vconf = ($scope.listaddconfig === '' || vjson);
            var ret= false;
            if($scope.wp84listeform.$valid && vconf){
                $scope.jsoninvalid=false;
                var toReq={apiKey:$scope.apikey, projetId: $scope.idproj, selectionIds:[$scope.idselect], locales:[$scope.listelangue.selectedOption.id]}
                if($scope.listselectdata.selectedOption.id !== ''){
                    toReq.order=$scope.listselectdata.selectedOption.id;
                    toReq.asc=!$scope.listasc;
                }
                if($scope.fieldlist !== ''){
                    var vls = $scope.fieldlist.split(',');
                    toReq.responseFields=vls;
                }
                var objReq = angular.merge({}, toReq, vjson?JSON.parse($scope.listaddconfig):{});
                ret = JSON.stringify(objReq);
            }else{
                if(!vconf){
                    $scope.jsoninvalid=true;
                }
            }
            return ret;
        };
        /*
         * Dans l'affichage avancé affiche la reqête JSON pour aide développeur
         */
        $scope.genreq=function(){
            var greq = $scope._genreq();
            if(greq === false){
                $scope.requeteliste='erreur... veuillez terminer de remplir le formulaire correctement...';
            }else{
                $scope.requeteliste=greq;
            }
        };
        /*
         * Sauve la liste saisie
         */
        $scope.savelist=function(){
            var greq = $scope._genreq();
            if(greq === false){
                $scope.listaddmodstatus='enregistrement impossible... veuillez remplir le formulaire correctement...';
            }else{
                $scope.listresarray=[];    
                var req= basereq;
                req.data={action:'setlist',desc:$scope.listdesc,nameid:$scope.listmodaddid,confvalue:greq,nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.listemsg = response.data.message;
                    $scope.listadd=false;
                    $scope.listready=false;
                    setTimeout(function(){
                        $scope.refreshstatelist();
                    },1500);

                });
            }
        }
        /*
         * Rafraichissement de l'affichage des listes
         */
        $scope.refreshstatelist=function(){
            var req=basereq;
              $scope.listresarray = [];
                    $scope.listemsg='chargement en cours';
                    $scope.listadd=false;
                    $scope.listready=false;
                    req.data={action:'getlist',nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.listemsg = response.data.message;
                        $scope.listresarray = response.data.res;
                        $scope.listready=true;
                        //$scope.$apply();
                    });
        };
        /*
         * Listener pour savoir quand l'onglet liste de l'interface est sélectionné
         */
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'listes'){
                    $scope.refreshstatelist();
                    var req=basereq;
                    req.data={action:'getdoc','docpage':4,nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.doc=response.data;
                    });
                }
        });

    }])
/*
 * Controller template de liste
 */
    .controller('wp84ApidaeTemplatesListe', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
        $scope.resetlist=function(){
            $scope.templatesready=false;
            $scope.tlistaddmodstatus='';
            $scope.templateadd=false;
            $scope.templatesmsg='';
            $scope.templatedesc='';
            $scope.templatevalidtext='';
            $scope.tlisthead='';
            $scope.tlistcontent='';
            $scope.tlistfooter='';
            $scope.templatesresarray=[];
        }
        $scope.resetlist();
        $scope.addtemplate=function(){
            $scope.resetlist();
            $scope.templateadd=true;
            $scope.tlistaddmodstatus='';
            $scope.ltempmodaddid='';
            $scope.templatevalidtext='Ajouter le template';
            $scope.ltempmodaddlabel='nouveau template';
        };
        $scope.modtemplate=function(mid){
            $scope.templatesready=false;
            var row = $filter('filter')($scope.templatesresarray, {'nameid':mid})[0];
            var vjson = JSON.parse(row.confvalue);
            $scope.templatedesc=row.descript;
            $scope.tlisthead=vjson.header;
            $scope.tlistcontent=vjson.content;
            $scope.tlistfooter=vjson.footer;
            $scope.templateadd=true;
            $scope.tlistaddmodstatus='';
            $scope.ltempmodaddid=mid;
            $scope.templatevalidtext='Modifier le template';
            $scope.ltempmodaddlabel=row.descript;
        };
        $scope.savetemplate=function(){
            var req= basereq;
            var aggjson = {'header':$scope.tlisthead,'content':$scope.tlistcontent,'footer':$scope.tlistfooter};
            if($scope.wp84templatelisteform.$valid){
                $scope.templatesresarray=[]; 
                req.data={action:'setlistTemplate',desc:$scope.templatedesc,nameid:$scope.ltempmodaddid,confvalue:JSON.stringify(aggjson),nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.templatesmsg = response.data.message;
                    $scope.templateadd=false;
                    $scope.templatesready=false;
                    setTimeout(function(){
                        $scope.refreshstatetemplatelist();
                    },1500);
                });
            }else{
                $scope.tlistaddmodstatus='enregistrement impossible... veuillez remplir le formulaire correctement...';
            }
        };
        $scope.suptemplate=function(nid){
                $scope.templatesresarray=[];    
                var req= basereq;
                req.data={action:'setlistTemplate',delid:nid,nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.templatesmsg = response.data.message;
                    $scope.templateadd=false;
                    $scope.templatesready=false;
                    setTimeout(function(){
                        $scope.refreshstatetemplatelist();
                    },1500);
                });
        };
        $scope.undoaddmodtemplate=function(){
            $scope.refreshstatetemplatelist();
        };
        $scope.refreshstatetemplatelist=function(){
            $scope.resetlist();
            var req=basereq;
            $scope.templatesmsg='chargement en cours';
            req.data={action:'getlisttemplate',nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.templatesmsg = response.data.message;
                $scope.templatesresarray = response.data.res;
                $scope.templatesready=true;
                //$scope.$apply();
            });
        };
        /*
         * Listener pour savoir quand l'onglet templates de liste de l'interface est sélectionné
         */
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'templates_liste'){
                    $scope.refreshstatetemplatelist();
                    var req=basereq;
                    req.data={action:'getdoc','docpage':5,nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.doc=response.data;
                    });
                }
        });
    }])
    .controller('wp84ApidaeMoteur', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
            $scope.resetmoteur=function(){
            $scope.moteurready=false;
            $scope.moteuraddmodstatus='';
            $scope.moteuradd=false;
            $scope.moteurmsg='';
            $scope.moteurdesc='';
            $scope.moteurvalidtext='';
            $scope.moteurconfig='';
            $scope.moteurresarray=[];
        };
        $scope.addmoteur=function(){
            $scope.moteurmodaddid='';
            $scope.moteuradd=true;
            $scope.moteurvalidtext='Ajouter le moteur';
            $scope.moteurmodaddlabel='nouveau moteur';
        };
        $scope.modmoteur=function(mid){
            $scope.moteurready=false;
            var row = $filter('filter')($scope.moteurresarray, {'nameid':mid})[0];
            $scope.moteurmodaddid=mid;
            $scope.moteuradd=true;
            $scope.moteurvalidtext='modifier le moteur';
            $scope.moteurconfig=row.confvalue;
            $scope.moteurmodaddlabel=row.descript;
            $scope.moteurdesc=row.descript;
        };        
        $scope.supmoteur=function(nid){
                $scope.moteurresarray=[];    
                var req= basereq;
                req.data={action:'setmoteur',delid:nid,nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.moteurmsg = response.data.message;
                    $scope.moteuradd=false;
                    $scope.moteurready=false;
                    setTimeout(function(){
                        $scope.refreshstatemoteur();
                    },1500);
                });
        };
        $scope.savemoteur=function(){
            var req= basereq;
            if($scope.wp84moteurform.$valid){
                $scope.moteurresarray=[]; 
                //TODO add mod supp moteurs dans db
                req.data={action:'setmoteur',desc:$scope.moteurdesc,nameid:$scope.moteurmodaddid,confvalue:$scope.moteurconfig,nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.moteurmsg = response.data.message;
                    $scope.moteuradd=false;
                    $scope.moteurready=false;
                    setTimeout(function(){
                        $scope.refreshstatemoteur();
                    },1500);
                });
            }
        };
        $scope.undoaddmodmoteur=function(){
            $scope.refreshstatemoteur();
        }
        $scope.refreshstatemoteur=function(){
            $scope.resetmoteur();
            var req=basereq;
            $scope.moteurmsg='chargement en cours';
            req.data={action:'getlistmoteur',nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.moteurmsg = response.data.message;
                $scope.moteurresarray = response.data.res;
                $scope.moteurready=true;
                //$scope.$apply();
            });
        };
        /*
         * Listener pour savoir quand l'onglet moteurs de l'interface est sélectionné
         */
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'moteurs'){
                    $scope.refreshstatemoteur();
                    var req=basereq;
                    req.data={action:'getdoc','docpage':7,nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.doc=response.data;
                    });
                }
        });  
    }])
    .controller('wp84ApidaeDetail', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
        $scope.detaillistelangue = {
         selectedOption: {id: 'fr', name: 'Français'},
         availableOptions: [
           {id: 'fr', name: 'Français'},
           {id: 'en', name: 'Anglais'},
           {id: 'de', name: 'Allemand'},
           {id: 'nl', name: 'Hollandais'},
           {id: 'it', name: 'Italien'},
           {id: 'es', name: 'Espagnol'},
           {id: 'ru', name: 'Russe'},
           {id: 'zh', name: 'Chinois'},
           {id: 'pt-br', name: 'Portugais (Brésil)'}
         ]
        };
        $scope.fieldlist='';
        $scope.detailmetatitle='';
        $scope.resetdetail=function(){
            $scope.detailready=false;
            $scope.detailaddmodstatus='';
            $scope.detailadd=false;
            $scope.detailmsg='';
            $scope.detaildesc='';
            $scope.fieldlist='';
            $scope.detailjs='';
            $scope.detailcss='';
            $scope.detailoverload='';
            $scope.detaillistelangue.selectedOption={id: 'fr', name: 'Français'};
            $scope.detailvalidtext='';
            $scope.detailconfig='';
            $scope.detailmetatitle='';
            $scope.detailresarray=[];
            $scope.detailavance=false;
        };
        $scope.adddetail=function(){
            $scope.detailmodaddid='';
            $scope.detailadd=true;
            $scope.detailvalidtext='Ajouter le template de detail';
            $scope.detailmodaddlabel='nouveau template de detail';
        };
        $scope.moddetail=function(mid){
            $scope.detailready=false;
            var row = $filter('filter')($scope.detailresarray, {'nameid':mid})[0];
            $scope.detailmodaddid=mid;
            $scope.detailadd=true;
            $scope.detailvalidtext='modifier le detail';
            var aggjson=JSON.parse(row.confvalue);
            $scope.detailjs=aggjson.js;
            $scope.detailcss=aggjson.css;
            $scope.detailconfig=aggjson.code;
            $scope.detailoverload=aggjson.overload;
            $scope.detailmetatitle=aggjson.title;
            $scope.detaillistelangue.selectedOption=$filter('filter')($scope.detaillistelangue.availableOptions,{'id':aggjson.locales})[0];
            $scope.fieldlist=aggjson.fields;
            $scope.detailmodaddlabel=row.descript;
            $scope.detaildesc=row.descript;
        };        
        $scope.supdetail=function(nid){
                $scope.detailresarray=[];    
                var req= basereq;
                req.data={action:'setdetail',delid:nid,nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.detailmsg = response.data.message;
                    $scope.detailadd=false;
                    $scope.detailready=false;
                    setTimeout(function(){
                        $scope.refreshstatedetail();
                    },1500);
                });
        };
        $scope.savedetail=function(){
            var req= basereq;
            if($scope.wp84detailform.$valid){
                $scope.detailresarray=[]; 
                var aggjson={'code':$scope.detailconfig,'fields':$scope.fieldlist,'locales':$scope.detaillistelangue.selectedOption.id,'title':$scope.detailmetatitle,'js':$scope.detailjs,'css':$scope.detailcss,'overload':$scope.detailoverload};
                req.data={action:'setdetail',desc:$scope.detaildesc,nameid:$scope.detailmodaddid,confvalue:JSON.stringify(aggjson),nonce:ajax_object.nonce};
                $http(req).then(function(response) {
                    $scope.detailmsg = response.data.message;
                    $scope.detailadd=false;
                    $scope.detailready=false;
                    setTimeout(function(){
                        $scope.refreshstatedetail();
                    },1500);
                });
            }
        };
        $scope.undoaddmoddetail=function(){
            $scope.refreshstatedetail();
        }
        $scope.refreshstatedetail=function(){
            $scope.resetdetail();
            var req=basereq;
            $scope.detailmsg='chargement en cours';
            req.data={action:'getlistdetail',nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.detailmsg = response.data.message;
                $scope.detailresarray = response.data.res;
                $scope.detailready=true;
                //$scope.$apply();
            });
        };
        /*
         * Listener pour savoir quand l'onglet templates_detail de l'interface est sélectionné
         */
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'templates_detail'){ 
                    $scope.refreshstatedetail();
                    var req=basereq;
                    req.data={action:'getdoc','docpage':6,nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.doc=response.data;
                    });
                }
        });  
    }])
    .controller('wp84ApidaeShortTag', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
        $scope.refresh=function(){
            $scope.moteur=[];
            $scope.selectedmoteur='';
            $scope.detail=[];
            $scope.selecteddetail='';
            $scope.liste=[];
            $scope.selectedliste='';
            $scope.tliste=[];
            $scope.selectedtliste='';
            $scope.allmsg='';
            $scope.formdisplay=false;
            $scope.inb='';
            $scope.listejs='';
            $scope.listecss='';
        };
        $scope.refreshfactory=function(){
            $scope.refresh();
            var req=basereq;
            req.data={action:'getall4short',nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.allmsg=response.data.message;
                if(response.data.haserror===false){
                    $scope.moteur=response.data.res.moteur;
                    $scope.detail=response.data.res.detail;
                    $scope.liste=response.data.res.liste;
                    $scope.tliste=response.data.res.tliste;
                    $scope.formdisplay=true;
                }else{
                    $scope.formdisplay=false;
                }
            });
        };
        $scope.delshort=function(){
            $scope.shorttag='';
        };
        $scope.generateshort=function(){
            if($scope.selecteddetail !== '' && $scope.selectedliste !== '' && $scope.selectedtliste !== '' && $scope.wp84tagform.$valid){
                var ret = '[apidaelist ';
                ret+='list='+$scope.selectedliste.id+' ';
                ret+='templist='+$scope.selectedtliste.id+' ';
                ret+='detail='+$scope.selecteddetail.id+' ';

                if($scope.selectedmoteur === null || typeof $scope.selectedmoteur.id === 'undefined' ){

                }else{
                    ret+='moteur='+$scope.selectedmoteur.id+' ';

                }
                if($scope.paged ===true){
                    ret+='paged=1 ';
                }
                
                if($scope.inb !== ''){
                    ret+='nb='+$scope.inb+' ';
                }
                if($scope.listecss !== ''){
                    ret+='css='+$scope.listecss+' ';
                }
                if($scope.listejs !== ''){
                    ret+='js='+$scope.listejs+' ';
                }
                ret+=']';
                $scope.shorttag=ret;
            }else{
                $scope.shorttag='erreur, veuillez choisir une liste, un template de liste et un template de détail, s\'il n\'en existe pas veuiller en créer...';
            }
        };
         /*
         * Listener pour savoir quand l'onglet short_tag de l'interface est sélectionné
         */
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'short_tag'){
                    $scope.refreshfactory();
                    var req=basereq;
                    req.data={action:'getdoc','docpage':8,nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.doc=response.data;
                    });
                }
        });   
    }])
    .controller('wp84ApidaeDoc', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
         /*
         * Listener pour savoir quand l'onglet doc de l'interface est sélectionné
         */
        $scope.resetdoc=function(){
          $scope.doc='';  
        };
        $scope.refreshdoc=function(){
          $scope.resetdoc();
          var req=basereq;
            req.data={action:'getdoc',nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.doc=response.data;
            });
        };
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'doc'){
                    $scope.refreshdoc();
                }
        });   
    }])
    .controller('wp84ApidaeJPath', ['$scope','$http','$filter','dispctrl', function($scope, $http, $filter, dispctrl) {
         /*
         * Listener pour savoir quand l'onglet doc de l'interface est sélectionné
         */
        $scope.jpathprocess=false;
        $scope.resetjpath=function(){
          $scope.idapidae='';
          $scope.jsonapidae='';
          $scope.jpathprocess=false;
          $scope.jsonpath='';
          $scope.jsonpathres='';
          $scope.detailoverload='responseFields=@all&locales=fr,en';
        };
        $scope.loadOBT=function(){
            var req=basereq;
            req.data={action:'getobt','idapidae':$scope.idapidae,'overload':$scope.detailoverload,nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                if(response.data==='notfound'){
                    $scope.jsonapidae='Objet non trouvé !';
                }else{
                    $scope.jsonapidae=response.data;
                    $scope.jpathprocess=true;
                }
            });
        };
        $scope.loadJPath=function(){
            var req=basereq;
            req.data={action:'resjpath','jpath':$scope.jsonpath,nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.jsonpathres=response.data;
            });
        };
        $scope.refreshjpath=function(){
          $scope.resetjpath();
          /*
          var req=basereq;
            req.data={action:'getdoc',nonce:ajax_object.nonce};
            $http(req).then(function(response) {
                $scope.doc=response.data;
            });*/
        };
        dispctrl.subscribe($scope, function () {
                if($scope.tabSelected === 'jpath'){
                    $scope.refreshjpath();
                    var req=basereq;
                    req.data={action:'getdoc','docpage':9,nonce:ajax_object.nonce};
                    $http(req).then(function(response) {
                        $scope.doc=response.data;
                    });
                }
        });   
    }])
    .factory('dispctrl', function($rootScope) {
        var storetemp= null;
        return {
            subscribe: function(scope, callback) {
                var handler = $rootScope.$on('notifying-service-event', callback);
                scope.$on('$destroy', handler);
            },

            notify: function() {
                $rootScope.$emit('notifying-service-event');
            },
            setConf: function(x) {
                storetemp=x;
            },
            getConf: function() {
                return storetemp;
            }
        };
    })
    .directive('ngConfirmClick', [
        function(){
            return {
                link: function (scope, element, attr) {
                    var msg = attr.ngConfirmClick || "Are you sure?";
                    var clickAction = attr.confirmedClick;
                    element.bind('click',function (event) {
                        if ( window.confirm(msg) ) {
                            scope.$eval(clickAction);
                        }
                    });
                }
            };
    }]);

