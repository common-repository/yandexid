<div id="yandex-id-container"></div>

<script>

  let tokenPageOrigin = '<?php echo wp_kses($tokenPageOrigin, kses_tags()); ?>';
  
  let oauthQueryParams = {
        client_id: '<?php echo wp_kses($client_id, kses_tags()); ?>',
        response_type: 'token',
        redirect_uri: '<?php echo wp_kses($redirect_uri, kses_tags()); ?>' 
      };
      
  let suggestParams = {
        view: 'button',
        parentId: '<?php echo wp_kses($parent_id, kses_tags()); ?>',
        buttonView: '<?php echo wp_kses($button_view, kses_tags()); ?>',
        buttonTheme: '<?php echo wp_kses($button_theme, kses_tags()); ?>',
        buttonSize: '<?php echo wp_kses($button_size, kses_tags()); ?>'
    };
     
  <?php if(!empty($button_border_radius)): ?>  
    suggestParams.buttonBorderRadius = '<?php echo wp_kses($button_border_radius, kses_tags()); ?>';
  <?php endif; ?>   
     
  <?php if(!empty($color_bg_base)): ?>  
    suggestParams.customBgColor = '<?php echo wp_kses($color_bg_base, kses_tags()); ?>';
  <?php endif; ?>  
  
  <?php if(!empty($color_bg_hovered)): ?>  
    suggestParams.customBgHoveredColor = '<?php echo wp_kses($color_bg_hovered, kses_tags()); ?>';
  <?php endif; ?> 
    
  <?php if(!empty($color_border_base)): ?>  
    suggestParams.customBorderColor = '<?php echo wp_kses($color_border_base, kses_tags()); ?>';
  <?php endif; ?> 
  
  <?php if(!empty($color_border_hovered)): ?>  
    suggestParams.customBorderHoveredColor = '<?php echo wp_kses($color_border_hovered, kses_tags()); ?>';
  <?php endif; ?> 
  
  <?php if(!empty($border_thickness)): ?>  
    suggestParams.customBorderWidth = '<?php echo wp_kses($border_thickness, kses_tags()); ?>';
  <?php endif; ?> 
  
  window.addEventListener("load", function(event) {
  
    <?php if($type_selection == 'widget'): ?>
      initYandexIdV1();
    <?php else: ?>
      initYandexIdV2();
    <?php endif; ?>

  });
  

  /* Виджет «Мгновенный вход» */
  function initYandexIdV1(){

    window.YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin)
    .then(function(result) {
      return result.handler();
    })
    .then(function(data) {
      authorizationUser(data.access_token);
    })
    .catch(function(error) {
      console.log('Что-то пошло не так: ', error);
    });  
  }
  
  /* Кнопка авторизации */
  function initYandexIdV2(){

    window.YaAuthSuggest.init(oauthQueryParams, tokenPageOrigin, suggestParams)
    .then(function(result) {
      return result.handler();
    })
    .then(function(data) {
      authorizationUser(data.access_token);
    })
    .catch(function(error) {
      console.log('Что-то пошло не так: ', error);
    });  
  }
  
  /* Авторизация пользователя */
  function authorizationUser(access_token){
    let url = '<?php echo admin_url( "admin-ajax.php" ) ?>';
    
    url = url + '?action=ajax_authorization_user&access_token=' + access_token;

    let options = {
        method: "GET",
        headers: {
          'Content-Type': 'application/json'
        }
    };    
    
    fetch(url, options).then(response => response.json()).then( result => {

      if(result.error == false){
        window.location.href = '/';
      }else{
        alert(result.message);
        window.location.href = '/';
      }
    }).catch(error => console.log("error", error));
  }  

</script>
