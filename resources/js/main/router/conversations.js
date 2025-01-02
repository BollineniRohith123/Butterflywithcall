import ConversationIndex from '../views/conversations/index.vue';
import ConversationShow from '../views/conversations/Show.vue';
import AllConversations from '../views/leads/AllConversations.vue';

export default [
    {
        path: '/admin/conversations',
        component: ConversationIndex,
        name: 'admin.conversations.index',
        meta: {
            requireAuth: true,
            menuParent: "communication",
            menuKey: "conversations",
            permissions: ['*'] // Allow all authenticated users
        }
    },
    {
        path: '/admin/conversations/:id',
        component: ConversationShow,
        name: 'admin.conversations.show',
        meta: {
            requireAuth: true,
            menuParent: "communication",
            menuKey: "conversations",
            permissions: ['*']
        }
    },
    {
        path: '/admin/leads/all-conversations',
        component: AllConversations,
        name: 'admin.leads.conversations',
        meta: {
            requireAuth: true,
            menuParent: "leads",
            menuKey: "all_conversations",
            permissions: ['*'] // Allow all authenticated users
        }
    },
]
