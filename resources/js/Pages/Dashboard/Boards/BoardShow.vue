<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    board: {
        type: Object,
        required: true
    }
});

// Reactively reference the DOM elements for synchronization
const kanbanViewport = ref(null);
const customScrollbarTrack = ref(null);
const customScrollbarThumb = ref(null);

const isDragging = ref(false);
const startX = ref(0);
const scrollLeftStart = ref(0);

// Reactive state parameters for structural viewport dynamic calculations
const showScrollbar = ref(false);
const thumbWidthPercent = ref(20); // Fallback default slider thumb size percentage

// Calculate dynamic element bounds to resize and show/hide the scrollbar proportionally
function checkOverflow() {
    if (!kanbanViewport.value) return;

    const viewport = kanbanViewport.value;
    const hasOverflow = viewport.scrollWidth > viewport.clientWidth;
    showScrollbar.value = hasOverflow;

    if (hasOverflow) {
        // Calculate the exact proportion of visible content compared to total content width
        const visibleRatio = viewport.clientWidth / viewport.scrollWidth;

        // Convert to percentage and bound between 10% (min size) and 90% (max size) to preserve UX feel
        thumbWidthPercent.value = Math.max(10, Math.min(visibleRatio * 100, 90));

        nextTick(() => {
            handleViewportScroll();
        });
    }
}

// Synchronize the custom thumb position when the user scrolls the kanban content via mouse wheel
function handleViewportScroll() {
    if (isDragging.value || !showScrollbar.value || !kanbanViewport.value || !customScrollbarThumb.value || !customScrollbarTrack.value) return;

    const viewport = kanbanViewport.value;
    const scrollPercentage = viewport.scrollLeft / (viewport.scrollWidth - viewport.clientWidth);

    const maxThumbLeft = customScrollbarTrack.value.clientWidth - customScrollbarThumb.value.clientWidth;
    customScrollbarThumb.value.style.left = `${(scrollPercentage || 0) * maxThumbLeft}px`;
}

// Initialize custom drag-and-drop mechanics for our handmade scrollbar thumb
function startThumbDrag(event) {
    isDragging.value = true;
    startX.value = (event.pageX || event.touches.pageX) - customScrollbarThumb.value.offsetLeft;
    scrollLeftStart.value = kanbanViewport.value.scrollLeft;

    document.addEventListener('mousemove', handleThumbMove);
    document.addEventListener('mouseup', stopThumbDrag);
    document.addEventListener('touchmove', handleThumbMove);
    document.addEventListener('touchend', stopThumbDrag);
}

function handleThumbMove(event) {
    if (!isDragging.value || !kanbanViewport.value || !customScrollbarThumb.value || !customScrollbarTrack.value) return;

    event.preventDefault();
    const pageX = event.pageX || event.touches.pageX;
    const currentThumbLeft = pageX - startX.value;

    const maxThumbLeft = customScrollbarTrack.value.clientWidth - customScrollbarThumb.value.clientWidth;
    const boundedThumbLeft = Math.max(0, Math.min(currentThumbLeft, maxThumbLeft));

    customScrollbarThumb.value.style.left = `${boundedThumbLeft}px`;

    const scrollPercentage = boundedThumbLeft / maxThumbLeft;
    const maxViewportScroll = kanbanViewport.value.scrollWidth - kanbanViewport.value.clientWidth;
    kanbanViewport.value.scrollLeft = scrollPercentage * maxViewportScroll;
}

function stopThumbDrag() {
    isDragging.value = false;
    document.removeEventListener('mousemove', handleThumbMove);
    document.removeEventListener('mouseup', stopThumbDrag);
    document.removeEventListener('touchmove', handleThumbMove);
    document.removeEventListener('touchend', stopThumbDrag);
}

// Set up listeners to check overflow status dynamically on load and resize events
onMounted(() => {
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkOverflow);
});
</script>

<template>
    <!-- Dynamic browser tab title with explicit board name -->
    <Head :title="`Boards :: ${board.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                {{ board.name }}
            </h2>
        </template>

        <!-- 1. Changed root background to bg-gray-50 for high contrast with white columns -->
        <div class="py-12 w-full bg-gray-50 dark:bg-zinc-950/40 min-h-[calc(100vh-65px)]">
            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Main Kanban Viewport -->
                <div
                    ref="kanbanViewport"
                    @scroll="handleViewportScroll"
                    class="flex pb-2 items-start select-none overflow-x-auto scrollbar-none"
                    :class="showScrollbar ? 'justify-start space-x-6' : 'justify-center space-x-6'"
                    style="scrollbar-width: none; -ms-overflow-style: none;"
                >
                    <!-- 2. Changed column background to solid white (bg-white) and added clear borders -->
                    <div
                        v-for="column in board.columns"
                        :key="column.id"
                        class="w-72 shrink-0 bg-white dark:bg-zinc-900 border border-gray-200/60 dark:border-zinc-800/80 p-4 rounded-2xl shadow-sm"
                    >
                        <!-- Column Header Title -->
                        <h4 class="font-bold text-gray-800 dark:text-zinc-200 mb-3">
                            {{ column.name }}
                        </h4>

                        <!-- Vertical tasks container -->
                        <div class="space-y-3">
                            <!-- 3. Tasks styles updated with dynamic hover shadow and scale effects -->
                            <div
                                v-for="task in column.tasks"
                                :key="task.id"
                                class="p-3 bg-gray-50/70 hover:bg-white dark:bg-zinc-800/50 dark:hover:bg-zinc-800 shadow-xs hover:shadow-md border border-gray-100/70 dark:border-zinc-700/50 rounded-xl text-gray-900 dark:text-zinc-300 text-sm transition-all duration-200 cursor-pointer transform hover:-translate-y-0.5"
                            >
                                {{ task.title }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- --- FIXED HANDMADE KANBAN SCROLLBAR TRACK --- -->
                <div
                    v-if="showScrollbar"
                    ref="customScrollbarTrack"
                    class="relative w-full h-3 bg-gray-200 dark:bg-zinc-800 rounded-full overflow-hidden select-none"
                >
                    <div
                        ref="customScrollbarThumb"
                        @mousedown="startThumbDrag"
                        @touchstart="startThumbDrag"
                        class="absolute top-0 left-0 h-full bg-gray-400 hover:bg-gray-500 dark:bg-zinc-600 dark:hover:bg-zinc-500 rounded-full cursor-grab active:cursor-grabbing transition-colors duration-150"
                        :style="{ width: thumbWidthPercent + '%' }"
                    ></div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none !important;
}
</style>
