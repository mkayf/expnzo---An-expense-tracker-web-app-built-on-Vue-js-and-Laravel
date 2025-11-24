<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification Mail | Expnzo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9fafb;
            padding: 20px 0;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .header {
            background-color: #328e6e;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .title {
            font-size: 22px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .content {
            padding: 30px 40px;
            color: #374151;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #111827;
        }
        
        .message {
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        .otp-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .otp-code {
            display: inline-block;
            background-color: #f0f9f5;
            color: #328e6e;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            padding: 15px 30px;
            border-radius: 8px;
            border: 1px dashed #328e6e;
        }
        
        .note {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin: 25px 0;
            font-size: 14px;
            border-left: 4px solid #328e6e;
        }
        
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer a {
            color: #328e6e;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .content {
                padding: 20px;
            }
            
            .otp-code {
                font-size: 28px;
                letter-spacing: 6px;
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Expnzo</div>
            <div class="title">OTP Verification</div>
        </div>
        
        <div class="content">
            <div class="greeting">Hello,</div>
            
            <div class="message">
                You are receiving this email because you requested a One-Time Password (OTP) for verification on Expnzo. 
                Please use the following code to complete your verification process:
            </div>
            
            <div class="otp-container">
                <div class="otp-code">{{ $OTP }}</div>
            </div>
            
            <div class="message">
                Enter this code on the verification page to proceed. This code is valid for the next 5 minutes.
            </div>
            
            <div class="note">
                <strong>Security Tip:</strong> Never share this OTP with anyone. Expnzo will never ask you for your password or OTP via email, phone, or text message.
            </div>
            
            <div class="message">
                If you did not request this OTP, please ignore this email or contact our support team if you have concerns.
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 Expnzo. All rights reserved.</p>
            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
            <p>Need help? <a href="mailto:support@expnzo.com">Contact our support team</a></p>
        </div>
    </div>
</body>
</html>