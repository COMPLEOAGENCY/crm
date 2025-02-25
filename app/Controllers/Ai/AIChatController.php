<?php

namespace Controllers\Ai;

use Framework\Controller;

class AiChatController extends Controller
{
    public function index()
    {
        return $this->view('admin.ai.chatai', [
            'title' => 'Chat AI Assistant'
        ]);
    }
}
