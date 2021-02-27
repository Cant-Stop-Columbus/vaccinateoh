<template>
    <div class="w-full h-full">
        <div id="map" class="w-full h-screen"></div>

        <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
            <inertia-link v-if="$page.props.user" href="/dashboard" class="text-sm text-gray-700 underline">
                Dashboard
            </inertia-link>

            <template v-else>
                <inertia-link :href="route('login')" class="text-sm text-gray-700 underline">
                    Log in
                </inertia-link>

                <inertia-link :href="route('register')" class="ml-4 text-sm text-gray-700 underline">
                    Register
                </inertia-link>
            </template>
        </div>

        <div class="max-w-6xl absolute top-0 inset-x-0 opacity-80 ml-1/2 z-50 mx-auto sm:px-6 lg:px-8">
            <div class="p-8">
                <div class="bg-white flex items-center rounded-full shadow-xl">
                    <input class="rounded-l-full w-full py-4 px-6 text-gray-700 leading-tight focus:outline-none active:outline-none border-0" id="search" type="text" placeholder="ZIP Code Search" v-model="zip">

                    <div class="p-4">
                        <button class="bg-blue-500 text-white rounded-full p-2 hover:bg-blue-400 focus:outline-none w-12 h-12 flex items-center justify-center" @click="searchLocations">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" class="fill-current text-white"><path d="M15.853 16.56c-1.683 1.517-3.911 2.44-6.353 2.44-5.243 0-9.5-4.257-9.5-9.5s4.257-9.5 9.5-9.5 9.5 4.257 9.5 9.5c0 2.442-.923 4.67-2.44 6.353l7.44 7.44-.707.707-7.44-7.44zm-6.353-15.56c4.691 0 8.5 3.809 8.5 8.5s-3.809 8.5-8.5 8.5-8.5-3.809-8.5-8.5 3.809-8.5 8.5-8.5z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
#map {
}
</style>

<script>
import { Loader } from '@googlemaps/js-api-loader';

export default {
    props: {
        locations: Array,
    },
    data() {
        return {
            map: {
                gmap: null,
                options: {
                    center: {lat: 39.9612, lng: -82.9988},
                    zoom: 8,
                },
                infoWindow: null,
                markers: [
                ],
            },
            zip: '43210',
            search_locations: [],
        };
    },
    methods: {
        searchLocations() {
            axios.get('/api/locations?zip=' + this.zip)
                .then(resp => {
                    this.search_locations = resp.data;
                    this.resetMarkers(this.search_locations);
                });
        },
        resetMarkers(locations) {
            let bounds = new google.maps.LatLngBounds();
            this.map.markers.forEach((marker) => {
                marker.setMap(null);
            });

            locations.forEach((loc, index) => {
                if(loc.latitude && loc.longitude) {
                    let latLng = { lat: parseFloat(loc.latitude), lng: parseFloat(loc.longitude)};
                    console.log(latLng);

                    let marker = new google.maps.Marker({
                        position: latLng,
                        map: this.map.gmap,
                        title: loc.name,
                        location_idx: index
                    });

                    marker.addListener("click", () => {
                        this.map.infoWindow.setContent('<h1>' + loc.name + '</h1>');
                        this.map.infoWindow.open(this.map.gmap, marker);
                    });

                    this.map.markers.push(marker);

                    bounds.extend(latLng);
                }
            });
            this.map.gmap.fitBounds(bounds);
        },
    },
    mounted() {
        const loader = new Loader({
            apiKey: process.env.MIX_GOOGLE_MAPS_KEY,
            version: "weekly",
            libraries: []
        });

        loader
            .load()
            .then(() => {
                this.map.gmap = new google.maps.Map(document.getElementById('map'), this.map.options)
                this.map.infoWindow = new google.maps.InfoWindow();
                window.map = this.map.gmap;
                window.infoWindow = this.map.infoWindow;

                this.resetMarkers(this.locations);
            });
    },
};
</script>
