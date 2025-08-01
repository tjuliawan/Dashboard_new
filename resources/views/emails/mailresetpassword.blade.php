<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://i.imgur.com/MHpXScU.jpeg" alt="Logo PT Handal Guna Sarana" style="width: 150px; border-radius: 6px;">
        </div>

        <p>Yth. {{ $details['name'] }},</p>

        <p>Dengan hormat,</p>

        <p>
            Anda menerima email ini karena terdapat permintaan untuk mengatur ulang kata sandi pada akun Anda
            yang terdaftar dengan alamat email <strong>{{ $details['email'] }}</strong>.
        </p>

        <h3 style="margin-top: 30px;">Detail Permintaan Reset Kata Sandi</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 8px 0;"><strong>Tanggal:</strong></td>
                <td style="padding: 8px 0;">{{ $details['tanggal'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>IP Address:</strong></td>
                <td style="padding: 8px 0;">{{ $details['ip'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Perangkat:</strong></td>
                <td style="padding: 8px 0;">{{ $details['deviceInfo']['platform'] }} - {{ $details['deviceInfo']['browser'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Model:</strong></td>
                <td style="padding: 8px 0;">{{ $details['deviceInfo']['model'] }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px;">
            Jika Anda tidak merasa melakukan permintaan ini, harap segera menghubungi tim administrator atau layanan dukungan
            PT Handal Guna Sarana untuk tindakan lebih lanjut.
        </p>

        <p>Terima kasih atas perhatian dan kerjasamanya.</p>

        <p>Hormat kami,</p>
        <p><strong>Divisi IT</strong><br>
            PT Handal Guna Sarana</p>
    </div>
</body>

</html>
