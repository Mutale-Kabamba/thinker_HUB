<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Contact Message</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin-bottom: 12px;">New Contact Form Message</h2>

    <p><strong>Name:</strong> {{ $name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>

    <p style="margin-top: 20px;"><strong>Message:</strong></p>
    <div style="white-space: pre-wrap; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px; background: #f8fafc;">
        {{ $bodyText }}
    </div>
</body>
</html>
