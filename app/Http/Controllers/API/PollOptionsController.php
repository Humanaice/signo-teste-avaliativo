<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PollOption;
use App\Events\PollOptionVoted;
use App\Http\Requests\PollOptionRequest as PollOptionsRequest;

class PollOptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method retrieves all poll options from the database
     * and returns them as a JSON response.
     */
    public function index()
    {
        $pollOptions = PollOption::all();
        return response()->json(['message' => 'Registros de Opções de Enquetes', 'data' => $pollOptions]);
    }

    /**
     * Store a newly created option for a poll.
     * 
     * @param PollOptionRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method validates the incoming request data,
     * creates a new poll option in the database,
     * and returns a JSON response with a success message and the created option data.
     */
    public function store(PollOptionsRequest $request)
    {
        $option = PollOption::create($request->only(['option_text', 'poll_id']));

        return response()->json(["message" => 'Opção criada com sucesso', 'data' => $option], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @param PollOption $pollOption
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method retrieves a specific poll option by its ID
     * and returns it as a JSON response.
     */
    public function show(PollOption $pollOption)
    {
        $option = $pollOption::with('poll')->find($pollOption->id);
        return response()->json(['message' => 'Registro de Opção de Enquete', 'data' => $option]);
    }

    /**
     * Update the specified option.
     * 
     * @param Request $request
     * @param PollOption $pollOption
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method validates the incoming request data,
     * updates the specified poll option in the database,
     * and returns a JSON response with a success message and the updated option data.
     */
    public function update(Request $request, PollOption $pollOption)
    {
        $request->validate([
            'option_text' => 'required|string|max:255',
        ]);

        $pollOption->update($request->only(['option_text']));

        return response()->json(["message" => 'Opção atualizada com sucesso', 'data' => $pollOption], 201);
    }

    /**
     * Remove the specified option from storage.
     * 
     * @param PollOption $pollOption
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method deletes the specified poll option from the database
     * and returns a JSON response with a success message.
     */
    public function destroy(PollOption $pollOption)
    {
        $pollOption->delete();
        return response()->json(['message' => 'Opção excluída com sucesso']);
    }

    /**
     * Register a vote for a poll option.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method increments the vote count for the specified poll option
     * and broadcasts an event to notify other users in real-time.
     * It returns a JSON response with a success message and the updated option data.
     */
    public function vote($id)
    {
        $pollOption = PollOption::findOrFail($id);
        $pollOption->increment('votes');

        // Emitir evento para atualização em tempo real
        broadcast(new PollOptionVoted($pollOption));
        return response()->json(['message' => 'Voto registrado com sucesso!', 'data' => $pollOption]);
    }
}
