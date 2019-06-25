( function ($, Drupal, drupalSettings) {

    $(document).ready(function() {

      userBox();
      //followControl();
      suscribeControl();

        $('header .right .top .ico-menu').click(function() {
          $(this).toggleClass("active");
          $('body').toggleClass('no_scroll');
          // $('#main-menu').fadeToggle();
          if ($('#main-menu').is(':visible')){
            $('#main-menu').fadeOut();
          } else {
            $('#main-menu').fadeIn().css('display', 'flex');
          }
        });
        //* USER TABS */
        if($('#usertabs')) {
            $('#usertabs').tabs();
            $('#observatories').tabs();
            $('#observations').tabs();
            $('#newsevents').tabs();
        }

        if($('#forms-wrapper')) {
            $('#newobservatori').click(function() {
                $('#forms-wrapper').show();
            });
            $('#forms-wrapper .close').click(function() {
                $('#forms-wrapper').hide();
            });
        }

        if($('#observation-form-wrapper')) {
            $('#askobervation').click(function() {
                $('#observation-form-wrapper').slideToggle();
            });
            $('.observation-map .close').click(function () {
                $('#observation-form-wrapper.observation-map').hide();
            });
        }
        if($('#search-observation-form-wrapper')) {
            $('.observation-map .close').click(function () {
                $('#search-observation-form-wrapper.observation-map').hide();
            });
        }

        if($('.view_obervatories-map')) {
            $('.past .made').click(function() {
                window.location.href ='/celestial-map';
            });
            $('.future .made').click(function () {
                $('#observation-form-wrapper.observation-map').css('display','flex');
                $('#edit-ra').val('3h 5m 38s');
                $('#edit-dec').val('48 50 2"');
                $('#edit-from-date').val('2019-10-01');
                $('#edit-from-time').val('00:00:00');
                $('#edit-to-date').val('2019-12-21');
                $('#edit-to-time').val('00:00:00');
                var observatory=$(this).closest('.timeline-row').attr('attr-observatory');
                $('#edit-observatory').val(observatory);
                if($('#edit-actions #edit-actions-schedule')) {$('#edit-actions #edit-actions-schedule').remove();}
                var export_to_schedule = '<input class="webform-button--submit button button--primary js-form-submit form-submit" data-drupal-selector="edit-actions-submit" type="button" disabled="disabled" id="edit-actions-schedule" name="op" value="Export to Schedule">'; 
                $('#edit-actions').append(export_to_schedule);
            });
            $('.askfor').click(function () {
                $('#observation-form-wrapper.observation-map').css('display','flex');
                $('#edit-ra').val('');
                $('#edit-dec').val('');
                $('#edit-from-date').val('');
                $('#edit-to-date').val('');
                $('#edit-observatory').val('');
            });
            $('.searchfor').click(function () {
                $('#search-observation-form-wrapper.observation-map').css('display','flex');
            });
        }
        
        if($('.view_display_askedfor')) {
            aproveControl();
        }
        

    });
/*
/* PASSEM DEL FOLLOW / UNFOLLOW A SUSCRIBE / UNSUSCRIBE */
    
    function suscribeControl() {
        const suscribeMarkup = ($('.user_page')) ? $('<div class="subscribe"><a href="#">Suscribe</a></div>') : $('<div class="subscribe">If you subscribe to this observatroy you will recieve alerts from it <a href="#">Suscribe</a></div>')
        const unSuscribeMarkup = ($('.user_page')) ? $('<div class="unsubscribe"><a href="#">Unsuscribe</a></div>') : $('<div class="unsubscribe">You are subscribed to the events and alerts of this observatory <a href="#">Unsuscribe</a></div>');
        const message = $('<div class="follow_message"><div class="box"><span class="close"></span><div id="message"></div></div></div>').hide();
        message.find('.close').click(function(){
            $(this).closest('.follow_message').fadeOut().remove();
            $('body').removeClass('no_scroll');
        })
        $('.suscribe a').each(function () {
            suscribeButtonControl($(this));
        });
        $('.unsuscribe a').each(function () {
            unSuscribeButtonControl($(this));
        });
        
        function suscribeButtonControl(elem) {
            elem.click(function(e) {
               e.preventDefault();
               elem.parent().append('<div class="ajax-progress"><div class="ajax-progress-throbber">&nbsp;</div></div>');
               var parent = elem.parent('.suscribe').parent("#suscription-wrapper");
               var tid = parent.attr('data-tid');
               
               $.ajax ({
                   url: "/demoasterics/suscribe/"+tid,
                   dataType: "json",
                   success: function(result) {
                       
                       $('body').append(message.clone(true));
                       $('.follow_message').find('#message').append(result.message);                        
                       if ($('.user_page')) {
                           $('.follow_message .close').click(function(){
                               window.location.reload();
                       });}
                       $('.follow_message').fadeIn();
                       $('body').addClass('no_scroll');
                       $(".ajax-progress").remove();
                       parent.empty();
                       parent.append(unSuscribeMarkup);
                       unSuscribeButtonControl(elem);

                   }
               });
               
            });
        }
        
        function unSuscribeButtonControl(elem) {
            elem.click(function(e) {
               e.preventDefault();
               var parent = elem.parent('.unsuscribe').parent("#suscription-wrapper");
               var tid = parent.attr('data-tid');
              $.ajax ({
                   url: "/demoasterics/unsuscribe/"+tid,
                   dataType: "json",
                   success: function(result) {
                       $('body').append(message.clone(true));
                       $('.follow_message').find('#message').append(result.message);
                       if ($('.user_page')) {
                           $('.follow_message .close').click(function(){
                               window.location.reload();
                       });}
                       $('.follow_message').fadeIn();
                       $('body').addClass('no_scroll');
                       $(".ajax-progress").remove();
                       parent.empty();
                       parent.append(suscribeMarkup);
                       suscribeButtonControl(elem);
                   }
               });
            });
        }
    }
    
    function aproveControl() {
        const aproveMessage = $('<div class="follow_message"><div class="box"><span class="close"></span>The observation has been scheduled</div></div>').hide();
        const message = $('<div class="follow_message"><div class="box"><span class="close"></span><div id="message"></div></div></div>').hide();
        message.find('.close').click(function(){
            $(this).closest('.follow_message').fadeOut().remove();
            $('body').removeClass('no_scroll');
        })
        
        aproveButtonControl($('.view_display_askedfor .schedule'));
        
        function aproveButtonControl(elem) {
            elem.click(function (e) {
                e.preventDefault();
                $(this).parent().append('<div class="ajax-progress"><div class="ajax-progress-throbber">&nbsp;</div></div>');
                var sid = $(this).attr('attr-sid');
                $.ajax ({
                   url: "/demoasterics/aprove/"+sid,
                   dataType: "json",
                   success: function(result) {
                       $('body').append(message.clone(true));
                       $('.follow_message').find('#message').append(result.message);
                       $('.follow_message').fadeIn();
                       $('body').addClass('no_scroll');
                       $('.sid-'+sid).find('.state').find('.field-content').text('Scheduled');
                       $('.ajax-progress').remove();
                       $('.sid-'+sid).find('.schedule').remove();
                       $('.sid-'+sid).find('.flexbox').append('<a class="button" disabled="disabled">Export to schedule</a>');
                   }
                });
            });
        }
    }
    
    /*
    function followControl() {

      const followMessage = $('<div class="follow_message"><div class="box"><span class="close"></span>Now you follow this observatory</div></div>').hide();
      const unFollowMessage = $('<div class="follow_message"><div class="box"><span class="close"></span>You have stopped following this observatory</div></div>').hide();

      unFollowMessage.add(followMessage).find('.close').click(function(){
        $(this).closest('.follow_message').fadeOut().remove();
        $('body').removeClass('no_scroll');
      })

      followButtonControl($('.follow a, .follow span'));
      unFollowButtonControl($('.unfollow a, .unfollow span'));

      function followButtonControl(elem){
        elem.each(function(){
          $(this).click(function(e){
            e.preventDefault();
            $('body').append(followMessage.clone(true));
            $('.follow_message').fadeIn();
            $('body').addClass('no_scroll');
            $(this).unbind('click');
            if (reverseText($(this)))
              unFollowButtonControl($(this));
          })
        })
      }

      function unFollowButtonControl(elem){
        elem.each(function(){
          $(this).click(function(e){
            e.preventDefault();
            $('body').append(unFollowMessage.clone(true));
            $('.follow_message').fadeIn();
            $('body').addClass('no_scroll');
            $(this).unbind('click');
            if (reverseText($(this)))
              followButtonControl($(this));
          })
        })
      }

      function reverseText(elem){
        if (elem.hasClass('reverse')){
          var actualReverseText = elem.text();
          elem.html(elem.attr('data-reverse'));
          elem.attr('data-reverse', actualReverseText);
          return true;
        }
        return false;
      }
    }
*/
    function userBox(){
      if ($('.user_box span').length){
        $('.user_box span').click(function(e){

          $(this).next().slideToggle();
        });
      }
    }
    

    /** PASWORD RESET BEHAVIOUR **/
    Drupal.behaviors.PasswordReset = {
      attach : function (context,settings) {
        if(settings.passreset) {
          $('.user-pass-reset .form-submit').trigger('click');
        }
      }
    }
    /** PASWORD RESET BEHAVIOUR **/

})(jQuery, Drupal, drupalSettings);
