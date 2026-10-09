<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-header text-center">

                    <h3 class="mb-0">
                        Login
                    </h3>

                </div>


                <div class="card-body">


                    {{-- SUCCESS MESSAGE --}}

                    @if(session('success'))

                        <div class="alert alert-success">

                            {{ session('success') }}

                        </div>

                    @endif


                    {{-- ERROR MESSAGE --}}

                    @if(session('error'))

                        <div class="alert alert-danger">

                            {{ session('error') }}

                        </div>

                    @endif


                    {{-- VALIDATION ERRORS --}}

                    @if($errors->any())

                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- LOGIN FORM --}}

                    <form
                        method="POST"
                        action="{{ route('login.process') }}"
                    >

                        @csrf


                        {{-- USERNAME --}}

                        <div class="mb-3">

                            <label
                                for="username"
                                class="form-label"
                            >
                                Username
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                value="{{ old('username') }}"
                                autocomplete="username"
                                required
                                autofocus
                            >

                        </div>


                        {{-- PASSWORD --}}

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="current-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    id="togglePassword"
                                    aria-label="Show password"
                                >
                                    <i class="fa fa-eye" id="passwordIcon"></i>
                                </button>

                            </div>

                        </div>


                        {{-- LOGIN BUTTON --}}

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>


                        {{-- CHANGE PASSWORD --}}

                        <div class="text-center mt-3">

                            <a href="{{ route('change.password') }}">
                                Change Password
                            </a>

                        </div>

                    </form>

                </div>

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

const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');
const passwordIcon = document.getElementById('passwordIcon');


togglePassword.addEventListener('click', function () {

    if (password.type === 'password') {

        password.type = 'text';

        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');

        togglePassword.setAttribute(
            'aria-label',
            'Hide password'
        );

    } else {

        password.type = 'password';

        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');

        togglePassword.setAttribute(
            'aria-label',
            'Show password'
        );
    }

});

</script>

</body>

</html>