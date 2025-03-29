<?php

namespace App\Http\Controllers\Auth;


use App\User;
use App\Driver;
use App\Vehicle;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
            'role'          => 'required|in:cliente,conductor',
        ];

        // Si se registra como conductor, validar campos de vehículo
        if (isset($data['role']) && $data['role'] === 'conductor') {
            $rules['plate_number']    = 'required|string|max:255';
            $rules['license_number']  = 'required|string|max:255';
            $rules['vehicle_type']    = 'required|in:sedan,suv,moto';
            $rules['brand']           = 'required|string|max:255';
            $rules['model']           = 'required|string|max:255';
            $rules['service_type_id'] = 'required|exists:service_types,id';
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    // Creación del usuario y, en caso de conductor, creación de registros asociados
    protected function create(array $data)
    {
        // Procesar imagen de perfil
        $imagePath = null;
        if (isset($data['profile_image'])) {
            $image = $data['profile_image'];
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('uploads'), $imageName);
            $imagePath = 'uploads/' . $imageName;
        }

        // Crear el usuario
        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'role'          => $data['role'],
            'profile_image' => $imagePath,
        ]);

        // Si es conductor, crear el registro en drivers y vehicles
        if ($data['role'] === 'conductor') {
            $driver = Driver::create([
                'user_id'        => $user->id,
                'license_number' => $data['license_number'], // Licencia de conducir
                'vehicle_type'   => $data['vehicle_type'],
                'service_type_id'=> $data['service_type_id'],
            ]);
    
            Vehicle::create([
                'driver_id'    => $driver->id,
                'plate_number' => $data['plate_number'],
                'brand'        => $data['brand'],
                'model'        => $data['model'],
            ]);
        }

        return $user;
    }

    public function register(Request $request)
    {
        $data = $request->all();

        // Validar la solicitud manualmente
        $validator = $this->validator($data);
        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        $user = $this->create($data);
        $this->guard()->login($user);

        return redirect($this->redirectPath());
    }

    public function showRegistrationForm()
    {
        $serviceTypes = \App\ServiceType::where('status', true)->get();
        return view('auth.register')->with(compact('serviceTypes'));
    }
}
