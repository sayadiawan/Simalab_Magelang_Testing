@php
    $videoDir = 'assets/admin/video';

    // Urut mengikuti alur kerja laboratorium
    $clips = [
        [
            'file' => 'mixkit-drops-filling-a-lab-tube-17456-hd-ready',
            'label' => 'Penyiapan sampel',
        ],
        [
            'file' => 'mixkit-woman-working-with-samples-in-laboratory-21457-hd-ready',
            'label' => 'Penanganan sampel',
        ],
        [
            'file' => 'mixkit-scientist-mixing-liquids-in-a-laboratory-4719-hd-ready',
            'label' => 'Pengujian laboratorium',
        ],
        [
            'file' => 'mixkit-laboratory-worker-looking-at-a-test-tube-21454-hd-ready',
            'label' => 'Pemeriksaan analis',
        ],
    ];

    // MP4 didahulukan: ukurannya lebih kecil dan didukung semua peramban
    $clips = array_values(array_filter(array_map(function ($clip) use ($videoDir) {
        $sources = [];
        foreach (['mp4' => 'video/mp4', 'webm' => 'video/webm'] as $ext => $mime) {
            $relative = $videoDir . '/' . $clip['file'] . '.' . $ext;
            if (is_file(public_path($relative))) {
                $sources[] = ['src' => $relative, 'type' => $mime];
            }
        }
        $clip['sources'] = $sources;

        return $clip;
    }, $clips), function ($clip) {
        return count($clip['sources']) > 0;
    }));

    $posterPath = $videoDir . '/poster-pengujian.jpg';
    $hasPoster = is_file(public_path($posterPath));
    $hasVideo = count($clips) > 0;

    $heroVideoPath = $videoDir . '/hero-lab.mp4';
    $heroPosterPath = $videoDir . '/hero-poster.jpg';
    $hasHeroVideo = is_file(public_path($heroVideoPath));
    $hasHeroPoster = is_file(public_path($heroPosterPath));
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SimaLab — Sistem Informasi Laboratorium untuk lingkungan pengujian dan demonstrasi.">
    <link rel="shortcut icon" href="{{ asset('assets/admin/images/logo/logo_magelang_mini.png') }}">
    <script>document.documentElement.className += ' js';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>SimaLab — Lingkungan Pengujian</title>
    <style>
        :root {
            --navy: #0b3a5c;
            --navy-deep: #06283f;
            --teal: #0d8f7f;
            --teal-bright: #16a892;
            --teal-soft: #e7f4f2;
            --sand: #f5f8f7;
            --ink: #1c2c33;
            --muted: #5c6d75;
            --line: #dbe5e3;
            --white: #ffffff;
            --max: 1180px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Manrope", sans-serif;
            color: var(--ink);
            background: var(--white);
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        /* —— Header —— */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--line);
        }

        .topbar-inner {
            max-width: var(--max);
            margin: 0 auto;
            padding: 13px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
            color: var(--navy);
        }

        .brand img {
            width: 34px;
            height: auto;
            display: block;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .brand-text strong {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.05em;
        }

        .brand-text small {
            font-size: 10.5px;
            font-weight: 500;
            color: var(--muted);
        }

        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .nav-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--teal);
        }

        .top-link {
            font-size: 13px;
            font-weight: 700;
            color: var(--navy);
            text-decoration: none;
            padding: 9px 18px;
            border: 1px solid var(--line);
            background: var(--white);
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }

        .top-link:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: var(--teal-soft);
        }

        /* —— Hero —— */
        .hero {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: var(--sand);
            border-bottom: 1px solid var(--line);
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background-image: radial-gradient(rgba(11, 58, 92, 0.09) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: radial-gradient(ellipse 90% 70% at 50% 30%, #000 35%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 30%, #000 35%, transparent 100%);
            pointer-events: none;
        }

        /* —— Latar hero berupa rekaman lab —— */
        .hero-media {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .hero-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Dicerminkan agar bagian ramai jatuh di sisi kanan, bukan di belakang teks */
            transform: scaleX(-1);
            /* Dicerahkan & dinetralkan supaya tetap terasa ringan seperti latar semula */
            filter: grayscale(0.92) contrast(0.95) brightness(1.12);
        }

        .hero-video-tint {
            position: absolute;
            inset: 0;
            background: linear-gradient(150deg, #0b3a5c 0%, #0e7a76 55%, #16a892 100%);
            mix-blend-mode: color;
        }

        /*
         * Selubung terang: pekat di sisi teks agar tetap terbaca,
         * menipis ke kanan supaya rekaman masih terlihat.
         */
        .hero-video-veil {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg,
                    rgba(245, 248, 247, 0.97) 0%,
                    rgba(245, 248, 247, 0.90) 34%,
                    rgba(245, 248, 247, 0.62) 62%,
                    rgba(245, 248, 247, 0.48) 100%),
                linear-gradient(0deg, rgba(245, 248, 247, 0.72) 0%, transparent 42%);
        }

        .glow {
            position: absolute;
            z-index: 1;
            border-radius: 50%;
            filter: blur(70px);
            pointer-events: none;
        }

        .glow-teal {
            width: 380px;
            height: 380px;
            background: rgba(22, 168, 146, 0.16);
            top: -110px;
            right: 8%;
            animation: drift 15s ease-in-out infinite;
        }

        .glow-navy {
            width: 320px;
            height: 320px;
            background: rgba(11, 58, 92, 0.12);
            bottom: -140px;
            left: -60px;
            animation: drift 19s ease-in-out infinite reverse;
        }

        .hero-inner {
            position: relative;
            z-index: 2;
            max-width: var(--max);
            margin: 0 auto;
            padding: clamp(46px, 6.5vw, 78px) 28px clamp(54px, 7vw, 86px);
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(0, 0.98fr);
            gap: clamp(36px, 5vw, 64px);
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--teal);
            margin-bottom: 20px;
        }

        .eyebrow::before {
            content: "";
            width: 26px;
            height: 2px;
            background: var(--teal);
        }

        .hero-brand {
            font-size: clamp(2.9rem, 6.4vw, 4.5rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 0.94;
            color: var(--navy-deep);
            margin-bottom: 14px;
        }

        .hero-brand .word-lab {
            color: var(--teal);
        }

        /* Text reveal: kata muncul berurutan */
        .line-reveal {
            display: inline-block;
            overflow: hidden;
            vertical-align: bottom;
        }

        .word-reveal {
            display: inline-block;
            will-change: transform, opacity;
        }

        .js .word-reveal {
            opacity: 0;
            transform: translateY(110%);
            transition: opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1), transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .js .is-visible .word-reveal,
        .js .word-reveal.is-shown {
            opacity: 1;
            transform: translateY(0);
        }

        .hero-title {
            font-size: clamp(1.05rem, 1.7vw, 1.28rem);
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 12px;
            max-width: 28ch;
        }

        .hero-lead {
            font-size: 1rem;
            color: var(--muted);
            max-width: 40ch;
            margin-bottom: 30px;
        }

        .cta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 32px;
        }

        /* Versi Mobile: sembunyikan di desktop lebar; tampilkan di layar kecil / touch */
        #mobile-menu-btn {
            display: none;
        }

        @media (max-width: 1024px), (hover: none) and (pointer: coarse) {
            #mobile-menu-btn {
                display: inline-flex;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
                justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            padding: 13px 24px;
            border: 1px solid transparent;
            transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease, background 0.2s, border-color 0.2s, color 0.2s;
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
        }

        .btn:active {
            transform: translateY(-1px) scale(0.99);
        }

        .btn-primary {
            background: var(--navy);
            color: var(--white);
            box-shadow: 0 8px 20px rgba(11, 58, 92, 0.22);
        }

        .btn-primary:hover {
            background: var(--navy-deep);
            box-shadow: 0 14px 28px rgba(11, 58, 92, 0.3);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--navy);
            border-color: var(--line);
        }

        .btn-secondary:hover {
            border-color: var(--teal);
            color: var(--teal);
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 26px;
            padding-top: 22px;
            border-top: 1px solid var(--line);
        }

        .hero-meta div strong {
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--navy);
            margin-bottom: 2px;
        }

        .hero-meta div span {
            font-size: 12.5px;
            color: var(--muted);
        }

        /* —— Brand card (floating + parallax) —— */
        .brand-float {
            position: relative;
            will-change: transform;
        }

        .brand-card {
            position: relative;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow: 0 24px 50px rgba(6, 40, 63, 0.1);
            animation: float 8s ease-in-out infinite;
            transition: box-shadow 0.35s ease, transform 0.35s ease, background 0.35s ease;
        }

        .brand-card:hover {
            box-shadow: 0 28px 58px rgba(6, 40, 63, 0.16);
        }

        .brand-card-body {
            padding: clamp(34px, 4.5vw, 52px) 32px clamp(30px, 4vw, 44px);
            display: flex;
                flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .brand-card-body img {
            width: min(230px, 62%);
            height: auto;
            display: block;
        }

        .brand-card-divider {
            width: 40px;
            height: 2px;
            background: var(--teal);
            margin: 26px 0 16px;
        }

        .brand-card-body p {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .brand-card-bar {
            position: relative;
            height: 10px;
            background: var(--teal);
            overflow: hidden;
        }

        .brand-card-bar::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 38%;
            background: var(--navy);
            clip-path: polygon(22% 0, 100% 0, 100% 100%, 0 100%);
        }

        .brand-card-corner {
            position: absolute;
            width: 46px;
            height: 46px;
            border-top: 2px solid var(--teal);
            border-left: 2px solid var(--teal);
            top: -1px;
            left: -1px;
        }

        /* —— Proses pengujian —— */
        .process {
            background: var(--white);
            border-bottom: 1px solid var(--line);
        }

        .process-inner {
            max-width: var(--max);
            margin: 0 auto;
            padding: clamp(56px, 7vw, 82px) 28px clamp(60px, 7vw, 86px);
        }

        .section-head {
            max-width: 46ch;
            margin-bottom: 36px;
        }

        .section-head h2 {
            font-size: clamp(1.45rem, 2.4vw, 1.9rem);
            font-weight: 800;
            color: var(--navy-deep);
            letter-spacing: -0.015em;
            line-height: 1.22;
            margin-bottom: 10px;
        }

        .section-head p {
            font-size: 15px;
            color: var(--muted);
        }

        .process-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            gap: clamp(28px, 4vw, 48px);
            align-items: start;
        }

        /* Sticky: visual tetap, konten langkah berganti mengikuti scroll */
        .stage-pin {
            position: sticky;
            top: 88px;
            align-self: start;
        }

        .stage {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: linear-gradient(160deg, #08324f 0%, #0b3a5c 55%, #0a4f55 100%);
            min-height: 420px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            transition: box-shadow 0.4s ease;
        }

        .stage.is-pinned {
            box-shadow: 0 20px 48px rgba(6, 40, 63, 0.18);
        }

        /* Tanpa z-index supaya video tetap bisa berpadu dengan latar panel */
        .stage-clips {
            position: absolute;
            inset: 0;
        }

        /*
         * Warna asli klip diredam lalu diwarnai ulang ke navy–teal.
         * Kontras dinaikkan agar detail tetap terbaca setelah digelapkan.
         */
        .stage-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.9s ease;
            filter: grayscale(0.9) contrast(1.4) brightness(0.42);
        }

        .stage-video.is-active {
            opacity: 1;
        }

        .stage-tint {
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(155deg, #0b3a5c 0%, #0d6f72 58%, #16a892 100%);
            mix-blend-mode: color;
            opacity: 0.92;
            pointer-events: none;
        }

        .stage-shade {
            position: absolute;
            inset: 0;
            z-index: 2;
            background:
                radial-gradient(115% 85% at 74% 10%, rgba(127, 212, 200, 0.12), transparent 62%),
                linear-gradient(0deg, rgba(4, 26, 41, 0.66) 0%, rgba(4, 26, 41, 0.08) 52%),
                linear-gradient(150deg, rgba(11, 58, 92, 0.5) 0%, rgba(10, 79, 85, 0.3) 100%);
            pointer-events: none;
        }

        /* Sisakan ruang di bawah agar animasi tidak tertimpa keterangan */
        .stage-scene {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 44px;
        }

        .stage-grid {
            position: absolute;
            inset: 0;
            z-index: 3;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 38px 38px;
            pointer-events: none;
        }

        .stage-caption {
            position: relative;
            z-index: 4;
            margin: 0;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: linear-gradient(0deg, rgba(4, 26, 41, 0.78), transparent);
            color: rgba(255, 255, 255, 0.9);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .stage-caption em {
            font-style: normal;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #7fd4c8;
        }

        .stage-caption em::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7fd4c8;
            animation: blink 1.8s ease-in-out infinite;
        }

        /* SVG scene animation */
        .liquid {
            transform-box: fill-box;
            transform-origin: bottom;
            animation: fill 5s ease-in-out infinite;
        }

        .liquid-b { animation-delay: -1.6s; }
        .liquid-c { animation-delay: -3.2s; }

        .bubble {
            transform-box: fill-box;
            animation: bubble 3.4s ease-in infinite;
            opacity: 0;
        }

        .bubble-2 { animation-delay: -1.1s; }
        .bubble-3 { animation-delay: -2.2s; }
        .bubble-4 { animation-delay: -0.6s; }
        .bubble-5 { animation-delay: -1.9s; }
        .bubble-6 { animation-delay: -2.8s; }

        .scanner {
            animation: scan 6s ease-in-out infinite;
        }

        .drop {
            transform-box: fill-box;
            animation: drop 2.6s ease-in infinite;
            opacity: 0;
        }

        .trace {
            stroke-dasharray: 620;
            stroke-dashoffset: 620;
            animation: trace 5.5s ease-in-out infinite;
        }

        .spark {
            transform-box: fill-box;
            animation: spark 7s ease-in-out infinite;
        }

        .spark-2 { animation-delay: -2.4s; }
        .spark-3 { animation-delay: -4.8s; }

        /* Steps */
        .steps {
            list-style: none;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 8px;
            padding-bottom: 24px;
        }

        .step {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 16px;
            padding: 18px 18px 16px;
            border-left: 2px solid var(--line);
            cursor: pointer;
            min-height: 108px;
            transition: background 0.3s ease, border-color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
            opacity: 0.55;
        }

        .step:hover {
            background: var(--sand);
            opacity: 0.85;
        }

        .step.is-active {
            background: var(--sand);
            border-left-color: var(--teal);
            opacity: 1;
            transform: translateX(4px);
        }

        .step-index {
            font-size: 12px;
            font-weight: 800;
            color: var(--line);
            letter-spacing: 0.04em;
            padding-top: 2px;
            transition: color 0.25s, transform 0.25s;
        }

        .step.is-active .step-index {
            color: var(--teal);
            transform: scale(1.08);
        }

        .step strong {
            display: block;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 3px;
        }

        .step p {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .step-bar {
            display: block;
            grid-column: 2;
            height: 2px;
            background: var(--line);
            position: relative;
            overflow: hidden;
        }

        .step-bar i {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 0;
            background: var(--teal);
        }

        .step.is-active .step-bar i {
            animation: fillbar 5s linear forwards;
        }

        /* —— Values —— */
        .values {
            background: var(--sand);
            border-bottom: 1px solid var(--line);
        }

        .values-inner {
            max-width: var(--max);
            margin: 0 auto;
            padding: 0 28px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
        }

        .value {
            padding: 34px 26px 34px 0;
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .value:hover {
            transform: translateY(-4px);
        }

        .value + .value {
            border-left: 1px solid var(--line);
            padding-left: 30px;
        }

        .value i {
            color: var(--teal);
            font-size: 17px;
            margin-bottom: 14px;
            display: inline-block;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .value:hover i {
            transform: translateY(-3px) scale(1.12);
        }

        .value strong {
            display: block;
            font-size: 14.5px;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .value span {
            display: block;
            font-size: 13.5px;
            color: var(--muted);
            max-width: 30ch;
        }

        /* —— About —— */
        .about {
            max-width: var(--max);
            margin: 0 auto;
            padding: clamp(56px, 7vw, 76px) 28px clamp(60px, 7vw, 80px);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: clamp(32px, 5vw, 56px);
            align-items: start;
        }

        .about h2 {
            font-size: clamp(1.4rem, 2.3vw, 1.8rem);
            font-weight: 800;
            color: var(--navy-deep);
            line-height: 1.22;
            letter-spacing: -0.01em;
            margin-bottom: 14px;
        }

        .about > div > p {
            color: var(--muted);
            font-size: 15px;
            max-width: 44ch;
        }

        .support {
            background: var(--teal-soft);
            border-left: 3px solid var(--teal);
            padding: 24px 26px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .support:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(11, 58, 92, 0.08);
        }

        .support strong {
            display: block;
            font-size: 14px;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .support p {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 18px;
        }

        .bsre {
            display: inline-flex;
            align-items: center;
            gap: 13px;
            background: var(--white);
            padding: 10px 14px;
        }

        .bsre img {
            height: 40px;
            width: auto;
            display: block;
        }

        .bsre span {
            font-size: 12px;
            color: var(--muted);
            max-width: 18ch;
        }

        /* —— Footer —— */
        .site-footer {
            position: relative;
            background: var(--navy-deep);
            color: rgba(255, 255, 255, 0.78);
            padding: 56px 28px 26px;
        }

        .site-footer::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--teal);
        }

        .footer-inner {
            max-width: var(--max);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.25fr 1fr 1fr;
            gap: 34px;
        }

        .site-footer h4 {
            color: var(--white);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .site-footer p,
        .site-footer li {
            font-size: 13px;
            line-height: 1.75;
        }

        .site-footer ul {
            list-style: none;
        }

        .site-footer a {
            color: rgba(255, 255, 255, 0.72);
            text-decoration: none;
            transition: color 0.2s, padding-left 0.2s;
        }

        .site-footer a:hover {
            color: #7fd4c8;
            padding-left: 4px;
        }

        .footer-bottom {
            max-width: var(--max);
            margin: 34px auto 0;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: rgba(255, 255, 255, 0.45);
            text-align: center;
        }

        /* —— Reveal (hanya aktif bila JavaScript hidup) —— */
        .js .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity 0.75s cubic-bezier(0.22, 1, 0.36, 1), transform 0.75s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .js .reveal.reveal-left {
            transform: translateX(-28px);
        }

        .js .reveal.reveal-right {
            transform: translateX(28px);
        }

        .js .reveal.is-visible {
            opacity: 1;
            transform: none;
        }

        /* Container stagger: parent tidak di-fade, hanya anaknya */
        .js .reveal.stagger {
            opacity: 1;
            transform: none;
        }

        .js .reveal[data-delay="1"] { transition-delay: 0.08s; }
        .js .reveal[data-delay="2"] { transition-delay: 0.16s; }
        .js .reveal[data-delay="3"] { transition-delay: 0.24s; }
        .js .reveal[data-delay="4"] { transition-delay: 0.32s; }
        .js .reveal[data-delay="5"] { transition-delay: 0.4s; }

        /* Stagger anak di dalam container */
        .js .stagger > * {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.65s cubic-bezier(0.22, 1, 0.36, 1), transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .js .stagger.is-visible > *:nth-child(1) { transition-delay: 0.05s; }
        .js .stagger.is-visible > *:nth-child(2) { transition-delay: 0.14s; }
        .js .stagger.is-visible > *:nth-child(3) { transition-delay: 0.23s; }
        .js .stagger.is-visible > *:nth-child(4) { transition-delay: 0.32s; }
        .js .stagger.is-visible > *:nth-child(5) { transition-delay: 0.41s; }

        .js .stagger.is-visible > * {
            opacity: 1;
            transform: none;
        }

        /* CTA penutup */
        .cta-band {
            background: linear-gradient(120deg, var(--navy-deep) 0%, var(--navy) 48%, #0a5a58 100%);
            color: var(--white);
            border-bottom: 1px solid var(--line);
            overflow: hidden;
            position: relative;
        }

        .cta-band::before {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            right: -80px;
            top: -120px;
            border-radius: 50%;
            background: rgba(22, 168, 146, 0.22);
            filter: blur(40px);
            pointer-events: none;
        }

        .cta-band-inner {
            position: relative;
            max-width: var(--max);
            margin: 0 auto;
            padding: clamp(42px, 6vw, 64px) 28px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
        }

        .cta-band h2 {
            font-size: clamp(1.25rem, 2.2vw, 1.65rem);
            font-weight: 800;
            letter-spacing: -0.015em;
            margin-bottom: 8px;
        }

        .cta-band p {
            font-size: 14.5px;
            color: rgba(255, 255, 255, 0.72);
            max-width: 42ch;
        }

        .cta-band .btn-primary {
            background: var(--white);
            color: var(--navy);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        }

        .cta-band .btn-primary:hover {
            background: var(--teal-soft);
            color: var(--navy-deep);
        }

        /* —— Keyframes —— */
        @keyframes drift {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(26px, -20px); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-9px); }
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.25; }
        }

        @keyframes fill {
            0%, 100% { transform: scaleY(0.34); }
            50% { transform: scaleY(0.82); }
        }

        @keyframes bubble {
            0% { transform: translateY(0); opacity: 0; }
            15% { opacity: 0.85; }
            80% { opacity: 0.5; }
            100% { transform: translateY(-58px); opacity: 0; }
        }

        @keyframes scan {
            0%, 100% { transform: translateY(40px); opacity: 0; }
            15% { opacity: 1; }
            85% { opacity: 1; }
            100% { transform: translateY(300px); opacity: 0; }
        }

        @keyframes drop {
            0% { transform: translateY(0); opacity: 0; }
            18% { opacity: 1; }
            70% { transform: translateY(38px); opacity: 1; }
            85% { transform: translateY(42px); opacity: 0; }
            100% { transform: translateY(42px); opacity: 0; }
        }

        @keyframes trace {
            0% { stroke-dashoffset: 620; }
            55% { stroke-dashoffset: 0; }
            85% { stroke-dashoffset: 0; opacity: 1; }
            100% { stroke-dashoffset: 0; opacity: 0; }
        }

        @keyframes spark {
            0%, 100% { transform: translateY(0); opacity: 0.25; }
            50% { transform: translateY(-22px); opacity: 0.7; }
        }

        @keyframes fillbar {
            to { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }

            .js .reveal,
            .js .stagger > *,
            .js .word-reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .glow-teal,
            .glow-navy,
            .brand-card,
            .liquid,
            .bubble,
            .scanner,
            .drop,
            .trace,
            .spark,
            .stage-caption em::before,
            .step.is-active .step-bar i {
                animation: none;
            }

            .bubble,
            .drop {
                opacity: 0.6;
            }

            .trace {
                stroke-dashoffset: 0;
            }

            .step.is-active .step-bar i {
            width: 100%;
        }

            .btn:hover,
            .value:hover,
            .support:hover,
            .step.is-active {
                transform: none;
            }

            .value:hover i {
                transform: none;
            }
        }

        @media (max-width: 960px) {
            .topbar-nav .nav-link {
                display: none;
            }

            /* Latar video dilewati di layar kecil supaya hemat kuota */
            .hero-media {
                display: none;
            }

            .hero-inner {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .brand-card {
                max-width: 420px;
                margin: 0 auto;
            width: 100%;
            }

            .process-grid {
                grid-template-columns: 1fr;
            }

            .stage-pin {
                position: relative;
                top: auto;
            }

            .stage {
                min-height: 320px;
            }

            .step {
                min-height: 0;
                opacity: 1;
            }

            .step.is-active {
                transform: none;
            }

            .values-inner {
                grid-template-columns: 1fr;
                padding: 6px 28px 18px;
            }

            .value {
                padding: 22px 0;
            }

            .value:hover {
                transform: none;
            }

            .value + .value {
                border-left: none;
                padding-left: 0;
                border-top: 1px solid var(--line);
            }

            .about {
                grid-template-columns: 1fr;
            }

            .cta-band-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .footer-inner {
                grid-template-columns: 1fr;
                gap: 26px;
            }
        }

        @media (max-width: 480px) {
            .topbar-inner,
            .hero-inner,
            .process-inner,
            .values-inner,
            .about,
            .cta-band-inner,
            .site-footer {
                padding-left: 18px;
                padding-right: 18px;
            }

            .brand-text small { display: none; }

            .cta-row { flex-direction: column; }

            .btn { width: 100%; }

            .hero-meta { gap: 18px; }

            .stage { min-height: 260px; }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a href="{{ url('') }}" class="brand">
                <img src="{{ asset('/assets/public/images/logo_magelang_mini.png') }}" alt="Logo SimaLab">
                <span class="brand-text">
                    <strong>SimaLab</strong>
                    <small>Lingkungan pengujian</small>
                </span>
            </a>
            <nav class="topbar-nav">
                <a class="nav-link" href="#alur">Alur pengujian</a>
                <a class="nav-link" href="#tentang">Tentang</a>
                @auth
                    <a class="top-link" href="{{ route('home') }}">Beranda</a>
                @else
                    <a class="top-link" href="{{ route('login-form') }}">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            @if ($hasHeroVideo)
                <div class="hero-media" id="heroMedia" aria-hidden="true" data-parallax="0.22">
                    <video class="hero-video" id="heroVideo" muted loop playsinline preload="none"
                        @if ($hasHeroPoster) poster="{{ asset($heroPosterPath) }}" @endif>
                        <source data-src="{{ asset($heroVideoPath) }}" type="video/mp4">
                    </video>
                    <span class="hero-video-tint"></span>
                    <span class="hero-video-veil"></span>
                </div>
            @endif

            <span class="glow glow-teal" aria-hidden="true" data-parallax="0.08"></span>
            <span class="glow glow-navy" aria-hidden="true" data-parallax="-0.06"></span>

            <div class="hero-inner">
                <div class="hero-copy">
                    <p class="eyebrow reveal">Sistem informasi laboratorium</p>
                    <h1 class="hero-brand reveal" data-split="chars" aria-label="SimaLab">
                        <span class="line-reveal"><span class="word-reveal" style="transition-delay:0.05s">Sima</span><span class="word-reveal word-lab" style="transition-delay:0.18s">Lab</span></span>
                    </h1>
                    <p class="hero-title reveal" data-delay="1" data-split="words">Kelola layanan laboratorium dalam satu alur kerja</p>
                    <p class="hero-lead reveal" data-delay="2">
                        Permohonan uji, penerimaan sampel, verifikasi hasil, hingga administrasi —
                        tercatat rapi dan siap dicetak kapan pun dibutuhkan.
                    </p>
                    <div class="cta-row reveal" data-delay="3">
                        @auth
                            <a class="btn btn-primary" href="{{ route('home') }}">Ke Beranda</a>
                        @else
                            <a class="btn btn-primary" href="{{ route('login-form') }}">Login Sistem</a>
                        @endauth
                        <a class="btn btn-secondary" href="{{ route('mobile.menu') }}" id="mobile-menu-btn">
                            <i class="fas fa-mobile-alt" aria-hidden="true"></i>
                            Versi Mobile
                        </a>
                    </div>
                    <div class="hero-meta reveal" data-delay="4">
                        <div>
                            <strong>Terakreditasi</strong>
                            <span>Mengikuti standar mutu laboratorium</span>
                        </div>
                        <div>
                            <strong>Tanda tangan elektronik</strong>
                            <span>Didukung sertifikat BSrE</span>
                        </div>
                    </div>
        </div>

                <div class="brand-float reveal reveal-right" data-delay="1" data-parallax="0.18">
                    <div class="brand-card">
                        <span class="brand-card-corner" aria-hidden="true"></span>
                        <div class="brand-card-body">
                            <img src="{{ asset('/assets/public/images/logo_magelang.png') }}" alt="Logo SimaLab">
                            <span class="brand-card-divider" aria-hidden="true"></span>
                            <p>Akurat · Terpercaya · Profesional</p>
            </div>
                        <div class="brand-card-bar" aria-hidden="true"></div>
        </div>
                </div>
            </div>
        </section>

        <section class="process" id="alur">
            <div class="process-inner">
                <div class="section-head reveal">
                    <h2>Gambaran alur pengujian</h2>
                    <p>Dari sampel diterima sampai hasil terbit, setiap tahap tercatat dan dapat ditelusuri.</p>
                </div>

                <div class="process-grid">
                    <div class="stage-pin">
                    <div class="stage reveal reveal-left" id="stage">
                        @if ($hasVideo)
                            <div class="stage-clips" id="stageClips">
                                @foreach ($clips as $i => $clip)
                                    <video class="stage-video{{ $i === 0 ? ' is-active' : '' }}"
                                        data-label="{{ $clip['label'] }}"
                                        muted playsinline preload="{{ $i === 0 ? 'auto' : 'metadata' }}"
                                        @if ($i === 0) autoplay @endif
                                        @if (count($clips) === 1) loop @endif
                                        @if ($i === 0 && $hasPoster) poster="{{ asset($posterPath) }}" @endif>
                                        @foreach ($clip['sources'] as $source)
                                            <source src="{{ asset($source['src']) }}" type="{{ $source['type'] }}">
                                        @endforeach
                                    </video>
                                @endforeach
                            </div>
                            <span class="stage-tint" aria-hidden="true"></span>
                            <span class="stage-shade" aria-hidden="true"></span>
                        @else
                            <svg class="stage-scene" viewBox="0 0 600 420" role="img"
                                aria-label="Animasi proses pengujian sampel di laboratorium">
                                <defs>
                                    <linearGradient id="liq" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#2ad3b6" />
                                        <stop offset="100%" stop-color="#0d8f7f" />
                                    </linearGradient>
                                    <linearGradient id="beam" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#7fd4c8" stop-opacity="0" />
                                        <stop offset="50%" stop-color="#7fd4c8" stop-opacity="0.28" />
                                        <stop offset="100%" stop-color="#7fd4c8" stop-opacity="0" />
                                    </linearGradient>
                                    <clipPath id="clipA">
                                        <path d="M0,0 h54 v167 a27,27 0 0 1 -27,27 a27,27 0 0 1 -27,-27 z" />
                                    </clipPath>
                                    <clipPath id="clipB">
                                        <path d="M0,0 h54 v167 a27,27 0 0 1 -27,27 a27,27 0 0 1 -27,-27 z" />
                                    </clipPath>
                                    <clipPath id="clipC">
                                        <path d="M0,0 h54 v167 a27,27 0 0 1 -27,27 a27,27 0 0 1 -27,-27 z" />
                                    </clipPath>
                                </defs>

                                <!-- partikel latar -->
                                <circle class="spark" cx="96" cy="120" r="2.5" fill="#7fd4c8" />
                                <circle class="spark spark-2" cx="504" cy="96" r="2" fill="#7fd4c8" />
                                <circle class="spark spark-3" cx="470" cy="210" r="2.5" fill="#7fd4c8" />

                                <!-- pipet + tetesan -->
                                <rect x="292" y="52" width="16" height="56" rx="8" fill="rgba(255,255,255,0.16)" />
                                <rect x="296" y="60" width="8" height="34" rx="4" fill="#2ad3b6" opacity="0.75" />
                                <circle class="drop" cx="300" cy="116" r="5" fill="#2ad3b6" />

                                <!-- tabung 1 -->
                                <g transform="translate(143,150)">
                                    <rect x="-6" y="-9" width="66" height="11" rx="5" fill="rgba(255,255,255,0.28)" />
                                    <path d="M0,0 h54 v167 a27,27 0 0 1 -27,27 a27,27 0 0 1 -27,-27 z"
                                        fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.35)" stroke-width="2" />
                                    <g clip-path="url(#clipA)">
                                        <rect class="liquid" x="0" y="0" width="54" height="194" fill="url(#liq)" />
                                        <circle class="bubble" cx="16" cy="170" r="3" fill="#bff3e9" />
                                        <circle class="bubble bubble-2" cx="36" cy="182" r="2.2" fill="#bff3e9" />
                                    </g>
                                </g>

                                <!-- tabung 2 -->
                                <g transform="translate(273,150)">
                                    <rect x="-6" y="-9" width="66" height="11" rx="5" fill="rgba(255,255,255,0.28)" />
                                    <path d="M0,0 h54 v167 a27,27 0 0 1 -27,27 a27,27 0 0 1 -27,-27 z"
                                        fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.35)" stroke-width="2" />
                                    <g clip-path="url(#clipB)">
                                        <rect class="liquid liquid-b" x="0" y="0" width="54" height="194" fill="url(#liq)" />
                                        <circle class="bubble bubble-3" cx="20" cy="176" r="2.6" fill="#bff3e9" />
                                        <circle class="bubble bubble-4" cx="38" cy="186" r="2" fill="#bff3e9" />
                                    </g>
                                </g>

                                <!-- tabung 3 -->
                                <g transform="translate(403,150)">
                                    <rect x="-6" y="-9" width="66" height="11" rx="5" fill="rgba(255,255,255,0.28)" />
                                    <path d="M0,0 h54 v167 a27,27 0 0 1 -27,27 a27,27 0 0 1 -27,-27 z"
                                        fill="rgba(255,255,255,0.07)" stroke="rgba(255,255,255,0.35)" stroke-width="2" />
                                    <g clip-path="url(#clipC)">
                                        <rect class="liquid liquid-c" x="0" y="0" width="54" height="194" fill="url(#liq)" />
                                        <circle class="bubble bubble-5" cx="18" cy="172" r="2.4" fill="#bff3e9" />
                                        <circle class="bubble bubble-6" cx="37" cy="184" r="2.8" fill="#bff3e9" />
                                    </g>
                                </g>

                                <!-- meja -->
                                <line x1="70" y1="348" x2="530" y2="348" stroke="rgba(255,255,255,0.28)" stroke-width="2" />

                                <!-- garis pindai -->
                                <rect class="scanner" x="70" y="0" width="460" height="64" fill="url(#beam)" />

                                <!-- grafik hasil -->
                                <path class="trace"
                                    d="M70,392 h96 l16,-26 l16,50 l16,-38 l14,14 h104 l16,-30 l14,44 l16,-28 h132"
                                    fill="none" stroke="#7fd4c8" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        @endif

                        <span class="stage-grid" aria-hidden="true"></span>
                        <p class="stage-caption">
                            <span>Proses pengujian</span>
                            <em id="stageLabel">{{ $hasVideo ? $clips[0]['label'] : 'Simulasi alur' }}</em>
                        </p>
                    </div>
                    </div>

                    <ol class="steps reveal reveal-right" data-delay="1" id="steps">
                        <li class="step is-active" data-clip="0">
                            <span class="step-index">01</span>
                <div>
                                <strong>Registrasi permohonan</strong>
                                <p>Data pemohon dan jenis pemeriksaan dicatat, nomor registrasi terbit otomatis.</p>
                            </div>
                            <span class="step-bar"><i></i></span>
                        </li>
                        <li class="step" data-clip="1">
                            <span class="step-index">02</span>
                            <div>
                                <strong>Penerimaan sampel</strong>
                                <p>Sampel diberi label dan kondisinya diperiksa sebelum masuk ke ruang uji.</p>
                            </div>
                            <span class="step-bar"><i></i></span>
                        </li>
                        <li class="step" data-clip="2">
                            <span class="step-index">03</span>
                            <div>
                                <strong>Pengujian laboratorium</strong>
                                <p>Analis mengerjakan pemeriksaan sesuai parameter dan metode yang ditetapkan.</p>
                            </div>
                            <span class="step-bar"><i></i></span>
                        </li>
                        <li class="step" data-clip="3">
                            <span class="step-index">04</span>
                            <div>
                                <strong>Verifikasi hasil</strong>
                                <p>Penyelia memeriksa hasil sebelum disetujui dan ditandatangani secara elektronik.</p>
                            </div>
                            <span class="step-bar"><i></i></span>
                        </li>
                        <li class="step" data-clip="3">
                            <span class="step-index">05</span>
                            <div>
                                <strong>Penerbitan laporan</strong>
                                <p>Laporan hasil uji dicetak atau diunduh lengkap dengan kop resmi laboratorium.</p>
                            </div>
                            <span class="step-bar"><i></i></span>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <section class="values" aria-label="Keunggulan layanan">
            <div class="values-inner stagger reveal">
                <div class="value">
                    <i class="fas fa-vial" aria-hidden="true"></i>
                    <strong>Akurat</strong>
                    <span>Alur verifikasi hasil yang konsisten dari pemeriksaan hingga cetak laporan.</span>
                </div>
                <div class="value">
                    <i class="fas fa-diagram-project" aria-hidden="true"></i>
                    <strong>Terintegrasi</strong>
                    <span>Data pasien, sampel, dan hasil saling terhubung dalam satu sistem.</span>
                </div>
                <div class="value">
                    <i class="fas fa-mobile-screen" aria-hidden="true"></i>
                    <strong>Siap operasional</strong>
                    <span>Akses desktop dan mobile untuk kebutuhan kerja di laboratorium maupun lapangan.</span>
                </div>
            </div>
        </section>

        <section class="about" id="tentang">
            <div class="reveal reveal-left">
                <h2>Tentang aplikasi</h2>
                <p>
                    SimaLab membantu laboratorium menjalankan layanan secara terkomputerisasi —
                    mulai dari registrasi hingga penerbitan hasil — agar alur kerja lebih tertib dan terdokumentasi.
                </p>
                    </div>
            <div class="support reveal reveal-right" data-delay="1">
                <strong>Dukungan sertifikasi elektronik</strong>
                <p>
                    Didukung Balai Sertifikasi Elektronik (BSrE) dan Badan Siber dan Sandi Negara (BSSN)
                    untuk penandatanganan dokumen secara elektronik.
                </p>
                <div class="bsre">
                    <img src="{{ asset('assets/admin/images/logo/logo-bsre-2.png') }}" alt="Logo BSrE">
                    <span>Tanda tangan elektronik tersertifikasi BSrE</span>
                </div>
            </div>
        </section>

        <section class="cta-band" aria-label="Mulai menggunakan SimaLab">
            <div class="cta-band-inner reveal">
                <div>
                    <h2>Siap mengelola laboratorium lebih tertib?</h2>
                    <p>Masuk ke sistem untuk mendaftarkan permohonan, memproses sampel, dan menerbitkan hasil uji.</p>
                </div>
                @auth
                    <a class="btn btn-primary" href="{{ route('home') }}">Ke Beranda</a>
                @else
                    <a class="btn btn-primary" href="{{ route('login-form') }}">Masuk ke SimaLab</a>
                @endauth
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
                <div>
                <h4>SimaLab</h4>
                <p>
                    Sistem Informasi Laboratorium untuk lingkungan pengujian —
                    mengelola alur permohonan, sampel, verifikasi, dan penerbitan hasil secara terintegrasi.
                </p>
                    </div>
            <div>
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="#alur">Alur pengujian</a></li>
                    <li><a href="#tentang">Tentang aplikasi</a></li>
                    @auth
                        <li><a href="{{ route('home') }}">Beranda sistem</a></li>
                    @else
                        <li><a href="{{ route('login-form') }}">Masuk ke sistem</a></li>
                    @endauth
                </ul>
                </div>
            <div>
                <h4>Lingkungan</h4>
                <p>
                    Instance pengujian / demo<br>
                    Data dan identitas bersifat contoh
                </p>
                <p style="margin-top: 12px;">
                    Digunakan untuk uji fitur dan pelatihan operator.
                </p>
            </div>
            </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} SimaLab — Lingkungan pengujian. Hak cipta dilindungi.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const supportsObserver = 'IntersectionObserver' in window;
            const wideScreen = window.innerWidth > 960;

            // Text reveal: pecah judul menjadi kata
            document.querySelectorAll('[data-split="words"]').forEach(function (el) {
                if (el.dataset.splitDone) return;
                const text = el.textContent.trim();
                const words = text.split(/\s+/);
                el.setAttribute('aria-label', text);
                el.innerHTML = words.map(function (word, i) {
                    const gap = i < words.length - 1 ? ' ' : '';
                    return '<span class="line-reveal"><span class="word-reveal" style="transition-delay:' +
                        (0.12 + i * 0.07) + 's">' + word + '</span></span>' + gap;
                }).join('');
                el.dataset.splitDone = '1';
            });

            // Munculkan elemen saat masuk layar
            const items = document.querySelectorAll('.reveal');
            if (reducedMotion || !supportsObserver) {
                items.forEach(function (el) { el.classList.add('is-visible'); });
                document.querySelectorAll('.word-reveal').forEach(function (w) {
                    w.classList.add('is-shown');
                });
            } else {
                const revealer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            revealer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -48px 0px' });

                items.forEach(function (el) { revealer.observe(el); });

                window.setTimeout(function () {
                    items.forEach(function (el) {
                        const box = el.getBoundingClientRect();
                        if (box.top < window.innerHeight && box.bottom > 0) {
                            el.classList.add('is-visible');
                        }
                    });
                }, 1000);
            }

            // Parallax ringan: latar, glow, dan kartu bergerak dengan kecepatan berbeda
            const parallaxNodes = Array.prototype.slice.call(document.querySelectorAll('[data-parallax]'));
            if (!reducedMotion && parallaxNodes.length && wideScreen) {
                let ticking = false;

                function updateParallax() {
                    const y = window.pageYOffset || document.documentElement.scrollTop;
                    parallaxNodes.forEach(function (node) {
                        const speed = parseFloat(node.getAttribute('data-parallax')) || 0;
                        const shift = Math.round(y * speed * 100) / 100;
                        if (node.classList.contains('brand-float')) {
                            node.style.transform = 'translate3d(0,' + shift + 'px,0)';
                        } else if (node.classList.contains('hero-media')) {
                            node.style.transform = 'translate3d(0,' + shift + 'px,0) scale(1.08)';
                        } else {
                            node.style.transform = 'translate3d(0,' + shift + 'px,0)';
                        }
                    });
                    ticking = false;
                }

                window.addEventListener('scroll', function () {
                    if (!ticking) {
                        ticking = true;
                        window.requestAnimationFrame(updateParallax);
                    }
                }, { passive: true });

                updateParallax();
            }

            // Latar hero: video baru dimuat di layar lebar dan saat animasi diizinkan
            const heroVideo = document.getElementById('heroVideo');
            if (heroVideo && !reducedMotion && wideScreen) {
                const source = heroVideo.querySelector('source[data-src]');
                if (source) {
                    source.src = source.dataset.src;
                    heroVideo.preload = 'auto';
                    heroVideo.load();
                    const started = heroVideo.play();
                    if (started && started.catch) { started.catch(function () {}); }
                }

                if (supportsObserver) {
                    const heroWatcher = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                const resumed = heroVideo.play();
                                if (resumed && resumed.catch) { resumed.catch(function () {}); }
                            } else {
                                heroVideo.pause();
                            }
                        });
                    }, { threshold: 0.05 });
                    heroWatcher.observe(heroVideo);
                }
            }

            // Rekaman lab + sticky storytelling
            const clipBox = document.getElementById('stageClips');
            const stage = document.getElementById('stage');
            const list = document.getElementById('steps');
            const clips = clipBox
                ? Array.prototype.slice.call(clipBox.querySelectorAll('.stage-video'))
                : [];
            const label = document.getElementById('stageLabel');
            let activeClip = 0;
            let scrollDriven = false;

            function playClip(index) {
                if (!clips.length) return;
                activeClip = ((index % clips.length) + clips.length) % clips.length;
                clips.forEach(function (video, i) {
                    video.classList.toggle('is-active', i === activeClip);
                    if (i !== activeClip) { video.pause(); }
                });

                const video = clips[activeClip];
                if (label && video.dataset.label) {
                    label.textContent = video.dataset.label;
                }
                try { video.currentTime = 0; } catch (e) { /* belum siap */ }
                if (!reducedMotion) {
                    const playing = video.play();
                    if (playing && playing.catch) { playing.catch(function () {}); }
                }

                const next = clips[(activeClip + 1) % clips.length];
                if (next !== video && next.preload !== 'auto') {
                    next.preload = 'auto';
                    next.load();
                }
            }

            if (clips.length === 1) {
                clips[0].loop = true;
            } else if (clips.length > 1) {
                clips.forEach(function (video, i) {
                    video.addEventListener('ended', function () {
                        if (!scrollDriven) {
                            playClip(i + 1);
                        } else if (i === activeClip && !reducedMotion) {
                            try { video.currentTime = 0; } catch (e) { /* ignore */ }
                            const again = video.play();
                            if (again && again.catch) { again.catch(function () {}); }
                        }
                    });
                });
            }

            if (clipBox && reducedMotion) {
                clips.forEach(function (video) { video.pause(); });
            } else if (clipBox && supportsObserver) {
                const stageWatcher = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            if (stage) { stage.classList.add('is-pinned'); }
                            const playing = clips[activeClip] && clips[activeClip].play();
                            if (playing && playing.catch) { playing.catch(function () {}); }
                        } else {
                            if (stage) { stage.classList.remove('is-pinned'); }
                            clips.forEach(function (video) { video.pause(); });
                        }
                    });
                }, { threshold: 0.15 });
                stageWatcher.observe(clipBox);
            }

            // Tahapan alur: scroll-driven di desktop, auto-cycle di mobile
            if (!list) return;

            const steps = Array.prototype.slice.call(list.querySelectorAll('.step'));
            if (!steps.length) return;

            const DURATION = 5000;
            let current = 0;
            let timer = null;

            function show(index, fromScroll) {
                current = ((index % steps.length) + steps.length) % steps.length;
                steps.forEach(function (step, i) {
                    step.classList.toggle('is-active', i === current);
                });

                const clipIndex = parseInt(steps[current].getAttribute('data-clip'), 10);
                if (!isNaN(clipIndex) && clips.length) {
                    if (clipIndex !== activeClip) {
                        playClip(clipIndex);
                    }
                }

                if (fromScroll) {
                    scrollDriven = true;
                }
            }

            function start() {
                if (timer || reducedMotion || scrollDriven) return;
                timer = setInterval(function () { show(current + 1); }, DURATION);
            }

            function stop() {
                clearInterval(timer);
                timer = null;
            }

            steps.forEach(function (step, i) {
                step.addEventListener('click', function () {
                    stop();
                    scrollDriven = false;
                    show(i);
                    start();
                });
            });

            // Sticky storytelling: langkah aktif mengikuti posisi scroll
            if (!reducedMotion && supportsObserver && wideScreen) {
                scrollDriven = true;
                stop();

                const stepWatcher = new IntersectionObserver(function (entries) {
                    let best = null;
                    let bestRatio = 0;
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && entry.intersectionRatio > bestRatio) {
                            bestRatio = entry.intersectionRatio;
                            best = entry.target;
                        }
                    });
                    if (best) {
                        const idx = steps.indexOf(best);
                        if (idx >= 0 && idx !== current) {
                            show(idx, true);
                        }
                    }
                }, {
                    root: null,
                    rootMargin: '-35% 0px -45% 0px',
                    threshold: [0.25, 0.5, 0.75]
                });

                steps.forEach(function (step) { stepWatcher.observe(step); });
            } else {
                list.addEventListener('mouseenter', stop);
                list.addEventListener('mouseleave', start);

                if (supportsObserver) {
                    const player = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) { start(); } else { stop(); }
                        });
                    }, { threshold: 0.25 });
                    player.observe(list);
                } else {
                    start();
                }
            }
        });
    </script>
</body>

</html>
