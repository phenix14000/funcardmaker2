/* SMF Funcard Maker Frontend by Lancel Thaledric */

$( document ).ready(function() {
    console.log( "Welcome on SMF Funcard Maker 2 !" );
    
function Funcard(){
    
    /**
     * Le nom du template
     */
    this.template = '';
    
    /**
     * La liste des champs remplis par l'utilisateur.
     * C'est un table d'objets de type Field.
     * L'exportation transformera donc ça en objet sérialisé.
     */
    this.fields = [];
    
    /**
     * La liste des panneaux permettant à l'utilisateur de modifier les champs
     */
    this.panels = [];
    
    /**
     * Taille de funcard
     */
    this.width = null;
    this.height = null;
    this.ratio = null;      // width/height
    this.illusWidth = null;
    this.illusHeight = null;
    
}
/**
 * type abstrait.
 * Définit un des paramètres de la funcard associé à un champ html nom=>valeur 
 */
function Field(){
    this.name = null;
    this.value = null;
};

/**
 * écrit le Field dans le champ html du même nom.
 * Cette fonction doit être surchargée dans chaque nouveau type de Field
 */
Field.prototype.writeForm = function(){
    // Fonction à surcharger
}


/**
 * input de type text
 */
function TextField(){
    Field.call(this);
};
TextField.prototype = Object.create(Field.prototype);

//Override
TextField.prototype.writeForm = function(){
    Field.prototype.writeForm.apply(this, arguments);
    // Fonction à surcharger
}/**
 * Gestion de l'affichage des panneaux
 */

function Panel(n, t){
    this.name = n;
    this.title = t;
    this.element = null;
}

Panel.ID_PREFIX = 'fcm-panel-';
Panel.CLASS_TOGGLE = 'active';

Panel.CONTAINER_ELEMENT = $('.fcm-panel-container');
Panel.MENU_ELEMENT = $('#fcm-menu');
Panel.TEMPLATE_CONTAINER_ELEMENT = $('#fcm-template-panel');
Panel.TEMPLATE_MENU_ELEMENT = $('#fcm-template-menu');

Panel.prototype.getId = function(){ return Panel.ID_PREFIX + this.name; }

// Retourne l'élément jQuery du panneau /!\Il peut être vide, si le panneau n'a pas été chargé
Panel.prototype.get = function(){ return Panel.CONTAINER_ELEMENT.find('#' + this.getId()); }

// Appelé quand le panneau est affiché
Panel.prototype.onFocus = function(){
    // À redéfinir
}

// Appelé quand le panneau est caché
Panel.prototype.onBlur = function(){
    // À redéfinir
}

// Appelé quand le panneau est activé
Panel.prototype.onActivate = function(){
    // À redéfinir
}

// Appelé quand le panneau est déchargé
Panel.prototype.onDeactivate = function(){
    // À redéfinir
}

/**
 * Affiche le panneau et met son lien du menu en surbrillance
 */
Panel.prototype.show = function(){
    this.get().addClass(Panel.CLASS_TOGGLE);
    Panel.MENU_ELEMENT.find('[data-panel="'+this.name+'"]').parent('li').addClass(Panel.CLASS_TOGGLE);
    this.onFocus();
}

/**
 * Cache le panel
 */
Panel.prototype.hide = function(){
    this.get().removeClass(Panel.CLASS_TOGGLE);
    Panel.MENU_ELEMENT.find('[data-panel="'+this.name+'"]').parent('li').removeClass(Panel.CLASS_TOGGLE);
    this.onBlur();
}

/**
 * Ajoute le panel au menu et l'active
 * (Le contenu du panel doit déjà avoir été ajouté au DOM)
 */
Panel.prototype.activate = function(){
    Panel.TEMPLATE_MENU_ELEMENT.append('<li><a href="#" data-panel="'+this.name+'">'+this.title+'</li>');
    this.element = $('#'+Panel.ID_PREFIX+this.name);
    this.onActivate();
}

/**
 * Appelle la fonction de désactivation du panneaux.
 * Certains panneaux, comme celui de l'illustration, ont besoin d'effectuer un tache lors de la désactivation.
 */
Panel.prototype.deactivate = function(){
    this.onDeactivate();
}





function IllustrationPanel(n, t){
    Panel.call(this, n, t);
    
    this.cropSelector = null;
}
IllustrationPanel.prototype = Object.create(Panel.prototype);

IllustrationPanel.prototype.onFocus = function(){
    // Gestion du plugin ImgAreaSelect
    if(this.cropSelector != null){
        this.cropSelector.setOptions({show:true, hide:false});
        this.cropSelector.update();
    }
}

IllustrationPanel.prototype.onBlur = function(){
    // Gestion du plugin ImgAreaSelect
    if(this.cropSelector != null){
        this.cropSelector.setOptions({show:false, hide:true});
        this.cropSelector.update();
    }
}

IllustrationPanel.prototype.onDeactivate = function(){
    // Gestion du plugin ImgAreaSelect
    if(this.cropSelector != null){
        this.cropSelector.remove();
    }
}
IllustrationPanel.prototype.onActivate = function(){
    
    var varthis = this;
    
    Panel.CONTAINER_ELEMENT.on('uploadSuccess', '#fcm-form-illustration', function(){
        varthis.onImageLoad();
    });
    
    Panel.CONTAINER_ELEMENT.on('uploadFailure', '#fcm-form-illustration', function(){
        varthis.onImageUnload();
    });
    
    Panel.CONTAINER_ELEMENT.on('click', '#fcm-illsutration-center-viewport', function(){
        varthis.centerImage();
        return false;
    });
}

IllustrationPanel.prototype.onImageLoad = function(){
    var image = this.element.find('.file-preview');
    var centerbutton = this.element.find('#fcm-illsutration-center-viewport');
    centerbutton.addClass('active');
    image.load(function(){
        // init imgAreaSelect
        centerbutton.trigger('click');
    });
}

IllustrationPanel.prototype.onImageUnload = function(){
    var image = this.element.find('.file-preview');
    var centerbutton = this.element.find('#fcm-illsutration-center-viewport');
    centerbutton.removeClass('active');
    image.imgAreaSelect({remove:true});
}

IllustrationPanel.prototype.centerImage = function(){
    
    // init imgAreaSelect
    var form = this.element.find('form');
    var image = form.find('.file-preview');
    var axis = true;    // true = X(portait), false = Y(paysage)
    if(image.width() / image.height() > myFuncard.illusWidth / myFuncard.illusHeight)
        axis = false;

    var cx1 = 0, cx2 = 0, cy1 = 0, cy2 = 0;

    if(axis){
        cx2 = image.width();
        var height = image.width() / (myFuncard.illusWidth / myFuncard.illusHeight);
        cy1 = (image.height() - height) / 2;
        cy2 = cy1 + height;
    } else {
        var width = image.height() * (myFuncard.illusWidth / myFuncard.illusHeight);
        cx1 = (image.width() - width) / 2;
        cx2 = cx1 + width;
        cy2 = image.height();
    }
    
    form.find('#fcm-field-illuscrop-x').val('');
    form.find('#fcm-field-illuscrop-y').val('');
    form.find('#fcm-field-illuscrop-w').val('');
    form.find('#fcm-field-illuscrop-h').val('');
    
    this.cropSelector = image.imgAreaSelect({
        instance: true,
        handles: true,
        persistent: true,
        aspectRatio: myFuncard.illusWidth + ':' + myFuncard.illusHeight,
        x1: cx1,
        y1: cy1,
        x2: cx2,
        y2: cy2,
        onSelectEnd: function (img, selection) {
            img = $(img);
            var px = selection.x1 / img.width();
            var py = selection.y1 / img.height();
            var pw = selection.width / img.width();
            var ph = selection.height / img.height();
            form.find('#fcm-field-illuscrop-x').val(px*100);
            form.find('#fcm-field-illuscrop-y').val(py*100);
            form.find('#fcm-field-illuscrop-w').val(pw*100);
            form.find('#fcm-field-illuscrop-h').val(ph*100);
        }

    });
    
}




function ModernPW3IllustrationPanel(n, t){
    IllustrationPanel.call(this, n, t);
}
ModernPW3IllustrationPanel.prototype = Object.create(IllustrationPanel.prototype);

ModernPW3IllustrationPanel.prototype.onActivate = function(){
    IllustrationPanel.prototype.onActivate.call(this);
}

ModernPW3IllustrationPanel.prototype.centerImage = function(){
    IllustrationPanel.prototype.centerImage.call(this);
    
    if(this.cropSelector != null){
        this.cropSelector.setOptions({classPrefix:'mpw3-imgareaselect', handles:true});
        this.cropSelector.update();
    }
    
}







function ModernPW4IllustrationPanel(n, t){
    IllustrationPanel.call(this, n, t);
}
ModernPW4IllustrationPanel.prototype = Object.create(IllustrationPanel.prototype);

ModernPW4IllustrationPanel.prototype.onActivate = function(){
    IllustrationPanel.prototype.onActivate.call(this);
}

ModernPW4IllustrationPanel.prototype.centerImage = function(){
    IllustrationPanel.prototype.centerImage.call(this);
    
    if(this.cropSelector != null){
        this.cropSelector.setOptions({classPrefix:'mpw4-imgareaselect', handles:true});
        this.cropSelector.update();
    }
    
}





function BackgroundPanel(n, t){
    Panel.call(this, n, t);
    
    
}
BackgroundPanel.prototype = Object.create(Panel.prototype);

BackgroundPanel.prototype.onActivate = function(){
    BackgroundPanel.SELECTOR_INNER_ELEMENT = $('#fcm-background-content');
    BackgroundPanel.LOADING_ICON = $('#fcm-background-loading-icon'); 
    
    this.loadBackgrounds();
}

BackgroundPanel.prototype.loadBackgrounds = function(){
    BackgroundPanel.LOADING_ICON.addClass('active');
    //showLoading();
    // Call getbackgrounds.php
    var thisvar = this;
    $.get(
        'getbackgrounds.php',
        {
            'template' : myFuncard.template,
            'titles' : myFuncard.titles
        },
        function(data){
            BackgroundPanel.SELECTOR_INNER_ELEMENT.html(data);
            thisvar.get().trigger('backgroundsLoaded');
            // Autoselect red skin
            thisvar.get().find('[id*="base"] button[data-value="r"]').trigger('click');
            // And none for skin parts
            thisvar.get().find('button[data-value=""]').trigger('click');
        }
    ).fail(function() {
        alert( "error" );
    })
    .always(function(){
        BackgroundPanel.LOADING_ICON.removeClass('active');
        //hideLoading();
    });
    
}







function ModernBasicBackgroundPanel(n, t){
    Panel.call(this, n, t);
}
ModernBasicBackgroundPanel.prototype = Object.create(Panel.prototype);

ModernBasicBackgroundPanel.prototype.onActivate = function(){
    ModernBasicBackgroundPanel.FORM_CUSTOM_BG = $('#fcm-form-custom-background');
    ModernBasicBackgroundPanel.BUTTONS_GENERATED_BG = $('.fcm-selector-button'); 
    ModernBasicBackgroundPanel.FORM_CUSTOM_BG_ERROR = ModernBasicBackgroundPanel.FORM_CUSTOM_BG.find('.file-error');
    ModernBasicBackgroundPanel.FORM_CUSTOM_BG_IMAGE = ModernBasicBackgroundPanel.FORM_CUSTOM_BG.find('.file-preview');
    
    ModernBasicBackgroundPanel.BUTTONS_GENERATED_BG.click(function(){
        ModernBasicBackgroundPanel.FORM_CUSTOM_BG.find('#fcm-file-background').val('');
        ModernBasicBackgroundPanel.FORM_CUSTOM_BG_ERROR.removeClass('active');
        ModernBasicBackgroundPanel.FORM_CUSTOM_BG_IMAGE.removeClass('active');
        ModernBasicBackgroundPanel.FORM_CUSTOM_BG.find('#fcm-field-background-custom').val('');
    });
}






function ExtensionSymbolPanel(n, t){
    Panel.call(this, n, t);
}
ExtensionSymbolPanel.prototype = Object.create(Panel.prototype);

ExtensionSymbolPanel.prototype.onActivate = function(){
    var varthis = this;
    
    ExtensionSymbolPanel.RARITY_SELECTOR = $('#fcm-se-rarity-selector');
    ExtensionSymbolPanel.RARITY_SELECTOR_BUTTONS = ExtensionSymbolPanel.RARITY_SELECTOR.find('.fcm-selector-button');
    ExtensionSymbolPanel.EXTENSION_SELECTOR = $('#fcm-se-extension-selector');
    ExtensionSymbolPanel.EXTENSION_SELECTOR_BUTTONS = ExtensionSymbolPanel.EXTENSION_SELECTOR.find('.fcm-selector-button');
    ExtensionSymbolPanel.SE_CLEAR_BUTTON = $('#fcm-se-clear-button');
    
    ExtensionSymbolPanel.FIELD_EXTENSION = $('#fcm-field-se-extension');
    ExtensionSymbolPanel.FIELD_RARITY = $('#fcm-field-se-rarity');
    ExtensionSymbolPanel.FIELD_CUSTOM = $('#fcm-field-se-custom');
    
    ExtensionSymbolPanel.CUSTOM_PREVIEW = $('#fcm-se-file-preview');
    ExtensionSymbolPanel.FILE_SELECTOR = $('#fcm-file-se');
    ExtensionSymbolPanel.UPLOAD_FORM = $('#fcm-form-custom-se');
    
    ExtensionSymbolPanel.EXTENSION_SELECTOR_BUTTONS.click(function(){
        ExtensionSymbolPanel.RARITY_SELECTOR.addClass('active');
        
        var img;
        // common
        img = ExtensionSymbolPanel.RARITY_SELECTOR.find('button[data-value="c"]>img');
        img.attr('src', 'resource/seThumb/'+$(this).data('value')+'-c.png');
        // uncommon
        img = ExtensionSymbolPanel.RARITY_SELECTOR.find('button[data-value="u"]>img');
        img.attr('src', 'resource/seThumb/'+$(this).data('value')+'-u.png');
        // rare
        img = ExtensionSymbolPanel.RARITY_SELECTOR.find('button[data-value="r"]>img');
        img.attr('src', 'resource/seThumb/'+$(this).data('value')+'-r.png');
        // mythic
        img = ExtensionSymbolPanel.RARITY_SELECTOR.find('button[data-value="m"]>img');
        img.attr('src', 'resource/seThumb/'+$(this).data('value')+'-m.png');
        // shifted
        img = ExtensionSymbolPanel.RARITY_SELECTOR.find('button[data-value="s"]>img');
        img.attr('src', 'resource/seThumb/'+$(this).data('value')+'-s.png');

        // auto rarity
        var field = ExtensionSymbolPanel.RARITY_SELECTOR.find('.fcm-selector-field');
        if(!field.val()){
            ExtensionSymbolPanel.RARITY_SELECTOR.find('button[data-value="c"]').trigger('click');
        }
        
    });
    
    ExtensionSymbolPanel.SE_CLEAR_BUTTON.click(function(){
        varthis.clearOfficialSE();
        varthis.clearCustomSE();
    });
    
    ExtensionSymbolPanel.UPLOAD_FORM.on('uploadSuccess', function(){
        varthis.clearOfficialSE();
    })
    
    ExtensionSymbolPanel.RARITY_SELECTOR_BUTTONS.click(function(){
        varthis.clearCustomSE();
    });
    ExtensionSymbolPanel.EXTENSION_SELECTOR_BUTTONS.click(function(){
        varthis.clearCustomSE();
    });
    
    
}

ExtensionSymbolPanel.prototype.clearOfficialSE = function(){
    // Clear rarity selector
    ExtensionSymbolPanel.RARITY_SELECTOR_BUTTONS.filter('.active').removeClass('active');
    ExtensionSymbolPanel.RARITY_SELECTOR.removeClass('active');
    ExtensionSymbolPanel.EXTENSION_SELECTOR_BUTTONS.filter('.active').removeClass('active');

    // hidden fields
    ExtensionSymbolPanel.FIELD_EXTENSION.val('');
    ExtensionSymbolPanel.FIELD_RARITY.val('');
}

ExtensionSymbolPanel.prototype.clearCustomSE = function(){
    // image
    ExtensionSymbolPanel.CUSTOM_PREVIEW.removeClass('active');
    ExtensionSymbolPanel.CUSTOM_PREVIEW.attr('src', '');
    ExtensionSymbolPanel.FILE_SELECTOR.val('');
    
    // hidden field
    ExtensionSymbolPanel.FIELD_CUSTOM.val('');
}




function getPanelByName(name){
    return existingPanels[name];
}


var existingPanels = [];
// Sections du header
existingPanels['home'] = new Panel('home', 'Accueil');
existingPanels['newcard'] = new Panel('newCard', 'Nouvelle carte');
existingPanels['help'] = new Panel('help', 'Aide');
existingPanels['credits'] = new Panel('credits', 'Crédits');
existingPanels['changelog'] = new Panel('changelog', 'Changelog');
existingPanels['tools'] = new Panel('tools', 'Outils');

// Sections du menu permanentes
existingPanels['template'] = new Panel('template', 'Template');
existingPanels['import-export'] = new Panel('import-export', 'Import / Export');
existingPanels['done'] = new Panel('done', 'Terminé !');

// Sections de génération de fonds
existingPanels['modernbasicbackground'] = new ModernBasicBackgroundPanel('modernbasicbackground', 'Fond de carte');
existingPanels['oldbasicbackground'] = new ModernBasicBackgroundPanel('oldbasicbackground', 'Fond de carte');
existingPanels['modernplaneswalkerbackground'] = new ModernBasicBackgroundPanel('modernplaneswalkerbackground', 'Fond de carte');

// Sections de fabrication de carte
existingPanels['illustration'] = new IllustrationPanel('illustration', 'Illustration');
existingPanels['mpw3-illustration'] = new ModernPW3IllustrationPanel('mpw3-illustration', 'Illustration');
existingPanels['mpw4-illustration'] = new ModernPW4IllustrationPanel('mpw4-illustration', 'Illustration');
existingPanels['titre-type'] = new Panel('titre-type', 'Titre et type');
existingPanels['cm'] = new Panel('cm', 'Coût de mana');
existingPanels['capa'] = new Panel('capa', 'Capacité<br/>Texte d\'ambiance');
existingPanels['mpw3-capa'] = new Panel('mpw3-capa', 'Capacité<br/>Texte d\'ambiance');
existingPanels['mpw4-capa'] = new Panel('mpw4-capa', 'Capacité<br/>Texte d\'ambiance');
existingPanels['fe'] = new Panel('fe', 'Force / Endurance');
existingPanels['modernbasicfe'] = new Panel('modernbasicfe', 'Force / Endurance');
existingPanels['loyalty'] = new Panel('loyalty', 'Loyauté de base');
existingPanels['se'] = new ExtensionSymbolPanel('se', 'Symbole d\'extension');
existingPanels['illus-copy'] = new Panel('illus-copy', 'Illustrateur<br/>Copyright');


// Ici, il n'y a pas de classes particulières pour ces panneaux, alors on ne passe pas par le prototype
existingPanels['done'].onFocus = function(){ updatePreview(); }
existingPanels['titre-type'].onFocus = function(){ $('#fcm-field-title').focus(); }
existingPanels['cm'].onFocus = function(){ $('#fcm-field-cm').focus(); }
existingPanels['capa'].onFocus = function(){ $('#fcm-field-capa').focus(); }
existingPanels['fe'].onFocus = function(){ $('#fcm-field-fe').focus(); }
existingPanels['illus-copy'].onFocus = function(){ $('#fcm-field-illustrator').focus(); }


// Dictionnaire des templates

var existingTemplates = [];     // contient tous mes templates de base

/**
 * Modern Basic
 */

function ModernBasicTemplate(){
    Funcard.call(this);
    
    this.template = 'modern-basic';
    
    this.panels = [
        existingPanels['modernbasicbackground'],
        existingPanels['illustration'],
        existingPanels['titre-type'],
        existingPanels['cm'],
        existingPanels['capa'],
        existingPanels['modernbasicfe'],
        existingPanels['se'],
        existingPanels['illus-copy']
    ];
    
    this.resetSize();
    /*this.width = Math.floor(791./2.);
    this.height = Math.floor(1107./2.);*/
    this.illusWidth = 651;
    this.illusHeight = 480;
    
}
ModernBasicTemplate.prototype = Object.create(Funcard.prototype);

ModernBasicTemplate.prototype.resetSize = function(){
    this.width = 791;
    this.height = 1107;
}

existingTemplates['modern-basic'] = new ModernBasicTemplate();









/**
 * Old Basic
 */

function OldBasicTemplate(){
    Funcard.call(this);
    
    this.template = 'old-basic';
    
    this.panels = [
        existingPanels['oldbasicbackground'],
        existingPanels['illustration'],
        existingPanels['titre-type'],
        existingPanels['cm'],
        existingPanels['capa'],
        existingPanels['fe'],
        existingPanels['se'],
        existingPanels['illus-copy']
    ];
    
    this.resetSize();
    /*this.width = Math.floor(791./2.);
    this.height = Math.floor(1107./2.);*/
    this.illusWidth = 601;
    this.illusHeight = 485;
    
}
OldBasicTemplate.prototype = Object.create(Funcard.prototype);

OldBasicTemplate.prototype.resetSize = function(){
    this.width = 787;
    this.height = 1087;
}

existingTemplates['old-basic'] = new OldBasicTemplate();













/**
 * Modern Planeswalker 3 capas
 */

function ModernPlaneswalker3Template(){
    Funcard.call(this);
    
    this.template = 'modern-planeswalker3';
    
    this.panels = [
        existingPanels['modernplaneswalkerbackground'],
        existingPanels['mpw3-illustration'],
        existingPanels['titre-type'],
        existingPanels['cm'],
        existingPanels['mpw3-capa'],
        existingPanels['loyalty'],
        existingPanels['se'],
        existingPanels['illus-copy']
    ];
    
    this.resetSize();
    /*this.width = Math.floor(791./2.);
    this.height = Math.floor(1107./2.);*/
    this.illusWidth = 665;  // Bounding box of illus-mask
    this.illusHeight = 890;
    
}
ModernPlaneswalker3Template.prototype = Object.create(Funcard.prototype);

ModernPlaneswalker3Template.prototype.resetSize = function(){
    this.width = 791;
    this.height = 1107;
}

existingTemplates['modern-planeswalker3'] = new ModernPlaneswalker3Template();









/**
 * Modern Planeswalker 4 capas
 */

function ModernPlaneswalker4Template(){
    Funcard.call(this);
    
    this.template = 'modern-planeswalker4';
    
    this.panels = [
        existingPanels['modernplaneswalkerbackground'],
        existingPanels['mpw4-illustration'],
        existingPanels['titre-type'],
        existingPanels['cm'],
        existingPanels['mpw4-capa'],
        existingPanels['loyalty'],
        existingPanels['se'],
        existingPanels['illus-copy']
    ];
    
    this.resetSize();
    /*this.width = Math.floor(791./2.);
    this.height = Math.floor(1107./2.);*/
    this.illusWidth = 665;  // Bounding box of illus-mask
    this.illusHeight = 890;
    
}
ModernPlaneswalker4Template.prototype = Object.create(Funcard.prototype);

ModernPlaneswalker4Template.prototype.resetSize = function(){
    this.width = 791;
    this.height = 1107;
}

existingTemplates['modern-planeswalker4'] = new ModernPlaneswalker4Template();/**
 * Gestion de la funcard de l'uilisateur
 */
var myFuncard = null;

function loadTemplate(template){
    if(template in existingTemplates)
        myFuncard = Object.create(existingTemplates[template]);
}var DEBUG = false;


// TODO Clean all the code

// Elements
var generator = $('#fcm-generator');
var loading = $('.loading-wrapper');
var loadingPreview = $('.fcm-preview-loading');
var preview = $('.fcm-preview');
var previewImage = $('.fcm-preview-image');
var previewReloder = $('#fcm-preview-reload');
var previewDebug = $('.fcm-preview-debug');
var previewTime = $('.fcm-preview-generation-time');

// focus element
var focusElement = null;

// innerLoading
var innerLoading = 0;

// Panels
var currentPanel = null;

// Events

/**
 * Changement de panneau lorsqu'on clique sur un bouton étant associé à un panneau
 */
$('#fcm-menu, #fcm-header').on('click', '[data-panel]', function(e){
    // Check if it's a valid link
    if(!$(this).data('panel')) return true;

    changePanel($(this).data('panel'));
    return false;
})

// Panel displayer

/**
 * Efface le panneau courant et affiche celui passé en paramètre.
 */
function changePanel(name){
    // hide the current panel
    if(currentPanel){
        currentPanel.hide();
    }
    // Show the new panel
    var panel = existingPanels[name];
    currentPanel = panel;
    panel.show();
}

/**
 * Retourne le panneau dans lequel est situé un élément du DOM passé en paramètre
 */
function getElementPanel(elem){
    var panelElement = elem.closest('[id^='+Panel.ID_PREFIX+']');
    //console.log(panelElement);
    if(!panelElement.length) return null;
    var panelName = panelElement.attr('id');
    //console.log(panelName);
    panelName = panelName.substr(Panel.ID_PREFIX.length);
    //console.log(panelName);
    if (!(panelName in existingPanels)) return null;
    return existingPanels[panelName];
}

/**
 * Efface la liste des panneaux et charge les nouveaux panneaux
 * à partir des données de la funcard passée en paramètre
 */
function loadPanels(funcard){
    
    showLoading();
    
    var panelsTab = getFuncardPanelNameList(funcard);
    
    // Call getpanels.php
    $.get(
        'getpanels.php',
        { 'panels[]' : panelsTab },
        function(data){
            // Destroy current pannels
            clearPanels();
            showPanels(funcard, data);
            changePanel(panelsTab[0]);
            generator.trigger('panelsLoaded');
            
            setTimeout( function(){
                $('.warning-template-changes').addClass('active');
            }, 10);
        }
    ).fail(function() {
        alert( 'Erreur au chargement des composantes de la funcard. Veuillez réessayer.' );
    })
    .always(function(){
        hideLoading();
    });

}

/**
 * Retourne la liste des noms de panneaux de la funcard
 */
function getFuncardPanelNameList(funcard){
    // On crée un tableau contenant les noms de panneaux à charger
    var panelsTab = [];
    if(funcard != null){
        for(var panel of funcard.panels){
            panelsTab.push(panel.name);
        }
    }
    return panelsTab;
}

/**
 * Efface tous les panneaux
 */
function clearPanels(){
    Panel.TEMPLATE_CONTAINER_ELEMENT.html('');
    Panel.TEMPLATE_MENU_ELEMENT.html('');
}

/**
 * Show Panels : Affiche les panneaux chargés
 */
function showPanels(funcard, data){
    // On désactive les anciens panneaux s'ils existent
    if(funcard != null){
        for(var panel of funcard.panels){
            panel.deactivate();
        }
    }
    
    // On ajoute les panneaux chargés
    Panel.TEMPLATE_CONTAINER_ELEMENT.html(data);
    // On active les panneaux (ça les ajoute dans le menu notamment)
    if(funcard != null){
        for(var panel of funcard.panels){
            panel.activate();
        }
    }
}


/**
 * Charge le template dans myFuncard
 */
Panel.CONTAINER_ELEMENT.on('click', '#fcm-template-selector .fcm-selector-button', function(){
    loadTemplate($(this).data('value'));
    // Une fois le template chargé, on update la preview une fois unique.
    generator.one('panelsLoaded', updatePreview);
    loadPanels(myFuncard);
});


/**
 * Affiche/Cache un icone loading sur le generator
 */
function showLoading(){
    ++innerLoading;
    if(innerLoading > 1) return;
    loading.addClass('active');
    setTimeout( function(elem){
        elem.addClass('opaque');
    }, 10, loading);
}
function hideLoading(){
    --innerLoading;
    if(innerLoading > 0) return;
    loading.removeClass('active');
    setTimeout( function(elem){
        elem.removeClass('opaque');
    }, 10, loading);
}


/**
 * Affiche/Cache un icone loading sur la prévisualisation
 */
function showPreviewLoading(){
    loadingPreview.addClass('active');
    setTimeout( function(elem){
        elem.addClass('opaque');
    }, 10, loadingPreview);
}
function hidePreviewLoading(){
    loadingPreview.removeClass('active');
    setTimeout( function(elem){
        elem.removeClass('opaque');
    }, 10, loadingPreview);
}


/**
 * Met à jour la prévisualition
 */
function updatePreview(){
    if(!myFuncard) return false;
    
    updateFields(myFuncard);
    
    myFuncard.resetSize();
    
    showPreviewLoading();
    //preview.css('width', myFuncard.width);
    //preview.css('height', myFuncard.height);
    // CALL THE GENERATION !!!! HELL YEAH !!!!
    $.post( "generate.php", {
        //  Data to send to the generation algorithm
        
        'width': myFuncard.width,
        'height': myFuncard.height,
        'template' : myFuncard.template,
        'fields' : myFuncard.fields
        
    },function( data ) {
        // success function
        if(DEBUG){
            previewDebug.html(data);
        } else {
            //console.log(data);
            preview.removeClass('nocard');
            previewImage.css('background-image', 'url(data:image/png;base64,'+data.image+')');
            previewTime.html('Généré en ' + data.generationTime.toFixed(3) + ' secondes.');
            myFuncard.width = data.width;
            myFuncard.height = data.height;
            preview.css('width', myFuncard.width);
            preview.css('height', myFuncard.height);
        }
        
    })
    .fail(function(xhr) {
        if(!isUserAborted(xhr)){
            alert( "error" );
        }
    })
    .always(function(){
        hidePreviewLoading();
    });
    
    return false;
}

previewReloder.click(updatePreview);

function isUserAborted(xhr) {
  return !xhr.getAllResponseHeaders();
}


/**
 * Met à jour la myFuncard d'après les valeurs des inputs HTML
 */
function updateFields(funcard){
    var htmlInputs = Panel.CONTAINER_ELEMENT.find('[name|="fcm-field"]');
    funcard.fields = {};
    htmlInputs.each(function(){
        var fieldname = $(this).attr('name');
        fieldname = fieldname.slice(10); // 10 characters in 'fcm-fields-'
        var fieldvalue= $(this).val();
        funcard.fields[fieldname] = fieldvalue;
    })
}


/**
 * Affiche le panel Home au chargement de la page, et au clic sur le logo
 */
if($('body').hasClass('index')){
    changePanel('home');
}

/**
 * Boutons d'insertion de contenu automatique dans le champ
 * Utilisé pour les symboles de mna et les caractères spéciaux
 */
// Il faut constamment enir à jour le dernier champ focussé
Panel.CONTAINER_ELEMENT.on('focus', 'input, textarea', function(){
    focusElement = $(this);
});

Panel.CONTAINER_ELEMENT.on('click', 'button.single-inserter', function(){
    var caretPosStart = focusElement[0].selectionStart;
    var caretPosEnd = focusElement[0].selectionEnd;
    var textAreaTxt = focusElement.val();
    var txtToAdd = $(this).data('insert');
    if(txtToAdd === undefined) return;
    focusElement.val(textAreaTxt.substring(0, caretPosStart)
                   + txtToAdd
                   + textAreaTxt.substring(caretPosEnd) );
    focusElement.focus();
    setCaretPosition(focusElement[0],
                     caretPosEnd + txtToAdd.length,
                     caretPosEnd + txtToAdd.length);
    
});

Panel.CONTAINER_ELEMENT.on('click', 'button.double-inserter', function(){
    var caretPosStart = focusElement[0].selectionStart;
    var caretPosEnd = focusElement[0].selectionEnd;
    var textAreaTxt = focusElement.val();
    var txtToAdd = $(this).data('insert');
    if(txtToAdd === undefined
       || !$.isArray(txtToAdd)
       || txtToAdd[0] === undefined
       || txtToAdd[1] === undefined) return;
    focusElement.val(textAreaTxt.substring(0, caretPosStart)
                   + txtToAdd[0]
                   + textAreaTxt.substring(caretPosStart, caretPosEnd)
                   + txtToAdd[1]
                   + textAreaTxt.substring(caretPosEnd) );
    focusElement.focus();
    setCaretPosition(focusElement[0],
                     caretPosStart,
                     caretPosEnd + txtToAdd[0].length + txtToAdd[1].length);
});

/**
 * Code from eternal : http://stackoverflow.com/questions/512528/set-cursor-position-in-html-textbox
 */
function setCaretPosition(ctrl, pos1, pos2)
{

    if(ctrl.setSelectionRange)
    {
        ctrl.focus();
        ctrl.setSelectionRange(pos1,pos2);
    }
    else if (ctrl.createTextRange) {
        var range = ctrl.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos2);
        range.moveStart('character', pos1);
        range.select();
    }
}

// Keyboard shortcut to print nbsp
$(document).keydown(function (event) {
    // espace avec Maj
    if (event.which == 32 && event.shiftKey) {
        var caretPosStart = focusElement[0].selectionStart;
        var caretPosEnd = focusElement[0].selectionEnd;
        var textAreaTxt = focusElement.val();
        var txtToAdd = '\u00A0';
        focusElement.val(textAreaTxt.substring(0, caretPosStart)
                       + txtToAdd
                       + textAreaTxt.substring(caretPosEnd) );
        focusElement.focus();
        setCaretPosition(focusElement[0],
                         caretPosEnd + txtToAdd.length,
                         caretPosEnd + txtToAdd.length);
        event.preventDefault();
    }
});


// Media Handling
Panel.CONTAINER_ELEMENT.on('change', '.fcm-media', function(){
    var form = $(this).closest('form');
    var formdata = (window.FormData) ? new FormData(form[0]) : null;
    var data = (formdata !== null) ? formdata : form.serialize();
    var loading = form.find('.file-loading-icon');
    var error = form.find('.file-error');
    var image = form.find('.file-preview');
    var field = form.find('.file-field');
    
    function showMediaUploadError(message){
        error.html(message);
        image.removeClass('active');
        error.addClass('active');
        field.val('');
        form.trigger('uploadFailure');
    }
    function showMediaUploadMessage(message){
        error.html(message);
        error.addClass('active');
    }
    
    loading.addClass('active');
    // début des opérations
    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        contentType: false, // obligatoire pour de l'upload
        processData: false, // obligatoire pour de l'upload
        //dataType: 'json', // selon le retour attendu
        data: data
    }).done(function(response){
        response = $.parseJSON(response);
        // On a reçu la réponse du serveur, c'est soit le filepath, soit une erreur. Soit un autre truc inconnu, mais là c'est bizarre.
        if("filepath" in response){
            image.attr('src', 'uploads/'+response.filepath);
            image.addClass('active');
            showMediaUploadMessage('Votre image sera disponible jusqu\'à minuit.');
            field.val(response.filepath);
            form.trigger('uploadSuccess');
        } else if("error" in response){
            showMediaUploadError(response.error);
        } else {
            showMediaUploadError('Erreur inconnue. Merci de nous en parler sur le forum.');
        }
        
    }).fail(function(){
        showMediaUploadError('Erreur lors de l\'envoi du fichier.');
    }).always(function(){
        loading.removeClass('active');
    });
});

/**
 * Gestion des sélecteurs
 */
Panel.CONTAINER_ELEMENT.on('click', '.fcm-selector .fcm-selector-button', function(){
    var container = $(this).closest('.fcm-selector');
    var field = container.find('.fcm-selector-field');
    
    // On décoche le clear button s'il existe
    container.find('.fcm-selector-clear-button.active').removeClass('active');
    
    //clear previous slected element
    container.find('.fcm-selector-button.active').removeClass('active');
    $(this).addClass('active');
    
    //set hidden field
    field.val($(this).data('value'));
});

Panel.CONTAINER_ELEMENT.on('click', '.fcm-toggle-button', function(){
    $(this).toggleClass('active');
});

Panel.CONTAINER_ELEMENT.on('click', '.fcm-toggle-duoselector', function(){
    var container = $(this).closest('.fcm-selector, .fcm-duoselector');
    
    container.toggleClass('fcm-duoselector');
    container.toggleClass('fcm-selector');
    
    if(container.hasClass('fcm-selector')){
        
        var button = $(container.find('.fcm-selector-button.active')[0]);
        container.find('.fcm-selector-button.active').removeClass('active first second');
        button.trigger('click');
        
    } else if(container.hasClass('fcm-duoselector')){
        
        container.find('.fcm-selector-button.active').addClass('first');
        
    }
});

Panel.CONTAINER_ELEMENT.on('click', '.fcm-duoselector .fcm-selector-button', function(){
    var container = $(this).closest('.fcm-duoselector');
    var field = container.find('.fcm-selector-field');
    var str = '';
    var separator = false;
    
    var checked = container.find('.fcm-selector-button.active');
    var nbchecked = checked.length;
    
    // On décoche le clear button s'il existe
    container.find('.fcm-selector-clear-button.active').removeClass('active');
    
    // Si nous avons déjà deux boutons sélectionnés, nous les effaçons
    if(nbchecked >= 2){
        container.find('.fcm-selector-button.active').removeClass('active first second');
        $(this).addClass('active first');
    }
    // Coche si premier bouton
    if(nbchecked == 0){
        $(this).addClass('active first');
    }
    // Coche si deuxième bouton différent du premier
    if(nbchecked == 1 && $(this)[0] != checked[0]){
        $(this).addClass('active second');
    }
    
    // On check quel est le séparateur
    if(container.data('separator')) separator = container.data('separator');
    
    // On calcule la valeur du champ
    var first = container.find('.fcm-selector-button.active.first');
    if(first.length) str += first.data('value');
    var second = container.find('.fcm-selector-button.active.second');
    if(separator != false && first.length && second.length) str += separator;
    if(second.length) str += second.data('value');
    
    field.val(str);
    
});

Panel.CONTAINER_ELEMENT.on('click', '.fcm-selector-clear-button', function(){
    var container = $(this).closest('.fcm-selector, .fcm-duoselector');
    var field = container.find('.fcm-selector-field');
    
    var buttons = container.find('.fcm-selector-button');
    buttons.removeClass('active first second');
    field.val('');
    $(this).addClass('active');
});

/**
 * Télécharge le rendu final
 */
Panel.CONTAINER_ELEMENT.on('click', '#fcm-download-jpg', function(){
    if(!myFuncard){
        alert('Vous ne voudriez pas créer une funcard, d\'abord ?');
        return;
    }
    
    updateFields(myFuncard);
    // CALL THE GENERATION !!!! HELL YEAH !!!!
    openWithPostData('generate.php',{
        //  Data to send to the generation algorithm
        
        'width' : myFuncard.width,
        'height' : myFuncard.height,
        'template' : myFuncard.template,
        'fields' : myFuncard.fields,
        'method' : 'thumbnail'
        
    });
});

Panel.CONTAINER_ELEMENT.on('click', '#fcm-download', function(){
    if(!myFuncard){
        alert('Vous ne voudriez pas créer une funcard, d\'abord ?');
        return;
    }
    
    updateFields(myFuncard);
    // CALL THE GENERATION !!!! HELL YEAH !!!!
    openWithPostData('generate.php',{
        //  Data to send to the generation algorithm
        
        'width' : myFuncard.width,
        'height' : myFuncard.height,
        'template' : myFuncard.template,
        'fields' : myFuncard.fields,
        'method' : 'download'
        
    });
});

function openWithPostData(page,data)
{
    var form = document.createElement('form');
    form.setAttribute('action', page);
    form.setAttribute('method', 'post');
    recursiveCreateFields(form, data, false);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function recursiveCreateFields(form, data, field){
    for (var n in data)
    {
        //console.log(n, data[n]);
        if(data[n] !== null && typeof data[n] === 'object')
            recursiveCreateFields(form, data[n], field || (n==='fields'));
        else
            createElementField(form, data, n, field);
    }
}

function createElementField(form, data, n, field){
    var inputvar = document.createElement('input');
    inputvar.setAttribute('type', 'hidden');
    inputvar.setAttribute('name', field ? 'fields[' + n + ']' : n);
    inputvar.setAttribute('value', data[n]);
    form.appendChild(inputvar);
}

/**
 * Exporte la funcard dans un fichier json
 */
Panel.CONTAINER_ELEMENT.on('click', '#fcm-export', function(){
    if(!myFuncard) return;
    
    updateFields(myFuncard);
    // CALL THE GENERATION !!!! HELL YEAH !!!!
    openWithPostData('export.php',{
        //  Data to send to the generation algorithm
        
        'width' : myFuncard.width,
        'height' : myFuncard.height,
        'template' : myFuncard.template,
        'fields' : myFuncard.fields,
        'method' : 'download'
        
    });
});

/**
 * Importe la funcard depuis un fichier json
 */

Panel.CONTAINER_ELEMENT.on('change', '#fcm-file-import', function(){
    
    if(!$(this).val()) return;
    
    var form = $(this).closest('form');
    var loading = form.find('.file-loading-icon');
    var error = form.find('.file-error');
    var inputElement = $(this);
    
    loading.addClass('active');
    
    // Code adapted from Trausti Kristjansson's.
    var input, file, fr, json;

    if (typeof window.FileReader !== 'function') {
        alert("Hum... votre navigateur semble un peu vieux, non ? Vous devriez utilisez un navigateur plus récent.");
        return;
    }

    input = $(this)[0];
    if (!input) {
        alert("Um, couldn't find the fileinput element.");
        loading.removeClass('active');
    }
    else if (!input.files) {
        alert("Hum... votre navigateur semble un peu vieux, non ? Vous devriez utilisez un navigateur plus récent.");
        loading.removeClass('active');
    }
    else if (!input.files[0]) {
        alert("Merci de sélectionner un fichier");
        loading.removeClass('active');
    }
    else {
        file = input.files[0];
        fr = new FileReader();
        fr.onload = receivedText;
        fr.readAsText(file);
    }

    function receivedText(e) {
        //loading.removeClass('active');
        var lines = e.target.result;
        //console.log(lines);
        json = JSON.parse(lines); 
        
        importFuncard(json);
        
    }
    
    function importFuncard(json){
        // on prend le bon template
        error.removeClass('active');

        if(!json.hasOwnProperty('template')
          || !json.hasOwnProperty('width')
          || !json.hasOwnProperty('height')
          || !json.hasOwnProperty('fields')){
            importError('Fichier corrompu. Tentez un autre fichier. (Erreur 1)');
            return;
        }

        if(!existingTemplates.indexOf(json.template)){
            importError('Fichier corrompu. Tentez un autre fichier. (Erreur 2)');
            return;
        }
        
        // Une fois les fond chargés, on continue l'importation.
        // BackgroundsLoaded s'est effectué après panelsLoaded
        generator.one('backgroundsLoaded', json.fields, function(event){
            importFuncardFieldsEvent(event);
        });
        loadTemplate(json.template);
        loadPanels(myFuncard);
        
        myFuncard.width = json.width;
        myFuncard.height = json.height;
        myFuncard.fields = json.fields;
    }

    function importError(msg){
        error.addClass('active').html(msg);
        loading.removeClass('active');
        inputElement.val('');
    }
    
    function importFuncardFieldsEvent(event){
        //console.log(event.data);
        var fieldsToImport = event.data;

        for( field in fieldsToImport ) {

            // on se charge du field
            var fieldElement = $('#fcm-field-'+field);
            fieldElement.val(fieldsToImport[field]);

            //on se charge du bouton s'il existe
            var selectorElement = $('.fcm-selector[data-field="fcm-field-'+field+'"]');
            var buttonElement = selectorElement.find('.fcm-selector-button[data-value="'+fieldsToImport[field]+'"]');
            //console.log(buttonElement);
            buttonElement.trigger('click');
        }

        loading.removeClass('active');
        inputElement.val('');
        updatePreview();
    }
    
});

/**
 * Afficher/cacher des paragraphes de l'aide
 */
Panel.CONTAINER_ELEMENT.on('click', '.help-title', function(){
    var paragraph = $(this).next('.help-paragraph');
    $(this).toggleClass('active');
    paragraph.toggleClass('active');
});

/**
 * Affiche / masque le menu principal sur petites résolutions
 */
$('#menu-toggle').click(function(){
    $('#main-menu').toggleClass('active');
    $('#menu-toggle .fa').toggleClass('fa-bars').toggleClass('fa-times');
});    
});

