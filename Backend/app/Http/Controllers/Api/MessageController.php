<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * List messages for one event. Access is restricted: customers may only
     * read messages on events that belong to them.
     */
    public function index(Request $request, Event $event)
    {
        $this->authorizeEventAccess($request, $event);

        return response()->json($event->messages()->with('sender')->orderBy('created_at')->get());
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeEventAccess($request, $event);

        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please check the highlighted fields.', 'errors' => $validator->errors()], 422);
        }

        $message = Message::create([
            'event_id' => $event->id,
            'sender_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        return response()->json($message->load('sender'), 201);
    }

    private function authorizeEventAccess(Request $request, Event $event): void
    {
        $user = $request->user();

        if ($user->role === 'customer' && $event->customer->user_id !== $user->id) {
            abort(403, 'You do not have access to this event.');
        }
    }
}
