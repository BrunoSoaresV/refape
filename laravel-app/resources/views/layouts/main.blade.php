<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>{{ $title ?? 'Refape' }}</title>

    <link rel="icon" href="{{ asset('assets/imagem1.png') }}">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/aos/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/boxicons/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/glightbox/css/glightbox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">

    @stack('styles')
</head>
<body>
    @yield('body')

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="{{ asset('assets/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('assets/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
