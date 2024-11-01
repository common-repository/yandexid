(function ( $ ) {
	$(function () {
  
    let form = $('.yandexid-form');

    btnSettings();
    btnSuggestSettings();
    showBtnExample();

    $('#yandexid_type_selection').change();
    
    $('#yandexid_type_selection').change(function(e){
      btnSettings();
    });
    
    $('#suggest_button_view').change(function(e){
      btnSuggestSettings();
      showBtnExample();
    });
    
    $('#suggest_button_theme').change(function(e){
      btnSuggestSettings();
      showBtnExample();
    });

    function btnSettings(){
      let yandexidType = form.find('#yandexid_type_selection').val();

      if(yandexidType == 'widget'){
        form.find('.settings-suggest').hide();
      }else{
        form.find('.settings-suggest').show();
      }    
    }
    
    function btnSuggestSettings(){
      let yandexidBtnView = form.find('#suggest_button_view').val();

      if(yandexidBtnView == 'iconGrey' || yandexidBtnView == 'iconBorder'){
        form.find('tr.icon').show();
      }else{
        form.find('tr.icon').hide();
      }    
    }
    
    function showBtnExample(){
      let view = form.find('#suggest_button_view').val();
      let theme = form.find('#suggest_button_theme').val();
      
      form.find('.btn-example img').hide();
      
      if(view == 'main' && theme == 'light'){
        form.find('.btn-example .main-light').show();
      }else if(view == 'main' && theme == 'dark'){
        form.find('.btn-example .main-dark').show();
      }else if(view == 'additional' && theme == 'light'){
        form.find('.btn-example .additional-light').show();
      }else if(view == 'additional' && theme == 'dark'){
        form.find('.btn-example .additional-dark').show();
      }else if(view == 'icon'){
        form.find('.btn-example .icon').show();
      }else if(view == 'iconGrey'){
        form.find('.btn-example .icongrey').show();
      }else if(view == 'iconBorder'){
        form.find('.btn-example .iconborder').show();
      } 
    }
	});
}( jQuery ));