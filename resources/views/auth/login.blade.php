<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Leon Plast</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#059669',
                        'primary-dark': '#047857',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-emerald-50/40 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 m-4 border border-slate-200">
        
        <div class="text-center mb-8">
            <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-industry text-2xl text-emerald-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Leon Plast</h1>
            <p class="text-sm text-slate-500 mt-1">Sistema de Control de Producción</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <div>
                <label for="nombre_usuario" class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</label>
                <div class="mt-1">
                    <input id="nombre_usuario" name="nombre_usuario" type="text" autocomplete="username" required value="{{ old('nombre_usuario') }}"
                           class="input-field">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">Contraseña</label>
                <div class="mt-1">
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="input-field">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-slate-600">
                        Recordar mi sesión
                    </label>
                </div>

                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-primary hover:text-primary-dark transition-colors">
                        ¿Olvidó su contraseña?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-150">
                    <i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
                </button>
            </div>
        </form>
    </div>

</body>
</html>