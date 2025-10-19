@extends('layouts.auth')

@section('content')
<section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                <div class="d-flex justify-content-center py-4">
                    <a href="{{ route('login') }}" class="logo d-flex align-items-center w-auto">
                        <span class="d-none d-lg-block">Refape</span>
                    </a>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="pt-4 pb-2">
                            <h5 class="card-title text-center pb-0 fs-4">Logar com sua conta</h5>
                            <p class="text-center small">Entre com seu e-mail e senha para login</p>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                            </div>
                        @endif
                        <form class="row g-3 needs-validation" novalidate method="POST" action="{{ route('login.submit') }}">
                            @csrf
                            <div class="col-12">
                                <label for="email" class="form-label">E-mail</label>
                                <div class="input-group has-validation">
                                    <input type="email" name="email" class="form-control" id="email" placeholder="Informe seu e-mail" value="{{ old('email') }}" required autofocus>
                                    <div class="invalid-feedback">Por favor, entre com o seu e-mail.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" name="password" class="form-control" id="senha" placeholder="Senha" required>
                                <div class="invalid-feedback">Por favor, entre com sua senha.</div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" onclick="toggleSenha()" id="msenha">
                                    <label class="form-check-label" for="msenha">Mostrar senha</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </div>
                            <div class="col-12">
                                <p class="small mb-0">Não tem uma conta? <a href="{{ route('register') }}">Criar uma conta</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function toggleSenha() {
        const senha = document.getElementById('senha');
        senha.type = senha.type === 'password' ? 'text' : 'password';
    }
</script>
@endpush
