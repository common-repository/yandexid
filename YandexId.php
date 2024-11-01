<?php
/**
* Plugin Name:       YandexId
* Plugin URI:        https://yandex.ru/dev/id/doc/ru/
* Description:       Яндекс ID — это единый аккаунт на Яндексе. API Яндекс ID позволяет настроить авторизацию пользователя.
* Version:           2.0
* Requires at least: 6.0
* Requires PHP:      7.2
* Author:            Outcode
* Author URI:        https://outcode.ru/
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Network:           true
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once ABSPATH . 'wp-admin/includes/user.php';
require_once('YandexApi.php');

function kses_tags(){
  
  $attr = [
    'id' => [],
    'class' => [],
    'data-action' => [],
    'method' => [],
    'action' => [],
    'type' => [],
    'value' => [],
    'selected' => [],
    'target' => [],
    'href' => [],
    'src' => [],
    'style' => [],
    'name' => [],
    'title' => [],
    'checked' => [],
  ];

  return [
    'div' => $attr,
    'h1' => $attr,
    'form' => $attr,
    'br' =>[],
    'p' => $attr,
    'a' => $attr,
    'span' => $attr,
    'h2' => $attr,
    'table' => $attr,
    'tbody' => $attr,
    'tr' => $attr,
    'th' => $attr,
    'td' => $attr,
    'img' => $attr,
    'input' => $attr,
    'select' => $attr,
    'option' => $attr,
  ];
}

add_shortcode('yandexid', 'yandexid_shortcode');

function yandexid_shortcode() { 
  ob_start();
  
  $client_id = get_option('yandexid_app_client_id');
  
  if($client_id == false){
    return '';
  }
  
  if ( is_user_logged_in() ) {
    return '';
  }

  set_query_var('type_selection', get_option('yandexid_type_selection')); 
  set_query_var('button_view', get_option('suggest_button_view')); 
  set_query_var('parent_id', get_option('suggest_parent_id')); 
  set_query_var('button_theme', get_option('suggest_button_theme')); 
  set_query_var('button_size', get_option('suggest_button_size')); 
  set_query_var('button_border_radius', get_option('suggest_button_border_radius'));   
  
  set_query_var('color_bg_base', get_option('suggest_color_bg_base')); 
  set_query_var('color_bg_hovered', get_option('suggest_color_bg_hovered')); 
  set_query_var('color_border_base', get_option('suggest_color_border_base')); 
  set_query_var('color_border_hovered', get_option('suggest_color_border_hovered')); 
  set_query_var('border_thickness', get_option('suggest_border_thickness')); 
  
  set_query_var('tokenPageOrigin', get_site_url( null, '/yandexid/oauth', 'https' )); 
  set_query_var('redirect_uri', get_site_url( null, '/yandexid/oauth', 'https' )); 
  set_query_var('client_id', $client_id); 

  $template = trailingslashit( dirname( __FILE__ ) ) . 'templates/yandex-id-form.php';
  
  load_template( $template );
  
  $buffer = ob_get_contents();
  ob_end_clean();
  
  $buffer = str_replace(array("\r\n", "\r", "\n"), ' ', $buffer);
  
  return $buffer;
}

add_action( 'wp_head', 'add_custom_script_to_wp_head' );

function add_custom_script_to_wp_head() {
  $show = true;
  $client_id = get_option('yandexid_app_client_id');
  
  if($client_id == false){
    $show = false;
  }
  
  if ( is_user_logged_in() ) {
    $show = false;
  }

  if($show != false){   
    wp_enqueue_script( 'sdk-suggest-with-polyfills-latest', 'https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js');
  }
}

add_action( 'admin_menu', 'yandexid_menu', 25 );
 
function yandexid_menu(){
 
	add_menu_page(
		'Настройки Яндекс ID',
		'Яндекс ID',
		'manage_options',
		'yandexid',
		'yandexid_settings_page',
		plugin_dir_url( __FILE__ ).'img/ico.png', 
		20
	);
}
 
function yandexid_settings_page(){
 
  wp_enqueue_style('yandexid_settings_css', plugins_url('/css/style.css',__FILE__ ));
  wp_enqueue_script( 'yandexid_settings_js', plugins_url('/js/settings.js',__FILE__ ), [], 1, true ); 

	echo wp_kses('<div class="wrap"><h1>' . get_admin_page_title() . '</h1><form class="yandexid-form" method="post" action="options.php">', kses_tags());
 
  settings_errors( 'yandexid_settings_errors' );
  settings_fields( 'yandexid_settings' );
  echo wp_kses('<div class="text">Яндекс ID можно добавить на любой этап воронки и там, где, нужна авторизация.<br>Например в корзину и на страницу с комментариями.</div>', kses_tags());
  do_settings_sections( 'yandexid' ); 

  $text = 'Если у вас возникли сложности при работе с плагином «Яндекс ID», или авторизация не работает,<br>обратитесь, пожалуйста, в поддержку сервиса ';
  $text .= '<a href="https://yandex.ru/dev/id/doc/ru/feedback" target="_blank">API Яндекс ID</a>';
  echo wp_kses('<div class="text">' . $text . '</div><br>', kses_tags());

  $text = 'Начиная с версии 2.0, для дальнейшей работы модуля вам необходимо зарегистрировать приложение самостоятельно по ссылке <a href="https://oauth.yandex.ru/">https://oauth.yandex.ru/</a><br>';
  $text .= 'Указав данные вашего хоста (Suggest Hostname) и ссылку для редиректа (Redirect URI). Пример ссылки редиректа - https://domen.ru/yandexid/oauth<br>';
  $text .= 'В настройках модуля укажите полученные ClientID и Client secret';
  echo wp_kses('<div class="text">' . $text . '</div>', kses_tags());

  submit_button();

	echo wp_kses('</form></div>', kses_tags());
}


/**
 *  
 * Настройки плагина
 *  
*/
add_action( 'admin_init',  'yandexid_settings_fields' );

function yandexid_settings_fields(){

	register_setting('yandexid_settings', 'yandexid_app_client_id', 'yandexid_app_client_id_validate');	
	register_setting('yandexid_settings', 'yandexid_app_client_secret', 'sanitize_text_field');	
	register_setting('yandexid_settings', 'yandexid_role_new_user', 'yandexid_title_validate');		
	register_setting('yandexid_settings', 'yandexid_type_selection', 'yandexid_type_selection'); 
  register_setting('yandexid_settings', 'scope_login_default_phone_field', 'sanitize_text_field');
  register_setting('yandexid_settings', 'scope_login_birthday_field', 'sanitize_text_field');

  // Внешний вид виджета авторизации    
  register_setting('yandexid_settings', 'suggest_button_view', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_parent_id', 'sanitize_text_field');  
  register_setting('yandexid_settings', 'suggest_button_theme', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_button_size', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_button_border_radius', 'sanitize_text_field');  
  register_setting('yandexid_settings', 'suggest_color_bg_base', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_color_bg_hovered', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_color_border_base', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_color_border_hovered', 'sanitize_text_field');
  register_setting('yandexid_settings', 'suggest_border_thickness', 'sanitize_text_field');

	add_settings_section(
		'yandexid_settings_id',
		'',
		'',
		'yandexid'
	);
  
	add_settings_field(
		'yandexid_app_client_id',
		'Client ID',
		'yandexid_settings_app_client_id_field',
		'yandexid',
		'yandexid_settings_id',
		array( 
			'label_for' => 'yandexid_app_client_id',
			'class' => 'yandexid-yandexid_app_client_id',
			'name' => 'yandexid_app_client_id',
		)
	);
  
	add_settings_field(
		'yandexid_app_client_secret',
		'Client secret',
		'yandexid_settings_app_client_secret_field',
		'yandexid',
		'yandexid_settings_id',
		array( 
			'label_for' => 'yandexid_app_client_secret',
			'class' => 'yandexid-yandexid_app_client_secret',
			'name' => 'yandexid_app_client_secret',
		)
	);

	add_settings_field(
		'yandexid_type_selection',
		'Блок авторизации',
		'yandexid_settings_type_selection',
		'yandexid',
		'yandexid_settings_id',
		array( 
			'label_for' => 'yandexid_type_selection',
			'class' => 'yandexid-yandexid_type_selection',
			'name' => 'yandexid_type_selection',
		)
	);
  
	add_settings_field(
		'yandexid_role_new_user',
		'Применяемая роль при создании пользователя',
		'yandexid_settings_role_new_user',
		'yandexid',
		'yandexid_settings_id',
		array( 
			'label_for' => 'yandexid_role_new_user',
			'class' => 'yandexid-yandexid_role_new_user',
			'name' => 'yandexid_role_new_user',
		)
	);    
  
	add_settings_section(
		'yandexid_settings_scopes',
		'Запрашиваемый данные пользователя',
		'',
		'yandexid',
    [
      'before_section' => '<div class="settings-scopes">',
      'after_section' => '</div>'
    ]
	);
  
	add_settings_field(
		'scope_login_default_phone_field',
		'Выберите поле, куда сохранить номер телефона',
		'yandexid_settings_scope_login_default_phone_field',
		'yandexid',
		'yandexid_settings_scopes',
		array( 
			'label_for' => 'scope_login_default_phone_field',
			'class' => 'field-map yandexid-scope_login_default_phone_field',
			'name' => 'scope_login_default_phone_field',
		)
	);
  
	add_settings_field(
		'scope_login_birthday_field',
		'Выберите поле, куда сохранить дату рождения',
		'yandexid_settings_scope_login_birthday_field',
		'yandexid',
		'yandexid_settings_scopes',
		array( 
			'label_for' => 'scope_login_birthday_field',
			'class' => 'field-map yandexid-scope_login_birthday_field',
			'name' => 'scope_login_birthday_field',
		)
	);

  // Внешний вид виджета авторизации 
	add_settings_section(
		'yandexid_settings_suggest',
		'Настройки внешнего вида для кнопки авторизации',
		'',
		'yandexid',
    [
      'before_section' => '<div class="settings-suggest">',
      'after_section' => '</div>'
    ]
	);
  
	add_settings_field(
		'suggest_button_view',
		'Тип кнопки',
		'yandexid_settings_suggest_button_view',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_button_view',
			'class' => 'yandexid-suggest_button_view',
			'name' => 'suggest_button_view',
		)
	);
  
	add_settings_field(
		'suggest_parent_id',
		'Значение id атрибута контейнера, в который нужно встроить кнопку',
		'yandexid_settings_suggest_parent_id',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_parent_id',
			'class' => 'yandexid-suggest_parent_id',
			'name' => 'suggest_parent_id',
		)
	);
  
	add_settings_field(
		'suggest_button_border_radius',
		'Радиус скругления границ кнопки',
		'yandexid_settings_suggest_button_border_radius',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_button_border_radius',
			'class' => 'yandexid-suggest_button_border_radius',
			'name' => 'suggest_button_border_radius',
		)
	);  
  
	add_settings_field(
		'suggest_button_theme',
		'Тема кнопки',
		'yandexid_settings_suggest_button_theme',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_button_theme',
			'class' => 'yandexid-suggest_button_theme',
			'name' => 'suggest_button_theme',
		)
	); 
  
	add_settings_field(
		'suggest_button_size',
		'Размер кнопки',
		'yandexid_settings_suggest_button_size',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_button_size',
			'class' => 'yandexid-suggest_button_size',
			'name' => 'suggest_button_size',
		)
	);   

	add_settings_field(
		'suggest_color_bg_base',
		'Цвет фона подложки',
		'yandexid_settings_suggest_color_bg_base',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_color_bg_base',
			'class' => 'icon yandexid-suggest_color_bg_base',
			'name' => 'suggest_color_bg_base',
		)
	); 
  
	add_settings_field(
		'suggest_color_bg_hovered',
		'Цвет фона подложки под курсором',
		'yandexid_settings_suggest_color_bg_hovered',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_color_bg_hovered',
			'class' => 'icon yandexid-suggest_color_bg_hovered',
			'name' => 'suggest_color_bg_hovered',
		)
	); 
  
	add_settings_field(
		'suggest_color_border_base',
		'Цвет обводки',
		'yandexid_settings_suggest_color_border_base',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_color_border_base',
			'class' => 'icon yandexid-suggest_color_border_base',
			'name' => 'suggest_color_border_base',
		)
	); 
  
	add_settings_field(
		'suggest_color_border_hovered',
		'Цвет обводки под курсором',
		'yandexid_settings_suggest_color_border_hovered',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_color_border_hovered',
			'class' => 'icon yandexid-suggest_color_border_hovered',
			'name' => 'suggest_color_border_hovered',
		)
	);
  
	add_settings_field(
		'suggest_border_thickness',
		'Толщина обводки',
		'yandexid_settings_suggest_border_thickness',
		'yandexid',
		'yandexid_settings_suggest',
		array( 
			'label_for' => 'suggest_border_thickness',
			'class' => 'icon yandexid-suggest_border_thickness',
			'name' => 'suggest_border_thickness',
		)
	);

}
 
function yandexid_settings_app_client_id_field( $args ){
  $value = get_option( $args[ 'name' ] );

  echo wp_kses('<input type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_app_client_secret_field( $args ){
  $value = get_option( $args[ 'name' ] );

  echo wp_kses('<input type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_scope_login_default_phone_field( $args ){
	$value = get_option( $args[ 'name' ] );  
  $values = get_user_fields();

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'">';
  $output .= '<option selected value="no">Не выбрано</option>';
  
  foreach($values as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v.'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v.'</option>';
    }    
  }
  
  $output .= '</select>';

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_scope_login_birthday_field( $args ){
	$value = get_option( $args[ 'name' ] );  
  $values = get_user_fields();

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'">';
  $output .= '<option selected value="no">Не выбрано</option>';
  
  foreach($values as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v.'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v.'</option>';
    }    
  }
  
  $output .= '</select>';

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_type_selection( $args ){
	$value = get_option( $args[ 'name' ] );

  if($value == false){
    $value = 'widget';
  }
  
  $values = [
    'widget' => 'Виджет «Мгновенный вход»',
    'button' => 'Кнопка авторизации'
  ];

  $txt = '<div>';
  $txt .= 'Виджет «Мгновенный вход» -  <a target="_blank" href="'.plugins_url( 'img/widget.jpg', __FILE__ ).'">Пример</a><br>';
  $txt .= 'Кнопка авторизации -  <a target="_blank" href="'.plugins_url( 'img/button.jpg', __FILE__ ).'">Пример</a><br>';
  $txt .= '</div>';

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" >';
  
  foreach($values as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v.'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v.'</option>';
    }    
  }
  
  $output .= '</select>'.$txt;

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_role_new_user( $args ){
	$value = get_option( $args[ 'name' ] );
  $roles = get_editable_roles();
  
  if($value == false){
    $value = 'subscriber';
  }

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" >';
  
  foreach($roles as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v['name'].'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v['name'].'</option>';
    }    
  }
  
  $output .= '</select>';

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_suggest_button_view( $args ){
	$value = get_option( $args[ 'name' ] );
  
  $values = [
    'main'       => 'Основная версия',
    'additional' => 'Основная версия с контрастной обводкой',
    'icon'       => 'Кнопка-иконка с логотипом Яндекса',
    'iconGrey'   => 'Серая кнопка-иконка с логотипом Яндекса',
    'iconBorder' => 'Белая кнопка-иконка с логотипом Яндекса и черной обводкой по периметру'
  ];

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" >';

  $txt = '<div class="btn-example">';
  $txt .= '<img class="img main-light" src="'.plugins_url( 'img/btn/main-light.jpg', __FILE__ ).'">';
  $txt .= '<img class="img main-dark" src="'.plugins_url( 'img/btn/main-dark.jpg', __FILE__ ).'">';
  $txt .= '<img class="img additional-light" src="'.plugins_url( 'img/btn/additional-light.jpg', __FILE__ ).'">';
  $txt .= '<img class="img additional-dark" src="'.plugins_url( 'img/btn/additional-dark.jpg', __FILE__ ).'">';
  $txt .= '<img class="img icon" src="'.plugins_url( 'img/btn/icon.jpg', __FILE__ ).'">';
  $txt .= '<img class="img icongrey" src="'.plugins_url( 'img/btn/iconGrey.jpg', __FILE__ ).'">';
  $txt .= '<img class="img iconborder" src="'.plugins_url( 'img/btn/iconBorder.jpg', __FILE__ ).'">';
  $txt .= '</div>';  
  
  foreach($values as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v.'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v.'</option>';
    }    
  }
  
  $output .= '</select>'.$txt;

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_suggest_parent_id( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_suggest_button_border_radius( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input type="number" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_suggest_button_theme( $args ){
	$value = get_option( $args[ 'name' ] );
  
  $values = [
    'light'  => 'Светлая тема',
    'dark'   => 'Темная тема'
  ];

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'">';
  
  foreach($values as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v.'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v.'</option>';
    }    
  }
  
  $output .= '</select>';

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_suggest_button_size( $args ){
	$value = get_option( $args[ 'name' ] );
  
  $values = [
    'xs'  => 'xs',
    's'   => 's',
    'm'   => 'm',
    'l'   => 'l',
    'xl'  => 'xl',
    'xxl' => 'xxl'
  ];

  $output = '<select id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'">';
  
  foreach($values as $k => $v){
    if($k == $value){
      $output .= '<option selected value="'.$k.'">'.$v.'</option>';
    }else{
      $output .= '<option value="'.$k.'">'.$v.'</option>';
    }    
  }
  
  $output .= '</select>';

  echo wp_kses($output, kses_tags());
}

function yandexid_settings_suggest_color_bg_base( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input title="Пример: #FFFFFF" type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_suggest_color_bg_hovered( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input title="Пример: #FFFFFF" type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_suggest_color_border_base( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input title="Пример: #FFFFFF" type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_suggest_color_border_hovered( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input title="Пример: #FFFFFF" type="text" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}

function yandexid_settings_suggest_border_thickness( $args ){
	$value = get_option( $args[ 'name' ] );

  echo wp_kses('<input type="number" id="'.$args[ 'name' ].'" name="'.$args[ 'name' ].'" value="'.$value.'" />', kses_tags());
}


/**
 *  
 * Валидация полей настроек 
 *  
*/
function yandexid_app_client_id_validate( $input ) {
 	
	$input = trim($input);
 
	if( $input == '' ) {
		add_settings_error(
			'yandexid_settings_errors',
			'yandexid_app_client_id',
			'Не указан "Client ID"',
			'error' // success, warning, info
		);

		$input = get_option( 'yandexid_app_client_id' );
	}
 
	return $input; 
}


add_action( 'admin_notices', 'yandexid_settings_notice' );
 
function yandexid_settings_notice() {

	if(
		isset( $_GET[ 'page' ] ) && 
		$_GET[ 'page' ] == 'yandexid' && 
		isset( $_GET[ 'settings-updated' ] ) && 
		true == $_GET[ 'settings-updated' ]
	) {

		echo wp_kses('<div class="notice notice-success is-dismissible"><p>Настройки сохранены!</p></div>', kses_tags());
	} 
}

add_action( 'init', 'yandexid_add_cors_http_header' );

function yandexid_add_cors_http_header(){
  header("Content-Security-Policy: frame-ancestors 'self' https://*.yandex.ru");
}

add_action( 'init', 'yandexid_routs' );

function yandexid_routs(){

  // Разрешаем переменные
  add_filter( 'query_vars', function( $vars ){
    $vars[] = 'pagename';
    $vars[] = 'action';
    $vars[] = 'access_token';
    return $vars;
  } );   
  
  add_rewrite_rule(
    '^yandexid/([a-zA-Z-]+)/?',
    'index.php?pagename=yandexid&action=$matches[1]',
    'top'
  );
}

add_filter( 'template_include', 'yandexid_template' );

function yandexid_template($template) {
  global $wp_query;
  
  $page = get_query_var('pagename');
  $action = get_query_var('action');

  if($page == 'yandexid' && $action == 'oauth'){
    wp_enqueue_script( 'sdk-suggest-token-with-polyfills-latest', 'https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-token-with-polyfills-latest.js');
    set_query_var('originUrl', get_site_url( null, '/yandexid/authorization', 'https' )); 
    status_header(200);
    $wp_query->is_404 = false;    
    $template = trailingslashit( dirname( __FILE__ ) ) . 'templates/oauth.php';
  }
  
  if($page == 'yandexid' && $action == 'authorization'){
    status_header(200);
    $wp_query->is_404 = false;    
    $template = trailingslashit( dirname( __FILE__ ) ) . 'templates/oauth.php';
  }
  
  return $template;
}

// Для авторизованных
add_action( 'wp_ajax_ajax_authorization_user', 'yandexid_authorization_user' );
// Для не авторизованных
add_action( 'wp_ajax_nopriv_ajax_authorization_user', 'yandexid_authorization_user' );

function yandexid_authorization_user($email = null){
  global $wpdb;
  
  $client_id = get_option('yandexid_app_client_id');

  if(isset($_GET['access_token'])){
    $YandexApi = new YandexApi();
    $user_data = $YandexApi->getUserInfo(sanitize_text_field($_GET['access_token'])); 

    if($client_id != $user_data->client_id){
      echo json_encode([
        'error' => true,
        'message' => wp_kses('Невозможно авторизовать пользователя.')
        ]);
        
      die(); 
    }

    if(isset($user_data->default_email)){
      $email = $user_data->default_email;
    }else{
      $email = null;
    }    
  }  

  if(is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
      'error' => true,
      'message' => wp_kses('Невозможно авторизовать пользователя. Войдите в аккаунт с email', kses_tags())
      ]);
      
    die();  
  }

  $table = $wpdb->prefix . "users";
  
  $sql = "SELECT ID FROM $table WHERE user_email='$email'";  
  $user = $wpdb->get_row($sql); 
  
  if(!is_null($user)){
    wp_set_auth_cookie($user->ID);
  }else{
    yandexid_create_user($user_data); 
  }
  
  if(isset($_GET['access_token'])){
    echo json_encode([
      'error' => false
      ]);
      
    die();   
  }
} 

function yandexid_create_user($user_data){
  $role = get_option('yandexid_role_new_user');
  
  if($role == false){
    return false;
  }

  $email = $user_data->default_email;

  $userdata = [
    'user_login'  => $email,
    'user_pass'   => wp_generate_password( 8, false ),
    'user_email'  => $email,
    'role'        => $role,
  ];
  
  $phone_field = get_option('scope_login_default_phone_field');
  $birthday_field = get_option('scope_login_birthday_field');

  if(isset($user_data->first_name)){
    $userdata['first_name'] = $user_data->first_name;
  }
  
  if(isset($user_data->last_name)){
    $userdata['last_name'] = $user_data->last_name;
  }

  if($phone_field != 'no'){
    $userdata[$phone_field] = isset($user_data->default_phone->number) ? $user_data->default_phone->number : '';
  }
  
  if($birthday_field != 'no'){
    $userdata[$birthday_field] = isset($user_data->birthday) ? $user_data->birthday : '';
  }

  $user_id = wp_insert_user($userdata);
  
	if(!is_wp_error($user_id)){
		wp_set_auth_cookie($user_id);
    wp_send_new_user_notifications($user_id);
	}else {
    $user_id->get_error_message();
		return false;
	}
}

function get_user_fields(){
  $user_id = get_current_user_id(); 
  $user_meta = get_user_meta($user_id);  

  $field_list = [];

  $exception = [
    'rich_editing', 
    'syntax_highlighting', 
    'admin_color', 
    'use_ssl', 
    'show_admin_bar_front', 
    'locale', 
    'wp_capabilities', 
    'wp_user_level', 
    'dismissed_wp_pointers', 
    'show_welcome_panel', 
    'wp_dashboard_quick_press_last_post_id', 
    'community-events-location', 
    'closedpostboxes_dashboard', 
    'metaboxhidden_dashboard', 
    'wp_persisted_preferences', 
    'session_tokens', 
    'comment_shortcuts', 
    'nickname', 
    'first_name', 
    'last_name', 
  ];
  
  foreach($user_meta as $field => $meta){
    if(!in_array($field, $exception)){
      $field_list[$field] = $field;
    }
  }

  return $field_list;
}  
