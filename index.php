<?php
require_once __DIR__ . '/src/app_data.php';

$bowlers = getBowlerData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>League Bowlers</title>

    <!-- PWA / Mobile Capable -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Bowlers">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="https://cdn-icons-png.flaticon.com/512/3163/3163246.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ios: {
                            bg: '#000000',
                            card: '#1c1c1e',
                            separator: '#38383a',
                            blue: '#0a84ff',
                            text: '#ffffff',
                            subtext: '#8e8e93'
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
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            -webkit-tap-highlight-color: transparent;
        }
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        .tab-active { color: #0a84ff; }
        .tab-inactive { color: #8e8e93; }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden bg-white dark:bg-black text-black dark:text-white" x-data="app()">

    <!-- Main Content Area -->
    <main class="flex-1 overflow-hidden relative w-full">

        <!-- Tab 1: Bowlers List -->
        <div x-show="currentTab === 'bowlers'"
             class="h-full w-full overflow-y-auto no-scrollbar pb-20"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0">

            <!-- Header -->
            <div class="sticky top-0 z-10 bg-white/95 dark:bg-ios-bg/95 backdrop-blur-md px-4 py-2 border-b border-gray-200 dark:border-ios-separator">
                <h1 class="text-3xl font-bold mb-2">Bowlers</h1>
                <!-- Search Bar -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input x-model="searchQuery"
                           type="text"
                           class="w-full bg-gray-100 dark:bg-ios-card text-black dark:text-white rounded-lg pl-10 pr-10 py-2 focus:outline-none focus:ring-1 focus:ring-ios-blue placeholder-gray-500"
                           placeholder="Search name or team...">

                    <!-- Clear Button -->
                    <button x-show="searchQuery !== ''"
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 active:text-black dark:active:text-white">
                        <svg class="h-4 w-4 bg-gray-300 dark:bg-gray-600 rounded-full p-0.5 text-white dark:text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Filter Pills -->
                <div class="flex space-x-2 mt-3 overflow-x-auto no-scrollbar pb-1">
                    <button @click="sortKey = 'Average'"
                            class="px-3 py-1 rounded-full text-xs font-medium transition-colors border border-gray-200 dark:border-ios-separator"
                            :class="sortKey === 'Average' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 text-gray-500 dark:bg-ios-card dark:text-ios-subtext'">
                        Sort by Avg
                    </button>
                    <button @click="sortKey = 'BowlerName'"
                            class="px-3 py-1 rounded-full text-xs font-medium transition-colors border border-gray-200 dark:border-ios-separator"
                            :class="sortKey === 'BowlerName' ? 'bg-black text-white dark:bg-white dark:text-black' : 'bg-gray-100 text-gray-500 dark:bg-ios-card dark:text-ios-subtext'">
                        Sort by Name
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="px-4 py-2 space-y-1">
                <template x-for="bowler in filteredBowlers" :key="bowler.BowlerID">
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-ios-separator last:border-0 active:bg-gray-100 dark:active:bg-ios-card/50 transition-colors rounded-lg px-2 -mx-2">
                        <div class="flex flex-col">
                            <span class="font-semibold text-lg" x-text="bowler.BowlerName"></span>
                            <button @click.stop="openTeamDetail(bowler.TeamName)" class="text-sm text-gray-500 dark:text-ios-subtext text-left hover:text-ios-blue transition-colors" x-text="bowler.TeamName"></button>
                        </div>
                        <div class="flex flex-col items-end w-16">
                            <span class="font-bold text-xl text-ios-blue" x-text="bowler.Average"></span>
                            <span class="text-xs text-gray-500 dark:text-ios-subtext">Avg</span>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="filteredBowlers.length === 0" class="text-center py-10 text-gray-500 dark:text-ios-subtext">
                    No bowlers found.
                </div>
            </div>
        </div>

        <!-- Tab 2: Teams -->
        <div x-show="currentTab === 'teams'"
             class="h-full w-full overflow-y-auto no-scrollbar pb-20"
             style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0">

            <div class="sticky top-0 z-10 bg-white/95 dark:bg-ios-bg/95 backdrop-blur-md px-4 py-4 border-b border-gray-200 dark:border-ios-separator">
                <h1 class="text-3xl font-bold">Teams</h1>
            </div>

            <div class="px-4 py-2">
                <template x-for="team in teamsList" :key="team.name">
                    <div @click="openTeamDetail(team.name)"
                         class="flex justify-between items-center py-4 border-b border-gray-200 dark:border-ios-separator active:bg-gray-100 dark:active:bg-ios-card/50 transition-colors cursor-pointer">
                        <span class="font-semibold text-lg" x-text="team.name"></span>
                        <div class="flex items-center text-gray-500 dark:text-ios-subtext">
                            <span class="text-sm mr-2" x-text="team.count + ' Bowlers'"></span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab 3: Compare -->
        <div x-show="currentTab === 'compare'"
             class="h-full w-full overflow-y-auto no-scrollbar pb-20"
             style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0">

            <div class="sticky top-0 z-10 bg-white/95 dark:bg-ios-bg/95 backdrop-blur-md px-4 py-4 border-b border-gray-200 dark:border-ios-separator">
                <h1 class="text-3xl font-bold">Compare</h1>
            </div>

            <div class="p-4 space-y-4">
                <!-- Selectors -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-ios-subtext mb-1">Team A</label>
                        <select x-model="compareTeamA" class="w-full bg-gray-100 dark:bg-ios-card text-black dark:text-white rounded-lg p-2 text-sm border border-gray-200 dark:border-ios-separator">
                            <option value="">Select Team</option>
                            <template x-for="team in teamsList" :key="team.name">
                                <option :value="team.name" x-text="team.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-ios-subtext mb-1">Team B (Optional)</label>
                        <select x-model="compareTeamB" class="w-full bg-gray-100 dark:bg-ios-card text-black dark:text-white rounded-lg p-2 text-sm border border-gray-200 dark:border-ios-separator">
                            <option value="">None</option>
                            <template x-for="team in teamsList" :key="team.name">
                                <option :value="team.name" x-text="team.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Content -->
                <div x-show="compareTeamA && !compareTeamB">
                    <!-- Single Team Analysis -->
                    <h2 class="text-xl font-bold mb-2 text-center" x-text="compareTeamA"></h2>
                    <h3 class="text-sm text-gray-500 dark:text-ios-subtext text-center mb-4">Top 6 by Average</h3>

                    <div class="bg-gray-100 dark:bg-ios-card rounded-xl overflow-hidden">
                        <template x-for="(bowler, index) in getTop6(compareTeamA)" :key="bowler.BowlerID">
                            <div class="flex justify-between items-center p-3 border-b border-gray-200 dark:border-ios-separator last:border-0">
                                <div class="flex items-center">
                                    <span class="text-gray-500 dark:text-ios-subtext w-6 font-mono" x-text="'#' + (index + 1)"></span>
                                    <span class="font-medium" x-text="bowler.BowlerName"></span>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-ios-blue" x-text="bowler.Average"></div>
                                    <div class="text-xs text-gray-500 dark:text-ios-subtext" x-text="'High: ' + bowler.HighScratchGame"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="compareTeamA && compareTeamB">
                    <!-- Head to Head -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-center w-1/2">
                            <h2 class="font-bold text-sm leading-tight" x-text="compareTeamA"></h2>
                            <span class="text-xs text-gray-500 dark:text-ios-subtext" x-text="calculateTeamAverage(compareTeamA) + ' Team Avg (Top 6)'"></span>
                        </div>
                        <div class="text-gray-500 font-bold">VS</div>
                        <div class="text-center w-1/2">
                            <h2 class="font-bold text-sm leading-tight" x-text="compareTeamB"></h2>
                            <span class="text-xs text-gray-500 dark:text-ios-subtext" x-text="calculateTeamAverage(compareTeamB) + ' Team Avg (Top 6)'"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(row, index) in getComparisonRows()" :key="index">
                            <div class="bg-gray-100 dark:bg-ios-card rounded-lg p-2 flex items-center justify-between text-xs">
                                <!-- Team A Bowler -->
                                <div class="w-[45%] text-left">
                                    <div class="font-semibold truncate" x-text="row.a ? row.a.BowlerName : '-'"></div>
                                    <div class="flex justify-between mt-1 text-gray-500 dark:text-ios-subtext">
                                        <span x-text="row.a ? 'Avg: ' + row.a.Average : ''"></span>
                                        <span x-text="row.a ? 'Hi: ' + row.a.HighScratchGame : ''"></span>
                                    </div>
                                </div>

                                <!-- Rank -->
                                <div class="w-[10%] text-center font-mono text-gray-500 font-bold" x-text="index + 1"></div>

                                <!-- Team B Bowler -->
                                <div class="w-[45%] text-right">
                                    <div class="font-semibold truncate" x-text="row.b ? row.b.BowlerName : '-'"></div>
                                    <div class="flex justify-between mt-1 text-gray-500 dark:text-ios-subtext">
                                        <span x-text="row.b ? 'Avg: ' + row.b.Average : ''"></span>
                                        <span x-text="row.b ? 'Hi: ' + row.b.HighScratchGame : ''"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="!compareTeamA" class="text-center text-gray-500 dark:text-ios-subtext py-10">
                    Select a team to begin analysis.
                </div>
            </div>
        </div>

    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 w-full bg-white/95 dark:bg-ios-card/95 backdrop-blur-md border-t border-gray-200 dark:border-ios-separator pb-safe pt-2 px-6 flex justify-around items-center z-50 h-[83px]">

        <!-- Bowlers Tab -->
        <button @click="currentTab = 'bowlers'" class="flex flex-col items-center space-y-1 w-16">
            <svg class="h-6 w-6 transition-colors" :class="currentTab === 'bowlers' ? 'text-ios-blue' : 'text-gray-400 dark:text-ios-subtext'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors" :class="currentTab === 'bowlers' ? 'text-ios-blue' : 'text-gray-400 dark:text-ios-subtext'">Bowlers</span>
        </button>

        <!-- Teams Tab -->
        <button @click="currentTab = 'teams'" class="flex flex-col items-center space-y-1 w-16">
            <svg class="h-6 w-6 transition-colors" :class="currentTab === 'teams' ? 'text-ios-blue' : 'text-gray-400 dark:text-ios-subtext'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors" :class="currentTab === 'teams' ? 'text-ios-blue' : 'text-gray-400 dark:text-ios-subtext'">Teams</span>
        </button>

        <!-- Compare Tab -->
        <button @click="currentTab = 'compare'" class="flex flex-col items-center space-y-1 w-16">
            <svg class="h-6 w-6 transition-colors" :class="currentTab === 'compare' ? 'text-ios-blue' : 'text-gray-400 dark:text-ios-subtext'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="text-[10px] font-medium transition-colors" :class="currentTab === 'compare' ? 'text-ios-blue' : 'text-gray-400 dark:text-ios-subtext'">Compare</span>
        </button>

    </nav>

    <!-- Team Detail Modal (Placeholder for now) -->
    <div x-show="showTeamModal"
         class="fixed inset-0 z-[60] bg-black"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full">

        <div class="relative h-full w-full flex flex-col bg-white dark:bg-ios-bg">
            <!-- Modal Header -->
            <div class="px-4 py-4 flex justify-between items-center border-b border-gray-200 dark:border-ios-separator bg-white/95 dark:bg-ios-card/95 backdrop-blur-md">
                <button @click="showTeamModal = false" class="text-ios-blue font-medium text-lg">Close</button>
                <h2 class="text-lg font-bold" x-text="selectedTeamName"></h2>
                <div class="w-10"></div> <!-- Spacer -->
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto">
                <!-- Team Stats Summary (Optional, if we calculated team average) -->

                <!-- Bowlers List -->
                <div class="px-4 py-2 space-y-1">
                    <template x-for="bowler in selectedTeamBowlers" :key="bowler.BowlerID">
                        <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-ios-separator last:border-0">
                            <div class="flex flex-col">
                                <span class="font-semibold text-lg" x-text="bowler.BowlerName"></span>
                                <div class="flex space-x-3 text-xs text-gray-500 dark:text-ios-subtext">
                                    <span>H.Game: <span x-text="bowler.HighScratchGame"></span></span>
                                    <span>Series: <span x-text="bowler.HighScratchSeries"></span></span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="font-bold text-xl text-ios-blue" x-text="bowler.Average"></span>
                                <span class="text-xs text-gray-500 dark:text-ios-subtext">Avg</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialization Script -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                currentTab: 'bowlers',
                searchQuery: '',
                sortKey: 'Average',
                bowlers: <?php echo json_encode($bowlers); ?>,
                showTeamModal: false,
                selectedTeamName: '',
                compareTeamA: '',
                compareTeamB: '',

                get selectedTeamBowlers() {
                    return this.bowlers
                        .filter(b => b.TeamName === this.selectedTeamName)
                        .sort((a, b) => parseFloat(b.Average || 0) - parseFloat(a.Average || 0));
                },

                getTop6(teamName) {
                    if (!teamName) return [];
                    return this.bowlers
                        .filter(b => b.TeamName === teamName)
                        .sort((a, b) => parseFloat(b.Average || 0) - parseFloat(a.Average || 0))
                        .slice(0, 6);
                },

                getComparisonRows() {
                    const teamA = this.getTop6(this.compareTeamA);
                    const teamB = this.getTop6(this.compareTeamB);
                    const rows = [];
                    for (let i = 0; i < 6; i++) {
                        rows.push({ a: teamA[i], b: teamB[i] });
                    }
                    return rows;
                },

                calculateTeamAverage(teamName) {
                    const top6 = this.getTop6(teamName);
                    if (top6.length === 0) return 0;
                    const sum = top6.reduce((acc, curr) => acc + parseFloat(curr.Average || 0), 0);
                    return Math.round(sum / top6.length);
                },

                get filteredBowlers() {
                    let result = this.bowlers;

                    if (this.searchQuery !== '') {
                        const query = this.searchQuery.toLowerCase();
                        result = result.filter(b =>
                            (b.BowlerName && b.BowlerName.toLowerCase().includes(query)) ||
                            (b.TeamName && b.TeamName.toLowerCase().includes(query))
                        );
                    }

                    // Clone to avoid mutating original array in place during sort
                    return [...result].sort((a, b) => {
                        if (this.sortKey === 'Average') {
                            // Handle cases where Average might be string
                            return parseFloat(b.Average || 0) - parseFloat(a.Average || 0);
                        } else {
                            return (a.BowlerName || '').localeCompare(b.BowlerName || '');
                        }
                    });
                },

                get teamsList() {
                    const teams = {};
                    this.bowlers.forEach(b => {
                        if (b.TeamName) {
                            if (!teams[b.TeamName]) {
                                teams[b.TeamName] = { name: b.TeamName, count: 0 };
                            }
                            teams[b.TeamName].count++;
                        }
                    });
                    return Object.values(teams).sort((a, b) => a.name.localeCompare(b.name));
                },

                openTeamDetail(teamName) {
                    this.selectedTeamName = teamName;
                    this.showTeamModal = true;
                }
            }));
        });
    </script>
</body>
</html>
