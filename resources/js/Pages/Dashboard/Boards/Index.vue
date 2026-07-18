<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
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

// 2. Placeholder function for the next lesson
function openCreateModal() {
    console.log('Open modal logic will be implemented here');
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
    </AuthenticatedLayout>
</template>
