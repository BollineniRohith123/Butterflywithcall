export default [
    {
        path: "/call-recordings",
        name: "admin.call_recordings.index",
        component: () => import("../views/call-recordings/Index.vue"),
        meta: {
            requireAuth: true,
            menuParent: "call_recordings",
            menuKey: "call_recordings",
            permission: "call_recordings_view",
        }
    }
];
