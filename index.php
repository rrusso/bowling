<?php
require_once __DIR__ . '/src/app_data.php';

$bowlers = getBowlerData();
$history = getBowlerHistory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>StrikeStats</title>
    <!-- PWA -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="StrikeStats">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="https://testrusso.lsu.edu/my/bowling/assets/StrikeStats.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    colors: {
                        ios: {
                            bg: '#000000',
                            card: '#1c1c1e',
                            separator: '#38383a',
                            blue: '#0a84ff',
                            text: '#ffffff',
                            subtext: '#8e8e93',
                            gold: '#ffd700',
                            silver: '#c0c0c0',
                            bronze: '#cd7f32',
                            green: '#34c759',
                            red: '#ff3b30'
                        }
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <style>
        body { padding-bottom: env(safe-area-inset-bottom); -webkit-tap-highlight-color: transparent; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-black text-black dark:text-white h-screen flex flex-col overflow-hidden" x-data="app()">

    <!-- Main Scrollable Area -->
    <main class="flex-1 overflow-y-auto no-scrollbar pb-24">

        <!-- Header / Hero -->
        <div class="relative pt-safe pb-4 px-4 bg-white dark:bg-black border-b border-gray-100 dark:border-ios-separator sticky top-0 z-40 bg-opacity-90 backdrop-blur-md">
             <div class="flex items-center justify-between mb-4">
                <div>
                     <h1 class="text-3xl font-extrabold tracking-tight text-black dark:text-white flex items-center">
                        <img src="assets/StrikeStats.png" class="h-10 w-auto mr-2 object-contain rounded-lg" alt="Logo">
                        StrikeStats
                    </h1>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wide">League Leaderboard</p>
                </div>
             </div>
             <!-- Search Bar -->
             <div x-show="currentTab === 'bowlers' || currentTab === 'history'" class="relative" x-transition>
                 <input type="text" x-model="searchQuery" placeholder="Search bowlers or schools..."
                        class="w-full bg-gray-100 dark:bg-gray-800 text-black dark:text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ios-blue transition-all">
                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                 </div>
                 <button x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
             </div>
        </div>

        <!-- Tab: Bowlers (Leaderboard) -->
        <div x-show="currentTab === 'bowlers'" class="px-4" x-transition.opacity>

            <!-- Podium (Top 3) -->
            <div x-show="!searchQuery" class="flex justify-center items-end space-x-2 mb-8 mt-6" x-transition>
                <!-- Rank 2 (Silver) -->
                <template x-if="top3[1]">
                    <div @click="openBowler(top3[1])" class="flex flex-col items-center w-1/3 cursor-pointer transform hover:scale-105 transition duration-200">
                        <div class="relative mb-2">
                             <div class="w-20 h-20 rounded-full border-2 border-ios-silver bg-gray-200 dark:bg-gray-800 overflow-hidden shadow-md">
                                <img :src="getBowlerImage(top3[1].BowlerID)" class="w-full h-full object-cover" alt="Avatar">
                             </div>
                             <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-ios-silver text-black text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm z-10">#2</div>
                        </div>
                        <h3 class="text-xs font-bold text-center truncate w-full mt-1" x-text="cleanName(top3[1].BowlerName)"></h3>
                        <p @click.stop="openTeamDetail(top3[1].TeamName)" class="text-[10px] text-gray-500 dark:text-gray-400 truncate w-full text-center hover:underline cursor-pointer" x-text="top3[1].TeamName"></p>
                        <p class="text-sm font-black text-ios-blue" x-text="top3[1].Average"></p>
                    </div>
                </template>

                <!-- Rank 1 (Gold) -->
                <template x-if="top3[0]">
                    <div @click="openBowler(top3[0])" class="flex flex-col items-center w-1/3 cursor-pointer transform hover:scale-105 transition duration-200 z-10 -mb-2">
                         <div class="relative mb-2">
                             <div class="w-24 h-24 rounded-full border-4 border-ios-gold bg-gray-200 dark:bg-gray-800 overflow-hidden shadow-lg shadow-ios-gold/20">
                                <img :src="getBowlerImage(top3[0].BowlerID)" class="w-full h-full object-cover" alt="Avatar">
                             </div>
                             <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 bg-ios-gold text-black text-xs font-bold px-3 py-0.5 rounded-full shadow-sm z-10">#1</div>
                        </div>
                        <h3 class="text-sm font-bold text-center truncate w-full mt-2" x-text="cleanName(top3[0].BowlerName)"></h3>
                        <p @click.stop="openTeamDetail(top3[0].TeamName)" class="text-[10px] text-gray-500 dark:text-gray-400 truncate w-full text-center hover:underline cursor-pointer" x-text="top3[0].TeamName"></p>
                        <p class="text-lg font-black text-ios-blue" x-text="top3[0].Average"></p>
                    </div>
                </template>

                <!-- Rank 3 (Bronze) -->
                <template x-if="top3[2]">
                    <div @click="openBowler(top3[2])" class="flex flex-col items-center w-1/3 cursor-pointer transform hover:scale-105 transition duration-200">
                        <div class="relative mb-2">
                             <div class="w-20 h-20 rounded-full border-2 border-ios-bronze bg-gray-200 dark:bg-gray-800 overflow-hidden shadow-md">
                                <img :src="getBowlerImage(top3[2].BowlerID)" class="w-full h-full object-cover" alt="Avatar">
                             </div>
                             <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-ios-bronze text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm z-10">#3</div>
                        </div>
                        <h3 class="text-xs font-bold text-center truncate w-full mt-1" x-text="cleanName(top3[2].BowlerName)"></h3>
                        <p @click.stop="openTeamDetail(top3[2].TeamName)" class="text-[10px] text-gray-500 dark:text-gray-400 truncate w-full text-center hover:underline cursor-pointer" x-text="top3[2].TeamName"></p>
                        <p class="text-sm font-black text-ios-blue" x-text="top3[2].Average"></p>
                    </div>
                </template>
            </div>

            <!-- List (Rank 4+) -->
            <div class="space-y-3 mt-8">
                <template x-for="(bowler, index) in listItems" :key="bowler.BowlerID">
                    <div @click="openBowler(bowler)" class="flex items-center p-3 bg-white dark:bg-ios-card rounded-xl shadow-sm border border-gray-100 dark:border-ios-separator active:scale-[0.98] transition-transform cursor-pointer">
                        <!-- Rank -->
                        <div class="w-8 text-center font-mono text-gray-400 font-bold text-sm" x-text="searchQuery ? (index + 1) : (index + 4)"></div>

                        <!-- Avatar -->
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden mr-3 border border-gray-200 dark:border-gray-600">
                            <img :src="getBowlerImage(bowler.BowlerID)" class="w-full h-full object-cover" loading="lazy" alt="Avatar">
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-black dark:text-white truncate" x-text="cleanName(bowler.BowlerName)"></h3>
                            <p @click.stop="openTeamDetail(bowler.TeamName)" class="text-xs text-gray-500 truncate hover:underline cursor-pointer" x-text="bowler.TeamName"></p>
                        </div>

                        <!-- Average -->
                        <div class="text-right">
                            <div class="text-lg font-bold text-ios-blue" x-text="bowler.Average"></div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-wider">Avg</div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab: History -->
        <div x-show="currentTab === 'history'" class="px-4" x-transition.opacity style="display: none;">
            <div class="space-y-3 mt-4">
                <template x-for="(bowler, index) in filteredBowlers" :key="'hist-' + bowler.BowlerID">
                    <div @click="openHistoryGraph(bowler)" class="flex items-center p-3 bg-white dark:bg-ios-card rounded-xl shadow-sm border border-gray-100 dark:border-ios-separator active:scale-[0.98] transition-transform cursor-pointer">
                        <!-- Avatar -->
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden mr-3 border border-gray-200 dark:border-gray-600">
                            <img :src="getBowlerImage(bowler.BowlerID)" class="w-full h-full object-cover" loading="lazy" alt="Avatar">
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-black dark:text-white truncate" x-text="cleanName(bowler.BowlerName)"></h3>
                            <p class="text-xs text-gray-500 truncate" x-text="bowler.TeamName"></p>
                        </div>

                        <!-- Icon -->
                        <div class="text-right text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab: Teams -->
        <div x-show="currentTab === 'teams'" class="px-4 py-4" x-transition.opacity style="display: none;">
             <h2 class="text-2xl font-bold mb-4">Teams</h2>
             <div class="space-y-2">
                <template x-for="team in teamsList" :key="team.name">
                    <div @click="openTeamDetail(team.name)" class="flex justify-between items-center p-4 bg-white dark:bg-ios-card rounded-xl border border-gray-100 dark:border-ios-separator cursor-pointer active:scale-[0.99] transition-transform shadow-sm">
                        <span class="font-semibold" x-text="team.name"></span>
                        <div class="flex items-center text-gray-500 text-sm">
                            <span x-text="team.count"></span>
                            <span class="ml-1">Bowlers</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </template>
             </div>
        </div>

        <!-- Tab: Compare -->
        <div x-show="currentTab === 'compare'" class="px-4 py-4" x-transition.opacity style="display: none;">
            <h2 class="text-2xl font-bold mb-4">Compare</h2>
             <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-xs text-gray-500 uppercase font-bold ml-1">Team A</label>
                    <div class="relative">
                        <select x-model="compareTeamA" class="w-full mt-1 bg-white dark:bg-ios-card rounded-lg p-3 text-sm border border-gray-200 dark:border-ios-separator appearance-none">
                            <option value="">Select Team</option>
                            <template x-for="team in teamsList" :key="team.name"><option :value="team.name" x-text="team.name"></option></template>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase font-bold ml-1">Team B</label>
                    <div class="relative">
                        <select x-model="compareTeamB" class="w-full mt-1 bg-white dark:bg-ios-card rounded-lg p-3 text-sm border border-gray-200 dark:border-ios-separator appearance-none">
                            <option value="">None</option>
                            <template x-for="team in teamsList" :key="team.name"><option :value="team.name" x-text="team.name"></option></template>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Output -->
            <div x-show="compareTeamA" class="bg-white dark:bg-ios-card rounded-xl p-4 border border-gray-100 dark:border-ios-separator shadow-sm">
                <!-- Head to Head or Single Team -->
                <div class="flex justify-between items-center mb-6">
                    <div class="text-center w-1/2">
                        <h2 class="font-bold text-sm leading-tight" x-text="compareTeamA"></h2>
                        <span class="text-xs text-gray-500" x-text="calculateTeamAverage(compareTeamA) + ' Avg (Top 6)'"></span>
                    </div>
                    <div class="text-gray-300 font-bold" x-show="compareTeamB">VS</div>
                    <div class="text-center w-1/2" x-show="compareTeamB">
                        <h2 class="font-bold text-sm leading-tight" x-text="compareTeamB"></h2>
                        <span class="text-xs text-gray-500" x-text="calculateTeamAverage(compareTeamB) + ' Avg (Top 6)'"></span>
                    </div>
                </div>

                <div class="space-y-3">
                    <template x-for="(row, index) in getComparisonRows()" :key="index">
                        <div @click="openComparison(row)" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2 flex items-center justify-between text-xs cursor-pointer active:scale-[0.98] transition-transform">
                            <!-- Team A Bowler -->
                            <div class="w-[45%] text-left overflow-hidden flex items-center space-x-2">
                                <template x-if="row.a">
                                    <div class="flex items-center space-x-2 w-full">
                                        <img :src="getBowlerImage(row.a.BowlerID)" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="Avatar">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold truncate" x-text="cleanName(row.a.BowlerName)"></div>
                                            <div class="text-gray-500" x-text="'Avg: ' + row.a.Average"></div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!row.a">
                                    <div class="w-full text-center text-gray-400">-</div>
                                </template>
                            </div>

                            <!-- Rank -->
                            <div class="w-[10%] text-center font-mono text-gray-400 font-bold" x-text="index + 1"></div>

                            <!-- Team B Bowler -->
                            <div class="w-[45%] text-right overflow-hidden flex items-center justify-end space-x-2">
                                <template x-if="compareTeamB">
                                    <div class="flex items-center justify-end space-x-2 w-full">
                                        <template x-if="row.b">
                                            <div class="flex items-center justify-end space-x-2 w-full">
                                                <div class="min-w-0 flex-1 text-right">
                                                    <div class="font-semibold truncate" x-text="cleanName(row.b.BowlerName)"></div>
                                                    <div class="text-gray-500" x-text="'Avg: ' + row.b.Average"></div>
                                                </div>
                                                <img :src="getBowlerImage(row.b.BowlerID)" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="Avatar">
                                            </div>
                                        </template>
                                        <template x-if="!row.b">
                                            <div class="w-full text-center text-gray-400">-</div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!compareTeamB && row.a">
                                     <div class="text-gray-500 text-[10px] text-right w-full">
                                         <div x-text="'Hi: ' + row.a.HighScratchGame"></div>
                                         <div x-text="'Ser: ' + row.a.HighScratchSeries"></div>
                                     </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

             <div x-show="!compareTeamA" class="text-center text-gray-400 py-10">
                Select a team to begin analysis.
            </div>
        </div>

    </main>

    <!-- Bottom Nav -->
    <nav class="fixed bottom-0 w-full bg-white/90 dark:bg-ios-card/90 backdrop-blur-md border-t border-gray-200 dark:border-ios-separator pb-safe pt-2 px-6 flex justify-around items-center z-50 h-[83px]">
        <button @click="currentTab = 'bowlers'" class="flex flex-col items-center w-16 transition-colors duration-200" :class="currentTab === 'bowlers' ? 'text-ios-blue' : 'text-gray-400 dark:text-gray-500'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <span class="text-[10px] font-medium mt-1">Bowlers</span>
        </button>
        <button @click="currentTab = 'teams'" class="flex flex-col items-center w-16 transition-colors duration-200" :class="currentTab === 'teams' ? 'text-ios-blue' : 'text-gray-400 dark:text-gray-500'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span class="text-[10px] font-medium mt-1">Teams</span>
        </button>
        <button @click="currentTab = 'history'" class="flex flex-col items-center w-16 transition-colors duration-200" :class="currentTab === 'history' ? 'text-ios-blue' : 'text-gray-400 dark:text-gray-500'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-[10px] font-medium mt-1">History</span>
        </button>
        <button @click="currentTab = 'compare'" class="flex flex-col items-center w-16 transition-colors duration-200" :class="currentTab === 'compare' ? 'text-ios-blue' : 'text-gray-400 dark:text-gray-500'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-[10px] font-medium mt-1">Compare</span>
        </button>
    </nav>

    <!-- History Graph Modal -->
    <div x-show="showHistoryModal"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-transition.opacity
         style="display: none;">
        <div class="bg-white dark:bg-ios-card w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform transition-all h-[80vh] flex flex-col"
             @click.away="showHistoryModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">

             <!-- Header -->
             <div class="bg-gray-100 dark:bg-gray-900 p-4 border-b border-gray-200 dark:border-ios-separator flex justify-between items-center shrink-0">
                 <div>
                    <h3 class="font-bold text-lg dark:text-white" x-text="selectedHistoryBowler ? cleanName(selectedHistoryBowler.BowlerName) : ''"></h3>
                    <p class="text-xs text-gray-500" x-text="selectedHistoryBowler ? selectedHistoryBowler.TeamName : ''"></p>
                 </div>
                 <button @click="showHistoryModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
             </div>

             <!-- Main Content -->
             <div class="flex-1 flex flex-col p-4 overflow-hidden">

                <!-- Selected Game Info -->
                <div class="text-center mb-6 shrink-0 h-24 flex flex-col justify-center">
                    <template x-if="selectedGame">
                        <div>
                            <div class="text-4xl font-black text-ios-blue mb-1" x-text="selectedGame.Score"></div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-300" x-text="formatDate(selectedGame.Date)"></div>
                            <div class="text-xs text-gray-400 mt-1 uppercase tracking-wide">
                                Week <span x-text="selectedGame.Week"></span> • Game <span x-text="selectedGame.Game"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedGame && historyData.length === 0">
                        <div class="text-gray-400">No game history available.</div>
                    </template>
                </div>

                <!-- Graph Container -->
                <div class="flex-1 relative border-l border-b border-gray-200 dark:border-ios-separator pl-1 pb-1">
                    <!-- Y-Axis Lines (0, 100, 200, 300) -->
                    <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
                         <div class="border-t border-gray-400 w-full h-0"></div> <!-- 300 -->
                         <div class="border-t border-gray-400 w-full h-0"></div> <!-- 200 -->
                         <div class="border-t border-gray-400 w-full h-0"></div> <!-- 100 -->
                         <div class="border-t border-gray-400 w-full h-0"></div> <!-- 0 -->
                    </div>

                    <!-- Scrollable Area -->
                    <div class="absolute inset-0 overflow-x-auto overflow-y-hidden no-scrollbar flex items-end px-4 space-x-2">
                         <template x-for="(game, index) in historyData" :key="index">
                             <div @click="selectedGame = game"
                                  class="flex-shrink-0 flex flex-col justify-end items-center group cursor-pointer transition-all duration-200 h-full"
                                  :class="selectedGame === game ? 'opacity-100 scale-105' : 'opacity-70 hover:opacity-90'">

                                 <!-- Bar -->
                                 <div class="w-6 sm:w-8 rounded-t-md transition-all duration-500 ease-out shadow-sm relative"
                                      :class="getBarColor(index)"
                                      :style="`height: ${(Math.min(game.Score, 300) / 300) * 100}%`">
                                 </div>

                                 <!-- X-Axis Label -->
                                 <div class="text-[10px] text-gray-400 mt-1 font-mono absolute -bottom-6" x-text="index + 1"></div>
                             </div>
                         </template>
                    </div>
                </div>

                <div class="text-center mt-6 text-[10px] text-gray-400 uppercase tracking-widest shrink-0">Game Number</div>
             </div>
        </div>
    </div>

    <!-- Bowler Modal -->
    <div x-show="selectedBowler"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-transition.opacity
         style="display: none;">
        <div class="bg-white dark:bg-ios-card w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden transform transition-all"
             @click.away="selectedBowler = null"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">

            <template x-if="selectedBowler">
                <div>
                    <!-- Modal Header -->
                    <div class="relative h-32 bg-gradient-to-br from-ios-blue to-blue-600 p-4 flex flex-col justify-end">
                        <button @click="selectedBowler = null" class="absolute top-4 right-4 text-white/80 hover:text-white bg-black/20 rounded-full p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                        <h2 class="text-2xl font-bold text-white leading-none mb-1" x-text="cleanName(selectedBowler.BowlerName)"></h2>
                        <p @click="openTeamDetail(selectedBowler.TeamName); selectedBowler = null" class="text-white/80 text-sm font-medium hover:underline cursor-pointer" x-text="selectedBowler.TeamName"></p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl text-center">
                            <div class="text-3xl font-black text-ios-blue" x-text="selectedBowler.Average"></div>
                            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Average</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl text-center flex flex-col justify-center">
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-200" x-text="selectedBowler.TotalPins"></div>
                            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Total Pins</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl text-center">
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-200" x-text="selectedBowler.HighScratchGame"></div>
                            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">High Game</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl text-center">
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-200" x-text="selectedBowler.HighScratchSeries"></div>
                            <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">High Series</div>
                        </div>
                         <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl text-center col-span-2 flex justify-between px-6 items-center">
                             <div class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Games Played</div>
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-200" x-text="selectedBowler.TotalGames"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Head-to-Head Comparison Modal -->
    <div x-show="showComparisonModal"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition.opacity
         style="display: none;">
        <div class="bg-white dark:bg-ios-card w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform transition-all border border-gray-100 dark:border-ios-separator"
             @click.away="showComparisonModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">

             <template x-if="comparisonRow">
                 <div class="flex flex-col h-full max-h-[90vh]">
                     <!-- Header -->
                     <div class="bg-gray-100 dark:bg-gray-900 p-4 border-b border-gray-200 dark:border-ios-separator flex justify-between items-center">
                         <h3 class="font-bold text-lg dark:text-white">Head-to-Head</h3>
                         <button @click="showComparisonModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                     </div>

                     <div class="flex flex-1 overflow-y-auto">
                         <!-- Left Side (Team A) -->
                         <div class="flex-1 p-4 border-r border-gray-100 dark:border-ios-separator flex flex-col items-center">
                             <template x-if="comparisonRow.a">
                                 <div class="flex flex-col items-center w-full text-center">
                                     <img :src="getBowlerImage(comparisonRow.a.BowlerID)" class="w-20 h-20 rounded-full border-4 border-ios-blue shadow-lg object-cover mb-3">
                                     <h4 class="font-bold text-sm leading-tight mb-1" x-text="cleanName(comparisonRow.a.BowlerName)"></h4>
                                     <p class="text-xs text-gray-500 mb-6" x-text="comparisonRow.a.TeamName"></p>

                                     <!-- Stats A -->
                                     <div class="space-y-4 w-full">
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">Average</div>
                                             <div class="text-xl font-black text-ios-blue" x-text="comparisonRow.a.Average"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.a.Average, comparisonRow.b?.Average)" x-text="getDiff(comparisonRow.a.Average, comparisonRow.b?.Average)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">High Game</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.a.HighScratchGame"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.a.HighScratchGame, comparisonRow.b?.HighScratchGame)" x-text="getDiff(comparisonRow.a.HighScratchGame, comparisonRow.b?.HighScratchGame)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">High Series</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.a.HighScratchSeries"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.a.HighScratchSeries, comparisonRow.b?.HighScratchSeries)" x-text="getDiff(comparisonRow.a.HighScratchSeries, comparisonRow.b?.HighScratchSeries)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">Total Pins</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.a.TotalPins"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.a.TotalPins, comparisonRow.b?.TotalPins)" x-text="getDiff(comparisonRow.a.TotalPins, comparisonRow.b?.TotalPins)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">Games</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.a.TotalGames"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.a.TotalGames, comparisonRow.b?.TotalGames)" x-text="getDiff(comparisonRow.a.TotalGames, comparisonRow.b?.TotalGames)"></div>
                                         </div>
                                     </div>
                                 </div>
                             </template>
                         </div>

                         <!-- Right Side (Team B) -->
                         <div class="flex-1 p-4 flex flex-col items-center">
                             <template x-if="comparisonRow.b">
                                 <div class="flex flex-col items-center w-full text-center">
                                     <img :src="getBowlerImage(comparisonRow.b.BowlerID)" class="w-20 h-20 rounded-full border-4 border-ios-red shadow-lg object-cover mb-3">
                                     <h4 class="font-bold text-sm leading-tight mb-1" x-text="cleanName(comparisonRow.b.BowlerName)"></h4>
                                     <p class="text-xs text-gray-500 mb-6" x-text="comparisonRow.b.TeamName"></p>

                                     <!-- Stats B -->
                                     <div class="space-y-4 w-full">
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">Average</div>
                                             <div class="text-xl font-black text-ios-blue" x-text="comparisonRow.b.Average"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.b.Average, comparisonRow.a?.Average)" x-text="getDiff(comparisonRow.b.Average, comparisonRow.a?.Average)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">High Game</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.b.HighScratchGame"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.b.HighScratchGame, comparisonRow.a?.HighScratchGame)" x-text="getDiff(comparisonRow.b.HighScratchGame, comparisonRow.a?.HighScratchGame)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">High Series</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.b.HighScratchSeries"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.b.HighScratchSeries, comparisonRow.a?.HighScratchSeries)" x-text="getDiff(comparisonRow.b.HighScratchSeries, comparisonRow.a?.HighScratchSeries)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">Total Pins</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.b.TotalPins"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.b.TotalPins, comparisonRow.a?.TotalPins)" x-text="getDiff(comparisonRow.b.TotalPins, comparisonRow.a?.TotalPins)"></div>
                                         </div>
                                         <div class="bg-gray-50 dark:bg-gray-800 p-2 rounded-lg">
                                             <div class="text-xs text-gray-500 uppercase">Games</div>
                                             <div class="text-lg font-bold" x-text="comparisonRow.b.TotalGames"></div>
                                             <div class="text-[10px] font-bold" :class="getDiffClass(comparisonRow.b.TotalGames, comparisonRow.a?.TotalGames)" x-text="getDiff(comparisonRow.b.TotalGames, comparisonRow.a?.TotalGames)"></div>
                                         </div>
                                     </div>
                                 </div>
                             </template>
                         </div>
                     </div>
                 </div>
             </template>
        </div>
    </div>

    <!-- Team Modal -->
    <div x-show="showTeamModal" class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm" style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

         <div class="absolute bottom-0 w-full h-[90%] bg-white dark:bg-ios-bg rounded-t-3xl overflow-hidden flex flex-col"
              @click.away="showTeamModal = false"
              x-transition:enter="transition ease-out duration-300"
              x-transition:enter-start="translate-y-full"
              x-transition:enter-end="translate-y-0"
              x-transition:leave="transition ease-in duration-200"
              x-transition:leave-start="translate-y-0"
              x-transition:leave-end="translate-y-full">

            <div class="p-4 flex justify-between items-center border-b border-gray-100 dark:border-ios-separator bg-white/50 dark:bg-ios-card/50 backdrop-blur-md sticky top-0 z-10">
                <div class="w-10"></div> <!-- Spacer -->
                <h2 class="font-bold text-lg" x-text="selectedTeamName"></h2>
                <button @click="showTeamModal = false" class="text-gray-500 bg-gray-200 dark:bg-gray-700 rounded-full p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                <template x-for="bowler in selectedTeamBowlers" :key="bowler.BowlerID">
                    <div @click="openBowler(bowler); showTeamModal = false" class="flex justify-between items-center p-4 bg-gray-50 dark:bg-ios-card rounded-xl border border-gray-100 dark:border-ios-separator cursor-pointer active:scale-[0.98] transition-transform">
                        <div class="flex items-center">
                            <img :src="getBowlerImage(bowler.BowlerID)" class="w-10 h-10 rounded-full mr-3 object-cover border border-gray-200 dark:border-gray-600" alt="Avatar">
                            <span class="font-medium" x-text="cleanName(bowler.BowlerName)"></span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="font-bold text-ios-blue text-lg" x-text="bowler.Average"></span>
                            <span class="text-[10px] text-gray-400 uppercase">Avg</span>
                        </div>
                    </div>
                </template>
            </div>
         </div>
    </div>


    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('./sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                currentTab: 'bowlers',
                bowlers: <?php echo json_encode($bowlers); ?>,
                history: <?php echo json_encode($history); ?>,
                selectedBowler: null,
                searchQuery: '',

                // Teams Tab
                showTeamModal: false,
                selectedTeamName: '',

                // Compare Tab
                compareTeamA: '',
                compareTeamB: '',
                comparisonRow: null,
                showComparisonModal: false,

                // History Tab
                showHistoryModal: false,
                selectedHistoryBowler: null,
                historyData: [],
                selectedGame: null,
                barColors: ['bg-ios-blue', 'bg-ios-green', 'bg-ios-red', 'bg-ios-gold', 'bg-ios-bronze', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500'],

                init() {
                    // Pre-sort bowlers by Average DESC
                    this.bowlers.sort((a, b) => parseFloat(b.Average || 0) - parseFloat(a.Average || 0));
                },

                cleanName(name) {
                    if (!name) return '';
                    // Remove (dd) or (d) at the end, handling potential trailing spaces
                    return name.replace(/\s*\(\d+\)\s*$/, '');
                },

                get filteredBowlers() {
                    if (!this.searchQuery) return this.bowlers;
                    const q = this.searchQuery.toLowerCase();
                    return this.bowlers.filter(b => {
                        return (b.BowlerName && b.BowlerName.toLowerCase().includes(q)) ||
                               (b.TeamName && b.TeamName.toLowerCase().includes(q));
                    });
                },

                get top3() {
                    if (this.searchQuery) return [];
                    return this.bowlers.slice(0, 3);
                },

                get listItems() {
                    if (this.searchQuery) return this.filteredBowlers;
                    return this.bowlers.slice(3);
                },

                get teamsList() {
                    const teams = {};
                    this.bowlers.forEach(b => {
                        if (b.TeamName) {
                            if (!teams[b.TeamName]) teams[b.TeamName] = { name: b.TeamName, count: 0 };
                            teams[b.TeamName].count++;
                        }
                    });
                    return Object.values(teams).sort((a, b) => a.name.localeCompare(b.name));
                },

                openBowler(bowler) {
                    this.selectedBowler = bowler;
                },

                openTeamDetail(teamName) {
                    this.selectedTeamName = teamName;
                    this.showTeamModal = true;
                },

                get selectedTeamBowlers() {
                    return this.getAllTeamBowlers(this.selectedTeamName);
                },

                // Compare Helper functions
                calculateTeamAverage(teamName) {
                    const top6 = this.getTop6(teamName);
                    if (top6.length === 0) return 0;
                    const sum = top6.reduce((acc, curr) => acc + parseFloat(curr.Average || 0), 0);
                    return Math.round(sum / top6.length);
                },

                getAllTeamBowlers(teamName) {
                    if (!teamName) return [];
                    return this.bowlers
                        .filter(b => b.TeamName === teamName)
                        .sort((a, b) => parseFloat(b.Average || 0) - parseFloat(a.Average || 0));
                },

                getTop6(teamName) {
                    return this.getAllTeamBowlers(teamName).slice(0, 6);
                },

                getComparisonRows() {
                    const teamA = this.getAllTeamBowlers(this.compareTeamA);
                    const teamB = this.getAllTeamBowlers(this.compareTeamB);
                    const rows = [];
                    const maxCount = Math.max(teamA.length, teamB.length);
                    for (let i = 0; i < maxCount; i++) {
                        rows.push({ a: teamA[i], b: teamB[i] });
                    }
                    return rows;
                },

                openComparison(row) {
                    if (row.a && row.b) {
                        this.comparisonRow = row;
                        this.showComparisonModal = true;
                    } else if (row.a) {
                        this.openBowler(row.a);
                    } else if (row.b) {
                        this.openBowler(row.b);
                    }
                },

                openHistoryGraph(bowler) {
                    this.selectedHistoryBowler = bowler;
                    this.historyData = this.history[bowler.BowlerID] || [];
                    // Select the last game by default if available
                    if (this.historyData.length > 0) {
                        this.selectedGame = this.historyData[this.historyData.length - 1];
                    } else {
                        this.selectedGame = null;
                    }
                    this.showHistoryModal = true;
                },

                getBarColor(index) {
                    return this.barColors[index % this.barColors.length];
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
                },

                getDiff(val1, val2) {
                    if (val1 === undefined || val2 === undefined || val1 === null || val2 === null) return '';
                    const v1 = parseFloat(val1);
                    const v2 = parseFloat(val2);
                    const diff = v1 - v2;
                    if (diff > 0) return '+' + diff.toLocaleString();
                    if (diff < 0) return diff.toLocaleString();
                    return '0';
                },

                getDiffClass(val1, val2) {
                    if (val1 === undefined || val2 === undefined || val1 === null || val2 === null) return 'text-gray-400';
                    const v1 = parseFloat(val1);
                    const v2 = parseFloat(val2);
                    if (v1 > v2) return 'text-ios-green';
                    if (v1 < v2) return 'text-ios-red';
                    return 'text-gray-400';
                },

                getBowlerImage(id) {
                    if (!id) return 'assets/balls/1.jpg';
                    const num = (parseInt(id) % 417) + 1;
                    return `assets/balls/${num}.jpg`;
                }
            }));
        });
    </script>
</body>
</html>
