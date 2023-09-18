<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVENT PENTING</title>
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
            max-width: 150px;
            margin: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            color: #007BFF;
        }

        .pesan {
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

        
        pre {
                overflow-x: auto;
                white-space: pre-wrap;
                white-space: -moz-pre-wrap;
                white-space: -o-pre-wrap;
                word-wrap: break-word;
               /*  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; */
        }
    </style>
</head>
<body>
    <div class="header">
        <img class="logo" src="{{ $message->embed(asset('assets/logo.png')) }}" alt="Logo"> 
        <h1 style="text-align: center; margin:auto">Hi {{ $data['first_name'] }}, anda diundang untuk hadir 👻</h1>
    </div>
    <div class="container">
        <div class="pesan">
            Pesan Penyelenggara
        </div>
        <div class="message">
           <pre>{{ $data['message'] }}</pre>
        </div>
        <div class="pesan">
            Lokasi Event <!-- Gantilah dengan kode OTP yang sesuai -->
        </div>
        <div class="button-container">
           Silahkan hadir tepat waktu di lokasi berikut 🗺️<a style="color:blue" href="{{ $data['map'] }}">{{ $data['place'] }}</a>
        </div>
    </div>
    <div class="regards">
        Terimakasih atas kepercayaan Anda pada kami. <br>
        {{ env('APP_NAME') }}
    </div>
</body>
</html>
