<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'yandexid_app_client_id' );
delete_option( 'yandexid_app_client_secret' );
delete_option( 'yandexid_role_new_user' );
delete_option( 'yandexid_type_selection' );
delete_option( 'scope_login_default_phone_field' );
delete_option( 'scope_login_birthday_field' );
delete_option( 'suggest_button_view' );
delete_option( 'suggest_parent_id' );
delete_option( 'suggest_button_theme' );
delete_option( 'suggest_button_size' );
delete_option( 'suggest_button_border_radius' );
delete_option( 'suggest_color_bg_base' );
delete_option( 'suggest_color_bg_hovered' );
delete_option( 'suggest_color_border_base' );
delete_option( 'suggest_color_border_hovered' );
delete_option( 'suggest_border_thickness' );