
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="shortcut icon" href="./favicon.ico" />
    <link rel="stylesheet" href="./static/css/style.css">
</head>
<div class="sidebar">
    <button class="new-chat-btn" onclick="location.reload();">
        <i class="fas fa-plus"></i> New chat
    </button>
        <div class="history">
            <!-- Chat history items will appear here -->
        </div>
        <div class="user-section">
            <div class="history-item">
                <i class="fas fa-user"></i> Your account
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="messages-container" id="messages">
            <div class="message assistant">
                <div class="avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <p>Hello! How can I assist you today?</p>
                </div>
            </div>
            <!-- Messages will appear here -->
        </div>
        
        <div class="input-container">
            <div class="input-box">
                <textarea id="input" placeholder="Message AI assistant..." rows="1"></textarea>
                <button id="send" class="send-button" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            El asistente de IA puede producir información inexacta sobre personas, lugares o hechos. </div>
        </div>
    </div>

    <script src="./static/js/marked.min.js"></script>
    <script src="./static/js/highlight.min.js"></script>
    <script src="./static/js/chat.js?v=03240800"></script>
    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            
            // Limit to max height
            if(this.scrollHeight > 200) {
                this.style.overflowY = 'auto';
                this.style.height = '200px';
            } else {
                this.style.overflowY = 'hidden';
            }
        });
        
        // Handle Enter key for sending messages
        textarea.addEventListener('keydown', function(e) {
            if(e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('send').click();
            }
        });
    </script>
</body>
</html>