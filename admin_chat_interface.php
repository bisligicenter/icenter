<?php
// Admin Chat Interface Component
?>
<!-- Admin Chat Toggle Button (Hidden - now using header button) -->
<div id="admin-chat-toggle" class="admin-chat-toggle" style="display: none !important;">
  <i class="fas fa-headset" style="font-size: 24px !important; color: white !important;"></i>
  <span style="font-size: 24px !important; color: white !important; display: none;" class="fallback-icon">🎧</span>
  <span style="font-size: 16px !important; color: white !important; display: none; font-weight: bold;" class="text-fallback">CHAT</span>
  <span class="admin-chat-badge" id="admin-chat-badge" style="display: none;">0</span>
  <div class="admin-chat-pulse"></div>
</div>

<div id="admin-chat-container" class="admin-chat-container" style="display: none;">
  <!-- Chat Header -->
  <div class="chat-header">
          <div class="chat-header-content">
        <div class="chat-title">
          <img src="images/iCenter.png" alt="BISLIG iCENTER" class="chat-logo">
          <span>Admin Dashboard</span>
        </div>
        <div class="chat-status">
          <span class="status-dot"></span>
          <span class="status-text">Active</span>
        </div>
      </div>
    <div class="chat-controls">
      <button id="toggle-chat" class="chat-toggle-btn">
        <i class="fas fa-minus"></i>
      </button>
    </div>
  </div>
  
  <!-- Chat Content -->
  <div class="chat-content" id="chat-content">
    <!-- Sessions List -->
    <div class="sessions-list" id="sessions-list">
      <div class="sessions-header">
        <div class="sessions-title">
          <h3>Chat Sessions</h3>
          <div class="sessions-stats">
            <span class="active-sessions" id="active-sessions">0</span>
            <span class="total-sessions" id="total-sessions">0</span>
          </div>
        </div>
        <div class="sessions-actions">
          <button id="refresh-sessions" class="refresh-sessions-btn" title="Refresh">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>
      <div class="sessions-container" id="sessions-container">
        <!-- Sessions will be loaded here -->
      </div>
    </div>
    
    <!-- Chat Messages -->
    <div class="chat-messages-container" id="chat-messages-container" style="display: none;">
      <div class="chat-messages-header">
        <button id="back-to-sessions" class="back-btn">
          <i class="fas fa-arrow-left"></i>
        </button>
        <div class="customer-info">
          <span id="current-customer">Select a customer</span>
          <div class="customer-details" id="customer-details"></div>
        </div>
        <div class="chat-actions">
          <button id="delete-session" class="delete-session-btn" title="Delete conversation">
            <i class="fas fa-trash"></i>
          </button>
          <button id="close-session" class="close-session-btn" title="Close session">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="messages-area" id="messages-area">
        <!-- Messages will be loaded here -->
      </div>
      
      <div class="message-input-area">
        <div class="input-wrapper">
          <input type="text" id="admin-message-input" placeholder="Type your message..." maxlength="500">
          <div class="input-actions">
            <button id="admin-send-btn" class="send-btn">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </div>
        <div class="typing-indicator" id="admin-typing-indicator" style="display: none; padding: 8px 20px; font-size: 12px; color: #6c757d; font-style: italic;">
            <div class="typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
            <span class="typing-text">Customer is typing...</span>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* Admin Chat Toggle Button */
.admin-chat-toggle {
  position: fixed !important;
  bottom: 20px !important;
  right: 20px !important;
  width: 60px !important;
  height: 60px !important;
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%) !important;
  border-radius: 50% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  color: white !important;
  cursor: pointer !important;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
  z-index: 9999 !important;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
  position: relative !important;
  overflow: hidden !important;
}

.admin-chat-toggle::before {
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

.admin-chat-toggle:hover {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.admin-chat-toggle:hover::before {
  opacity: 1;
}

.admin-chat-toggle i {
  font-size: 24px;
  transition: transform 0.3s ease;
}

.admin-chat-toggle:hover i {
  transform: scale(1.1);
}

.admin-chat-badge {
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

.admin-chat-pulse {
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

.admin-chat-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 420px;
  height: 620px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  z-index: 1000;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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

.chat-toggle-btn {
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

.chat-toggle-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

.chat-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sessions-list {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.sessions-header {
  padding: 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.sessions-title {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sessions-title h3 {
  margin: 0;
  font-size: 18px;
  color: #333;
  font-weight: 600;
}

.sessions-stats {
  display: flex;
  gap: 12px;
  font-size: 12px;
}

.active-sessions {
  color: #4ade80;
  font-weight: 600;
}

.total-sessions {
  color: #6c757d;
}

.sessions-actions {
  display: flex;
  gap: 8px;
}

.refresh-sessions-btn {
  background: rgba(0, 0, 0, 0.05);
  border: none;
  color: #6c757d;
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
}

.refresh-sessions-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
  transform: scale(1.1);
}

.sessions-container {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  scroll-behavior: smooth;
}

.sessions-container::-webkit-scrollbar {
  width: 6px;
}

.sessions-container::-webkit-scrollbar-track {
  background: transparent;
}

.sessions-container::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.sessions-container::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.2);
}

.session-item {
  padding: 16px;
  border-radius: 16px;
  margin-bottom: 12px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid #e9ecef;
  background: white;
  position: relative;
  overflow: hidden;
}

.session-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.01) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.session-item:hover {
  background: #f8f9fa;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: #dee2e6;
}

.session-item:hover::before {
  opacity: 1;
}

.session-item.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.session-item.unread {
  border-left: 4px solid #ff4757;
  background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
}

.session-item.unread::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(135deg, #ff4757 0%, #e63946 100%);
  border-radius: 0 2px 2px 0;
}

.session-customer {
  font-weight: 600;
  margin-bottom: 8px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.session-customer i {
  font-size: 12px;
  opacity: 0.7;
}

.session-item.active .session-customer {
  color: white;
}

.session-customer-info {
  font-size: 11px;
  color: #6c757d;
  margin-bottom: 8px;
  line-height: 1.4;
}

.session-item.active .session-customer-info {
  color: rgba(255, 255, 255, 0.8);
}

.session-last-message {
  font-size: 12px;
  color: #6c757d;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-bottom: 6px;
}

.session-item.active .session-last-message {
  color: rgba(255, 255, 255, 0.8);
}

.session-time {
  font-size: 10px;
  color: #adb5bd;
  display: flex;
  align-items: center;
  gap: 4px;
}

.session-item.active .session-time {
  color: rgba(255, 255, 255, 0.6);
}

.unread-badge {
  background: linear-gradient(135deg, #ff4757 0%, #e63946 100%);
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: bold;
  margin-left: auto;
  box-shadow: 0 2px 6px rgba(255, 71, 87, 0.3);
  animation: badgePulse 2s infinite;
}

.session-typing-indicator {
  font-size: 11px;
  color: #065f46; /* A green color */
  gap: 4px;
  align-items: center;
  margin-top: 4px;
  font-style: italic;
}
/* Closed session styles */
.closed-session {
  opacity: 0.7;
  background: #f8f9fa;
  border-left: 3px solid #6c757d;
}

.closed-session:hover {
  opacity: 0.9;
}

.closed-badge {
  background: #6c757d;
  color: white;
  font-size: 9px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 8px;
  font-weight: bold;
  text-transform: uppercase;
}

.chat-messages-container {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.chat-messages-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  align-items: center;
  gap: 12px;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.customer-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.customer-info span {
  font-weight: 600;
  font-size: 14px;
  color: #333;
}

.customer-details {
  font-size: 11px;
  color: #6c757d;
  line-height: 1.3;
}

.chat-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.back-btn {
  background: rgba(0, 0, 0, 0.05);
  border: none;
  color: #6c757d;
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
}

.back-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
  transform: scale(1.1);
}

.delete-session-btn {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
}

.delete-session-btn:hover {
  background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.close-session-btn {
  background: linear-gradient(135deg, #ff4757 0%, #e63946 100%);
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(255, 71, 87, 0.3);
}

.close-session-btn:hover {
  background: linear-gradient(135deg, #e63946 0%, #d63384 100%);
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(255, 71, 87, 0.4);
}

.messages-area {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  scroll-behavior: smooth;
}

.messages-area::-webkit-scrollbar {
  width: 6px;
}

.messages-area::-webkit-scrollbar-track {
  background: transparent;
}

.messages-area::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

.messages-area::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.2);
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

.admin-message-content {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.admin-icon {
  width: 24px;
  height: 24px;
  background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.admin-icon i {
  color: white;
  font-size: 12px;
}

.admin-message-text {
  flex: 1;
  line-height: 1.4;
}

.message-time {
  font-size: 11px;
  color: #6c757d;
  margin-top: 6px;
  margin-left: 12px;
  margin-right: 12px;
  opacity: 0.8;
}

.message-input-area {
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

#admin-message-input {
  flex: 1;
  border: none;
  border-radius: 20px;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  background: transparent;
  resize: none;
}

#admin-message-input::placeholder {
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

.no-sessions {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
}

.no-sessions i {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
  color: #adb5bd;
}

.no-sessions p {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
}

.no-sessions .no-sessions-subtitle {
  font-size: 12px;
  opacity: 0.7;
  margin-top: 8px;
}

/* Admin Notification Animations */
@keyframes slideInLeft {
  from {
    transform: translateX(-100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOutLeft {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(-100%);
    opacity: 0;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .admin-chat-container {
    width: 360px;
    height: 520px;
  }
}

@media (max-width: 480px) {
  .admin-chat-container {
    width: 320px;
    height: 450px;
  }
  
  .admin-chat-toggle {
    width: 50px;
    height: 50px;
  }
  
  .admin-chat-toggle i {
    font-size: 20px;
  }
  
  .admin-chat-badge {
    width: 18px;
    height: 18px;
    font-size: 10px;
  }
}

.typing-indicator {
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

.typing-dots.small span {
  width: 4px;
  height: 4px;
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
/* Delete Confirmation Dialog Styles */
.confirmation-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
}

.confirmation-modal {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 400px;
  width: 90%;
  animation: slideInScale 0.3s ease;
  position: relative;
  z-index: 1;
}

@keyframes slideInScale {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.confirmation-header {
  padding: 24px 24px 0 24px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 16px;
}

.confirmation-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.confirmation-body {
  padding: 24px;
}

.confirmation-body p {
  margin: 0 0 12px 0;
  color: #555;
  line-height: 1.5;
}

.confirmation-actions {
  padding: 0 24px 24px 24px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.cancel-btn {
  background: #6c757d;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.cancel-btn:hover {
  background: #5a6268;
  transform: translateY(-1px);
}

.confirm-delete-btn {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 2px 6px rgba(220, 53, 69, 0.3);
}

.confirm-delete-btn:hover {
  background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}
</style>

<script>
class AdminChatInterface {
  constructor() {
    this.currentSessionId = null;
    this.lastMessageIdMap = {}; // Track last message ID per session
    this.pollInterval = null;
    this.sessionsPollInterval = null;
    this.sentButUnread = {}; // Track sent messages that are not yet read { sessionId: [msgId1, msgId2] }
    this.unreadSessionState = {}; // To track unread messages and prevent repeated notifications
    this.renderedIdsMap = {}; // { sessionId: Set(message_id) }
    
    this.init();
  }
  
  init() {
    this.bindEvents();
    this.startPolling();
    this.loadSessions();
  }
  
  bindEvents() {
    // Toggle chat interface with header button
    const headerToggle = document.getElementById('admin-chat-toggle-header');
    if (headerToggle) {
      headerToggle.addEventListener('click', () => {
        this.toggleChatInterface();
      });
    }
    
    // Toggle chat interface with floating icon (fallback)
    const floatingToggle = document.getElementById('admin-chat-toggle');
    if (floatingToggle) {
      floatingToggle.addEventListener('click', () => {
        this.toggleChatInterface();
      });
    }
    
    // Toggle chat interface
    document.getElementById('toggle-chat').addEventListener('click', () => {
      this.toggleChat();
    });
    
    // Back to sessions
    document.getElementById('back-to-sessions').addEventListener('click', () => {
      this.showSessions();
    });
    
    // Delete session
    document.getElementById('delete-session').addEventListener('click', () => {
      this.deleteCurrentSession();
    });
    
    // Close session
    document.getElementById('close-session').addEventListener('click', () => {
      this.closeCurrentSession();
    });
    
    // Send message
    document.getElementById('admin-send-btn').addEventListener('click', () => {
      this.sendMessage();
    });
    
    // Send message on Enter key
    document.getElementById('admin-message-input').addEventListener('keydown', (e) => {
      // Only intercept plain Enter; allow Shift+Enter (new line) and other shortcuts
      if ((e.key === 'Enter' || e.keyCode === 13) && !e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey && !e.isComposing && e.keyCode !== 229) {
        e.preventDefault();
        this.sendMessage();
      }
    });

    // Admin typing indicator
    const adminInput = document.getElementById('admin-message-input');
    let adminTypingTimer;
    adminInput.addEventListener('input', () => {
        this.sendAdminTypingEvent(true);
        clearTimeout(adminTypingTimer);
        adminTypingTimer = setTimeout(() => {
            this.sendAdminTypingEvent(false);
        }, 2000); // 2 seconds of inactivity
    });
    
    // Refresh sessions button
    document.getElementById('refresh-sessions').addEventListener('click', () => {
      this.loadSessions();
      this.showAdminNotification('Sessions refreshed', 'success');
    });
  }
  
     async loadSessions() {
     try {
       const response = await fetch('chat_api.php?action=get_sessions&status=all', {
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
       
       if (data.success) {
          this.renderSessions(data.sessions);
          this.updateAdminChatBadge(data.sessions);

          // --- NEW LOGIC FOR NOTIFICATION SOUND ---
          let playSound = false;
          const newUnreadState = {};

          data.sessions.forEach(session => {
              if (session.unread_count > 0) {
                  newUnreadState[session.session_id] = {
                      count: session.unread_count,
                      last_sender: session.last_sender
                  };
              }

              const oldUnreadCount = this.unreadSessionState[session.session_id] ? this.unreadSessionState[session.session_id].count : 0;
              
              // Play sound if a session has new unread messages from a customer
              if (session.unread_count > oldUnreadCount && session.last_sender === 'customer') {
                  playSound = true;
              }
          });

          // Update the state for the next poll
          this.unreadSessionState = newUnreadState;

          if (playSound && typeof playChatNotification === 'function') {
              playChatNotification();
              this.showAdminNotification(`New message(s) received`);
          }
          // --- END NEW LOGIC ---

        } else {
          throw new Error(data.error || 'Failed to load sessions');
        }
     } catch (error) {
       console.error('Error loading sessions:', error);
       this.showAdminNotification('Failed to load sessions. Retrying...', 'error');
     }
   }
   
   showAdminNotification(message, type = 'info') {
     // Remove existing notification
     const existingNotification = document.querySelector('.admin-notification');
     if (existingNotification) {
       existingNotification.remove();
     }
     
     // Create notification element
     const notification = document.createElement('div');
     notification.className = `admin-notification ${type}`;
     notification.textContent = message;
     
     // Add styles
     notification.style.cssText = `
       position: fixed;
       top: 20px;
       left: 20px;
       padding: 12px 20px;
       border-radius: 8px;
       color: white;
       font-size: 14px;
       font-weight: 500;
       z-index: 10000;
       animation: slideInLeft 0.3s ease;
       max-width: 300px;
       word-wrap: break-word;
     `;
     
     // Set background color based on type
     if (type === 'success') {
       notification.style.background = 'linear-gradient(135deg, #4ade80 0%, #22c55e 100%)';
     } else if (type === 'error') {
       notification.style.background = 'linear-gradient(135deg, #f87171 0%, #ef4444 100%)';
     } else {
       notification.style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
     }
     
     // Add to page
     document.body.appendChild(notification);
     
     // Remove after 3 seconds
     setTimeout(() => {
       if (notification.parentNode) {
         notification.style.animation = 'slideOutLeft 0.3s ease';
         setTimeout(() => notification.remove(), 300);
       }
     }, 3000);
   }
  
  renderSessions(sessions) {
    const container = document.getElementById('sessions-container');
    const activeSessionsElement = document.getElementById('active-sessions');
    const totalSessionsElement = document.getElementById('total-sessions');
    
    const activeSessions = sessions.filter(s => s.session_status === 'active').length;
    const totalSessions = sessions.length;
    
    activeSessionsElement.textContent = activeSessions;
    totalSessionsElement.textContent = totalSessions;
    
    if (sessions.length === 0) {
      container.innerHTML = /*html*/`
        <div class="no-sessions">
          <i class="fas fa-comments"></i>
          <p>No chat sessions</p>
        </div>
      `;
      return;
    }
    
         container.innerHTML = sessions.map(session => /*html*/`
       <div class="session-item ${session.unread_count > 0 ? 'unread' : ''} ${session.session_status === 'closed' ? 'closed-session' : ''}" data-session-id="${session.session_id}">
         <div class="flex justify-between items-start">
           <div class="session-customer">
             <i class="fas fa-user"></i>
             ${session.customer_name}
             ${session.session_status === 'closed' ? '<span class="closed-badge">CLOSED</span>' : ''}
           </div>
           <div class="session-time">
             <i class="fas fa-clock"></i>
             ${this.formatTime(session.last_message_at)}
           </div>
         </div>
         <div class="session-typing-indicator" id="typing-indicator-${session.session_id}" style="display: ${session.customer_is_typing ? 'flex' : 'none'};">
            <div class="typing-dots small"><span></span><span></span><span></span></div>
            <i>typing...</i>
         </div>
         <div class="session-customer-info">
           ${session.customer_email ? `<div><i class="fas fa-envelope"></i> ${session.customer_email}</div>` : ''}
           ${session.additional_info ? `<div><i class="fas fa-comment"></i> ${session.additional_info.substring(0, 50)}${session.additional_info.length > 50 ? '...' : ''}</div>` : ''}
         </div>
         <div class="flex justify-between items-center mt-2">
            <div class="session-last-message" title="${session.last_message || 'No messages yet'}">
              <i class="fas fa-comment-dots"></i>
              ${session.last_message || 'No messages yet'}
            </div>
            <div class="flex items-center gap-2">
                <div class="session-total-messages" title="Total messages in this session">
                    <i class="fas fa-comments"></i>
                    <span>${session.total_messages}</span>
                </div>
                ${session.unread_count > 0 ? `<div class="unread-badge">${session.unread_count}</div>` : ''}
            </div>
         </div>
       </div>
     `).join('');
    
    // Add click events to session items
    container.querySelectorAll('.session-item').forEach(item => {
      item.addEventListener('click', () => {
        const sessionId = item.dataset.sessionId;
        this.openSession(sessionId);
      });
    });
  }
  
  async openSession(sessionId) {
    this.currentSessionId = sessionId;

    // Clear current messages UI to avoid any duplication leftovers
    const messagesArea = document.getElementById('messages-area');
    if (messagesArea) messagesArea.innerHTML = '';

    // init rendered set for this session (reset to ensure clean render)
    this.renderedIdsMap[sessionId] = new Set();

    // Get all messages for this session to find the highest message_id
    const response = await fetch(`chat_api.php?action=get_messages&session_id=${sessionId}`, {
      method: 'GET',
      headers: {
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache'
      }
    });
    const data = await response.json();
    let highestId = 0;
    if (data.success && data.messages.length > 0) {
      highestId = Math.max(...data.messages.map(m => m.message_id));
      this.renderMessages(data.messages);
    }
    this.lastMessageIdMap[sessionId] = highestId;

    // Update UI
    document.getElementById('sessions-list').style.display = 'none';
    document.getElementById('chat-messages-container').style.display = 'flex';

    // Get customer info
    const sessionItem = document.querySelector(`[data-session-id="${sessionId}"]`);
    const customerName = sessionItem.querySelector('.session-customer').textContent;
    const customerInfo = sessionItem.querySelector('.session-customer-info').innerHTML;

    // Display customer info in header
    document.getElementById('current-customer').innerHTML = `
      <div style="font-weight: 600;">${customerName}</div>
      <div style="font-size: 11px; color: #6c757d; margin-top: 2px;">${customerInfo}</div>
    `;

    // Mark messages as read
    await this.markAsRead();
  }
  
     async loadMessages() {
  if (!this.currentSessionId) return;

  const lastId = this.lastMessageIdMap[this.currentSessionId] || 0;

  try {
    const response = await fetch(`chat_api.php?action=get_messages&session_id=${this.currentSessionId}&last_message_id=${lastId}`, {
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

    if (data.success) {
      data.messages.forEach(msg => {
        const incoming = Number(msg.message_id) || 0;
        const currentLast = Number(lastId || 0);
        if (incoming > currentLast) {
          this.renderMessages([msg]);
          // Update lastMessageId for this session
          this.lastMessageIdMap[this.currentSessionId] = incoming;
        }
      });

      // Handle typing indicator
      const typingIndicator = document.getElementById('admin-typing-indicator');
      if (typingIndicator) {
          typingIndicator.style.display = data.customer_is_typing ? 'flex' : 'none';
      }

      // Check for read status updates for messages sent by admin
      if (this.sentButUnread[this.currentSessionId] && this.sentButUnread[this.currentSessionId].length > 0) {
        this.checkReadStatus();
      }

    } else {
      throw new Error(data.error || 'Failed to load messages');
    }
  } catch (error) {
    console.error('Error loading messages:', error);
    if (!error.message.includes('timeout')) {
      this.showAdminNotification('Connection issue. Retrying...', 'error');
    }
  }
}
  
  renderMessages(messages) {
  const container = document.getElementById('messages-area');
  const seen = this.renderedIdsMap[this.currentSessionId] || (this.renderedIdsMap[this.currentSessionId] = new Set());
  
  messages.forEach(msg => {
    const idKey = msg.message_id != null ? String(msg.message_id) : null;
    if (idKey && seen.has(idKey)) return;
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${msg.sender_type}`;
    
    const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    if (msg.sender_type === 'admin') {
      messageDiv.innerHTML = `
        <div class="message-bubble">
          <div class="admin-message-content">
            <div class="admin-icon">
              <i class="ri-user-star-line"></i>
            </div>
            <div class="admin-message-text">${this.escapeHtml(msg.message_text)}</div>
          </div>
        </div>
        <div class="message-time">
            ${time}
            <span class="read-receipt" id="read-receipt-${msg.message_id}">
                <i class="fas ${msg.is_read ? 'fa-check-double' : 'fa-check'}"></i>
            </span>
        </div>
      `;
    } else {
      messageDiv.innerHTML = `
        <div class="message-bubble">${this.escapeHtml(msg.message_text)}</div>
        <div class="message-time">${time}</div>
      `;
    }
    
    container.appendChild(messageDiv);
    if (idKey) seen.add(idKey);
  });
  
  container.scrollTop = container.scrollHeight;
}
  
  async sendMessage() {
     const input = document.getElementById('admin-message-input');
     const message = input.value.trim();
     
     if (!message || !this.currentSessionId) return;
     
     // Disable send button and show loading state
     const sendBtn = document.getElementById('admin-send-btn');
     const originalIcon = sendBtn.innerHTML;
     sendBtn.disabled = true;
     sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
     
     try {
       const response = await fetch('chat_api.php?action=send_message', {
         method: 'POST',
         headers: {
           'Content-Type': 'application/x-www-form-urlencoded',
         },
         body: `session_id=${this.currentSessionId}&sender_type=admin&sender_name=Admin&message_text=${encodeURIComponent(message)}`
       });
       
       if (!response.ok) {
         throw new Error(`HTTP error! status: ${response.status}`);
       }
       
               const data = await response.json();
        if (data.success) {
          input.value = '';
          // Render the sent message immediately for a better UX
          const sentMessage = {
            message_id: data.message_id,
            sender_type: 'admin',
            sender_name: 'Admin',
            message_text: message,
            created_at: new Date().toISOString(),
            is_read: 0 // It's not read yet
          };
          this.renderMessages([sentMessage]);
          // Add to unread tracking
          if (!this.sentButUnread[this.currentSessionId]) {
            this.sentButUnread[this.currentSessionId] = [];
          }
          this.sentButUnread[this.currentSessionId].push(data.message_id);
          // Update last message ID to prevent polling from re-fetching it
          this.lastMessageIdMap[this.currentSessionId] = data.message_id;
        } else {
          throw new Error(data.error || 'Failed to send message');
        }
     } catch (error) {
       console.error('Error sending message:', error);
       this.showAdminNotification('Failed to send message. Please try again.', 'error');
       // Restore message to input for retry
       input.value = message;
     } finally {
       // Restore send button
       sendBtn.disabled = false;
       sendBtn.innerHTML = originalIcon;
     }
   }
  
  addMessage(senderType, senderName, messageText, timestamp = null) {
    const container = document.getElementById('messages-area');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${senderType}`;
    
    const time = timestamp || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageDiv.innerHTML = `
      <div class="message-bubble">${this.escapeHtml(messageText)}</div>
      <div class="message-time">${time}</div>
    `;
    
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
  }
  
  async sendAdminTypingEvent(isTyping) {
    if (!this.currentSessionId) return;

    try {
        await fetch('chat_api.php?action=update_typing_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `session_id=${this.currentSessionId}&sender_type=admin&is_typing=${isTyping ? 1 : 0}`
        });
    } catch (error) {
        console.error('Error sending admin typing event:', error);
    }
  }

  async checkReadStatus() {
    const unreadIds = this.sentButUnread[this.currentSessionId];
    if (!unreadIds || unreadIds.length === 0) return;

    try {
      const response = await fetch('chat_api.php?action=check_read_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `session_id=${this.currentSessionId}&message_ids=${unreadIds.join(',')}`
      });
      const data = await response.json();
      if (data.success && data.read_ids.length > 0) {
        data.read_ids.forEach(readId => {
          const receipt = document.getElementById(`read-receipt-${readId}`);
          if (receipt) {
            receipt.innerHTML = '<i class="fas fa-check-double"></i>';
          }
          this.sentButUnread[this.currentSessionId] = this.sentButUnread[this.currentSessionId].filter(id => id !== readId);
        });
      }
    } catch (error) {
      console.error('Error checking read status:', error);
    }
  }

  async markAsRead() {
    if (!this.currentSessionId) return;
    
    try {
      await fetch('chat_api.php?action=mark_read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `session_id=${this.currentSessionId}&sender_type=customer`
      });
    } catch (error) {
      console.error('Error marking as read:', error);
    }
  }
  
  async deleteCurrentSession() {
    if (!this.currentSessionId) return;
    
    // Show custom confirmation dialog
    const customerName = document.getElementById('current-customer').textContent.trim();
    const confirmed = await this.showDeleteConfirmation(customerName);
    
    if (!confirmed) return;
    
    try {
      const response = await fetch('chat_api.php?action=delete_session', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `session_id=${this.currentSessionId}`
      });
      
      const data = await response.json();
      if (data.success) {
        this.showAdminNotification('Conversation deleted successfully!', 'success');
        this.showSessions();
        this.loadSessions();
      } else {
        throw new Error(data.error || 'Failed to delete conversation');
      }
    } catch (error) {
      console.error('Error deleting session:', error);
      this.showAdminNotification('Failed to delete conversation. Please try again.', 'error');
    }
  }
  
  showDeleteConfirmation(customerName) {
    return new Promise((resolve) => {
      // Remove existing confirmation dialog
      const existingDialog = document.querySelector('.delete-confirmation-dialog');
      if (existingDialog) {
        existingDialog.remove();
      }
      
      // Create confirmation dialog
      const dialog = document.createElement('div');
      dialog.className = 'delete-confirmation-dialog';
      dialog.innerHTML = `
        <div class="confirmation-overlay"></div>
        <div class="confirmation-modal">
          <div class="confirmation-header">
            <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 24px; margin-right: 10px;"></i>
            <h3>Delete Conversation</h3>
          </div>
          <div class="confirmation-body">
            <p>Are you sure you want to delete the conversation with <strong>${customerName}</strong>?</p>
            <p style="color: #dc3545; font-size: 12px; margin-top: 10px;">
              <i class="fas fa-warning"></i> This action cannot be undone. All messages will be permanently deleted.
            </p>
          </div>
          <div class="confirmation-actions">
            <button class="cancel-btn" id="cancel-delete">Cancel</button>
            <button class="confirm-delete-btn" id="confirm-delete">Delete Conversation</button>
          </div>
        </div>
      `;
      
      // Add styles
      dialog.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10001;
        display: flex;
        align-items: center;
        justify-content: center;
      `;
      
      // Add to page
      document.body.appendChild(dialog);
      
      // Add event listeners
      document.getElementById('cancel-delete').addEventListener('click', () => {
        dialog.remove();
        resolve(false);
      });
      
      document.getElementById('confirm-delete').addEventListener('click', () => {
        dialog.remove();
        resolve(true);
      });
      
      // Close on overlay click
      dialog.querySelector('.confirmation-overlay').addEventListener('click', () => {
        dialog.remove();
        resolve(false);
      });
      
      // Close on Escape key
      const handleEscape = (e) => {
        if (e.key === 'Escape') {
          dialog.remove();
          document.removeEventListener('keydown', handleEscape);
          resolve(false);
        }
      };
      document.addEventListener('keydown', handleEscape);
    });
  }
  
  async closeCurrentSession() {
    if (!this.currentSessionId) return;
    
    try {
      const response = await fetch('chat_api.php?action=close_session', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `session_id=${this.currentSessionId}`
      });
      
      const data = await response.json();
      if (data.success) {
        this.showSessions();
        this.loadSessions();
      }
    } catch (error) {
      console.error('Error closing session:', error);
    }
  }
  
  showSessions() {
    this.currentSessionId = null;
    document.getElementById('sessions-list').style.display = 'flex';
    document.getElementById('chat-messages-container').style.display = 'none';
    document.getElementById('messages-area').innerHTML = '';
  }
  
  toggleChatInterface() {
    const toggleButton = document.getElementById('admin-chat-toggle');
    const container = document.getElementById('admin-chat-container');
    
    if (container.style.display === 'none') {
      container.style.display = 'flex';
      toggleButton.style.display = 'none';
    } else {
      container.style.display = 'none';
      toggleButton.style.display = 'flex';
    }
  }
  
  toggleChat() {
    const container = document.getElementById('admin-chat-container');
    const icon = document.querySelector('#toggle-chat i');
    
    if (container.style.height === '50px') {
      container.style.height = '600px';
      icon.className = 'fas fa-minus';
    } else {
      container.style.height = '50px';
      icon.className = 'fas fa-plus';
    }
  }
  
  startPolling() {
    // Poll for new sessions
    this.sessionsPollInterval = setInterval(() => {
      this.loadSessions();
    }, 5000);
    
    // Poll for new messages
    this.pollInterval = setInterval(() => {
      if (this.currentSessionId) {
        this.loadMessages();
      }
    }, 2000);
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  updateAdminChatBadge(sessions) {
    const floatingBadge = document.getElementById('admin-chat-badge');
    const headerBadge = document.getElementById('admin-chat-badge-header');
    let totalUnread = 0;
    
    sessions.forEach(session => {
      totalUnread += session.unread_count || 0;
    });
    
    const badgeText = totalUnread > 99 ? '99+' : totalUnread;
    
    // Update floating badge
    if (floatingBadge) {
      if (totalUnread > 0) {
        floatingBadge.textContent = badgeText;
        floatingBadge.style.display = 'flex';
      } else {
        floatingBadge.style.display = 'none';
      }
    }
    
    // Update header badge
    if (headerBadge) {
      if (totalUnread > 0) {
        headerBadge.textContent = badgeText;
        headerBadge.style.display = 'flex';
      } else {
        headerBadge.style.display = 'none';
      }
    }
  }
  
  formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) { // Less than 1 minute
      return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
      const minutes = Math.floor(diff / 60000);
      return `${minutes}m ago`;
    } else if (diff < 86400000) { // Less than 1 day
      const hours = Math.floor(diff / 3600000);
      return `${hours}h ago`;
    } else {
      return date.toLocaleDateString();
    }
  }
  
  destroy() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
    if (this.sessionsPollInterval) {
      clearInterval(this.sessionsPollInterval);
    }
  }
}

// Initialize admin chat interface when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  console.log('Admin chat interface initializing...');
  window.adminChat = new AdminChatInterface();
  
  // Test if chat toggle is visible
  const chatToggle = document.getElementById('admin-chat-toggle');
  if (chatToggle) {
    console.log('Admin chat toggle found and should be visible');
    chatToggle.style.display = 'flex';
    chatToggle.style.zIndex = '9999';
    chatToggle.style.position = 'fixed';
    chatToggle.style.bottom = '20px';
    chatToggle.style.right = '20px';
    
    // Check if FontAwesome icon is loading
    const icon = chatToggle.querySelector('i.fas');
    const fallbackIcon = chatToggle.querySelector('.fallback-icon');
    const textFallback = chatToggle.querySelector('.text-fallback');
    
    if (icon && icon.offsetWidth === 0) {
      // FontAwesome not loaded, show fallback
      console.log('FontAwesome not loaded, showing fallback icon');
      if (fallbackIcon) {
        fallbackIcon.style.display = 'inline';
        icon.style.display = 'none';
      }
    } else {
      console.log('FontAwesome icon loaded successfully');
    }
    
    // Force the toggle to be visible with a bright background
    chatToggle.style.background = 'linear-gradient(135deg, #ff0000 0%, #cc0000 100%) !important';
    chatToggle.style.border = '3px solid white !important';
    chatToggle.style.boxShadow = '0 8px 32px rgba(255, 0, 0, 0.5) !important';
  } else {
    console.error('Admin chat toggle not found!');
  }
});
</script>

<script>
function handleIncomingChatMessage(message) {
  // Check if the message is from the kiosk/customer
  if (message.sender === 'customer' || message.from_kiosk) {
    playChatNotification();
  }
  // ...existing code to display the message...
}
</script>