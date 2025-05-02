<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Poll;
use App\Http\Requests\PollRequest;

class PollController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method retrieves all polls from the database,
     * including their associated poll options,
     * and returns them as a JSON response.
     */
    public function index()
    {
        $polls = Poll::with('pollOptions')->get();
        return response()->json(['message' => 'Registros de Enquetes ', 'data' => $polls]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param PollRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method validates the incoming request data,
     * creates a new poll in the database,
     * and returns a JSON response with a success message and the created poll data.
     */
    public function store(PollRequest $request)
    {
        $poll = Poll::create($request->only(['title', 'start_date', 'end_date']));
        return response()->json(["message" => 'Enquete criada com sucesso', 'data' => $poll], 201);
    }

    /**
     * Display the specified resource.
     * 
     * @param Poll $poll
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method retrieves a specific poll by its ID,
     * including its associated poll options,
     * and returns it as a JSON response.
     */
    public function show(Poll $poll)
    {
        $returnPoll = $poll::with('pollOptions')->find($poll->id);
        return response()->json([ 'message' => 'Registro de Enquete', 'data' => $returnPoll]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param Poll $poll
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method validates the incoming request data,
     * updates the specified poll in the database,
     * and returns a JSON response with a success message and the updated poll data.
     */
    public function update(Request $request, Poll $poll)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);

        $poll->update($request->only(['title', 'start_date', 'end_date']));

        return response()->json(["message" => 'Enquete atualizada com sucesso', 'data' => $poll], 201);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param Poll $poll
     * @return \Illuminate\Http\JsonResponse
     * 
     * This method deletes the specified poll from the database
     * and returns a JSON response with a success message.
     */
    public function destroy(Poll $poll)
    {
        $poll->delete();
        return response()->json(['message' => 'Enquete deletada com sucesso']);
    }
}
