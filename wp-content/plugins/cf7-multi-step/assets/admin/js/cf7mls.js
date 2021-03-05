(function($) {
  jQuery(document).ready(function($) {
    jQuery(".cf7mls-color-field").wpColorPicker();

    jQuery('#cf7mls-progress-bar .cf7mls_progress_bar_filter').wpColorPicker(
      { 
        change: function(event, ui){
          // Change background color progress bar
          let color = ui.color.toString();
          // cf7mls_change_color_bar(color);
        }
      }
    );

    jQuery('#cf7mls-progress-bar .cf7mls_progress_bar_percent_filter').wpColorPicker(
      { 
        change: function(event, ui){
          // Change background color progress bar
          let color = ui.color.toString();
          // $('.cf7mls_progress_bar_percent_wrap .cf7mls_progress_barinner').css('background', color);
          // $('.cf7mls_progress_bar_per_mobie_wrap .cf7mls_progress_barinner').css('background', color);
        }
      }
    );

    var btnColorPickers = jQuery('button.wp-color-result')
    for (var i = 0; i < btnColorPickers.length; i++) {
      btnColorPickers[i].defaultValue = btnColorPickers[i].value
    }

    var title_steps = [];
    if(cf7mls.steps.length >= 2 && Array.isArray(cf7mls.steps)) {
      title_steps = cf7mls.steps;
    }
    $('#postbox-container-1').append($('#cf7mls_PostBoxUpgradePro'));
    $('#tag-generator-list a.thickbox').click(function(){
      $( 'textarea#wpcf7-form' ).attr('id', 'wpcf7-form-bak');
    });
    var tb_unload_count = 1;
    $(window).bind('tb_unload', function () {
      if (tb_unload_count > 1) {
        tb_unload_count = 1;
      } else {
        $( 'textarea#wpcf7-form-bak' ).attr('id', 'wpcf7-form');
        tb_unload_count = tb_unload_count + 1;
      }
    });

    //cf7mls_load_step_name(jQuery('#wpcf7-form').val());
    // $("[data-config-field]").change(function() {
    //   var val = $(this).val();
    //   cf7mls_load_step_name(val);
    //đây là đoạn code tớ xử lý, khi cái textarea kia change, thì load mấy cái input nhập name, mà có lúc dc lúc ko, có gì cậu coi giùm tớ nhé :d
    // });

    // jQuery("#contact-form-editor").on("tabsactivate", function(event, ui) {
    //   if ((ui.newTab.context.hash === "#cf7mls-progress-bar") && Array.isArray(title_steps)) {
    //     cf7mls_load_step_name(title_steps);
    //   }
    // });

    // Transition effects
    if($('#cf7mls_multi_step_wrap input.cf7mls_toggle_transition_effects').is(":checked") === false){ 
      $('#cf7mls_multi_step_wrap .cf7mls-stype-transition-wrap').css('display', 'none');
    }

    $('#cf7mls_multi_step_wrap .cf7mls_switch').click(function(){
      if($('#cf7mls_multi_step_wrap input.cf7mls_toggle_transition_effects').is(":checked")){
        $('#cf7mls_multi_step_wrap input.cf7mls_toggle_transition_effects').attr('checked', true);
        $('#cf7mls_multi_step_wrap .cf7mls-stype-transition-wrap').css('display', '');
      }else {
        $('#cf7mls_multi_step_wrap input.cf7mls_toggle_transition_effects').attr('checked', false);
        $('#cf7mls_multi_step_wrap .cf7mls-stype-transition-wrap').css('display', 'none');
      }
    });

    if($('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar').is(":checked")  === false){
      $('#cf7mls_progress_bar').addClass('hide');
      $('.cf7mls_number_step_wrap').addClass('hide');
      $('.cf7mls_form_demo_one').css('margin-top', '28px');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress').addClass('hide');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_progress_style_wrap').addClass('hide');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .title_options_wrap').addClass('hide');
    }else {
      $('#cf7mls_progress_bar').removeClass('hide');
      $('.cf7mls_number_step_wrap').removeClass('hide');
      $('.cf7mls_form_demo_one').css('margin-top', '');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress').removeClass('hide');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_progress_style_wrap').removeClass('hide');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .title_options_wrap').removeClass('hide');
    }

    $('.cf7mls-pogress-bar .cf7mls_progress_bars_witch').click(function(event) {
      event.stopPropagation();
      // if($('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar').is(":checked")){
      //   $('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar').attr('checked', true);
      //   $('#cf7mls_progress_bar').removeClass('hide');
      //   $('.cf7mls_number_step_wrap').removeClass('hide');
      //   $('.cf7mls_form_demo_one').css('margin-top', '');
      //   $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress').removeClass('hide');
      //   $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_progress_style_wrap').removeClass('hide');
      //   $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .title_options_wrap').removeClass('hide');
      // }else {
      //   $('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar').attr('checked', false);
      //   $('#cf7mls_progress_bar').addClass('hide');
      //   $('.cf7mls_number_step_wrap').addClass('hide');
      //   $('.cf7mls_form_demo_one').css('margin-top', '28px');
      //   $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress').addClass('hide');
      //   $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_progress_style_wrap').addClass('hide');
      //   $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .title_options_wrap').addClass('hide');
      // }
    });

    // Background Color Progress Bar
    var progress_bar_bg_color = $('.cf7mls_progress_bar_filter').val();
    $('head').append('<style id="cf7mls_style_progress_bar" type="text/css"></style>');
    if(progress_bar_bg_color) {
      cf7mls_change_color_bar(progress_bar_bg_color);
    }

    // Progress bar percent
    if($('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar_percent').is(":checked") === false){
      // $('.cf7mls_progress_bar_percent_wrap').addClass('hide');
      $('.cf7mls_progress_bar_per_mobie_wrap').addClass('hide');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress_percent').addClass('hide');
    }else {
      $('.cf7mls_progress_bar_per_mobie_wrap').removeClass('hide');
      $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress_percent').removeClass('hide');
    }

    $('.cf7mls-pogress-bar-percent .cf7mls_progress_bars_witch').click(function() {
      if($('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar_percent').is(":checked")){
        $('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar_percent').attr('checked', true);
        // $('.cf7mls_progress_bar_percent_wrap').removeClass('hide');
        $('.cf7mls_progress_bar_per_mobie_wrap').removeClass('hide');
        $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress_percent').removeClass('hide');
      }else {
        $('.cf7mls_progress_bars_witch input.cf7mls_enable_progress_bar_percent').attr('checked', false);
        // $('.cf7mls_progress_bar_percent_wrap').addClass('hide');
        $('.cf7mls_progress_bar_per_mobie_wrap').addClass('hide');
        $('.cf7mls_pogress_bar_wrap .cf7mls-group-pogress-bar .cf7mls_bg_color_wrap.cf7mls_bg_color_progress_percent').addClass('hide');
      }
    });

    // Background Color Progress Bar Percent
    var progress_bar_per_color = $('.cf7mls_progress_bar_percent_filter').val();
    if(progress_bar_per_color) {
      // $('.cf7mls_progress_bar_percent_wrap .cf7mls_progress_barinner').css('background', progress_bar_per_color);
      $('.cf7mls_progress_bar_per_mobie_wrap .cf7mls_progress_barinner').css('background', progress_bar_per_color);
    }


    
    function cf7mls_change_color_bar(color) {
      let style_bar = 'cf7mls_bar_style_' + $('#cf7mls_progress_bar_style').val();
      let style_bar_icon = ($('#cf7mls_progress_bar_style').val() == 'box_vertical' || $('#cf7mls_progress_bar_style').val() == 'box_larerSign') ? 'squaren' : $('#cf7mls_progress_bar_icon_style').val();
      style_bar += '_' + style_bar_icon;
      let style_text = 'cf7mls_bar_style_text_' + $('.cf7mls-select-style-text .active').attr('data-style-text');

      let css_item_icon = '.' + style_bar + '.' + style_text + ' li .cf7_mls_steps_item_icon { background: '+ color + ';}';
      let css_item_icon_befor = '.' + style_bar + '.' + style_text + ' li:before { background: '+ color + ';}';

      let css_bg_li = '.' + style_bar + '.' + style_text + ' li{ background: '+ color + ';}';
      let css_step = '.' + style_bar + '.' + style_text + ' li .cf7_mls_count_step{ color: '+ color + ';}'
      let css_check = '.' + style_bar + '.' + style_text + ' li .cf7_mls_check{ color: '+ color + ';}';
      let css_li_after = '.' + style_bar + '.' + style_text + ' li:after{ background: '+ color + ';}';

      //
      let css_li = '.' + style_bar + '.' + style_text + ' li:nth-child';
      let css_title_after = '.cf7mls_progress_bar_title:after{ background: '+ color + ';}';
      let css_title_border = '.cf7mls_progress_bar_title:after{ border-color: '+ color + ';}'

      let css_progress_bar = '';
      // progress bar on ipad, mobile
      css_progress_bar += '.cf7mls_number_step_wrap .cf7mls_progress_barinner { background:' + color + ';}';

      // progress bar on computer
      
    }

  });
})(jQuery);

