<template>
    <div class="min-h-screen bg-gray-200">
        <div class="container mx-auto">
            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="flex items-center justify-between">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        <h1 class="text-2xl">VaccinateOH Stats</h1>
                    </div>
                    <div>
                        <a href="/">Take me to the map!</a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="bg-white shadow py-6 px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="underline text-lg">VaccinateOH Database Statistics:</h2>
                    <div>Locations in the Database: {{ locationsCount }}</div>
                    <div>Locations in the Database with Future Availability: {{ availableLocationsCount }}</div>
                    <div>Locations never updated: {{ countNeverUpdated }}</div>
                </div>
                <div class="my-6">
                    <h2 class="underline text-lg">VaccinateOH Update Activity:</h2>
                    <div>Updates in the last 24 hours: {{ last24Hrs }}</div>
                    <div>Updates in the last 3 days: {{ last3Days }}</div>
                    <div>Updates in the last week: {{ lastWeek }}</div>
                    <div class="italic text-sm">Note: The above stats include <strong>only</strong> manual updates</div>
                </div>

                <div id="histogram" class="mb-6">
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
    import Welcome from '@/Jetstream/Welcome';
    import * as d3 from 'd3';

    export default {
        mounted() {
            this.generateLastUpdatedHistogram();
        },
        methods: {
            generateLastUpdatedHistogram() {
                const data = this.lastUpdatedHistogramData;

                // set the dimensions and margins of the graph
                const margin = {top: 30, right: 30, bottom: 70, left: 60};
                const width = 800 - margin.left - margin.right;
                const height = 400 - margin.top - margin.bottom;

                // append the svg object to the body of the page
                const svg = d3.select("#histogram")
                  .append("svg")
                  .attr("width", width + margin.left + margin.right)
                  .attr("height", height + margin.top + margin.bottom)
                  .append("g")
                  .attr("transform",
                        "translate(" + margin.left + "," + margin.top + ")");

                // X axis
                const x = d3.scaleBand()
                  .range([ 0, width ])
                  .domain(data.map(function(d, v) { return data.length - v - 1; }))
                  .paddingOuter(0.2);

                svg.append("g")
                  .attr("transform", "translate(0," + height + ")")
                  .call(d3.axisBottom(x))
                  .selectAll("text")
                  .attr("transform", "translate(-10,0)rotate(-45)")
                  .style("text-anchor", "end");

                // Add Y axis
                const y = d3.scaleSymlog()
                  .domain([0, Math.max(...data)])
                  .range([ height, 0]);

                svg.append("g")
                  .call(d3.axisLeft(y));

                // Bars
                svg.selectAll("mybar")
                  .data(data)
                  .enter()
                  .append("rect")
                  .attr("x", function(d, k) { return x(k); })
                  .attr("y", function(d) { return y(d); })
                  .attr("width", x.bandwidth())
                  .attr("height", function(d) { return height - y(d); })
                  .attr("fill", "#69b3a2")
            }
        },
        props: {
            locationsCount: Number,
            availableLocationsCount: Number,
            topUpdaters: Array,
            topUpdatersToday: Array,
            topUpdaters7Days: Array,
            last24Hrs: Number,
            last3Days: Number,
            lastWeek: Number,
            countNeverUpdated: Number,
            lastUpdatedHistogramData: Array,
        },
        components: {
            Welcome
        },
    };
</script>
