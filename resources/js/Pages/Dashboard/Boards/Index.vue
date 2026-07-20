<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CreateBoardCard from '@/Components/Boards/CreateBoardCard.vue';
import BoardCard from '@/Components/Boards/BoardCard.vue';

// Define the incoming props from Laravel Controller
const props = defineProps({
    shared_boards: {
        type: Array,
        required: true
    }
});

// Filter boards where the user is the owner (checking the Laravel pivot object)
const myBoards = computed(() => {
    return (props.shared_boards || []).filter(board => board?.pivot?.role === 'owner');
});

// Filter boards where the user has any role other than owner
const sharedBoards = computed(() => {
    return (props.shared_boards || []).filter(board => board?.pivot?.role !== 'owner');
});

// Safe computed properties for counts to prevent blank screen issues
const myBoardsCount = computed(() => myBoards.value.length);
const sharedBoardsCount = computed(() => sharedBoards.value.length);
const totalBoardsCount = computed(() => (props.shared_boards || []).length);

// --- MODAL & FORM LOGIC ---
const isCreateModalOpen = ref(false);

// Explicitly named form helper for clearer domain understanding
const boardForm = useForm({
    name: '',
});

function openCreateModal() {
    boardForm.clearErrors();
    boardForm.reset();
    isCreateModalOpen.value = true;
}

function closeCreateModal() {
    isCreateModalOpen.value = false;
}

function submitCreateBoardForm() {
    // Send POST request via Inertia to our Laravel backend endpoint
    boardForm.post(route('boards.store'), {
        onSuccess: () => {
            boardForm.reset();
            closeCreateModal();
        }
    });
}
</script>

<template>
    <Head title="Boards" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                Boards
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-12">

                <!-- Overall Empty State: Shows the card starting from the top-left if zero boards exist -->
                <div v-if="totalBoardsCount === 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- The card now naturally occupies the very first slot on the left -->
                    <CreateBoardCard @add-new-board-button-clicked="openCreateModal" />
                </div>

                <template v-else>
                    <!-- SECTION 1: My Boards -->
                    <section v-if="myBoardsCount > 0">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-zinc-800 pb-2">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                                <span>📁</span>
                                <span>My Boards</span>
                            </h3>
                            <span class="px-3 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-full dark:bg-blue-900/30 dark:text-blue-400">
                                Total: {{ myBoardsCount }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            <CreateBoardCard @add-new-board-button-clicked="openCreateModal" />
                            <BoardCard v-for="board in myBoards" :key="board.id" :board="board" />
                        </div>
                    </section>

                    <!-- SECTION 2: Shared Boards -->
                    <section v-if="sharedBoardsCount > 0">
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-zinc-800 pb-2">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                                <span>🤝</span>
                                <span>Shared Boards</span>
                            </h3>
                            <span class="px-3 py-0.5 text-xs font-semibold bg-purple-50 text-purple-700 rounded-full dark:bg-purple-900/30 dark:text-purple-400">
                                Total: {{ sharedBoardsCount }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                            <BoardCard v-for="board in sharedBoards" :key="board.id" :board="board" />
                        </div>
                    </section>
                </template>
            </div>
        </div>

        <!-- SIMPLE MODAL OVERLAY (Tailwind v4 compatible, fully supporting Dark Mode) -->
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="w-full max-w-md p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-gray-100 dark:border-zinc-800 transform transition-all">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Create New Project Board</h3>
                    <button @click="closeCreateModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer text-xl">✕</button>
                </div>

                <form @submit.prevent="submitCreateBoardForm" class="space-y-4">
                    <div>
                        <label for="board-name" class="block text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-zinc-500 mb-1">Board Title</label>
                        <input
                            v-model="boardForm.name"
                            type="text"
                            id="board-name"
                            placeholder="e.g., Mobile App Development"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:border-blue-500 transition-colors"
                            required
                            :disabled="boardForm.processing"
                        />
                        <!-- Dynamic Validation/SaaS Limit Error Rendering -->
                        <p v-if="boardForm.errors.name" class="mt-1.5 text-xs font-medium text-red-500 flex items-center space-x-1">
                            <span>⚠️</span> <span>{{ boardForm.errors.name }}</span>
                        </p>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button
                            @click="closeCreateModal"
                            type="button"
                            class="px-4 py-2 rounded-xl text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-zinc-200 cursor-pointer"
                            :disabled="boardForm.processing"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 transition-colors cursor-pointer"
                            :disabled="boardForm.processing"
                        >
                            {{ boardForm.processing ? 'Creating...' : 'Create Board' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
