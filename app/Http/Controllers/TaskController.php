<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        /**
         * Consulta el listado de tareas.
         */
        $tasks = Task::orderBy('created_at', 'desc')->get();

        foreach($tasks as $task){
            /**
             * Prepara la respuesta:
             * Añade información adicional al objeto $task.
             */
            $task['start_date_human'] = Carbon::parse($task->start_date)->translatedFormat('d F Y');
            //------------------------------ Propiedades ocultas ------------------------------
            $task->user->makeHidden('email_verified_at');
            $task->makeHidden('user_id');
        }

        /**
         * Envía una respuesta JSON con todos los detalles al usuario.
         */
        return response()->json([
            'success' => true,
            'message' => 'Listado de tareas generado.',
            'data' => [
                'tasks' => $tasks
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        // Obtiene al usuario actualmente autenticado y decodifica los datos JSON de la solicitud.
        $user = $request->user();
        $requestData = json_decode($request->getContent(), true);

        if($requestData){
            /**
             * Valida los datos entrantes de la solicitud.
             */
            $dataValidation = Validator::make($requestData, [
                'name' => ['required', 'string', 'max:45'],
                'description' => ['string', 'max:255'],
                'start_date' => ['required', 'date']
            ]);

            if($dataValidation->fails()){
                return response()->json([
                    'success' => false,
                    'step' => 1,
                    'errors' => $dataValidation->errors()->all()
                ], 400);
            }

            /**
             * Verifica que la fecha de inicio de la tarea que se creará no sea menor que la fecha del día actual.
             */
            $startDate = Carbon::parse($requestData['start_date']);
            $currentDate = Carbon::today();

            if($startDate < $currentDate){
                return response()->json([
                    'success' => false,
                    'step' => 2,
                    'errors' => ['La fecha de inicio no puede ser menor que la fecha actual.']
                ], 403);
            }

            /**
             * Crea la tarea.
             */
            $task = Task::create([
                'name' => $requestData['name'],
                'description' => $requestData['description'],
                'start_date' => $requestData['start_date'],
                'user_id' => $user->user_id,
                'status' => 0
            ]);

            /**
             * Prepara la respuesta:
             * Añade información adicional al objeto $task.
             */
            $task['start_date_human'] = Carbon::parse($task->start_date)->translatedFormat('d F Y');
            //------------------------------ Propiedades ocultas ------------------------------
            $task->user->makeHidden('email_verified_at');
            $task->makeHidden('user_id');

            /**
             * Envía una respuesta JSON con todos los detalles al usuario.
             */
            return response()->json([
                'success' => true,
                'message' => 'La tarea se ha creado.',
                'data' => [
                    'task' => $task
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        // Obtiene al usuario actualmente autenticado y decodifica los datos JSON de la solicitud.
        $user = $request->user();
        $requestData = json_decode($request->getContent(), true);

        if($requestData){
            /**
             * Valida los datos entrantes de la solicitud.
             */
            $dataValidation = Validator::make($requestData, [
                'name' => ['required', 'string', 'max:45'],
                'description' => ['string', 'max:255'],
                'start_date' => ['required', 'date']
            ]);

            if($dataValidation->fails()){
                return response()->json([
                    'success' => false,
                    'step' => 1,
                    'errors' => $dataValidation->errors()->all()
                ], 400);
            }

            /**
             * Verifica que la fecha de inicio de la tarea que se actualizará no sea menor que la fecha del día actual.
             */
            $startDate = Carbon::parse($requestData['start_date']);
            $currentDate = Carbon::today();

            if($startDate < $currentDate){
                return response()->json([
                    'success' => false,
                    'step' => 2,
                    'errors' => ['La fecha de inicio no puede ser menor que la fecha actual.']
                ], 403);
            }

            /**
             * Consulta y verifica la existencia de la tarea que se actualizará perteneciente al usuario.
             */
            $task = Task::where('task_id', $id)->where('user_id', $user->user_id)->first();

            if($task){
                $task->name = $requestData['name'];
                $task->description = $requestData['description'];
                $task->start_date = $requestData['start_date'];
                $task->save();

                /**
                 * Prepara la respuesta:
                 * Añade información adicional al objeto $task.
                 */
                $task['start_date_human'] = Carbon::parse($task->start_date)->translatedFormat('d F Y');
                //------------------------------ Propiedades ocultas ------------------------------
                $task->user->makeHidden('email_verified_at');
                $task->makeHidden('user_id');

                /**
                 * Envía una respuesta JSON con todos los detalles al usuario.
                 */
                return response()->json([
                    'success' => true,
                    'message' => 'La tarea se ha actualizado.',
                    'data' => [
                        'task' => $task
                    ]
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'step' => 3,
                    'errors' => ['Tarea no encontrada.']
                ], 404);
            }
        }else{
            return response()->json([
                'success' => false,
                'step' => 0,
                'errors' => ['La solicitud llegó sin datos.']
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
