<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Poll;

class PollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $polls = Poll::with('pollOptions')->get();
        return response()->json(['message' => 'Registros de Enquetes ', 'data' => $polls]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $poll = Poll::create($request->only(['title', 'start_date', 'end_date']));

        return response()->json(["message" => 'Enquete criada com sucesso', 'data' => $poll], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Poll $poll)
    {
        $returnPoll = $poll::with('pollOptions')->find($poll->id);
        return response()->json([ 'message' => 'Registro de Enquete', 'data' => $returnPoll]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Poll $poll)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);

        // $poll = Poll::findOrFail($id);
        $poll->update($request->only(['title', 'start_date', 'end_date']));

        return response()->json(["message" => 'Enquete atualizada com sucesso', 'data' => $poll], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poll $poll)
    {
        // $poll = Poll::findOrFail($id);
        $poll->delete();
        return response()->json(['message' => 'Enquete deletada com sucesso']);
    }
}
