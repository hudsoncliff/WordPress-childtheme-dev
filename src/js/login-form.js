const bsForm = document.getElementById('bs-form'),
nonce = document.getElementById('nonce'),
ajaxUrl = loginForm.ajax_url,
submitBtn = document.getElementById('bs-submit'),
userLogin = document.getElementById('user_login'),
userEmail = document.getElementById('user_email'),
firstName = document.getElementById('first_name'),
lastName = document.getElementById('last_name'),
userGender = document.getElementById('user_gender'),
userAddress = document.getElementById('user_address'),
userTel = document.getElementById('user_tel'),
userPass = document.getElementById('user_pass');

if( bsForm ) {
    
    console.log('login form');

    submitBtn.addEventListener('click', () => {

        let data = new FormData();
        data.append('action', 'bs_user_register');
        data.append('nonce', nonce.value);
        data.append('user_login', userLogin.value);
        data.append('user_email', userEmail.value);
        data.append('first_name', firstName.value);
        data.append('last_name', lastName.value);
        data.append('user_gender', userGender.value);
        data.append('user_address', userAddress.value);
        data.append('user_tel', userTel.value);
        data.append('user_pass', userPass.value);

        fetch(
            ajaxUrl,
            {method: 'POST', body: data }
        )
        .then(res => res.json())
        .then(response => {

            console.dir(response);
            alert(response.message);

        });

        event.stopPropagation();
        event.preventDefault();

    });
    
}