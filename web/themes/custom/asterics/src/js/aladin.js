(function($, Drupal, drupalSettings) {
 
    Drupal.aladin = Drupal.aladin || {};
    
    Drupal.aladin.Aladin = function(settings) {
        var aladin = A.aladin('#aladin-lite-div');
        aladin.setImageSurvey(aladin.createImageSurvey("BIG_color", "Color BiH HiPS", "http://astrodeep.u-strasbg.fr/ff/data/HiPS/o_BIH_color", "equatorial", 13, {imgFormat: 'png'})); // setting a custom HiPS
        aladin.gotoRaDec(3.5920474,-30.405748);
        aladin.setFoV(0.02); 
    }
    
    Drupal.behaviors.Aladin = {
        attach : function (context,settings) {
            if(settings.aladin) {
                 console.log(drupalSettings.aladin);
                if(Drupal.aladin[0] === undefined) {
                    Drupal.aladin[0] = new Drupal.aladin.Aladin(drupalSettings.aladin);
                }
            }
        }
    }
    
})(jQuery, Drupal, drupalSettings);