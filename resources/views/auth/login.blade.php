<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema FICCT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border-radius: 15px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
            background: white;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-calendar-check fs-1"></i>
                        <h3 class="mt-3 mb-0">Sistema de Horarios</h3>
                        <p class="mb-0 mt-2 opacity-75">FICCT - Universidad</p>
                    </div>
                    <div class="login-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif
                        
                        <form method="POST" action="/login">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="username" class="form-label fw-medium">
                                    <i class="bi bi-person me-2"></i>Usuario
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username') }}" 
                                       required 
                                       autofocus
                                       placeholder="Ingrese su usuario">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-medium">
                                    <i class="bi bi-lock me-2"></i>Contraseña
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="Ingrese su contraseña">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-login w-100 btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-white mt-4 opacity-75">
                    <small>© 2025 Sistema de Gestión de Horarios - FICCT</small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>