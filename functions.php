<?php

//main scripts
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'main-style', get_stylesheet_directory_uri() . '/style.css', ['parent-style'], date('YmdGis', filemtime(dirname(__FILE__) . '/style.css') ) );
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