<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Access Request</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #e1e1e1;
        }
        .content {
            padding: 20px 0;
        }
        h1 {
            color: #0d9488;
            margin-top: 0;
            font-size: 24px;
        }
        p {
            margin-bottom: 16px;
        }
        .button {
            display: inline-block;
            background-color: #0d9488;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #0f766e;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e1e1e1;
            padding-top: 20px;
        }
        .details {
            background-color: #f0f9ff;
            border-left: 4px solid #0d9488;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pageant Management System</h1>
        </div>
        <div class="content">
            <h1>New Edit Access Request</h1>
            
            <p>Hello Admin,</p>
            
            <p>An organizer has requested edit access for a pageant that has already started or is locked.</p>
            
            <div class="details">
                <p><span class="label">Organizer:</span> {{ $organizer->name }} ({{ $organizer->email }})</p>
                <p><span class="label">Pageant:</span> {{ $pageant->name }}</p>
                <p><span class="label">Reason:</span></p>
                <p style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #e2e8f0;">{{ $reason }}</p>
            </div>
            
            <p>Please review this request in the admin dashboard.</p>
            
            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="button">Review Request</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Pageant Management System. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
