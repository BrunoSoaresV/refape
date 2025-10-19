@extends('layouts.auth')

@section('content')
<section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                <div class="d-flex justify-content-center py-4">
                    <a href="{{ route('register') }}" class="logo d-flex align-items-center w-auto">
                        <span class="d-none d-lg-block">Cadastro</span>
                    </a>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="pt-4 pb-2">
                            <h5 class="card-title text-center pb-0 fs-4">Criar uma conta</h5>
                            <p class="text-center small">Insira seus dados para a criação da conta</p>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                            </div>
                        @endif
                        <form class="row g-3 needs-validation" novalidate method="POST" action="{{ route('register.submit') }}">
                            @csrf
                            <div class="col-12">
                                <label for="nome" class="form-label">Nome da empresa</label>
                                <input type="text" name="nome" class="form-control" id="nome" placeholder="Informe o nome da sua empresa" value="{{ old('nome') }}" required>
                                <div class="invalid-feedback">Por favor, coloque o nome da sua empresa.</div>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">E-mail</label>
                                <div class="input-group has-validation">
                                    <input type="email" name="email" class="form-control" id="email" placeholder="Informe seu e-mail" value="{{ old('email') }}" required>
                                    <div class="invalid-feedback">Por favor, coloque um e-mail válido!</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="cnpj" class="form-label">CNPJ</label>
                                <input type="text" name="cnpj" class="form-control" id="cnpj" placeholder="Informe seu CNPJ" value="{{ old('cnpj') }}" required maxlength="18" onkeyup="formataCnpj(this,event)" onblur="if(!validarCNPJ(this.value)){alert('CNPJ inválido!');this.value='';}">
                                <div class="invalid-feedback">Por favor, coloque seu CNPJ.</div>
                            </div>
                            <div class="col-12">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" name="password" class="form-control" id="senha" placeholder="Informe sua senha" required>
                                <div class="invalid-feedback">Por favor, coloque sua senha.</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                            </div>
                            <div class="col-12">
                                <p class="small mb-0">Já possui uma conta? <a href="{{ route('login') }}">Login</a></p>
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
    function formataCnpj(campo, event) {
        const tecla = event.keyCode;
        let valor = campo.value.replace(/\D/g, '');
        if (tecla !== 14) {
            if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d)/, '$1.$2');
            }
            if (valor.length > 5) {
                valor = valor.replace(/(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            }
            if (valor.length > 8) {
                valor = valor.replace(/(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4');
            }
            if (valor.length > 12) {
                valor = valor.replace(/(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');
            }
        }
        campo.value = valor;
    }

    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');
        if (cnpj.length !== 14) {
            return false;
        }
        if (/^(\d)\1{13}$/.test(cnpj)) {
            return false;
        }
        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        const digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) {
                pos = 9;
            }
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado !== parseInt(digitos.charAt(0), 10)) {
            return false;
        }
        tamanho += 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) {
                pos = 9;
            }
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        return resultado === parseInt(digitos.charAt(1), 10);
    }
</script>
@endpush
