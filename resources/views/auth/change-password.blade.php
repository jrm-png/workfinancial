<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WFP System | Change Password</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

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
            font-weight: 700;
        }

        .info-section p {
            font-size: 14px;
            opacity: 0.8;
            margin: 0;
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
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .subheader {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 25px;
            line-height: 1.4;
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
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #4ca1af;
            box-shadow: 0 0 0 3px rgba(76, 161, 175, 0.1);
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
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary:hover {
            background: #146c5b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 83, 70, 0.3);
        }

        .alert-info {
            padding: 12px 15px;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            line-height: 1.4;
        }

        .error-msg {
            color: #b91c1c;
            font-size: 12px;
            margin-top: 6px;
            display: block;
            font-weight: 500;
        }

        /* --- MOBILE VIEW RESPONSIVENESS --- */
        @media (max-width: 900px) {
            .container {
                width: 90vw;
                max-width: 450px;
                height: auto;
                max-height: 92vh;
                flex-direction: column;
                border-radius: 16px;
            }
            
            .info-section {
                width: 100%;
                padding: 30px 20px;
                box-sizing: border-box;
            }

            .info-section img {
                width: 80px;
                margin-bottom: 10px;
            }

            .info-section h2 {
                font-size: 16px;
            }

            .info-section p {
                font-size: 12px;
            }

            .info-section div {
                margin: 10px auto !important;
            }

            .form-container {
                width: 100%;
                padding: 30px 24px;
                box-sizing: border-box;
            }

            .header {
                font-size: 22px;
                text-align: center;
            }

            .subheader {
                font-size: 13px;
                text-align: center;
                margin-bottom: 20px;
            }
            
            .btn-primary {
                padding: 12px;
                font-size: 15px;
            }
        }

        @media (max-height: 660px) {
            body {
                height: auto;
                padding: 20px 0;
            }
            .container {
                height: auto;
            }
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
                <div class="header">Security Update</div>
                <div class="subheader">Please setup your new password.</div>
                
                @if(session('info'))
                    <div class="alert-info">
                        <i class="fas fa-info-circle" style="margin-top: 3px;"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.change.update') }}">
                    @csrf

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" type="password" name="password" required class="form-control" placeholder="Minimum 8 characters..." autofocus autocomplete="new-password">
                        @error('password')
                            <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save" style="margin-right: 6px;"></i> Save & Continue
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>