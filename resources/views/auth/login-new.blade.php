<!DOCTYPE html>
<html lang="en">
<head>
    <base href="../../../" />
    <title>Sistem Pengurusan Staf Kontrak IKMa</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="shortcut icon" href="{{ asset('templates/backend/assets/media/logos/favicon.ico') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('templates/backend/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('templates/backend/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .ikma-left-section {
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        #particles-logo {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
            display: block;
            pointer-events: none;
        }

        .d-flex.flex-column.flex-column-fluid.flex-lg-row {
            position: relative;
            z-index: 2;
        }

        .ikma-left-content {
            position: relative;
            z-index: 2;
        }

        .login-card-glass {
            background: rgba(255,255,255,.94) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.35);
            box-shadow: 0 15px 50px rgba(0,0,0,.15), 0 0 30px rgba(255,255,255,.1);
        }
    </style>

    <script>
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>

<body id="kt_body" class="auth-bg bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">

<script>
    var defaultThemeMode = "light";
    var themeMode;

    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }

        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }

        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
</script>

<div class="d-flex flex-column flex-root">

    <canvas id="particles-logo"></canvas>

    <style>
        body {
            background-image: url("{{ asset('templates/backend/assets/media/auth/bg4.jpg') }}");
        }

        [data-bs-theme="dark"] body {
            background-image: url("{{ asset('templates/backend/assets/media/auth/bg4-dark.jpg') }}");
        }
    </style>

    <div class="d-flex flex-column flex-column-fluid flex-lg-row">

        <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10 ikma-left-section">

            <div class="d-flex flex-center flex-lg-start flex-column align-items-center justify-content-center ikma-left-content" style="height:100vh;">

                <a href="{{ route('login') }}" class="mb-5">
                    <img class="h-150px" alt="Logo" src="{{ asset('assets/images/ikmalogo.png') }}" />
                </a>

                <div class="text-center">
                    <h1 class="fw-bold mb-2 text-white" style="letter-spacing:1.5px; font-size:2.5rem;">SISTEM KAKITANGAN</h1>
                    <h2 class="fw-normal mb-2 text-white" style="font-style:italic; font-size:2rem;">(eKakitangan)</h2>
                    <h3 class="fw-semibold text-white" style="font-size:1.7rem;">Institut Koperasi Malaysia</h3>
                </div>

            </div>
        </div>

        <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">

            <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20 login-card-glass">

                <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">

                    <form action="{{ route('login') }}" class="form w-100" method="post">
                        @csrf

                        <div class="text-center mb-11">
                            <h1 class="text-gray-900 fw-bolder mb-3">Sila Masuk Log Masuk</h1>
                            <div class="text-gray-500 fw-semibold fs-6">Masukkan E-Mel Dan Kata Laluan Anda</div>
                        </div>

                        <div class="fv-row mb-8">
                            <input type="text" placeholder="E-mel/No. Kad Pengenalan" name="email" autocomplete="off" class="form-control bg-transparent" value="{{ old('email') }}" required/>
                        </div>

                        <div class="fv-row mb-3">
                            <input type="password" placeholder="Kata Laluan" name="password" autocomplete="off" class="form-control bg-transparent" required/>
                        </div>

                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <div></div>
                            <a href="{{ route('password.request') }}" class="link-primary">Lupa Kata Laluan ?</a>
                        </div>

                        <div class="d-grid mb-10">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                <span class="indicator-label">Log Masuk</span>
                            </button>
                        </div>

{{--                        <div class="text-gray-500 text-center fw-semibold fs-6">Akaun Baru?--}}
{{--                            <a href="{{ route('register') }}" class="link-primary">Daftar Sekarang</a></div>--}}

                    </form>

                    @if(!empty($errors))
                        @foreach($errors->get('email') as $e)
                            <div class="text-center text-danger fw-bold">
                                {{ $e }}
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>var hostUrl = "assets/";</script>
<script src="{{ asset('templates/backend/assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('templates/backend/assets/js/scripts.bundle.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById("particles-logo");

    if (!canvas) return;

    const ctx = canvas.getContext("2d");

    let particles = [];
    let boxes = [];
    let mouse = {
        x: null,
        y: null
    };

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);

    // ===== MOUSE TARIK GARISAN SELURUH BACKGROUND =====
    window.addEventListener("mousemove", function (e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    window.addEventListener("mouseleave", function () {
        mouse.x = null;
        mouse.y = null;
    });

    // ===== TITIK =====
    for (let i = 0; i < 95; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 1.8,
            vy: (Math.random() - 0.5) * 1.8,
            radius: Math.random() * 2 + 1,
            alpha: Math.random()
        });
    }

    // ===== POSISI KOTAK TIDAK SENTUH =====
    function getSafeBoxPosition(size) {
        let x, y, safe;
        let tries = 0;
        const gap = 120;

        do {
            safe = true;
            x = Math.random() * (canvas.width - size);
            y = -Math.random() * canvas.height;

            for (let i = 0; i < boxes.length; i++) {
                let b = boxes[i];

                let dx = (x + size / 2) - (b.x + b.size / 2);
                let dy = (y + size / 2) - (b.y + b.size / 2);
                let distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < (size / 2 + b.size / 2 + gap)) {
                    safe = false;
                    break;
                }
            }

            tries++;
        } while (!safe && tries < 100);

        return { x: x, y: y };
    }

    // ===== KOTAK =====
    for (let i = 0; i < 15; i++) {
        let size = Math.random() * 120 + 50;
        let pos = getSafeBoxPosition(size);

        boxes.push({
            x: pos.x,
            y: pos.y,
            size: size,
            speed: Math.random() * 1.5 + 0.8,
            angle: Math.random() * Math.PI * 2,
            rotateSpeed: (Math.random() - 0.5) * 0.04,
            opacity: Math.random() * 0.08 + 0.04
        });
    }

    function drawRoundedBox(x, y, size, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + size - radius, y);
        ctx.quadraticCurveTo(x + size, y, x + size, y + radius);
        ctx.lineTo(x + size, y + size - radius);
        ctx.quadraticCurveTo(x + size, y + size, x + size - radius, y + size);
        ctx.lineTo(x + radius, y + size);
        ctx.quadraticCurveTo(x, y + size, x, y + size - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // ===== LUKIS KOTAK =====
        boxes.forEach(function (b) {
            b.y += b.speed;
            b.angle += b.rotateSpeed;

            if (b.y > canvas.height + b.size) {
                b.size = Math.random() * 120 + 50;

                let pos = getSafeBoxPosition(b.size);

                b.x = pos.x;
                b.y = pos.y;
                b.speed = Math.random() * 0.8 + 0.4;
                b.opacity = Math.random() * 0.08 + 0.04;
            }

            ctx.save();

            ctx.translate(b.x + b.size / 2, b.y + b.size / 2);
            ctx.rotate(b.angle);
            ctx.translate(-b.size / 2, -b.size / 2);

            drawRoundedBox(0, 0, b.size, 15);

            ctx.shadowBlur = 15;
            ctx.shadowColor = "rgba(255,255,255,0.3)";

            ctx.fillStyle = "rgba(255,255,255," + b.opacity + ")";
            ctx.fill();

            ctx.strokeStyle = "rgba(255,255,255,0.18)";
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.shadowBlur = 0;

            ctx.restore();
        });

        // ===== LUKIS TITIK =====
        particles.forEach(function (p) {
            p.x += p.vx;
            p.y += p.vy;

            p.alpha += (Math.random() - 0.5) * 0.05;

            if (p.alpha > 1) p.alpha = 1;
            if (p.alpha < 0.3) p.alpha = 0.3;

            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

            ctx.beginPath();
            ctx.shadowBlur = 8;
            ctx.shadowColor = "#ffffff";

            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);

            ctx.fillStyle = "rgba(255,255,255," + p.alpha + ")";
            ctx.fill();

            ctx.shadowBlur = 0;
        });

        // ===== GARISAN + MOUSE INTERACTION =====
        for (let i = 0; i < particles.length; i++) {

            for (let j = i + 1; j < particles.length; j++) {
                let dx = particles[i].x - particles[j].x;
                let dy = particles[i].y - particles[j].y;
                let distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 300) {
                    ctx.beginPath();

                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);

                    ctx.strokeStyle = "rgba(255,255,255," + ((1 - distance / 180) * 0.50) + ")";
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                }
            }

            // ===== TARIK GARISAN KE CURSOR =====
            if (mouse.x !== null && mouse.y !== null) {
                let dx = particles[i].x - mouse.x;
                let dy = particles[i].y - mouse.y;
                let distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 250) {
                    ctx.beginPath();

                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(mouse.x, mouse.y);

                    ctx.strokeStyle = "rgba(255,255,255," + ((1 - distance / 180) * 0.7) + ")";
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(draw);
    }

    draw();

});
</script>

</body>
</html>