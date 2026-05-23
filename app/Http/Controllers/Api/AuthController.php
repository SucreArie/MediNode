<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'etablissement' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
            'condition' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'etablissement' => $request->etablissement ?? null,
            'role' => 'patient',
            'age' => $request->age,
            'telephone' => $request->telephone,
            'condition' => $request->condition,
            'status' => $request->status,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur créé',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $token = $user->createToken('login_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'role' => $user->role,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|nullable|min:6',
            'etablissement' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
            'condition' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'email', 'etablissement', 'age', 'telephone', 'condition', 'status']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $user,
        ]);
    }

    // Liste des utilisateurs (avec filtre optionnel par rôle : ?role=patient)
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        return response()->json($query->get());
    }

    // Ajout d'un utilisateur par un administrateur
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,doctor,receptionist,patient',
            'etablissement' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
            'condition' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'etablissement' => $request->etablissement,
            'age' => $request->age,
            'telephone' => $request->telephone,
            'condition' => $request->condition,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Utilisateur créé', 'user' => $user], 201);
    }

    // Détails d'un utilisateur spécifique
    public function show(User $user)
    {
        return response()->json($user);
    }

    // Modification d'un utilisateur spécifique par un administrateur
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|nullable|min:6',
            'role' => 'sometimes|required|in:admin,doctor,receptionist,patient',
            'etablissement' => 'nullable|string|max:255',
            'age' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
            'condition' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'email', 'role', 'etablissement', 'age', 'telephone', 'condition', 'status']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['message' => 'Utilisateur mis à jour', 'user' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
