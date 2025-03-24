<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'cliente',
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        public function edit($id)
        {
            // Cargar el usuario autenticado y, en caso de conductor, cargar también sus datos
            $user = User::findOrFail($id);
            //dd($user);
            return view('admin.users.edit', compact('user'));
        }

    public function editprofile()
    {
        // Cargar el usuario autenticado y, en caso de conductor, cargar también sus datos
        $user = auth()->user();
        $user->load('driver');
        return view('admin.users.updateprofile', compact('user'));
    }


    /**
     * metodo usado por las opciones administrativas
     */
    public function update(Request $request, User $user)
    {

        //
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'active'    => 'required|boolean',
            // La contraseña es opcional; si se proporciona, se validará
            //'password'  => 'nullable|string|min:6|confirmed',
        ]);
        //dd($request);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->active = $request->active;
        // Solo se actualiza la contraseña si se ingresó
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        //dd($user);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }


    public function updateProfile(Request $request)
    {
        // Validar y actualizar los datos del usuario y, si es conductor, de su modelo relacionado
        $user = auth()->user();
    
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password'          => 'nullable|string|min:8|confirmed',
        ]);
    
        // Actualizar imagen de perfil si se cargó un archivo
        if ($request->hasFile('profile_image')) {
            // Guarda la imagen en public/uploads
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('uploads'), $imageName);
            $data['profile_image'] = 'uploads/' . $imageName;
        }
    
        // Actualizar contraseña si se ingresó
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
    
        // Actualizar usuario
        $user->update($data);
    
        // Si el usuario es conductor, actualizar los datos adicionales
        if ($user->role === 'conductor') {
            $driverData = $request->validate([
                'license_number' => 'required|string|max:255',
                'vehicle_type'   => 'required|in:sedan,suv,moto',
                'brand'          => 'nullable|string|max:255',
                'model'          => 'nullable|string|max:255',
                'plate_number'   => 'nullable|string|max:255',
            ]);
            if ($user->driver) {
                $user->driver->update($driverData);
            } else {
                // Si aún no existe, crearlo
                $user->driver()->create($driverData);
            }
        }
    
        return redirect()->back()->with('status', 'Perfil actualizado correctamente.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
