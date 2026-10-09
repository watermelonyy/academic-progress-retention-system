<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Password</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        body {
            min-height: 100vh;
            background: #f4f7f5;
            font-family: Arial, sans-serif;
        }

        .password-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .password-card {
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .password-header {
            background: #198754;
            color: #ffffff;
            padding: 25px 30px;
        }

        .password-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .password-header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .password-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.15rem rgba(25, 135, 84, 0.12);
        }

        .toggle-password {
            background: #ffffff;
            border-color: #ced4da;
            color: #6c757d;
        }

        .toggle-password:hover {
            background: #f1f3f5;
            color: #198754;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            font-size: 13px;
            margin-top: 6px;
        }

        .password-rules {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px 18px;
            margin-top: 22px;
            margin-bottom: 22px;
        }

        .password-rules-title {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
        }

        .password-rules ul {
            margin: 0;
            padding-left: 20px;
            color: #6c757d;
            font-size: 14px;
            line-height: 1.7;
        }

        .change-button {
            background: #198754;
            border-color: #198754;
            border-radius: 9px;
            padding: 11px;
            font-weight: 600;
        }

        .change-button:hover {
            background: #157347;
            border-color: #146c43;
        }

        .back-button {
            border-radius: 9px;
            padding: 9px 20px;
        }

        .alert {
            border-radius: 10px;
            font-size: 14px;
        }

        .success-alert {
            border-left: 4px solid #198754;
        }

        @media (max-width: 576px) {

            .password-wrapper {
                padding: 20px 12px;
            }

            .password-body {
                padding: 24px 20px;
            }

            .password-header {
                padding: 22px 20px;
            }

        }

    </style>

</head>


<body>

<div class="password-wrapper">

    <div class="password-card">

        {{-- HEADER --}}
        <div class="password-header">

            <h4>
                <i class="fa-solid fa-lock me-2"></i>
                Change Password
            </h4>

            <p>
                Update your account password securely.
            </p>

        </div>


        <div class="password-body">

            {{-- SUCCESS MESSAGE --}}
            @if(session('success'))

                <div class="alert alert-success success-alert"
                     role="alert">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    {{ session('success') }}

                </div>

            @endif


            {{-- GENERAL ERROR MESSAGE --}}
            @if(session('error'))

                <div class="alert alert-danger"
                     role="alert">

                    <i class="fa-solid fa-circle-exclamation me-2"></i>

                    {{ session('error') }}

                </div>

            @endif


            <form action="{{ route('change.password.update') }}"
                  method="POST"
                  id="changePasswordForm">

                @csrf


                {{-- CURRENT PASSWORD --}}
                <div class="mb-4">

                    <label for="current_password"
                           class="form-label">

                        Current Password

                    </label>

                    <div class="input-group">

                        <input type="password"
                               name="current_password"
                               id="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               autocomplete="current-password"
                               required>

                        <button type="button"
                                class="btn toggle-password"
                                data-target="current_password"
                                aria-label="Show current password">

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>


                    {{-- CURRENT PASSWORD ERROR --}}
                    @error('current_password')

                        <div class="invalid-feedback">

                            <i class="fa-solid fa-circle-exclamation me-1"></i>

                            {{ $message }}

                        </div>

                    @enderror

                </div>


                {{-- NEW PASSWORD --}}
                <div class="mb-4">

                    <label for="new_password"
                           class="form-label">

                        New Password

                    </label>

                    <div class="input-group">

                        <input type="password"
                               name="new_password"
                               id="new_password"
                               class="form-control @error('new_password') is-invalid @enderror"
                               autocomplete="new-password"
                               minlength="16"
                               required>

                        <button type="button"
                                class="btn toggle-password"
                                data-target="new_password"
                                aria-label="Show new password">

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>


                    @error('new_password')

                        <div class="invalid-feedback">

                            <i class="fa-solid fa-circle-exclamation me-1"></i>

                            {{ $message }}

                        </div>

                    @enderror

                </div>


                {{-- CONFIRM NEW PASSWORD --}}
                <div class="mb-3">

                    <label for="new_password_confirmation"
                           class="form-label">

                        Confirm New Password

                    </label>

                    <div class="input-group">

                        <input type="password"
                               name="new_password_confirmation"
                               id="new_password_confirmation"
                               class="form-control @error('new_password_confirmation') is-invalid @enderror"
                               autocomplete="new-password"
                               minlength="16"
                               required>

                        <button type="button"
                                class="btn toggle-password"
                                data-target="new_password_confirmation"
                                aria-label="Show confirmation password">

                            <i class="fa-solid fa-eye"></i>

                        </button>

                    </div>


                    @error('new_password_confirmation')

                        <div class="invalid-feedback">

                            <i class="fa-solid fa-circle-exclamation me-1"></i>

                            {{ $message }}

                        </div>

                    @enderror

                </div>


                {{-- PASSWORD REQUIREMENTS --}}
                <div class="password-rules">

                    <div class="password-rules-title">

                        <i class="fa-solid fa-shield-halved me-1"></i>
                        Password Requirements

                    </div>

                    <ul>

                        <li>Minimum 16 characters</li>
                        <li>At least one uppercase letter</li>
                        <li>At least one lowercase letter</li>
                        <li>At least one number</li>
                        <li>At least one special character</li>

                    </ul>

                </div>


                {{-- CHANGE PASSWORD BUTTON --}}
                <button type="submit"
                        class="btn btn-success change-button w-100">

                    <i class="fa-solid fa-key me-2"></i>

                    Change Password

                </button>

            </form>


            {{-- BACK BUTTON --}}
            <div class="text-center mt-4">

                <a href="{{ route('login') }}"
                   class="btn btn-outline-secondary back-button">

                    <i class="fa-solid fa-arrow-left me-1"></i>

                    Back

                </a>

            </div>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.toggle-password').forEach(function(button) {

    button.addEventListener('click', function() {

        const targetId = this.getAttribute('data-target');

        const passwordInput = document.getElementById(targetId);

        const icon = this.querySelector('i');


        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');

            this.setAttribute(
                'aria-label',
                'Hide password'
            );

        } else {

            passwordInput.type = 'password';

            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');

            this.setAttribute(
                'aria-label',
                'Show password'
            );

        }

    });

});


/*
|--------------------------------------------------------------------------
| NEW PASSWORD AND CONFIRM PASSWORD MATCH VALIDATION
|--------------------------------------------------------------------------
*/

const changePasswordForm =
    document.getElementById('changePasswordForm');

const newPassword =
    document.getElementById('new_password');

const confirmPassword =
    document.getElementById('new_password_confirmation');


function checkPasswordMatch() {

    if (confirmPassword.value === '') {

        confirmPassword.setCustomValidity('');

        return;

    }


    if (newPassword.value !== confirmPassword.value) {

        confirmPassword.setCustomValidity(
            'The new password and confirmation password do not match.'
        );

    } else {

        confirmPassword.setCustomValidity('');

    }

}


newPassword.addEventListener(
    'input',
    checkPasswordMatch
);

confirmPassword.addEventListener(
    'input',
    checkPasswordMatch
);


changePasswordForm.addEventListener(
    'submit',
    function(event) {

        checkPasswordMatch();


        if (!changePasswordForm.checkValidity()) {

            event.preventDefault();

            changePasswordForm.reportValidity();

        }

    }
);


/*
|--------------------------------------------------------------------------
| REMOVE CLIENT-SIDE MATCH WARNING WHEN USER EDITS THE FIELD
|--------------------------------------------------------------------------
*/

confirmPassword.addEventListener(
    'input',
    function() {

        if (newPassword.value === confirmPassword.value) {

            confirmPassword.setCustomValidity('');

        }

    }
);

</script>

</body>
</html>