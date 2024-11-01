<?php $originUrl = get_query_var('originUrl'); ?>

<!DOCTYPE html>
<html>
  <head>
    <?php wp_head(); ?>
  </head>

  <body>
    Авторизация...
    
    <script>

      let origin = '<?php echo wp_kses($originUrl, kses_tags()); ?>';  
      let extraData = {};

      window.addEventListener("load", function(event) {
        YaSendSuggestToken(origin, extraData);
      });

    </script>    

  </body>  
</html>


