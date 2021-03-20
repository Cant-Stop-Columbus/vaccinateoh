<template>
    <div class="min-h-screen bg-gray-200">

        <div class="container mx-auto">
            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl">VaccinateOH Stats</h1>
                </div>
            </header>

            <!-- Page Content -->
            <main class="bg-white shadow py-6 px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="underline text-lg">VaccinateOH Database Statistics:</h2>
                    <div>Locations in the Database: {{ locationsCount }}</div>
                    <div>Locations in the Database with Future Availability: {{ availableLocationsCount }}</div>
                </div>
                <div class="my-6">
                    <h2 class="underline text-lg">VaccinateOH Update Activity:</h2>
                    <div>Updates in the last 24 hours: {{ last24Hrs }}</div>
                    <div>Updates in the last 3 days: {{ last3Days }}</div>
                    <div>Updates in the last week: {{ lastWeek }}</div>
                    <div class="italic text-sm">Note: The above stats include <strong>only</strong> manual updates</div>
                </div>
                
                <div class="md:flex my-6">
                    <div class="md:w-1/3">
                        <h2 class="underline text-lg">Top Updaters (Today):</h2>
                        <div v-if="!topUpdatersToday.length" class="italic">
                            No updates yet :-(
                        </div>
                        <table v-else>
                            <thead>
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th class="text-right">Updates</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="updater in topUpdatersToday">
                                    <td>{{updater.name}}</td>
                                    <td class="text-right">{{ updater.update_count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="md:w-1/3">
                        <h2 class="underline text-lg">Top Updaters (Last 7 Days):</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th class="text-right">Updates</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="updater in topUpdaters7Days">
                                    <td>{{updater.name}}</td>
                                    <td class="text-right">{{ updater.update_count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="md:w-1/3">
                        <h2 class="underline text-lg">Top Updaters (All Time):</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th class="text-right">Updates</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="updater in topUpdaters">
                                    <td>{{updater.name}}</td>
                                    <td class="text-right">{{ updater.update_count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
th,td {
    padding: 5px;
}
tbody tr:nth-child(odd) {
    background: #eee;
}
</style>

<script>
    import Welcome from '@/Jetstream/Welcome'

    export default {
        props: {
            locationsCount: Number,
            availableLocationsCount: Number,
            topUpdaters: Array,
            topUpdatersToday: Array,
            topUpdaters7Days: Array,
            last24Hrs: Number,
            last3Days: Number,
            lastWeek: Number,
        },
        components: {
            Welcome
        },
    }
</script>
