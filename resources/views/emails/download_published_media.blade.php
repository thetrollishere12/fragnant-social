<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Donation Reel Links</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .header h1 {
            color: #007BFF;
            margin: 0;
            font-size: 24px;
        }
        .intro {
            font-size: 16px;
            line-height: 1.6;
            margin-top: 20px;
            text-align: center;
        }
        .reel-list {
            margin-top: 20px;
        }
        .reel-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .reel-title {
            font-size: 18px;
            font-weight: bold;
            color: #007BFF;
            margin-bottom: 10px;
        }
        .reel-text {
            font-size: 15px;
            color: #555;
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            color: #fff;
            background-color: #007BFF;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-top: 30px;
            border-top: 2px solid #e0e0e0;
            padding-top: 20px;
        }

        .reel-day{
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: bold;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Generated Reels</h1>
        </div>

        <p class="intro">
            Below are the download links for today's generated donation reels. Each link is labeled with the respective day count and current donation progress.
        </p>

        <div class="reel-list">
       
                <div class="reel-item">

                    <a style="color: white;" 
                       href="{{ route('download.published-media', ['id' => $publishedMedia->id]) }}" 
                       class="button">
                        Download Reel
                    </a>
                </div>
          
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{env('APP_NAME')}}. All rights reserved.
        </div>
    </div>
</body>
</html>