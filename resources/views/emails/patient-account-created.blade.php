<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Medora patient portal account</title>
</head>
<body style="margin:0;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;line-height:1.6">
    <main style="max-width:620px;margin:32px auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:32px">
        <h1 style="margin:0 0 16px;font-size:24px">Welcome to Medora</h1>
        <p>Hello {{ $user->name }},</p>
        <p>Your patient portal account has been created. Use these credentials to sign in:</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8fafc">
            <tr><td style="padding:12px;font-weight:bold">Patient ID</td><td style="padding:12px">{{ $patient->patient_code }}</td></tr>
            <tr><td style="padding:12px;font-weight:bold">Username</td><td style="padding:12px">{{ $user->username }}</td></tr>
            <tr><td style="padding:12px;font-weight:bold">Email</td><td style="padding:12px">{{ $user->email }}</td></tr>
            <tr><td style="padding:12px;font-weight:bold">Default password</td><td style="padding:12px;font-family:monospace;font-size:16px">{{ $defaultPassword }}</td></tr>
        </table>
        <p><a href="{{ url('/login') }}" style="display:inline-block;background:#0f172a;color:#ffffff;padding:12px 18px;border-radius:8px;text-decoration:none">Sign in to Medora</a></p>
        <p>After signing in, open <strong>Patient Portal</strong> to review your profile and update your email address or password.</p>
        <p style="color:#64748b;font-size:13px">If you did not expect this account, contact your hospital administrator.</p>
    </main>
</body>
</html>
