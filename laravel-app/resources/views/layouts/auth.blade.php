<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>{{ $title ?? 'Refape' }}</title>

    <link rel="icon" href="{{ asset('assets/imagem1.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/boxicons/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/quill/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/quill/quill.bubble.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/remixicon/remixicon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/style1.css') }}">

    @stack('styles')
</head>
<body>
    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="{{ asset('assets/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('assets/main1.js') }}"></script>
    @stack('scripts')
</body>
</html>
