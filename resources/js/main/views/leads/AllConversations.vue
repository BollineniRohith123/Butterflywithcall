<template>
    <a-layout>
        <LeftSidebar />
        <a-layout :style="{ marginLeft: '240px' }">
            <a-layout-content class="main-content">
                <div class="chat-container">
                    <!-- Leads Panel -->
                    <div class="leads-panel">
                        <div class="search-container p-3">
                            <a-input
                                v-model:value="searchQuery"
                                placeholder="Search conversations..."
                                @input="onSearch"
                            >
                                <template #prefix>
                                    <SearchOutlined />
                                </template>
                            </a-input>
                        </div>
                        
                        <div class="leads-list">
                            <a-list
                                :data-source="filteredConversations"
                                :loading="loading"
                            >
                                <template #renderItem="{ item }">
                                    <a-list-item
                                        class="lead-item"
                                        :class="{ active: selectedConversation && selectedConversation.phone === item.phone }"
                                        @click="selectConversation(item)"
                                    >
                                        <div class="lead-item-content">
                                            <div class="lead-avatar">
                                                {{ getInitials(item) }}
                                            </div>
                                            <div class="lead-details">
                                                <div class="lead-name">
                                                    {{ item.from || 'Unknown' }}
                                                </div>
                                                <div class="lead-phone">
                                                    {{ item.phone }}
                                                </div>
                                                <div class="lead-message">
                                                    {{ truncateMessage(item.message) }}
                                                </div>
                                            </div>
                                        </div>
                                    </a-list-item>
                                </template>
                            </a-list>
                        </div>
                    </div>

                    <!-- Chat Panel -->
                    <div class="chat-panel">
                        <template v-if="selectedConversation">
                            <!-- Chat Header -->
                            <div class="chat-header">
                                <div class="lead-avatar">
                                    {{ getInitials(selectedConversation) }}
                                </div>
                                <div class="lead-info">
                                    <div class="lead-name">
                                        {{ selectedConversation.from || 'Unknown' }}
                                    </div>
                                    <div class="lead-status">
                                        {{ selectedConversation.phone }}
                                    </div>
                                </div>
                            </div>

                            <!-- Messages Container -->
                            <div 
                                ref="messagesContainer" 
                                class="message-list"
                            >
                                <div 
                                    v-for="message in currentMessages" 
                                    :key="message.id"
                                    class="message-item"
                                    :class="{ 'outbound': message.direction === 'outbound' }"
                                >
                                    <div class="message-content">
                                        {{ message.message }}
                                        <div class="message-meta">
                                            <span class="message-time">{{ formatDateTime(message.created_at) }}</span>
                                            <span class="message-status" v-if="message.direction === 'outbound'">
                                                <i class="ri-check-double-line" v-if="message.status === 'delivered'"></i>
                                                <i class="ri-check-line" v-else></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Input -->
                            <div class="message-input-container">
                                <a-input
                                    v-model:value="newMessage"
                                    placeholder="Type a message..."
                                    class="message-input"
                                    @keyup.enter="sendMessage"
                                >
                                    <template #prefix>
                                        <i class="ri-message-2-line" style="color: #9ca3af"></i>
                                    </template>
                                </a-input>
                                <button 
                                    class="send-button"
                                    :disabled="!newMessage.trim()"
                                    @click="sendMessage"
                                >
                                    <i class="ri-send-plane-fill"></i>
                                    Send
                                </button>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div v-else class="empty-state">
                            <i class="ri-message-2-line"></i>
                            <h3>Select a conversation to start chatting</h3>
                            <p>Choose a conversation from the list to view and send messages</p>
                        </div>
                    </div>
                </div>
            </a-layout-content>
        </a-layout>
    </a-layout>
</template>

<script>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import LeftSidebar from "../../../common/layouts/LeftSidebar.vue";
import common from "../../../common/composable/common";
import axios from 'axios';

export default {
    components: {
        LeftSidebar,
        SearchOutlined
    },
    setup() {
        const router = useRouter();
        const { formatDateTime } = common();
        const messagesContainer = ref(null);
        
        const conversations = ref([]);
        const filteredConversations = ref([]);
        const searchQuery = ref('');
        const loading = ref(false);
        const selectedConversation = ref(null);
        const newMessage = ref('');
        const currentMessages = ref([]);

        // Fetch all conversations grouped by phone
        const fetchConversations = async () => {
            try {
                loading.value = true;
                const response = await axios.get('/api/v1/conversations');
                conversations.value = response.data;
                filteredConversations.value = [...conversations.value];
            } catch (error) {
                message.error('Failed to load conversations');
                console.error('Error:', error);
            } finally {
                loading.value = false;
            }
        };

        // Fetch messages for a specific phone number
        const fetchMessages = async (phone) => {
            try {
                const response = await axios.get(`/api/v1/conversations/${phone}`);
                currentMessages.value = response.data;
                await nextTick();
                scrollToBottom();
            } catch (error) {
                message.error('Failed to load messages');
                console.error('Error:', error);
            }
        };

        const getInitials = (conversation) => {
            if (!conversation || !conversation.from) return '??';
            return conversation.from
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase();
        };

        const selectConversation = async (conversation) => {
            selectedConversation.value = conversation;
            await fetchMessages(conversation.phone);
            // Mark unread messages as read
            if (currentMessages.value) {
                const unreadMessages = currentMessages.value.filter(
                    msg => msg.direction === 'inbound' && !msg.read_at
                );
                for (const msg of unreadMessages) {
                    try {
                        await axios.post(`/api/v1/conversations/${msg.id}/read`);
                    } catch (error) {
                        console.error('Error marking message as read:', error);
                    }
                }
            }
        };

        const onSearch = (event) => {
            const query = event.target.value.toLowerCase();
            searchQuery.value = query;
            
            if (!query) {
                filteredConversations.value = conversations.value;
                return;
            }

            filteredConversations.value = conversations.value.filter(conversation => {
                const phone = conversation.phone.toLowerCase();
                const from = (conversation.from || '').toLowerCase();
                const message = (conversation.message || '').toLowerCase();
                return phone.includes(query) || from.includes(query) || message.includes(query);
            });
        };

        const sendMessage = async () => {
            if (!newMessage.value.trim() || !selectedConversation.value) return;

            try {
                const response = await axios.post('/api/v1/conversations/send', {
                    phone: selectedConversation.value.phone,
                    message: newMessage.value,
                    direction: 'outbound'
                });

                currentMessages.value.push(response.data);
                newMessage.value = '';
                await nextTick();
                scrollToBottom();
                await fetchConversations(); // Refresh conversation list
            } catch (error) {
                message.error('Failed to send message');
                console.error('Error:', error);
            }
        };

        const scrollToBottom = () => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        };

        const truncateMessage = (text, length = 30) => {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        };

        // Watch for new messages and scroll to bottom
        watch(currentMessages, () => {
            nextTick(() => scrollToBottom());
        });

        onMounted(() => {
            fetchConversations();
        });

        return {
            conversations,
            filteredConversations,
            searchQuery,
            loading,
            selectedConversation,
            newMessage,
            currentMessages,
            messagesContainer,
            getInitials,
            selectConversation,
            onSearch,
            sendMessage,
            formatDateTime,
            truncateMessage
        };
    }
};
</script>

<style scoped>
.chat-container {
    display: flex;
    height: calc(100vh - 64px);
    background: #fff;
}

.leads-panel {
    width: 300px;
    border-right: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
}

.leads-list {
    flex: 1;
    overflow-y: auto;
}

.lead-item {
    cursor: pointer;
    padding: 12px;
    transition: background-color 0.3s;
}

.lead-item:hover {
    background-color: #f5f5f5;
}

.lead-item.active {
    background-color: #e6f7ff;
}

.lead-item-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.lead-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #1890ff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.lead-details {
    flex: 1;
}

.lead-name {
    font-weight: 500;
}

.lead-phone {
    color: #666;
    font-size: 0.9em;
}

.lead-message {
    color: #999;
    font-size: 0.8em;
}

.chat-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.chat-header {
    padding: 16px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.message-list {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.message-item {
    display: flex;
}

.message-item.outbound {
    justify-content: flex-end;
}

.message-content {
    max-width: 70%;
    padding: 8px 12px;
    border-radius: 12px;
    background-color: #f0f0f0;
}

.message-item.outbound .message-content {
    background-color: #1890ff;
    color: white;
}

.message-meta {
    font-size: 0.8em;
    color: #666;
    margin-top: 4px;
}

.message-item.outbound .message-meta {
    color: #ccc;
}

.message-status {
    margin-left: 8px;
}

.message-input-container {
    padding: 16px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    gap: 12px;
}

.message-input {
    flex: 1;
}

.send-button {
    height: 40px;
    padding: 0 24px;
    border-radius: 20px;
    background: #1890ff;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.send-button:hover {
    background: #40a9ff;
}

.send-button:disabled {
    background: #d9d9d9;
    cursor: not-allowed;
}

.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    gap: 12px;
}

.empty-state i {
    font-size: 48px;
    color: #d9d9d9;
}

.empty-state h3 {
    margin: 0;
    font-size: 1.2em;
}

.empty-state p {
    margin: 0;
    color: #999;
}
</style>
