<template>
    <h1 class="underline">Vaccinate Ohio Database Statistics:</h1>
        Locations in the Database: {{ locationsCount }} <br>
        Locations in the Database with Future Availability: {{ availableLocationsCount }} <br><br>
    <h1 class="underline">Vaccinate Ohio Update Activity:</h1>
        Updates in the last 24 hours: {{ last24Hrs }} <br>
        Updates in the last 3 days: {{ last3Days }} <br>
        Updates in the last week: {{ lastWeek }} <br><br>
    <h1 class="underline">Top 10 Updaters:</h1>
    <table>
        <tr><td> Name </td><td> Updates </td></tr>
        <tr v-for="updater in topUpdaters">
            <td> {{updater.name}} </td><td> {{ updater.update_count }} </td>
        </tr>
    </table>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
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
            AppLayout,
            Welcome,
        },
    }
</script>
