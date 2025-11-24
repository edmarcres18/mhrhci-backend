<script setup lang="ts">
import { login } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const mobileMenuOpen = ref(false);
const { siteSettings } = useSiteSettings();
const currentYear = new Date().getFullYear();

// Animation state for interactive demo
const activeCard = ref(0);
const cursorPosition = ref({ x: 50, y: 30 });
const isClicking = ref(false);
const currentAction = ref('');
const actionProgress = ref(0);

// Card positions for accurate cursor targeting (percentages relative to the white container)
const cardPositions = [
    { x: 28, y: 45 },  // Products (top-left)
    { x: 72, y: 45 },  // Blog Posts (top-right)
    { x: 28, y: 70 },  // Users (bottom-left)
    { x: 72, y: 70 },  // Database (bottom-right)
];

// Cycle through different management sections with actions
const managementSections = ref([
    { 
        title: 'Products', 
        count: 24, 
        icon: 'shopping', 
        color: 'blue',
        action: 'Adding new product...',
        subAction: 'Product "Medical Mask" created'
    },
    { 
        title: 'Blog Posts', 
        count: 18, 
        icon: 'blog', 
        color: 'green',
        action: 'Creating blog post...',
        subAction: 'Post "Health Tips 2025" published'
    },
    { 
        title: 'Users', 
        count: 156, 
        icon: 'users', 
        color: 'purple',
        action: 'Managing users...',
        subAction: 'User permissions updated'
    },
    { 
        title: 'Database', 
        count: 99, 
        icon: 'database', 
        color: 'orange',
        action: 'Scheduling backup...',
        subAction: 'Backup scheduled for 2:00 AM'
    },
]);

// Auto-cycle through cards
let cardInterval: number | null = null;
const cursorInterval: number | null = null;
const actionInterval: number | null = null;

const moveCursorToCard = (cardIndex: number) => {
    const targetPos = cardPositions[cardIndex];
    const startPos = { ...cursorPosition.value };
    const steps = 20;
    let currentStep = 0;

    const moveInterval = setInterval(() => {
        currentStep++;
        const progress = currentStep / steps;
        const easeProgress = 1 - Math.pow(1 - progress, 3); // Ease out cubic
        
        cursorPosition.value = {
            x: startPos.x + (targetPos.x - startPos.x) * easeProgress,
            y: startPos.y + (targetPos.y - startPos.y) * easeProgress,
        };

        if (currentStep >= steps) {
            clearInterval(moveInterval);
            // Simulate click
            isClicking.value = true;
            setTimeout(() => {
                isClicking.value = false;
            }, 200);
        }
    }, 30);
};

const startActionAnimation = () => {
    actionProgress.value = 0;
    currentAction.value = managementSections.value[activeCard.value].action;
    
    const progressInterval = setInterval(() => {
        actionProgress.value += 5;
        if (actionProgress.value >= 100) {
            clearInterval(progressInterval);
            currentAction.value = managementSections.value[activeCard.value].subAction;
            
            // Increment count on completion
            if (activeCard.value < 3) { // Not for database (percentage)
                managementSections.value[activeCard.value].count++;
            }
            
            setTimeout(() => {
                currentAction.value = '';
            }, 1500);
        }
    }, 50);
};

const startAnimations = () => {
    // Initial cursor movement
    setTimeout(() => {
        moveCursorToCard(0);
        startActionAnimation();
    }, 500);

    // Card cycling animation with accurate cursor movement
    cardInterval = window.setInterval(() => {
        const nextCard = (activeCard.value + 1) % managementSections.value.length;
        activeCard.value = nextCard;
        moveCursorToCard(nextCard);
        startActionAnimation();
    }, 4000);
};

const stopAnimations = () => {
    if (cardInterval) clearInterval(cardInterval);
    if (cursorInterval) clearInterval(cursorInterval);
};

// Lifecycle
import { onMounted, onUnmounted } from 'vue';
onMounted(() => {
    startAnimations();
});

onUnmounted(() => {
    stopAnimations();
});
</script>

<template>
    <Head title="MHR Health Care Inc - Backend Management Panel">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>
    <!-- Main Container -->
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        
        <!-- Navigation Bar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm border-b border-blue-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 md:h-20">
                    <!-- Logo & Brand -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-lg flex items-center justify-center shadow-lg border border-neutral-200">
                            <img 
                                v-if="siteSettings.site_logo" 
                                :src="siteSettings.site_logo" 
                                :alt="siteSettings.site_name"
                                class="h-8 w-8 md:h-10 md:w-10 object-contain"
                            />
                            <AppLogoIcon
                                v-else
                                class="w-6 h-6 md:w-7 md:h-7 fill-current text-neutral-900"
                            />
                        </div>
                        <div>
                            <span class="text-lg md:text-xl font-bold text-blue-900 block leading-tight">{{ siteSettings.site_name }}</span>
                            <span class="text-xs text-blue-600 font-medium hidden sm:block">Management Panel</span>
                        </div>
                    </div>

                    <!-- Login & Mobile Menu -->
                    <div class="flex items-center space-x-4">
                        <Link :href="login()" class="inline-flex items-center px-4 md:px-6 py-2 md:py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                            <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Login</span>
                        </Link>
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-blue-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-24 md:pt-32 pb-12 md:pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-8 md:gap-12 items-center">
                    <!-- Hero Content -->
                    <div class="space-y-6 md:space-y-8 text-center lg:text-left">
                        <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                                <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                                <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z" />
                            </svg>
                            Backend Management System
                        </div>

                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-gray-900 leading-tight">
                            Backend Database
                            <span class="text-blue-600 block mt-2">Management Panel</span>
                        </h1>

                        <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto lg:mx-0">
                            Manage the backend database for MHR Health Care Inc's website. Control content, products, blogs, and client interactions through a powerful administrative interface.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <Link :href="login()" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                                Access Dashboard
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>

                        <!-- Management Features -->
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 pt-6 md:pt-8 border-t border-blue-100">
                            <div class="text-center">
                                <div class="text-blue-600">
                                    <svg class="w-8 h-8 md:w-10 md:h-10 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                    </svg>
                                </div>
                                <div class="text-xs md:text-sm text-gray-600 mt-2 font-medium">Products</div>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-600">
                                    <svg class="w-8 h-8 md:w-10 md:h-10 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd" />
                                        <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z" />
                                    </svg>
                                </div>
                                <div class="text-xs md:text-sm text-gray-600 mt-2 font-medium">Blogs</div>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-600">
                                    <svg class="w-8 h-8 md:w-10 md:h-10 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="text-xs md:text-sm text-gray-600 mt-2 font-medium">Website Info</div>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-600">
                                    <svg class="w-8 h-8 md:w-10 md:h-10 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                                        <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                                        <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z" />
                                    </svg>
                                </div>
                                <div class="text-xs md:text-sm text-gray-600 mt-2 font-medium">Database</div>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-600">
                                    <svg class="w-8 h-8 md:w-10 md:h-10 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                </div>
                                <div class="text-xs md:text-sm text-gray-600 mt-2 font-medium">Users</div>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Visual - Animated Dashboard Mockup -->
                    <div class="relative lg:mt-0 mt-8">
                        <div class="relative bg-gradient-to-br from-blue-100 to-blue-200 rounded-3xl p-6 md:p-8 shadow-2xl overflow-hidden">
                            <!-- Animated Cursor -->
                            <div 
                                class="absolute pointer-events-none transition-all duration-75 z-50"
                                :style="{
                                    left: `${cursorPosition.x}%`,
                                    top: `${cursorPosition.y}%`,
                                    transform: 'translate(-50%, -50%)'
                                }"
                            >
                                <div class="relative">
                                    <!-- Cursor Icon -->
                                    <svg 
                                        class="w-6 h-6 text-gray-800 drop-shadow-lg transition-transform duration-200"
                                        :class="isClicking ? 'scale-90' : 'scale-100'"
                                        fill="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M5 3l3.057 13.573L10.5 14.5l2.073 2.073L23 5z" />
                                    </svg>
                                    <!-- Click Ripple Effect -->
                                    <div 
                                        v-if="isClicking" 
                                        class="absolute top-0 left-0 w-8 h-8 -translate-x-1/4 -translate-y-1/4 bg-blue-400 rounded-full opacity-50 animate-ping"
                                    ></div>
                                </div>
                            </div>

                            <!-- Action Notification -->
                            <transition name="slide-down">
                                <div 
                                    v-if="currentAction" 
                                    class="absolute top-4 left-1/2 -translate-x-1/2 bg-white px-4 py-2 rounded-lg shadow-lg border-2 border-blue-500 z-40 min-w-[200px]"
                                >
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                        <p class="text-sm font-semibold text-gray-800">{{ currentAction }}</p>
                                    </div>
                                    <div v-if="actionProgress < 100" class="mt-2 h-1 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-blue-500 rounded-full transition-all duration-100"
                                            :style="{ width: `${actionProgress}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </transition>

                            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-lg relative">
                                <!-- Dashboard Header -->
                                <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center shadow-md animate-pulse">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                                                <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                                                <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z" />
                                            </svg>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="h-3 w-32 bg-gradient-to-r from-gray-300 to-gray-200 rounded animate-pulse"></div>
                                            <div class="h-2 w-20 bg-gradient-to-r from-gray-200 to-gray-100 rounded animate-pulse" style="animation-delay: 0.2s"></div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <div class="w-3 h-3 bg-red-400 rounded-full shadow-sm"></div>
                                        <div class="w-3 h-3 bg-yellow-400 rounded-full shadow-sm"></div>
                                        <div class="w-3 h-3 bg-green-400 rounded-full shadow-sm"></div>
                                    </div>
                                </div>

                                <!-- Interactive Management Cards -->
                                <div class="grid grid-cols-2 gap-4 mt-6">
                                    <div 
                                        v-for="(section, index) in managementSections" 
                                        :key="section.title"
                                        class="rounded-xl p-4 space-y-2 transition-all duration-500 cursor-pointer relative overflow-hidden"
                                        :class="[
                                            activeCard === index 
                                                ? `bg-${section.color}-50 ring-2 ring-${section.color}-400 scale-105 shadow-lg` 
                                                : 'bg-gray-50 hover:bg-gray-100'
                                        ]"
                                    >
                                        <!-- Shimmer effect on active card -->
                                        <div 
                                            v-if="activeCard === index" 
                                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-30"
                                            style="animation: shimmer 2s infinite; transform: translateX(-100%)"
                                        ></div>
                                        
                                        <div class="flex items-center justify-between relative z-10">
                                            <div 
                                                class="w-10 h-10 rounded-lg flex items-center justify-center shadow-md transition-transform duration-300"
                                                :class="[
                                                    activeCard === index 
                                                        ? `bg-${section.color}-600 scale-110` 
                                                        : 'bg-gray-400'
                                                ]"
                                            >
                                                <!-- Dynamic Icons -->
                                                <svg v-if="section.icon === 'shopping'" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                                                </svg>
                                                <svg v-else-if="section.icon === 'blog'" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else-if="section.icon === 'users'" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                </svg>
                                                <svg v-else-if="section.icon === 'database'" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                                                    <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                                                </svg>
                                            </div>
                                            <div 
                                                class="text-2xl font-bold transition-colors duration-300"
                                                :class="activeCard === index ? `text-${section.color}-600` : 'text-gray-400'"
                                            >
                                                {{ section.icon === 'database' ? section.count + '%' : section.count }}
                                            </div>
                                        </div>
                                        <div class="relative z-10">
                                            <div 
                                                class="text-sm font-semibold transition-colors duration-300"
                                                :class="activeCard === index ? `text-${section.color}-700` : 'text-gray-500'"
                                            >
                                                {{ section.title }}
                                            </div>
                                            <div class="h-1 bg-gray-200 rounded-full mt-2 overflow-hidden">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-1000"
                                                    :class="`bg-${section.color}-500`"
                                                    :style="{ width: activeCard === index ? '100%' : '0%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Animated Content Lines -->
                                <div class="space-y-3 mt-6">
                                    <div 
                                        v-for="i in 3" 
                                        :key="i"
                                        class="h-3 bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 rounded-full transition-all duration-500"
                                        :class="activeCard >= i - 1 ? 'opacity-100' : 'opacity-30'"
                                        :style="{ 
                                            width: i === 1 ? '100%' : i === 2 ? '85%' : '70%',
                                            animationDelay: `${i * 0.2}s`
                                        }"
                                    ></div>
                                </div>

                                <!-- Action Button Animation -->
                                <div class="mt-6 flex justify-end space-x-2">
                                    <div 
                                        v-for="i in 2" 
                                        :key="i"
                                        class="px-4 py-2 rounded-lg transition-all duration-300"
                                        :class="activeCard % 2 === i - 1 ? 'bg-blue-600 shadow-lg scale-105' : 'bg-gray-200'"
                                    >
                                        <div class="h-2 w-12 bg-white/50 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 px-4 sm:px-6 lg:px-8 bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto text-center">
                <p class="text-gray-600">&copy; {{ currentYear }} MHR Health Care Inc. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Slide down transition for notifications */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
}

.slide-down-enter-from {
    opacity: 0;
    transform: translateX(-50%) translateY(-10px);
}

.slide-down-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(-10px);
}

.slide-down-enter-to,
.slide-down-leave-from {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Color-specific classes for proper Tailwind compilation */
.bg-blue-50 { background-color: rgb(239 246 255); }
.bg-green-50 { background-color: rgb(240 253 244); }
.bg-purple-50 { background-color: rgb(250 245 255); }
.bg-orange-50 { background-color: rgb(255 247 237); }

.ring-blue-400 { --tw-ring-color: rgb(96 165 250); }
.ring-green-400 { --tw-ring-color: rgb(74 222 128); }
.ring-purple-400 { --tw-ring-color: rgb(192 132 252); }
.ring-orange-400 { --tw-ring-color: rgb(251 146 60); }

.bg-blue-600 { background-color: rgb(37 99 235); }
.bg-green-600 { background-color: rgb(22 163 74); }
.bg-purple-600 { background-color: rgb(147 51 234); }
.bg-orange-600 { background-color: rgb(234 88 12); }

.text-blue-600 { color: rgb(37 99 235); }
.text-green-600 { color: rgb(22 163 74); }
.text-purple-600 { color: rgb(147 51 234); }
.text-orange-600 { color: rgb(234 88 12); }

.text-blue-700 { color: rgb(29 78 216); }
.text-green-700 { color: rgb(21 128 61); }
.text-purple-700 { color: rgb(126 34 206); }
.text-orange-700 { color: rgb(194 65 12); }

.bg-blue-500 { background-color: rgb(59 130 246); }
.bg-green-500 { background-color: rgb(34 197 94); }
.bg-purple-500 { background-color: rgb(168 85 247); }
.bg-orange-500 { background-color: rgb(249 115 22); }
</style>
                              
