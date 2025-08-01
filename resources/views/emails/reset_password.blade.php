<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background-color: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #eee;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://i.imgur.com/MHpXScU.jpeg" alt="Logo PT Handal Guna Sarana" style="width: 150px;">
        </div>

        <p>Yth. {{ $name }},</p>
        <p>Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetLink }}" style="background-color: #1a73e8; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
        </p>

        <p><strong>Catatan:</strong> Link reset password hanya berlaku selama <strong>5 menit</strong> sejak email ini dikirim. Jika waktu habis, silakan lakukan permintaan ulang.</p>

        <p>Jika Anda tidak melakukan permintaan ini, abaikan email ini atau hubungi tim support kami.</p>

        <p>Hormat kami,<br><strong>Divisi IT</strong><br>PT Handal Guna Sarana</p>
    </div>
</body>
</html>
