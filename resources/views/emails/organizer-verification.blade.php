<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Organizer Account</title>
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
        .logo {
            max-height: 50px;
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
        .note {
            background-color: #f0f9ff;
            border-left: 4px solid #0d9488;
            padding: 10px 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .expiry {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pageant Management System</h1>
        </div>
        <div class="content">
            <h1>Verify Your Organizer Account</h1>
            
            <p>Hello {{ $name }},</p>
            
            <p>Your account has been created as an <strong>Organizer</strong> in our Pageant Management System. To complete your registration and access the system, please verify your email address by clicking the button below:</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
            </div>
            
            <div class="note">
                <p>After verification, you'll be able to:</p>
                <ul>
                    <li>Set up your permanent password</li>
                    <li>Manage assigned pageants</li>
                    <li>Coordinate with judges and tabulators</li>
                    <li>Access all organizer features</li>
                </ul>
            </div>
            
            <p>This verification link will expire on <span class="expiry">{{ $expiresAt }}</span>. If you don't verify your email before this time, you'll need to request a new verification link.</p>
            
            <p>If you didn't request this account, please ignore this email or contact the administrator.</p>
            
            <p>If the button above doesn't work, you can also verify by copying and pasting this link into your browser:</p>
            <p style="word-break: break-all; font-size: 14px; color: #666;">{{ $verificationUrl }}</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Pageant Management System. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html> 