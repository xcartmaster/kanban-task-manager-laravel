<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';

const props = defineProps({
    // Reusable prop that explicitly accepts any parent HTML container reference or null
    parentContainerRef: {
        type: [Object, null],
        required: true
    }
});

// Dynamic DOM element references (Template Refs automatically bound by Vue 3)
const customScrollbarTrackRef = ref(null); // The outer track boundary of our handmade scrollbar
const customScrollbarThumbRef = ref(null); // The inner draggable handle inside the scroll track

// Reactive flag states
const isDragging = ref(false);          // Reactive flag to track whether the user is currently dragging the custom scroll thumb
const startPointerX = ref(0);           // Stores the absolute X coordinate of the cursor or touch point at the exact moment dragging begins
const startThumbOffsetLeft = ref(0);    // Stores the initial physical horizontal offset position (offsetLeft) of the scroll thumb before dragging begins
const showScrollbar = ref(false);       // Reactive flag to toggle the visibility of the custom scrollbar track
const thumbWidthPercent = ref(20);      // The dynamic width of the scroll thumb in percentages (defaults to 20% fallback size)

// =========================================================================
// DIMENSION CACHE & GEOMETRY EVALUATION
// =========================================================================

// SYSTEM CACHE: Cache variables holding the latest verified layout metrics to share across methods safely
let lastParentContainerScrollWidth = 0;
let lastParentContainerClientWidth = 0;

// Calculates the maximum limit to which the parent DOM container can be scrolled, pulling values from the shared cache variables to avoid repetitive math
function getMaxParentContainerCanBeScrolledLeft() {
    return lastParentContainerScrollWidth - lastParentContainerClientWidth;
}

// Automatically evaluates the layout metrics and shifts the thumb proportionally to the current visible area
// of the parent container when a custom scrollbar becomes active or when the container changes its physical
// dimensions due to window resizing, sidebar toggles, or mobile device orientation flips (rotations).
// It also coordinates updates whenever dynamic child items are injected or removed via asynchronous Inertia.js streams.
// Check if content overflows the container width to dynamically toggle the scrollbar
function checkOverflow() {
    const target = props.parentContainerRef;

    if (!target) return;

    const currentScrollWidth = target.scrollWidth;
    const currentClientWidth = target.clientWidth;

    // Guard Clause: Instantly exits the function if the dimensions haven't shifted, eliminating heavy style recalculations (Reflow)
    if (currentScrollWidth === lastParentContainerScrollWidth && currentClientWidth === lastParentContainerClientWidth) {
        return;
    }

    // Save the verified layout metrics to the system cache for use in other scroll methods
    lastParentContainerScrollWidth = currentScrollWidth;
    lastParentContainerClientWidth = currentClientWidth;

    if (currentScrollWidth > currentClientWidth) {
        // If it overflows, explicitly enable the scrollbar
        showScrollbar.value = true;

        // Calculate the thumb width based on content proportions, then clamp bounds between 10% and 90% for clean UX metrics
        thumbWidthPercent.value = Math.max(10, Math.min((currentClientWidth / currentScrollWidth) * 100, 90));

        // BUGFIX VIA NEXTTICK: Guarantees that Vue completes domestic DOM updates and renders the track
        // into the viewport before we fetch its clientWidth metrics, securing perfect initial thumb alignment.
        nextTick(() => {
            syncThumbPosition(); // Synchronizes the custom thumb position
        });
    } else {
        // CRITICAL: If there is no overflow anymore (e.g. window resized back to huge screen), we must turn off the custom scrollbar track
        showScrollbar.value = false;
    }
}

// Continuously synchronizes the custom thumb position whenever the parent container is scrolled
// via hardware wheels, trackpads, touch gestures, or direct software code logic modifications
function syncThumbPosition() {
    const target = props.parentContainerRef;

    if (!target || isDragging.value) return;

    // Extract the scroll limit directly from the system cache memory to avoid heavy DOM recalculations
    const maxParentContainerCanBeScrolledLeft = getMaxParentContainerCanBeScrolledLeft();

    if (maxParentContainerCanBeScrolledLeft <= 0) return;

    // Calculates the current horizontal scroll ratio of the container (ranging from 0.0 to 1.0)
    // by mapping the live scroll position against the maximum available scroll limit
    const parentContainerScrollRatio = target.scrollLeft / maxParentContainerCanBeScrolledLeft;

    const maxThumbCanBeShiftedLeft = getMaxThumbCanBeShiftedLeft();

    // Smoothly apply the calculated position to the thumb's CSS style to move it visually
    if (customScrollbarThumbRef.value) {
        customScrollbarThumbRef.value.style.left = `${parentContainerScrollRatio * maxThumbCanBeShiftedLeft}px`;
    }
}

// Calculates the dynamic maximum limit to which the 'draggable handle' can be shifted from the left edge
function getMaxThumbCanBeShiftedLeft() {
    if (!customScrollbarTrackRef.value || !customScrollbarThumbRef.value) return 0;
    return customScrollbarTrackRef.value.clientWidth - customScrollbarThumbRef.value.clientWidth; // this is the ceiling for customScrollbarThumbRef.value.style.left
}

// Activate dragging mode and attach global mouse/touch event listeners to start scrolling
function startThumbDrag(event) {
    if (!customScrollbarThumbRef.value) return;

    isDragging.value = true;

    // Correctly extracts the exact X coordinate from the first active mobile touch point index if available, otherwise falls back to standard mouse cursor pageX matrix
    startPointerX.value = event.touches ? event.touches[0].pageX : event.pageX;

    // Lock the initial physical position of the thumb before it starts moving
    startThumbOffsetLeft.value = customScrollbarThumbRef.value.offsetLeft;

    // Add global tracking document event handlers
    document.addEventListener('mousemove', handleThumbMove);
    document.addEventListener('mouseup', stopThumbDrag);

    // { passive: false } is absolutely required here to allow event.preventDefault() on document level
    document.addEventListener('touchmove', handleThumbMove, { passive: false });
    document.addEventListener('touchend', stopThumbDrag, { passive: false });
}

// Recalculates the thumb position continuously while dragging and updates the parent container's scroll position according to the thumb's movement ratio
function handleThumbMove(event) {

    const target = props.parentContainerRef;

    if (!isDragging.value || !target || !customScrollbarThumbRef.value) return;

    // Prevents browser text selection, image dragging, and mobile page bouncing during drag operations
    event.preventDefault();

    // Extracts exact pointer position from the active mobile touch point or desktop mouse coordinate
    const currentPointerX = event.touches ? event.touches[0].pageX : event.pageX;

    // Calculate exactly how many pixels the user's hand/mouse has moved (Delta X)
    const deltaX = currentPointerX - startPointerX.value;

    // Move the thumb by adding this distance to its starting position
    const currentThumbLeft = startThumbOffsetLeft.value + deltaX;

    // Calculates the maximum distance the thumb can scroll from the left edge before hitting the right boundary
    const maxThumbCanBeShiftedLeft = getMaxThumbCanBeShiftedLeft();

    // Constrains the calculated horizontal offset to ensure the thumb stays bounded between 0 and maxThumbCanBeShiftedLeft
    const boundedThumbLeft = Math.max(0, Math.min(currentThumbLeft, maxThumbCanBeShiftedLeft));

    // Apply the calculated and bounded position to the thumb's CSS style to move it visually
    customScrollbarThumbRef.value.style.left = `${boundedThumbLeft}px`;

    // Calculates the current horizontal shift ratio of the slider thumb (ranging from 0.0 to 1.0)
    const thumbScrollRatio = maxThumbCanBeShiftedLeft > 0 ? (boundedThumbLeft / maxThumbCanBeShiftedLeft) : 0;

    // Apply the calculated shift ratio to scroll the actual parent container layout horizontally
    // NOTE: This triggers a standard browser 'scroll-linked effect' warning in Firefox console.
    // This is intentional and required here to update the DOM scroll position dynamically from the JS main thread.
    target.scrollLeft = thumbScrollRatio * getMaxParentContainerCanBeScrolledLeft();
}

// Stops the dragging process and cleans up global event listeners from the document to free memory
function stopThumbDrag() {
    isDragging.value = false;
    document.removeEventListener('mousemove', handleThumbMove);
    document.removeEventListener('mouseup', stopThumbDrag);

    // Must match the configuration used in addEventListener to unbind safely
    document.removeEventListener('touchmove', handleThumbMove, { passive: false });
    document.removeEventListener('touchend', stopThumbDrag, { passive: false });
}

// Global reference to the browser layout sensor monitoring physical boundary shifts
let parentContainerResizeObserver = null;

// Watches for the parent container prop updates. The watcher wakes up instantly, detaches old
// observer "sensors" to prevent memory leaks, executes a baseline geometry measurement for
// the new layout, and attaches fresh "sensors" to track all future layout updates.
watch(() => props.parentContainerRef, (newParentContainerRef, oldParentContainerRef) => {
    // Safely remove listeners from the previous container if it changed or unmounted
    if (oldParentContainerRef) {
        oldParentContainerRef.removeEventListener('scroll', syncThumbPosition);

        // Safely disconnects the observer instance to free up browser memory and prevent leaks
        if (parentContainerResizeObserver) parentContainerResizeObserver.disconnect();

        // Reset the system cache variables securely when the container unmounts
        lastParentContainerScrollWidth = 0;
        lastParentContainerClientWidth = 0;
    }

    // Bind native observers to the freshly resolved parent container element
    if (newParentContainerRef) {
        // Synchronizes the custom thumb position dynamically whenever the container is scrolled via wheels, trackpads, touch gestures, or code logic
        newParentContainerRef.addEventListener('scroll', syncThumbPosition);

        // Instantiates a ResizeObserver that automatically fires the 'checkOverflow' function whenever monitored elements (newParentContainerRef or its firstElementChild) change their size
        parentContainerResizeObserver = new ResizeObserver(checkOverflow);

        // Tracks outer container structure shifts caused by window stretching, sidebar toggles, or screen rotations
        parentContainerResizeObserver.observe(newParentContainerRef);

        // Tracks dynamic internal content shifts caused by adding or removing dynamic child items via Inertia.js
        if (newParentContainerRef.firstElementChild) {
            parentContainerResizeObserver.observe(newParentContainerRef.firstElementChild);
        }

        // Executes an immediate baseline check to verify layout constraints right after matching the element
        checkOverflow();
    }
}, {
    // Forces the watcher to run instantly upon component initialization, ensuring we capture the parent DOM element the exact microsecond it becomes available.
    immediate: true
    // CRITICAL NOTE: 'deep: true' is intentionally omitted here. Since 'parentContainerRef' holds a raw HTMLDivElement,
    // enabling deep watching would force Vue to recursively scan hundreds of native DOM properties, causing severe FPS drops.
});

// Check layout overflow on component mount
onMounted(() => {
    // Safely wait for Vue to finish domestic DOM patches before evaluating parent container geometries
    nextTick(() => {
        checkOverflow();
    });
});

// Disconnects active observers and listeners on unmount to free memory and prevent leaks
onUnmounted(() => {
    if (props.parentContainerRef) {
        props.parentContainerRef.removeEventListener('scroll', syncThumbPosition);
    }

    // Safely disconnects the observer instance to free up browser memory and prevent leaks
    if (parentContainerResizeObserver) parentContainerResizeObserver.disconnect();
});
</script>

<template>
    <!-- --- HANDMADE SCROLLBAR TRACK --- -->
    <div v-if="showScrollbar" ref="customScrollbarTrackRef"
         class="relative w-full h-3.5 bg-gray-200 dark:bg-zinc-800 rounded-full overflow-hidden select-none">
        <!-- Draggable track slider thumb with reactive inline width rules -->
        <div ref="customScrollbarThumbRef"
             @mousedown="startThumbDrag"
             @touchstart="startThumbDrag"
             class="absolute top-0 left-0 h-full bg-gray-400 hover:bg-gray-500 dark:bg-zinc-600 dark:hover:bg-zinc-500 rounded-full cursor-grab active:cursor-grabbing transition-colors duration-150"
             :style="{ width: thumbWidthPercent + '%' }">
        </div>
    </div>
</template>
