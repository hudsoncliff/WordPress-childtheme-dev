<?php

//main scripts
add_action('wp_enqueue_scripts', function(){

    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'main-style', get_stylesheet_directory_uri() . '/style.css', ['parent-style'], date('YmdGis', filemtime(dirname(__FILE__) . '/style.css') ) );

    wp_register_script( 'login-form', get_theme_file_uri( 'assets/js/login-form.js' ), [], date('YmdGis', filemtime(dirname(__FILE__) . '/assets/js/login-form.js' ) ), true );

    wp_localize_script( 'login-form', 'loginForm', [
        
        'ajax_url' => admin_url( 'admin-ajax.php' )

    ] );

});

//会員登録用のフォーム
add_shortcode( 'member-register', function(){

    wp_enqueue_script('login-form');

    ?>

    <div class="form_wrap">
        <form id="bs-form">
            <?php wp_nonce_field('bs_user_register','nonce'); ?>
            <input type="text" name="user_login" id="user_login" class="input_field" placeholder="ユーザーネーム（半角小文字の英語と数字）" required>
            <input type="email" name="user_email" id="user_email" class="input_field" placeholder="メールアドレス" required>
            <input type="text" name="last_name" id="last_name" class="input_field" placeholder="姓" required>
            <input type="text" name="first_name" id="first_name" class="input_field" placeholder="名" required>
            <p>性別</p>
            <label>
                男性
                <input type="radio" name="user_gender" id="user_gender" value="男性">
            </label>
            <label>
                女性
                <input type="radio" name="user_gender" id="user_gender" value="女性">
            </label>
            <input type="text" name="user_address" id="user_address" class="input_field" placeholder="住所" required>
            <input type="text" name="user_tel" id="user_tel" class="input_field" placeholder="携帯番号" required>
            <div class="submit">
                <input type="submit" value="会員登録" id="bs-submit">
            </div>
        </form>
    </div>

    <?php

});

//ユーザー登録AJAX
add_action( 'wp_ajax_nopriv_bs_user_register', function() {

    if( isset( $_POST['nonce'] ) ) $wp_nonce = $_POST['nonce'];
    $verify_nonce = wp_verify_nonce( $wp_nonce, 'bs_user_register' );

    if( !$verify_nonce ) {

        $json_response = [
            'status' => 'error',
            'message' => 'nonce error',
        ];

        wp_send_json_error( $json_response );
        return;

    }

    if( isset( $_POST['user_login'] ) ) $data_list['user_login'] = $_POST['user_login'];

    $json_response = [
        'status' => 'success',
        'message' => 'ユーザー登録処理',
        'data_list' => $data_list,
    ];

    wp_send_json( $json_response );

    die();

});

//ユーザーのオリジナル項目の追加
add_action('show_user_profile', 'bs_user_profile_fields', 10, 1);
add_action('edit_user_profile', 'bs_user_profile_fields', 10, 1);
add_action('user_new_form', 'bs_user_profile_fields', 10, 1);

function bs_user_profile_fields( $user ) {

    $user_id = $user->ID;
    $edit_user_role = $user->roles[0]; //編集中のユーザーの権限

    if( current_user_can( 'administrator' ) ) { //スーパー管理者の時だけは変更するためのフォームを出力。それ以下は結果だけを見せる

        $bs_customer_status = get_user_meta( $user_id, 'bs-customer-status', true );

        if( !empty( $bs_customer_status ) ) {

            if( array_key_exists( 'birthday', $bs_customer_status ) ) {

                $user_birthday = $bs_customer_status['birthday'];

            }

            if( array_key_exists( 'gender', $bs_customer_status ) ) {

                $user_gender = $bs_customer_status['gender'];
                $checked_male = ( $user_gender == 'male' ) ? ' checked="checked"' : null;
                $checked_female = ( $user_gender == 'female' ) ? ' checked="checked"' : null;

                $gender_select = '<label>男性
                <input type="radio" name="bs-customer-status[gender]" id="bs-customer-status[gender]" value="male" ' . $checked_male . '></label>
                <label>女性
                <input type="radio" name="bs-customer-status[gender]" id-"bs-customer-status[gender]" value="female" ' . $checked_female . '></label>';

            }

            if( array_key_exists( 'address', $bs_customer_status ) ) {

                $user_address = $bs_customer_status['address'];
            
            }

            if( array_key_exists( 'tel', $bs_customer_status ) ) {

                $user_tel = $bs_customer_status['tel'];
            
            }

        }
        
        ?>
        <h2>予約者情報</h2>
        <p>袴コンテストように追加した独自項目</p>
        <table class="form-table">
            <tr>
                <th>
                    <label for="bs-customer-status[birthday]">生年月日</label>
                </th>
                <td>
                    <input type="date" name="bs-customer-status[birthday]" id="bs-customer-status[birthday]" value="<?php echo $user_birthday; ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="bs-customer-status[gender]">性別</label>
                </th>
                <td>
                    <?php echo $gender_select; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="bs-customer-status[address]">住所</label>
                </th>
                <td>
                    <input type="text" name="bs-customer-status[address]" id="bs-customer-status[address]" value="<?php echo $user_address; ?>">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="bs-customer-status[tel]">電話番号</label>
                </th>
                <td>
                    <input type="tel" name="bs-customer-status[tel]" id="bs-customer-status[tel]" value="<?php echo $user_tel; ?>">
                </td>
            </tr>
        </table>

        <?php

    } 
    
}

//ユーザーオリジナル項目更新時のデータ保存
add_action('personal_options_update', 'bs_user_profile_fields_update', 10, 1);
add_action('edit_user_profile_update', 'bs_user_profile_fields_update', 10, 1);
add_action('user_register', 'bs_user_profile_fields_update', 10, 1);

function bs_user_profile_fields_update( $userId ) {

    $user_data = get_userdata( $userId );
    $user_role = $user_data->roles[0];

    if (!current_user_can('administrator')) return;

    $customer_status = $_REQUEST['bs-customer-status'];

    foreach( $customer_status as $key => $value ) {

        $sanitized_data[$key] = $value;

    }
    
    update_user_meta($userId, 'bs-customer-status', $sanitized_data);

}

?>