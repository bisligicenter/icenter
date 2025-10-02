<?php
// Customer Chat Widget Component
?>
<div id="chat-widget" class="chat-widget">
  <!-- Chat Toggle Button -->
  <div id="chat-toggle" class="chat-toggle">
    <i class="fas fa-comments"></i>
    <span class="chat-badge" id="chat-badge" style="display: none;">0</span>
    <div class="chat-pulse"></div>
  </div>
  
  <!-- Chat Window -->
  <div id="chat-window" class="chat-window" style="display: none;">
    <div class="chat-header">
      <div class="chat-header-content">
        <div class="chat-title">
          <img src="images/iCenter.png" alt="BISLIG iCENTER" class="chat-logo">
          <span>Live Support</span>
        </div>
        <div class="chat-status">
          <span class="status-dot"></span>
          <span class="status-text">Online</span>
        </div>
      </div>
      <div class="chat-controls">
        <button id="refresh-chat-btn" class="refresh-btn" title="Start New Chat">
          <i class="fas fa-redo"></i>
        </button>
        <button id="chat-close" class="chat-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
    
    <div class="chat-messages" id="chat-messages">
      <div class="chat-welcome" id="chat-welcome">
        <div class="welcome-header">
          <div class="welcome-avatar">
            <img src="images/iCenter.png" alt="BISLIG iCENTER" class="welcome-logo">
          </div>
          <h3>Welcome to BISLIG iCENTER!</h3>
          <p>Our support team is here to help you 24/7</p>
        </div>
        
        <!-- Customer Info Form -->
        <div class="customer-info-form" id="customer-info-form">
          <div class="form-header">
            <h4>Let's get started</h4>
            <p>Please provide your information to begin chatting</p>
          </div>
          <div class="form-group">
            <label for="customer-name-input">Your Name *</label>
            <input type="text" id="customer-name-input" placeholder="Enter your name" maxlength="50">
            <div class="input-icon">
              <i class="fas fa-user"></i>
            </div>
          </div>
          <div class="form-group">
            <label for="customer-email-input">Email (Optional)</label>
            <input type="email" id="customer-email-input" placeholder="Enter your email" maxlength="100">
            <div class="input-icon">
              <i class="fas fa-envelope"></i>
            </div>
          </div>
          <div class="form-group">
            <label for="customer-info-input">Additional Information</label>
            <textarea id="customer-info-input" placeholder="Tell us how we can help you..." maxlength="200"></textarea>
            <div class="input-icon">
              <i class="fas fa-comment"></i>
            </div>
          </div>
          <button id="start-chat-btn" class="start-chat-btn">
            <span class="btn-text">Start Chat</span>
            <span class="btn-icon">
              <i class="fas fa-paper-plane"></i>
            </span>
          </button>
        </div>
      </div>
    </div>
    
    <div class="chat-input-container">
      <div class="input-wrapper">
        <input type="text" id="chat-input" placeholder="Type your message..." maxlength="500">
        <div class="input-actions">
          <button id="chat-send" class="send-btn">
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>
      </div>
      <div class="typing-indicator" id="typing-indicator" style="display: none;">
        <div class="typing-dots">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <span class="typing-text">Admin is typing...</span>
      </div>
    </div>
  </div>
</div>

<style>
.chat-widget {
  position: fixed;
  bottom: 120px;
  right: 20px;
  z-index: 9999;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.chat-toggle {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.chat-toggle::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.chat-toggle:hover {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.chat-toggle:hover::before {
  opacity: 1;
}

.chat-toggle i {
  font-size: 24px;
  transition: transform 0.3s ease;
}

.chat-toggle:hover i {
  transform: scale(1.1);
}

.chat-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: linear-gradient(135deg, #ff4757 0%, #e63946 100%);
  color: white;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: bold;
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(255, 71, 87, 0.3);
  animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.chat-pulse {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(255, 71, 87, 0.3) 0%, rgba(230, 57, 70, 0.1) 100%);
  animation: pulse 2s infinite;
  opacity: 0;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 0.7;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.chat-window {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 380px;
  height: 520px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.chat-header {
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  color: white;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.chat-header::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
}

.chat-header-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.chat-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  font-size: 16px;
}

.chat-logo {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: white;
  padding: 3px;
}

.chat-status {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  opacity: 0.9;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #4ade80;
  border-radius: 50%;
  animation: statusPulse 2s infinite;
}

@keyframes statusPulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.chat-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.refresh-btn, .chat-close {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  cursor: pointer;
  font-size: 14px;
  padding: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.refresh-btn:hover, .chat-close:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

.chat-messages {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.2);
}

.chat-welcome {
  text-align: center;
  color: #6c757d;
  margin-bottom: 20px;
}

.welcome-header {
  margin-bottom: 30px;
}

.welcome-avatar {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  padding: 10px;
}

.welcome-logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 50%;
}

.welcome-header h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.welcome-header p {
  margin: 0;
  font-size: 14px;
  opacity: 0.8;
}

.customer-info-form {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.form-header {
  text-align: center;
  margin-bottom: 24px;
}

.form-header h4 {
  margin: 0 0 8px 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.form-header p {
  margin: 0;
  font-size: 13px;
  color: #6c757d;
}

.form-group {
  margin-bottom: 20px;
  position: relative;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #333;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px 16px 12px 40px;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-size: 14px;
  outline: none;
  transition: all 0.3s ease;
  background: #f8f9fa;
  resize: none;
}

.form-group input:focus,
.form-group textarea:focus {
  border-color: #000000;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

.form-group textarea {
  height: 80px;
  padding-top: 12px;
}

.input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 14px;
}

.form-group textarea + .input-icon {
  top: 24px;
  transform: none;
}

.start-chat-btn {
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  color: white;
  border: none;
  padding: 14px 24px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.start-chat-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s ease;
}

.start-chat-btn:hover::before {
  left: 100%;
}

.start-chat-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.btn-icon {
  transition: transform 0.3s ease;
}

.start-chat-btn:hover .btn-icon {
  transform: translateX(4px);
}

.message {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message.customer {
  align-items: flex-end;
}

.message.admin {
  align-items: flex-start;
}

.message-bubble {
  max-width: 85%;
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.4;
  word-wrap: break-word;
  position: relative;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.message.customer .message-bubble {
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  color: white;
  border-bottom-right-radius: 6px;
}

.message.admin .message-bubble {
  background: white;
  color: #333;
  border: 1px solid #e9ecef;
  border-bottom-left-radius: 6px;
}

.message-time {
  font-size: 11px;
  color: #6c757d;
  margin-top: 6px;
  margin-left: 12px;
  margin-right: 12px;
  opacity: 0.8;
}

.chat-input-container {
  padding: 20px;
  background: white;
  border-top: 1px solid #e9ecef;
  position: relative;
}

.input-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f8f9fa;
  border-radius: 25px;
  padding: 4px;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.input-wrapper:focus-within {
  border-color: #000000;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

#chat-input {
  flex: 1;
  border: none;
  border-radius: 20px;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  background: transparent;
  resize: none;
}

#chat-input::placeholder {
  color: #6c757d;
}

.send-btn {
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  color: white;
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.send-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.send-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.typing-indicator {
  padding: 12px 20px;
  color: #6c757d;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  animation: slideInUp 0.3s ease;
}

.typing-dots {
  display: flex;
  gap: 4px;
}

.typing-dots span {
  width: 6px;
  height: 6px;
  background: #6c757d;
  border-radius: 50%;
  animation: typingDot 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingDot {
  0%, 80%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

.typing-text {
  font-style: italic;
}

/* Loading States */
.loading-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 480px) {
  .chat-window {
    width: 320px;
    height: 450px;
    bottom: 70px;
  }
  
  .chat-toggle {
    width: 50px;
    height: 50px;
  }
  
  .chat-toggle i {
    font-size: 20px;
  }
  
  .chat-badge {
    width: 18px;
    height: 18px;
    font-size: 10px;
  }
}
</style>

<script>
class CustomerChatWidget {
  constructor() {
    this.sessionId = null;
    this.lastMessageId = 0;
    this.isConnected = false;
    this.pollInterval = null;
    this.sentButUnread = []; // Track sent messages that are not yet read
    this.customerInfo = {
      name: '',
      email: '',
      additionalInfo: ''
    };
    this.renderedMessageIds = new Set();
    this.renderedContentKeys = new Set();
    this.isSending = false; // prevent duplicate sends
    
    this.init();
  }
  
  init() {
    this.bindEvents();
    this.loadPersistedSession();
    this.startPolling();
  }
  
  bindEvents() {
    console.log('Binding events...');
    
    // Toggle chat window
    const chatToggle = document.getElementById('chat-toggle');
    if (chatToggle) {
      chatToggle.addEventListener('click', () => {
        this.toggleChat();
      });
      console.log('Chat toggle event bound');
    } else {
      console.error('Chat toggle element not found');
    }
    
    // Close chat window
    const chatClose = document.getElementById('chat-close');
    if (chatClose) {
      chatClose.addEventListener('click', () => {
        this.hideChat();
      });
      console.log('Chat close event bound');
    } else {
      console.error('Chat close element not found');
    }
    
    // Start chat button
    const startChatBtn = document.getElementById('start-chat-btn');
    if (startChatBtn) {
      startChatBtn.addEventListener('click', () => {
        console.log('Start chat button clicked');
        this.startChatWithInfo();
      });
      console.log('Start chat button event bound');
    } else {
      console.error('Start chat button element not found');
    }
    
    // Send message
    const chatSend = document.getElementById('chat-send');
    if (chatSend) {
      chatSend.addEventListener('click', () => {
        console.log('Send button clicked');
        this.sendMessage();
      });
      console.log('Chat send event bound');
    } else {
      console.error('Chat send element not found');
    }
    
    // Send message on Enter key
    const chatInput = document.getElementById('chat-input');
    if (chatInput) {
      chatInput.addEventListener('keydown', (e) => {
        // Only intercept plain Enter (no modifiers), and ignore IME composition
        if ((e.key === 'Enter' || e.keyCode === 13) && !e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey && !e.isComposing && e.keyCode !== 229) {
          e.preventDefault();
          this.sendMessage();
        }
      });

      // Add typing indicator logic
      let typingTimer;
      chatInput.addEventListener('input', () => {
        // User is typing, send a "start typing" event
        if (this.sessionId) {
          this.sendTypingEvent(true);
        }
        
        // Clear previous timer
        clearTimeout(typingTimer);
        // Set a timer to send "stop typing" event after user stops typing
        typingTimer = setTimeout(() => {
          if (this.sessionId) this.sendTypingEvent(false);
        }, 2000);
      });
      console.log('Chat input event bound');
    } else {
      console.error('Chat input element not found');
    }
    
    // Refresh chat button
    const refreshChatBtn = document.getElementById('refresh-chat-btn');
    if (refreshChatBtn) {
      refreshChatBtn.addEventListener('click', () => {
        this.refreshChat();
      });
      console.log('Refresh chat button event bound');
    } else {
      console.error('Refresh chat button element not found');
    }
  }
  
     async loadPersistedSession() {
     // Check if there's a persisted session
     const persistedSession = localStorage.getItem('chat_session_id');
     const persistedCustomerInfo = localStorage.getItem('chat_customer_info');
     
     if (persistedSession && persistedCustomerInfo) {
       try {
         const customerInfo = JSON.parse(persistedCustomerInfo);
         
         // Show loading state
         this.showMessageFeedback('Restoring previous session...', 'info');
         
         // Validate the session with the server
         const response = await fetch(`chat_api.php?action=validate_session&session_id=${persistedSession}`, {
           method: 'GET',
           headers: {
             'Cache-Control': 'no-cache',
             'Pragma': 'no-cache'
           }
         });
         
         if (!response.ok) {
           throw new Error(`HTTP error! status: ${response.status}`);
         }
         
         const data = await response.json();
         
         if (data.success && data.valid) {
           this.customerInfo = customerInfo;
           this.sessionId = persistedSession;
           this.isConnected = true;
           
           // Show chat interface with existing session
           document.getElementById('customer-info-form').style.display = 'none';
           
           // Update the welcome message without overwriting the entire HTML
           const welcomeElement = document.getElementById('chat-welcome');
           const welcomeText = welcomeElement.querySelector('p:first-child');
           if (welcomeText) {
             welcomeText.textContent = `👋 Welcome back, ${this.customerInfo.name}!`;
           }
           
           // Update the second paragraph if it exists
           const secondParagraph = welcomeElement.querySelector('p:nth-child(2)');
           if (secondParagraph) {
             secondParagraph.textContent = 'Your previous chat session has been restored.';
           }
           
           console.log('Chat session restored:', this.sessionId);
           this.showMessageFeedback('Session restored successfully!', 'success');
         } else {
           // Session is invalid, clear it and show form
           console.log('Persisted session is invalid:', data.message);
           this.clearPersistedSession();
           this.showCustomerInfoForm();
           this.showMessageFeedback('Previous session expired. Please start a new chat.', 'info');
         }
       } catch (error) {
         console.error('Error restoring chat session:', error);
         this.clearPersistedSession();
         this.showCustomerInfoForm();
         this.showMessageFeedback('Failed to restore session. Please start a new chat.', 'error');
       }
     } else {
       this.showCustomerInfoForm();
     }
   }
   
     showCustomerInfoForm() {
    document.getElementById('customer-info-form').style.display = 'block';
    // Don't overwrite the innerHTML since the form is already in the HTML structure
    // Just update the welcome message
    const welcomeElement = document.getElementById('chat-welcome');
    const welcomeText = welcomeElement.querySelector('p:first-child');
    if (welcomeText) {
      welcomeText.textContent = '👋 Welcome to BISLIG iCENTER!';
    }
  }
   
   persistSession() {
     if (this.sessionId) {
       localStorage.setItem('chat_session_id', this.sessionId);
       localStorage.setItem('chat_customer_info', JSON.stringify(this.customerInfo));
     }
   }
   
   clearPersistedSession() {
     localStorage.removeItem('chat_session_id');
     localStorage.removeItem('chat_customer_info');
   }
   
     async startSession() {
    console.log('startSession called with customer info:', this.customerInfo);
    
    try {
      const requestBody = `customer_name=${encodeURIComponent(this.customerInfo.name || 'Anonymous')}&customer_email=${encodeURIComponent(this.customerInfo.email || '')}`;
      console.log('Session request body:', requestBody);
      
      const response = await fetch('chat_api.php?action=start_session', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: requestBody
      });
      
      console.log('Session response status:', response.status);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('Session response data:', data);
      
      if (data.success) {
        this.sessionId = data.session_id;
        this.isConnected = true;
        console.log('Chat session started successfully:', this.sessionId);
        
        // Persist session to localStorage
        this.persistSession();
        
        // Show success feedback
        this.showMessageFeedback('Chat session started successfully!', 'success');
        
        // Update session with additional info if provided
        if (this.customerInfo.additionalInfo) {
          await this.updateSessionInfo();
        }
      } else {
        throw new Error(data.error || 'Failed to start chat session');
      }
    } catch (error) {
      console.error('Error starting chat session:', error);
      this.showMessageFeedback('Failed to start chat session. Please try again.', 'error');
      // Reset form for retry
      this.showCustomerInfoForm();
    }
  }
  
  async updateSessionInfo() {
    if (!this.sessionId || !this.customerInfo.additionalInfo) return;
    
    try {
      const response = await fetch('chat_api.php?action=update_session_info', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `session_id=${this.sessionId}&additional_info=${encodeURIComponent(this.customerInfo.additionalInfo)}`
      });
    } catch (error) {
      console.error('Error updating session info:', error);
    }
  }
  
  async sendTypingEvent(isTyping) {
    if (!this.sessionId) return;
    try {
      await fetch('chat_api.php?action=update_typing_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `session_id=${this.sessionId}&sender_type=customer&is_typing=${isTyping ? 1 : 0}`
      });
    } catch (e) {
      console.error('Failed to send typing event', e);
    }
  }
  
  async markAsRead() {
    if (!this.sessionId) return;
    try {
      await fetch('chat_api.php?action=mark_read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `session_id=${this.sessionId}&sender_type=admin`
      });
    } catch (e) {
      console.error('Failed to mark messages as read', e);
    }
  }
  
  startChatWithInfo() {
    const nameInput = document.getElementById('customer-name-input');
    const emailInput = document.getElementById('customer-email-input');
    const infoInput = document.getElementById('customer-info-input');
    
    // Get customer information
    this.customerInfo.name = nameInput.value.trim() || 'Anonymous';
    this.customerInfo.email = emailInput.value.trim();
    this.customerInfo.additionalInfo = infoInput.value.trim();
    
    console.log('startChatWithInfo called with customer info:', this.customerInfo);
    
    // Validate name is provided
    if (!this.customerInfo.name || this.customerInfo.name === 'Anonymous') {
      alert('Please enter your name to start the chat.');
      return;
    }
    
    // Hide the form and show chat interface
    document.getElementById('customer-info-form').style.display = 'none';
    
    // Update the welcome message without overwriting the entire HTML
    const welcomeElement = document.getElementById('chat-welcome');
    const welcomeText = welcomeElement.querySelector('p:first-child');
    if (welcomeText) {
      welcomeText.textContent = `👋 Welcome, ${this.customerInfo.name}!`;
    }
    
    console.log('Starting session...');
    // Start the session
    this.startSession();
  }
   
               refreshChat() {
       // Clear persisted session
       this.clearPersistedSession();
       
       // Reset chat state
       this.sessionId = null;
       this.isConnected = false;
       this.lastMessageId = 0;
       this.renderedMessageIds = new Set();
       this.renderedContentKeys = new Set();
       this.customerInfo = {
         name: '',
         email: '',
         additionalInfo: ''
       };
       
       // Clear messages but preserve the chat input container
       document.getElementById('chat-messages').innerHTML = `
         <div class="chat-welcome" id="chat-welcome">
           <div class="welcome-header">
             <div class="welcome-avatar">
               <i class="fas fa-headset"></i>
             </div>
             <h3>Welcome to BISLIG iCENTER!</h3>
             <p>Our support team is here to help you 24/7</p>
           </div>
           
           <!-- Customer Info Form -->
           <div class="customer-info-form" id="customer-info-form">
             <div class="form-header">
               <h4>Let's get started</h4>
               <p>Please provide your information to begin chatting</p>
             </div>
             <div class="form-group">
               <label for="customer-name-input">Your Name *</label>
               <input type="text" id="customer-name-input" placeholder="Enter your name" maxlength="50">
               <div class="input-icon">
                 <i class="fas fa-user"></i>
               </div>
             </div>
             <div class="form-group">
               <label for="customer-email-input">Email (Optional)</label>
               <input type="email" id="customer-email-input" placeholder="Enter your email" maxlength="100">
               <div class="input-icon">
                 <i class="fas fa-envelope"></i>
               </div>
             </div>
             <div class="form-group">
               <label for="customer-info-input">Additional Information</label>
               <textarea id="customer-info-input" placeholder="Tell us how we can help you..." maxlength="200"></textarea>
               <div class="input-icon">
                 <i class="fas fa-comment"></i>
               </div>
             </div>
             <button id="start-chat-btn" class="start-chat-btn">
               <span class="btn-text">Start Chat</span>
               <span class="btn-icon">
                 <i class="fas fa-paper-plane"></i>
               </span>
             </button>
           </div>
         </div>
       `;
       
       // Show customer info form
       this.showCustomerInfoForm();
       
       // Rebind events for new elements
       const startChatBtn = document.getElementById('start-chat-btn');
       if (startChatBtn) {
         startChatBtn.addEventListener('click', () => {
           this.startChatWithInfo();
         });
       }
       
       console.log('Chat refreshed - new session ready');
     }
  
     async sendMessage() {
     const input = document.getElementById('chat-input');
     const message = input.value.trim();

     // Guard against duplicate rapid submissions
     if (this.isSending) {
       return;
     }
     
     console.log('sendMessage called:', { message, sessionId: this.sessionId, isConnected: this.isConnected });
     
     if (!message) {
       console.log('Message is empty, returning');
       return;
     }
     
     if (!this.sessionId) {
       console.log('No session ID, returning');
       this.showMessageFeedback('Please start a chat session first.', 'error');
       return;
     }
     
     if (!this.isConnected) {
       console.log('Not connected, returning');
       this.showMessageFeedback('Chat session not connected. Please refresh and try again.', 'error');
       return;
     }
     
     // Disable send button and show loading state
     const sendBtn = document.getElementById('chat-send');
     const originalIcon = sendBtn.innerHTML;
     sendBtn.disabled = true;
     this.isSending = true;
     sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
     
     try {
       console.log('Sending message to API...');
       const requestBody = `session_id=${this.sessionId}&sender_type=customer&sender_name=${encodeURIComponent(this.customerInfo.name)}&message_text=${encodeURIComponent(message)}`;
       console.log('Request body:', requestBody);
       
       const response = await fetch('chat_api.php?action=send_message', {
         method: 'POST',
         headers: {
           'Content-Type': 'application/x-www-form-urlencoded',
         },
         body: requestBody
       });
       
       console.log('Response status:', response.status);
       console.log('Response headers:', response.headers);
       
       if (!response.ok) {
         throw new Error(`HTTP error! status: ${response.status}`);
       }
       
       const data = await response.json();
       console.log('Response data:', data);
       
       if (data.success) {
         input.value = '';
         // Add to unread tracking
         this.sentButUnread.push(data.message_id);
         // Render immediately for better UX (with de-duplication)
         this.addMessage('customer', this.customerInfo.name, message, null, data.message_id);
         // Update last message ID to prevent re-fetching
         this.lastMessageId = data.message_id;
       } else {
         throw new Error(data.error || 'Failed to send message');
       }
     } catch (error) {
       console.error('Error sending message:', error);
       this.showMessageFeedback('Failed to send message. Please try again.', 'error');
       // Restore message to input for retry
       input.value = message;
     } finally {
       // Restore send button
       sendBtn.disabled = false;
       sendBtn.innerHTML = originalIcon;
       this.isSending = false;
     }
   }
   
   showMessageFeedback(message, type = 'info') {
     // Remove existing feedback
     const existingFeedback = document.querySelector('.message-feedback');
     if (existingFeedback) {
       existingFeedback.remove();
     }
     
     // Create feedback element
     const feedback = document.createElement('div');
     feedback.className = `message-feedback ${type}`;
     feedback.textContent = message;
     
     // Add styles
     feedback.style.cssText = `
       position: fixed;
       top: 20px;
       right: 20px;
       padding: 12px 20px;
       border-radius: 8px;
       color: white;
       font-size: 14px;
       font-weight: 500;
       z-index: 10000;
       animation: slideIn 0.3s ease;
       max-width: 300px;
       word-wrap: break-word;
     `;
     
     // Set background color based on type
     if (type === 'success') {
       feedback.style.background = 'linear-gradient(135deg, #4ade80 0%, #22c55e 100%)';
     } else if (type === 'error') {
       feedback.style.background = 'linear-gradient(135deg, #f87171 0%, #ef4444 100%)';
     } else {
       feedback.style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
     }
     
     // Add to page
     document.body.appendChild(feedback);
     
     // Remove after 3 seconds
     setTimeout(() => {
       if (feedback.parentNode) {
         feedback.style.animation = 'slideOut 0.3s ease';
         setTimeout(() => feedback.remove(), 300);
       }
     }, 3000);
   }
  
  addMessage(senderType, senderName, messageText, timestamp = null, messageId = null) {
    const messagesContainer = document.getElementById('chat-messages');
    // De-duplication: skip if we've already rendered this messageId
    const idKey = messageId != null ? String(messageId) : null;
    if (idKey && this.renderedMessageIds.has(idKey)) {
      return;
    }

    // Secondary de-duplication by sender + content (only when no messageId)
    let contentKey = null;
    if (!idKey) {
      contentKey = `${senderType}|${this.escapeHtml(messageText)}`;
      if (this.renderedContentKeys.has(contentKey)) {
        return;
      }
    }

    // Ensure welcome/placeholder content is removed once a real message arrives
    const welcomeEl = document.getElementById('chat-welcome');
    if (welcomeEl) {
      welcomeEl.remove();
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${senderType}`;
    
    const time = timestamp || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageDiv.innerHTML = `
      <div class="message-bubble">${this.escapeHtml(messageText)}</div>
      <div class="message-time">${time}</div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    if (idKey) this.renderedMessageIds.add(idKey);
    if (contentKey) this.renderedContentKeys.add(contentKey);
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  async checkReadStatus() {
    if (!this.sessionId || this.sentButUnread.length === 0) return;

    try {
      const response = await fetch('chat_api.php?action=check_read_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `session_id=${this.sessionId}&message_ids=${this.sentButUnread.join(',')}`
      });
      const data = await response.json();
      if (data.success && data.read_ids.length > 0) {
        data.read_ids.forEach(readId => {
          const receipt = document.getElementById(`read-receipt-${readId}`);
          if (receipt) {
            receipt.innerHTML = '<i class="fas fa-check-double"></i>';
          }
          this.sentButUnread = this.sentButUnread.filter(id => id !== readId);
        });
      }
    } catch (error) {
      console.error('Error checking read status:', error);
    }
  }
  
     async loadMessages() {
     if (!this.sessionId) return;
     
     try {
       const response = await fetch(`chat_api.php?action=get_messages&session_id=${this.sessionId}&last_message_id=${this.lastMessageId}`, {
         method: 'GET',
         headers: {
           'Cache-Control': 'no-cache',
           'Pragma': 'no-cache'
         }
       });
       
       if (!response.ok) {
         throw new Error(`HTTP error! status: ${response.status}`);
       }
       
       const data = await response.json();
       
       if (data.success && data.messages.length > 0) {
         data.messages.forEach(msg => {
           const incomingId = Number(msg.message_id) || 0;
           if (incomingId > Number(this.lastMessageId || 0)) {
             this.addMessage(
               msg.sender_type,
               msg.sender_name,
               msg.message_text,
               new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
               msg.message_id
             );
             this.lastMessageId = incomingId;
           }
         });
         
         // Update unread count
         this.updateUnreadCount();
       }

        // Handle admin typing indicator
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.style.display = data.admin_is_typing ? 'flex' : 'none';
        }

        // Check for read status updates for messages sent by customer
        if (this.sentButUnread.length > 0) {
            this.checkReadStatus();
        }

     } catch (error) {
       console.error('Error loading messages:', error);
       // Only show error if it's not a network timeout
       if (!error.message.includes('timeout')) {
         this.showMessageFeedback('Connection issue. Retrying...', 'error');
       }
     }
   }
   
   showNewMessageNotification() {
     // Create notification sound (if supported)
     if ('AudioContext' in window || 'webkitAudioContext' in window) {
       try {
         const audioContext = new (window.AudioContext || window.webkitAudioContext)();
         const oscillator = audioContext.createOscillator();
         const gainNode = audioContext.createGain();
         
         oscillator.connect(gainNode);
         gainNode.connect(audioContext.destination);
         
         oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
         oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
         
         gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
         gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
         
         oscillator.start(audioContext.currentTime);
         oscillator.stop(audioContext.currentTime + 0.2);
       } catch (e) {
         // Ignore audio errors
       }
     }
     
     // Show visual notification
     this.showMessageFeedback('New message received!', 'info');
   }
  
  updateUnreadCount() {
    const badge = document.getElementById('chat-badge');
    const messages = document.querySelectorAll('.message.admin');
    let unreadCount = 0;
    
    messages.forEach(msg => {
      if (!msg.classList.contains('read')) {
        unreadCount++;
      }
    });
    
    if (unreadCount > 0) {
      badge.textContent = unreadCount;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }
  
  startPolling() {
    this.pollInterval = setInterval(() => {
      if (this.isConnected) {
        this.loadMessages();
      }
    }, 2000); // Poll every 2 seconds
  }
  
  toggleChat() {
    const chatWindow = document.getElementById('chat-window');
    if (chatWindow.style.display === 'none') {
      this.showChat();
    } else {
      this.hideChat();
    }
  }
  
  showChat() {
    document.getElementById('chat-window').style.display = 'flex';
    document.getElementById('chat-badge').style.display = 'none';
    
    // Mark messages as read
    this.markAsRead();
    document.querySelectorAll('.message.admin').forEach(msg => {
      msg.classList.add('read');
    });
  }
  
  hideChat() {
    document.getElementById('chat-window').style.display = 'none';
  }
  
  destroy() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  }
}

// Initialize chat widget when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  window.customerChat = new CustomerChatWidget();
});
</script> 