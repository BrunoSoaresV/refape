@extends('layouts.main', ['title' => 'Home'])

@section('body')
<header id="header" class="fixed-top header-transparent">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo">
            <h1 class="text-light"><a href="{{ route('dashboard') }}"><span>{{ $empresa->nome ?? 'Refape' }}</span></a></h1>
        </div>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto" href="{{ route('employees.create') }}">Cadastrar funcionários</a></li>
                <li><a class="nav-link scrollto" href="{{ route('employees.index') }}">Verificar funcionários cadastrados</a></li>
                <li><a class="nav-link scrollto" href="{{ route('attendance.capture') }}">Realizar Ponto</a></li>
                <li><a class="nav-link scrollto" href="{{ route('attendance.index') }}">Verificar os pontos registrados</a></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link">Sair</button>
                    </form>
                </li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
    </div>
</header>
<section id="hero">
    <div class="hero-container" data-aos="fade-up">
        <h1>Bem-vindo ao Refape</h1>
    </div>
</section>
<main id="main">
    <section id="portfolio" class="portfolio">
        <div class="container">
            <div class="section-title" data-aos="fade-in" data-aos-delay="100">
                <h2>Reconhecimento Facial de Pessoas</h2>
                <p>Primeiramente, cadastre os funcionários. Após isso, clique no botão de realizar ponto.</p>
            </div>
        </div>
    </section>
</main>
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row"></div>
        </div>
    </div>
    <div class="container">
        <div class="copyright">
            &copy; Copyright <strong><span>Refape</span></strong>. Todos os direitos reservados.
        </div>
        
    </div>
</footer>
@endsection
