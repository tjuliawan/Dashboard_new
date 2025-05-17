emptyTable: `
    <style>
        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <div style="
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        color: #666;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        animation: fadeUp 0.8s ease-out;
    ">
        <div style="
            background: #f9f9f9;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
        ">
            <i class="fas fa-box-open" style="
                font-size: 64px;
                color: #d3d3d3;
                margin-bottom: 15px;
                animation: fadeUp 1s ease-out;
            "></i>
            <div style="font-size: 16px; font-weight: 500;">Tidak ada data yang bisa ditampilkan</div>
            <div style="font-size: 13px; color: #999; margin-top: 5px;">
                Coba ubah filter atau <span style="color: #007bff; cursor: pointer;" onclick="location.reload()">refresh halaman</span>.
            </div>
        </div>
    </div>
`
