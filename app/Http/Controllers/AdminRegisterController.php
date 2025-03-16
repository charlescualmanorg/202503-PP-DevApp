<?php

namespace App\Http\Controllers;

use App\User; // En Laravel 7 suele estar en App\User; si usas modelos en App\Models, ajusta según corresponda.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Providers\RouteServiceProvider;

class AdminRegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Donde redirigir después del registro.
     */
    //protected $redirectTo = '/admin/dashboard';
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Crea una nueva instancia del controlador.
     */
    public function __construct()
    {
        // Puedes restringir este registro a invitados o agregar otra protección
        $this->middleware('guest');
    }

    /**
     * Muestra el formulario de registro para administradores.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.admin-register');
    }

    /**
     * Valida los datos de entrada para el registro.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
            'role'          => ['required', 'in:admin'],
        ]);
    }

    /**
     * Crea un nuevo usuario administrador después de la validación.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // Procesar la imagen de perfil:
        $profileImagePath = null;
        if (request()->hasFile('profile_image')) {
            $file = request()->file('profile_image');
            // Se guarda en storage/app/public/profile_images (asegúrate de tener configurado el storage link)
            $profileImagePath = $file->store('profile_images', 'public');
        }

        return User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'role'          => 'admin', // Forzamos el rol de administrador.
            'profile_image' => $profileImagePath,
        ]);
    }

    /**
     * Procesa el registro del administrador.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // Crea el usuario administrador
        $user = $this->create($request->all());

        // Opcionalmente, inicia la sesión del usuario
        $this->guard()->login($user);

        return $this->registered($request, $user)
                    ?: redirect($this->redirectPath());
    }
}
