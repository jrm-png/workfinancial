<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WFP System | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #105346, #4ca1af);
        }

        .container {
            display: flex;
            height: 85vh;
            width: 80vw;
            max-width: 1000px;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            box-shadow: 0px 20px 40px rgba(0, 0, 0, 0.3);
        }

        .info-section {
            width: 45%;
            background: #0a5346;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
        }

        .info-section img {
            width: 120px;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        }

        .info-section h2 {
            font-size: 22px;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .info-section p {
            font-size: 14px;
            opacity: 0.8;
        }

        .form-container {
            width: 55%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .card {
            width: 100%;
            max-width: 360px;
        }

        .header {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #1e293b;
        }

        .subheader {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 30px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #475569;
            font-size: 13px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4ca1af;
            box-shadow: 0 0 0 3px rgba(76, 161, 175, 0.1);
        }

        /* ⭐ BAGONG STYLES PARA SA EYE WRAPPER AT ICON */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper .form-control {
            padding-right: 45px; /* Para hindi matakpan ng icon ang mahabang password */
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            color: #64748b;
            cursor: pointer;
            transition: color 0.2s;
            z-index: 10;
        }

        .toggle-password:hover {
            color: #4ca1af;
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #0a5346;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: #146c5b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 83, 70, 0.3);
        }

        .alert {
            padding: 12px;
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .container { flex-direction: column; width: 90vw; height: auto; margin: 20px; }
            .info-section, .form-container { width: 100%; }
            .info-section { padding: 30px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="info-section">
            <img src="{{ asset('images/lldalogo.png') }}" alt="LLDA Logo">
            <h2>LAGUNA LAKE DEVELOPMENT AUTHORITY</h2>
            <div style="width: 50px; height: 3px; background: #4ca1af; margin: 15px auto;"></div>
            <p>Work and Financial Plan</p>
        </div>

        <div class="form-container">
            <div class="card">
                <div class="header">Welcome Back</div>
                <div class="subheader">Please enter your details to sign in.</div>
                
                @if ($errors->any())
                    <div class="alert">
                        <strong>Oops!</strong> Please check your credentials.
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-control" placeholder="name@llda.gov.ph">
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between;">
                            <label for="password" class="form-label">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" style="font-size: 11px; text-decoration: none; color: #4ca1af;">Forgot?</a>
                            @endif
                        </div>
                        
                        <div class="password-wrapper">
                            <input id="password" type="password" name="password" required class="form-control" placeholder="••••••••">
                            <i class="fa-solid fa-eye toggle-password" id="eyeIcon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        eyeIcon.addEventListener('click', function () {
            // I-check kung kasalukuyang password o text ang uri ng input
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Palitan ang icon sa eye-slash (may guhit)
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                // Ibalik sa normal na eye icon
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>