<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        // Decodifica los datos JSON de la solicitud.
        $requestData = json_decode($request->getContent(), true);

        if($requestData){
            /**
             * Valida los datos entrantes de la solicitud.
             */
            $dataValidation = Validator::make($requestData, [
                'name' => ['required', 'string', 'max:45'],
                'email' => ['required', 'email', 'max:255', Rule::unique(User::class)],
                'password' => ['required', 'string', 'min:8', 'confirmed']
            ]);

            if($dataValidation->fails()){
                return response()->json([
                    'success' => false,
                    'step' => 1,
                    'errors' => $dataValidation->errors()->all()
                ], 400);
            }

            /**
             * Crea el usuario.
             */
            User::create([
                'name' => $requestData['name'],
                'email' => $requestData['email'],
                'password' => Hash::make($requestData['password']),
                'status' => 1
            ]);

            /**
             * Consulta el usuario previamente creado.
             */
            $user = User::where('email', $requestData['email'])->first();

            // Añade información adicional al objeto $user.
            $parts = explode(' ', $user->name);
            $user['short_name'] = strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? '', 0, 1));

            /**
             * Crea un token de autenticación para el usuario.
             */
            $token = $user->createToken('auth_token')->plainTextToken;

            /**
             * Envía una respuesta JSON con todos los detalles al usuario.
             */
            return response()->json([
                'success' => true,
                'auth' => true,
                'message' => 'Te has registrado exitosamente.',
                'data' => [
                    'user' => $user,
                    'token' => [
                        'type' => 'Bearer',
                        'value' => $token
                    ]
                ]
            ], 201);
        }else{
            return response()->json([
                'success' => false,
                'step' => 0,
                'errors' => ['La solicitud llegó sin datos.']
            ], 400);
        }
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        // Decodifica los datos JSON de la solicitud.
        $requestData = json_decode($request->getContent(), true);

        if($requestData){
            /**
             * Valida los datos entrantes de la solicitud.
             */
            $dataValidation = Validator::make($requestData, [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string']
            ]);

            if($dataValidation->fails()){
                return response()->json([
                    'success' => false,
                    'step' => 1,
                    'errors' => $dataValidation->errors()->all()
                ], 400);
            }

            /**
             * Consulta y verifica la existencia del usuario con el correo electrónico proporcionado y que la contraseña
             * de acceso sea correcta.
             */
            $user = User::where('email', $requestData['email'])->first();

            if(!$user || !Hash::check($requestData['password'], $user->password)){
                return response()->json([
                    'success' => false,
                    'step' => 2,
                    'errors' => ['Correo o contraseña incorrectos.']
                ], 401);
            }

            /**
             * Prepara la respuesta:
             * Añade información adicional al objeto $user.
             */
            $parts = explode(' ', $user->name);
            $user['short_name'] = strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? '', 0, 1));

            /**
             * Crea un token de autenticación para el usuario.
             */
            $token = $user->createToken('auth_token')->plainTextToken;

            /**
             * Envía una respuesta JSON con todos los detalles al usuario.
             */
            return response()->json([
                'success' => true,
                'auth' => true,
                'message' => 'Has iniciado sesión.',
                'data' => [
                    'user' => $user,
                    'token' => [
                        'type' => 'Bearer',
                        'value' => $token
                    ]
                ]
            ]);
        }else{
            return response()->json([
                'success' => false,
                'step' => 0,
                'errors' => ['La solicitud llegó sin datos.']
            ], 400);
        }
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        /**
         * Elimina el token de autenticación del usuario.
         */
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Has cerrado sesión.'
        ]);
    }
}
