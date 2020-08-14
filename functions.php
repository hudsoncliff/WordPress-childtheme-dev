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

        if( $edit_user_role == 'subscriber' ) {

            $bs_customer_status = get_user_meta( $user_id, 'bs-customer-status', true );

            if( !empty( $bs_customer_status ) ) {

    
                if( array_key_exists( 'address', $bs_customer_status ) ) {
    
                    $user_address = $bs_customer_status['address'];
                
                }
    
                if( array_key_exists( 'tel', $bs_customer_status ) ) {
    
                    $user_tel = $bs_customer_status['tel'];
                
                }

                if( array_key_exists( 'agreement', $bs_customer_status ) ) {

                    $user_agreement = $bs_customer_status['agreement'];
                    $checked_agreement = ( $user_agreement ) ? 'checked="checked"' : null;

                }

            }
            
            ?>
            <h2>予約者情報</h2>
            <p>店舗管理者に通知されるユーザーの情報</p>
            <table class="form-table">
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
                <tr>
                    <th>
                        <?php bloginfo( 'name' ); ?>利用規約への同意
                    </th>
                    <td>
                        <input type="checkbox" name="bs-customer-status[agreement]" id="bs-customer-status[agreement]" <?php echo $checked_agreement; ?>>
                    </td>
                </tr>
            </table>

            <?php

        }

    } 
    
}

?>