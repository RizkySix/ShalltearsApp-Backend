<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESET PASSWORD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
        }

        .logo {
            width: 200px;
            margin: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #007BFF;
        }

        .password {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            font-size: 16px;
            margin-bottom: 30px;
            color: #666;
        }

        .button-container {
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .regards {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <img class="logo" src="{{ $message->embed(asset('assets/logo.png')) }}" alt="Logo"> 
        <h1 style="text-align: center; margin:auto">Hi {{ $data['firstName'] }}, konfirmasi reset password akun Shalltears anda</h1>
    </div>
    <div class="container">
        <div class="password">
          {{ $data['newPassword'] }}
        </div>
        <div class="message">
            Password akan direset setelah anda melakukan verifikasi. Silahkan login dengan password diatas. Link verifikasi kedaluarsa dalam 60 menit. 
        </div>
        <div class="button-container">
            <a href="http://localhost:5173/auth/reset-password/{{ $data['reset_password_token'] }}/{{ $data['email'] }}" class="button">Verifikasi Reset Password</a>
        </div>

        <div class="message" style="margin-top: 20px;">
          Abaikan pesan ini jika anda tidak melakukan permintaan ini.
        </div>
    </div>
    <div class="regards">
        Terimakasih atas kepercayaan Anda pada kami. <br>
       {{ env('APP_NAME') }}
    </div>
</body>
</html>
