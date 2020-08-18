const bsForm = document.getElementById('bs-form'),
nonce = document.getElementById('nonce'),
ajaxUrl = loginForm.ajax_url,
submitBtn = document.getElementById('bs-submit'),
userLogin = document.getElementById('user_login');

if( bsForm ) {
    
    console.log('login form');

    submitBtn.addEventListener('click', () => {

        let data = new FormData();
        data.append('action', 'bs_user_register');
        data.append('nonce', nonce.value);
        data.append('user_login', userLogin.value);

        fetch(
            ajaxUrl,
            {method: 'POST', body: data }
        )
        .then(res => res.json())
        .then(response => {

            console.dir(response);

            if(response.status == 'success') {
                alert('ユーザーの登録受付が完了しました');
            } else {
                alert('ユーザーの登録ができませんでした。');
            }

        });

        event.stopPropagation();
        event.preventDefault();

    });
    
}