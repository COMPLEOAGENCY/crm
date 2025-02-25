<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat AI - Administration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .chat-container {
            height: calc(100vh - 200px);
            background-color: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .message {
            margin-bottom: 20px;
            max-width: 80%;
        }
        
        .message-ai {
            margin-right: auto;
        }
        
        .message-user {
            margin-left: auto;
        }
        
        .message-content {
            padding: 12px 16px;
            border-radius: 15px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message-ai .message-content {
            background-color: #f0f2f5;
        }
        
        .message-user .message-content {
            background-color: #007bff;
            color: white;
        }
        
        .chat-input {
            padding: 20px;
            background-color: white;
            border-top: 1px solid #dee2e6;
        }
        
        .typing-indicator {
            padding: 20px;
            color: #6c757d;
            font-style: italic;
            display: none;
        }

        .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Chat AI Assistant</h1>
                
                <div class="chat-container">
                    <div class="chat-messages" id="chatMessages">
                        <!-- Messages exemple -->
                        <div class="message message-ai">
                            <div class="message-content">
                                Bonjour ! Je suis votre assistant AI. Comment puis-je vous aider aujourd'hui ?
                            </div>
                        </div>
                        <div class="message message-user">
                            <div class="message-content">
                                Bonjour !
                            </div>
                        </div>
                    </div>
                    
                    <div class="typing-indicator" id="typingIndicator">
                        L'assistant est en train d'écrire...
                    </div>
                    
                    <div class="chat-input">
                        <form id="chatForm">
                            <div class="input-group">
                                <input type="text" class="form-control" id="messageInput" 
                                       placeholder="Écrivez votre message ici..." required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 4 required scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('messageInput');
            const chatMessages = document.getElementById('chatMessages');
            
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (message) {
                    // Ajouter le message de l'utilisateur
                    appendMessage(message, 'user');
                    messageInput.value = '';
                    
                    // Simuler la réponse de l'AI (à remplacer par l'appel API réel)
                    document.getElementById('typingIndicator').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('typingIndicator').style.display = 'none';
                        appendMessage("Je suis en train de traiter votre demande...", 'ai');
                    }, 1000);
                }
            });
            
            function appendMessage(content, type) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message message-${type}`;
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${content}
                    </div>
                `;
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>
</body>
</html>
