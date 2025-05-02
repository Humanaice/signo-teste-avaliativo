<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PollOption;
use App\Events\PollOptionVoted;

class PollOptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pollOptions = PollOption::all();
        return response()->json(['message' => 'Registros de Opções de Enquetes', 'data' => $pollOptions]);
    }

    /**
     * Store a newly created option for a poll.
     * 
     */
    public function store(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|exists:polls,id',
            'option_text' => 'required|string|max:255',
        ]);

        $option = PollOption::create($request->only(['poll_id', 'option_text']));

        return response()->json(["message" => 'Opção criada com sucesso', 'data' => $option], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PollOption $pollOption)
    {
        $option = $pollOption::with('poll')->find($pollOption->id);
        return response()->json(['message' => 'Registro de Opção de Enquete', 'data' => $option]);
    }

    /**
     * Update the specified option.
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
     */
    public function destroy(PollOption $pollOption)
    {
        $pollOption->delete();
        return response()->json(['message' => 'Opção excluída com sucesso']);
    }

    /**
     * Register a vote for a poll option.
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
