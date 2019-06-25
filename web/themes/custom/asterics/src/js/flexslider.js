(function ($) {

  console.log('flexslider.js loaded');

  $(document).ready(function(){

    // EVA noticias - TX observatorio
    if ($('.view-id-news.view-display-id-entity_view_2').length) {
      $('.view-id-news.view-display-id-entity_view_2').flexslider({
        selector: ".view-content > .views-row",
        animation: "slide",
        // controlNav: true,
        directionNav: false,
        start: function(slider){
          slider.css('opacity',1);
        }
      })
    }
  })

}(jQuery));
