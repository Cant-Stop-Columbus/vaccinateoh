<template>
    <div class="min-h-screen bg-gray-200">

        <div class="container mx-auto">
            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl">VaccinateOH.org Stats</h1>
                </div>
            </header>

            <!-- Page Content -->
            <main class="bg-white shadow py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="underline">Vaccinate Ohio Database Statistics:</h2>
                    Locations in the Database: {{ locationsCount }} <br>
                    Locations in the Database with Future Availability: {{ availableLocationsCount }} <br><br>
                <h2 class="underline">Vaccinate Ohio Update Activity:</h2>
                    Updates in the last 24 hours: {{ last24Hrs }} <br>
                    Updates in the last 3 days: {{ last3Days }} <br>
                    Updates in the last week: {{ lastWeek }} <br><br>
                <h2 class="underline">Top Updaters:</h2>
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
            last24Hrs: Number,
            last3Days: Number,
            lastWeek: Number,
        },
        components: {
            Welcome
        },
    }
</script>
