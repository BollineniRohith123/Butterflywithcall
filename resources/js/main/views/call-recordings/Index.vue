<template>
    <a-layout>
        <LeftSidebar />
        <a-layout :style="{ marginLeft: '240px' }">
            <a-layout-content class="main-content">
                <div class="page-header">
                    <div class="title">
                        <h1>{{ $t("call_recordings.title") }}</h1>
                    </div>
                    <div class="actions">
                        <a-input-search
                            v-model:value="searchQuery"
                            placeholder="Search recordings..."
                            style="width: 250px; margin-right: 16px;"
                            @search="onSearch"
                        />
                    </div>
                </div>

                <a-card class="page-content-container">
                    <a-table
                        :columns="columns"
                        :data-source="recordings"
                        :loading="loading"
                        :pagination="pagination"
                        @change="handleTableChange"
                        row-key="id"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'recording_status'">
                                <a-tag :color="getStatusColor(record.recording_status)">
                                    {{ record.recording_status }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'recording_duration'">
                                {{ formatDuration(record.recording_duration) }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-space>
                                    <a-button 
                                        type="primary" 
                                        @click="playRecording(record)"
                                        :disabled="!record.recording_url"
                                    >
                                        <template #icon><PlayCircleOutlined /></template>
                                        {{ $t("common.play") }}
                                    </a-button>
                                    <a-button 
                                        type="primary" 
                                        @click="downloadRecording(record)"
                                        :disabled="!record.recording_url"
                                    >
                                        <template #icon><DownloadOutlined /></template>
                                        {{ $t("common.download") }}
                                    </a-button>
                                    <a-button 
                                        type="primary" 
                                        danger
                                        @click="confirmDelete(record)"
                                    >
                                        <template #icon><DeleteOutlined /></template>
                                        {{ $t("common.delete") }}
                                    </a-button>
                                </a-space>
                            </template>
                        </template>
                    </a-table>
                </a-card>

                <!-- Audio Player Modal -->
                <a-modal
                    v-model:visible="audioPlayerVisible"
                    :title="$t('call_recordings.play_recording')"
                    :footer="null"
                    @cancel="stopAudio"
                >
                    <audio 
                        ref="audioPlayer"
                        controls 
                        style="width: 100%"
                        :src="currentAudioUrl"
                    ></audio>
                </a-modal>
            </a-layout-content>
        </a-layout>
    </a-layout>
</template>

<script>
import { defineComponent, ref, onMounted, onBeforeUnmount } from "vue";
import { message, Modal } from "ant-design-vue";
import {
    PlayCircleOutlined,
    DownloadOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import LeftSidebar from "../../../common/layouts/LeftSidebar.vue";
import common from "../../../common/composable/common";
import axios from "axios";

export default defineComponent({
    components: {
        LeftSidebar,
        PlayCircleOutlined,
        DownloadOutlined,
        DeleteOutlined,
    },
    setup() {
        const { permsArray } = common();
        const loading = ref(false);
        const recordings = ref([]);
        const searchQuery = ref("");
        const audioPlayerVisible = ref(false);
        const currentAudioUrl = ref("");
        const audioPlayer = ref(null);
        
        const pagination = ref({
            total: 0,
            current: 1,
            pageSize: 10,
            showSizeChanger: true,
            showTotal: (total) => `Total ${total} items`,
        });

        const columns = [
            {
                title: "S.No",
                dataIndex: "serial_number",
                width: 80,
                customRender: ({ index }) => {
                    return ((pagination.value.current - 1) * pagination.value.pageSize) + index + 1;
                }
            },
            {
                title: "Call SID",
                dataIndex: "call_sid",
                sorter: true,
            },
            {
                title: "Phone Number",
                dataIndex: "phone_number",
                sorter: true,
                width: 150,
            },
            {
                title: "Recording SID",
                dataIndex: "recording_sid",
                sorter: true,
            },
            {
                title: "Status",
                dataIndex: "recording_status",
                sorter: true,
            },
            {
                title: "Duration",
                dataIndex: "recording_duration",
                sorter: true,
            },
            {
                title: "Source",
                dataIndex: "recording_source",
                sorter: true,
            },
            {
                title: "Created At",
                dataIndex: "created_at",
                sorter: true,
            },
            {
                title: "Action",
                dataIndex: "action",
                fixed: "right",
                width: 250,
            },
        ];

        const loadData = async (params = {}) => {
            loading.value = true;
            try {
                const response = await axios.get('/api/v1/recordings', {
                    params: {
                        page: params.page || pagination.value.current,
                        limit: params.limit || pagination.value.pageSize,
                        search: searchQuery.value,
                        sortField: params.sortField,
                        sortOrder: params.sortOrder,
                    },
                });

                if (response.data.success) {
                    recordings.value = response.data.data.list;
                    pagination.value.total = response.data.data.meta.total;
                    pagination.value.current = response.data.data.meta.current_page;
                    pagination.value.pageSize = response.data.data.meta.per_page;
                } else {
                    message.error('Failed to load recordings');
                }
            } catch (error) {
                message.error(error.response?.data?.message || 'Error loading recordings');
            } finally {
                loading.value = false;
            }
        };

        const handleTableChange = (pag, filters, sorter) => {
            loadData({
                page: pag.current,
                limit: pag.pageSize,
                sortField: sorter.field,
                sortOrder: sorter.order,
            });
        };

        const onSearch = () => {
            pagination.value.current = 1;
            loadData();
        };

        const playRecording = (record) => {
            currentAudioUrl.value = record.recording_url;
            audioPlayerVisible.value = true;
        };

        const stopAudio = () => {
            if (audioPlayer.value) {
                audioPlayer.value.pause();
                audioPlayer.value.currentTime = 0;
            }
        };

        const downloadRecording = (record) => {
            window.open(record.recording_url, '_blank');
        };

        const confirmDelete = (record) => {
            Modal.confirm({
                title: 'Are you sure you want to delete this recording?',
                content: 'This action cannot be undone.',
                okText: 'Yes',
                okType: 'danger',
                cancelText: 'No',
                async onOk() {
                    try {
                        const response = await axios.delete(`/api/v1/recordings/${record.id}`);
                        if (response.data.success) {
                            message.success(response.data.message);
                            loadData();
                        } else {
                            message.error(response.data.message);
                        }
                    } catch (error) {
                        message.error(error.response?.data?.message || 'Error deleting recording');
                    }
                },
            });
        };

        const formatDuration = (seconds) => {
            if (!seconds) return '0:00';
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        };

        const getStatusColor = (status) => {
            const colors = {
                'completed': 'success',
                'processing': 'processing',
                'failed': 'error',
                'in-progress': 'warning'
            };
            return colors[status] || 'default';
        };

        onMounted(() => {
            loadData();
        });

        onBeforeUnmount(() => {
            stopAudio();
        });

        return {
            loading,
            recordings,
            columns,
            pagination,
            searchQuery,
            audioPlayerVisible,
            currentAudioUrl,
            audioPlayer,
            handleTableChange,
            onSearch,
            playRecording,
            downloadRecording,
            confirmDelete,
            stopAudio,
            formatDuration,
            getStatusColor,
        };
    },
});
</script>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background-color: #fff;
    margin-bottom: 16px;
}

.title h1 {
    margin: 0;
    font-size: 24px;
}

.page-content-container {
    margin: 24px;
}

.main-content {
    padding: 24px;
    background: #f0f2f5;
    min-height: 100vh;
}
</style>
