<template>
    <div class="w-full md:h-full md:flex">

        <div id="page-wrapper" class="h-screen w-full flex flex-wrap">
            <div class="bg-blue w-full text-center text-white p-1">
                &nbsp;
            </div>
            <div class="border-b border-blue-500 w-full p-1">
                <h1 class="logo float-left text-center text-2xl mx-auto my-1 ml-2">Vaccinate OH</h1>

                <div class="float-right px-6 py-2 z-40">
                    <a href="/" class="text-blue font-bold ml-4">Home</a>

                    <a href="https://info.vaccinateoh.org" class="text-blue font-bold ml-4">About Us</a>

                     <a href="https://info.vaccinateoh.org/faq" class="text-blue font-bold ml-4">FAQ</a>
                </div>
            </div>
            <div class="relative w-full md:flex map-search-wrapper">
                <div id="location-sidebar" class="md:h-full p-2 md:w-96 flex-none md:overflow-y-auto md:order-1">

                    <div class="search-box">
                        <h2 class="text-blue font-bold">Vaccine Finder</h2>
                        <p>Search vaccine appointment availability by entering in your zipcode.</p>
                        <br>
                        <form @submit.prevent="searchLocations(null)" class="flex">
                            <input class="border border-blue rounded w-full px-2 text-gray-700 leading-tight focus:outline-none active:outline-none" id="search" type="text" placeholder="Address/City/Zip Search" v-model="search_q">

                            <button class="bg-blue text-white font-bold rounded-2 py-1 px-3 hover:bg-blue-light rounded ml-1" type="submit">
                                Search
                            </button>
                        </form>
                    </div>

                    <ul class="location-list w-full">
                        <h2 class="text-blue font-bold">Search Results</h2>
                        <li class="location relative bg-bluegray rounded p-2 my-2 flex" v-for="loc in (search_locations)" @mouseover="showLocationMarker(loc)">
                            <div class="location-details flex-grow">
                                <h3 class="font-bold color-blue mr-28">{{ loc.name }}</h3>
                                <div class="address text-sm pb-2" v-html="addressHtml(loc.address)"></div>
                                <div class="text-xs" v-if="loc.distance">{{ round(loc.distance) }} miles away</div>
                                <div class="text-xs pb-2">
                                    <a
                                        class="underline"
                                        :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(loc.address)"
                                        target="_blank">Get directions</a>
                                </div>
                                <div class="phone text-sm">
                                    <a :href="'tel:' + loc.phone">
                                        <img src="/img/phone.svg" alt="phone" class="inline" />
                                        {{ loc.phone }}
                                    </a>
                                </div>
                                <div class="appt-link my-2 float-right">
                                    <a :href="loc.bookinglink" target="_blank" v-if="loc.bookinglink" class="bg-blue hover:bg-blue-light text-white font-bold py-1 my-1 px-2 rounded">Search Appointments</a>
                                </div>
                                <div class="my-2 text-xs text-gray-600 float-left" v-if="loc.updated_at">
                                    Updated {{ formatDateRelative(loc.updated_at) }} 
                                    <div class="underline cursor-pointer" @click="showInputModal(loc)" v-if="$page.props.user">update now</div>
                                </div>


                                <div class="available absolute top-2 right-2 text-xs">
                                    <div v-if="loc.available">
                                        <span class="mr-1">Available {{ formatDate(loc.available) }}</span>
                                        <svg class="inline-block" width="22" height="21" viewBox="0 0 22 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <ellipse cx="11" cy="10.5" rx="11" ry="10.5" fill="#039D40"/>
                                            <path d="M9.17794 13.3117L6.80728 10.824L6 11.6652L9.17794 15L16 7.84116L15.1984 7L9.17794 13.3117Z" fill="#EEF7FF"/>
                                        </svg>
                                    </div>
                                    <div v-else-if="loc.unavailable_until" title="No appointments available">
                                        <span class="mr-1">Not Available</span>
                                        <svg class="inline-block" width="22" height="21" viewBox="0 0 22 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <ellipse cx="11" cy="10.5" rx="11" ry="10.5" fill="#FF0000"/>
                                            <path d="M16 7.00714L14.9929 6L11 9.99286L7.00714 6L6 7.00714L9.99286 11L6 14.9929L7.00714 16L11 12.0071L14.9929 16L16 14.9929L12.0071 11L16 7.00714Z" fill="white"/>
                                        </svg>
                                    </div>
                                    <div v-else>
                                        <span class="mr-1">Unknown</span>
                                        <svg class="inline-block" width="22" height="21" viewBox="0 0 22 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <ellipse cx="11" cy="10.5" rx="11" ry="10.5" fill="#CCCCCC"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <p class="p-2 text-xs">
                        Availability Preference: 
                        <select v-model="search_available" class="text-xs" @change="searchLocations()">
                            <option value="prefer">Prefer available; show both</option>
                            <option value="all">No preference; show both</option>
                            <option value="only">Show only available</option>
                            <option value="no">Show only unavailable</option>
                        </select>
                    </p>
                </div>
                <div id="map" class="h-full md:h-auto flex-grow md:order-2"></div>

                <div class="links md:absolute bottom-8 right-20">
                    <template v-if="!$page.props.user">
                        <a :href="route('register')" class="ml-4 text-sm text-gray-700 underline ml-4">Register as a Volunteer</a>
                        <a :href="route('login')" class="text-sm text-gray-700 underline ml-4">Log in</a>
                    </template>
                    <span v-else>
                        <a v-if="$page.props.user.is_admin" href="/admin/dashboard" class="text-sm text-gray-700 underline ml-4">Admin Dashboard</a>
                        <inertia-link :href="route('logout')" method="post" class="text-sm text-gray-700 underline ml-4">Log out</inertia-link>
                    </span>
                </div>
            </div>
            <div class="fixed left-0 bottom-0 grid grid-cols-3 bg-blue w-full p-1">
                <div class="col-span-2 text-left text-white text-xs">
               For questions about COVID-19 please call the Ohio Department of Health call center: 1-833-4-ASK-ODH (1-833-427-5634)
                </div>                
                <div class="text-right text-white text-xs">
               Made with <svg class="svg-inline--fa fa-heart" style="color: white;" aria-hidden="true" data-prefix="fa" data-icon="heart" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M414.9 24C361.8 24 312 65.7 288 89.3 264 65.7 214.2 24 161.1 24 70.3 24 16 76.9 16 165.5c0 72.6 66.8 133.3 69.2 135.4l187 180.8c8.8 8.5 22.8 8.5 31.6 0l186.7-180.2c2.7-2.7 69.5-63.5 69.5-136C560 76.9 505.7 24 414.9 24z"></path></svg> by volunteers as part of Can't Stop Columbus"
                </div>
            </div>
        </div>

        <div id="availability-modal" class="fixed top-0 left-0 w-full h-full z-50" :class="{hidden: !update_input.show_modal}">
            <div class="w-full h-full bg-gray-700 bg-opacity-50 p-20" @click.self="hideInputModal">
                <div class="bg-white my-10 bg-white p-10 w-96 mx-auto">
                    <p class="text-lg font-bold">{{ update_input.location.name }}</p>
                    <p class="text-sm text-gray-900">{{ update_input.location.address }}</p>
                    <p class="py-2"><input type="checkbox" name="no_availability" v-model="update_input.no_availability" value="true" /> No appointments currently availabile</p>
                    <div v-if="!update_input.no_availability">
                        <p class="py-2">
                            Next availability date: &nbsp;
                                <input v-model="update_input.date_next_available" placeholder="yyyy-mm-dd" id="datepicker" />
                        </p>
                        <p class="py-2">Vaccine Brand <span class="text-xs text-gray-600">(optional)</span>: &nbsp;
                            <select v-model="update_input.brand">
                                <option value="">-- Select Brand --</option>
                                <option value="p">Pfizer</option>
                                <option value="m">Moderna</option>
                                <option value="j">Johnson & Johnson</option>
                            </select>
                        </p>
                        <p class="py-2 text-xs italic"><input type="checkbox" name="clear_existing" v-model="update_input.clear_existing" value="true" /> Clear existing availability</p>
                    </div>
                    <button class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" @click="submitAvailability">
                        Submit Availability
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
#map {
}
.svg-inline--fa {
    display: inline-block;
    font-size: inherit;
    height: 1em;
    overflow: visible;
    vertical-align: -.125em;
}
.svg-inline--fa.fa-w-18 {
    width: 1.125em;
}
svg:not(:root).svg-inline--fa {
    overflow: visible;
}
</style>

<script>
import { Loader } from '@googlemaps/js-api-loader';
import toastr from 'toastr';
import * as dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime)

export default {
    props: {
        locations: Array,
    },
    data() {
        return {
            update_input: {
                location: {},
                date_next_available: null,
                no_availability: false,
                clear_existing: true,
                show_modal: false,
            },
            map: {
                gmap: null,
                options: {
                    center: {lat: 39.9612, lng: -82.9988},
                    zoom: 8,
                    mapTypeControl: false,
                    fullscreenControl: false,
                    /* Add the below after google loads
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.BOTTOM_LEFT,
                    }
                    */
                },
                infoWindow: null,
                markers: [
                ],
            },
            search_q: '',
            search_available: 'prefer',
            search_locations: [],
            search_center: {
                lat:null,
                lng:null
            },
            current_location: null,
            view: 'list',
        };
    },
    methods: {
        searchLocations(event) {
            // don't try to use the first arg if it isn't a string
            let q = typeof(event) === 'string' ? event : null;

            // if a search query isn't specified and we have the user location, search on it
            if(!q && !this.search_q && this.current_location) {
                q = this.current_location.lat + ',' + this.current_location.lng;
            }

            let search_term = q || this.search_q;

            if(window.gtag) {
                gtag('event', 'search', {
                    search_term: search_term
                });
            }

            toastr.info('Locating vaccine appointments near ' + search_term, 'Searching', {
                    closeButton: true,
                    timeOut: 0,
                    extendedTimeOut: 0,
                });
            axios.get('/api/locations?q=' + search_term + '&available=' + this.search_available)
                .then(resp => {
                    // If no locations are found, show a warning but don't clear the results;
                    this.clearNotifications();
                    if(!resp.data.locations.total) {
                        toastr.warning('No locations found. Try a different search.');
                        return;
                    }
                    let this_page_count = resp.data.locations.to - resp.data.locations.from + 1;
                    if(resp.data.total > this_page_count) {
                        toastr.success('We found ' + resp.data.locations.total + ' locations. Showing the ' + this_page_count + ' closest.');
                    } else {
                        toastr.success('We found ' + this_page_count + ' locations.');
                    }
                    this.search_locations = resp.data.locations.data;
                    this.search_center = resp.data.q;
                    document.querySelector('#location-sidebar').scrollTop = 0;
                    this.resetMarkers(this.search_locations);
                });
        },
        resetMarkers(locations) {
            window.markers = window.markers || [];
            window.markers.forEach((marker) => {
                marker.setMap(null);
            });

            // From http://kml4earth.appspot.com/icons.html
            let iconBase = 'https://maps.google.com/mapfiles/kml/paddle/';
            locations.forEach((loc, index) => {
                if(loc.latitude && loc.longitude) {
                    let latLng = { lat: parseFloat(loc.latitude), lng: parseFloat(loc.longitude)};

                    let icon = iconBase;
                    if(loc.unavailable_until) {
                        icon = 'red-square-lv.png';
                    } else if(loc.available) {
                        icon = 'grn-stars.png';
                    } else {
                        icon = 'wht-blank-lv.png';
                    }

                    let marker = new google.maps.Marker({
                        position: latLng,
                        map: this.map.gmap,
                        title: loc.name,
                        location_idx: index,
                        icon: iconBase + icon,
                    });

                    marker.addListener("click", () => {
                        let content = '<h3 class="font-bold">' + loc.name + '</h3>'
                            + '<div class="address text-sm text-gray-700">' + this.addressHtml(loc.address) + '</div>'
                            + '<div class="phone text-sm text-gray-500"><a href="tel:' + loc.phone + '">' + loc.phone + '</a></div>'
                            + (!loc.distance ? '' : '<div class="my-1 text-xs text-gray-500">' + this.round(loc.distance) + ' miles</div>')
                            + (!loc.available ? '' : '<div class="my-1 text-xs text-green-500"> Next appointment: ' + this.formatDate(loc.available) + '</div>')
                            + (!loc.unavailable_until ? '' : '<div class="my-1 text-xs text-red-400"> No appointments available</div>')
                            + (loc.available || loc.unavailable_until ? '' : '<div class="my-1 text-xs text-gray-400"> Availability Unknown</div>')
                            + '<div class="my-1 text-xs text-gray-600">Last updated ' + this.formatDateRelative(loc.updated_at)
                            + (!this.$page.props.user ? '' : ' <span class="my-1 text-xs text-gray-500 cursor-pointer underline" onclick="vaccine_vue.showInputModal(' + loc.id + ')">update now</span>')
                            + '</div>'
                            + (!loc.bookinglink ? '' : '<div class="my-1 appt-link"><a href="' + loc.bookinglink + '" target="_blank" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold inline-block hover:text-white py-1 my-1 px-2 border border-blue-500 hover:border-transparent rounded">Search Appointments</a></div>');
                        this.map.infoWindow.setContent(content);
                        this.map.infoWindow.open(this.map.gmap, marker);
                    });

                    window.markers.push(marker);

                    loc.marker = marker;

                }
            });

            if(this.search_center.lat != null) {
                let latlng = {
                    lat: parseFloat(this.search_center.lat),
                    lng: parseFloat(this.search_center.lng),
                }
                console.log(latlng);
                this.map.gmap.setCenter(latlng);
                this.map.gmap.setZoom(10);
            }
        },
        showInputModal(loc) {
            if(typeof(loc) !== 'object') {
                console.log('Searching for' + loc);
                loc = this.search_locations.find(function(location) { return location.id == loc; });
            }
            this.update_input.location = loc;
            this.update_input.date_next_available = null;
            this.update_input.show_modal = true;
        },
        hideInputModal() {
            this.update_input.location = false;
            this.update_input.show_modal = false;
            console.log('trying to hide');
            console.log(this.update_input);
        },
        showLocationMarker(loc) {
            new google.maps.event.trigger( loc.marker, 'click' );
        },
        updateCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const pos = {
                            lat: Math.round(position.coords.latitude*1000)/1000,
                            lng: Math.round(position.coords.longitude*1000)/1000,
                        };
                        console.log({currentPosition: position});

                        if(window.current_location_marker) {
                            window.current_location_marker.setMap(null);
                        }

                        window.current_location_marker = new google.maps.Marker({
                            position: pos,
                            map: this.map.gmap,
                            title: 'Current Location',
                            icon: 'https://maps.google.com/mapfiles/kml/shapes/placemark_circle_highlight.png',
                        });

                        this.map.current_location_marker = window.current_location_marker;

                        this.search_q = '';
                        this.current_location = { lat: pos.lat, lng: pos.lng };
                        toastr.info('Thanks for sharing your location. We\'re searching near you.');
                        this.searchLocations();
                    }
                );
            }
        },
        submitAvailability() {
            let vue = this;
            axios.post('/api/locations/' + this.update_input.location.id + '/availability', {
                no_availability: this.update_input.no_availability,
                availability_time: this.update_input.date_next_available,
                brand: this.update_input.brand,
                clear_existing: this.update_input.clear_existing,
            }).then(function(response) {
                if(response.data.error) {
                    toastr.error(error, 'Availability Update Error');
                    return;
                }

                toastr.success(response.data.location.name, 'Availability updated!');
                vue.searchLocations();
                vue.hideInputModal();

                console.log(response);
            }).catch(function(error) {
                console.log(error);
            });
        },
        clearNotifications() {
            toastr.clear();
        },
        round(num, digits) {
            if(digits == null) {
                digits = 1;
            }
            return Math.round(num * Math.pow(10,digits)) / Math.pow(10, digits);
        },
        formatDate(date_string) {
            return dayjs(date_string).format('M/D');
        },
        formatDateTime(date_string) {
            return dayjs(date_string).format('M/D H:m a');
        },
        formatDateRelative(date_string) {
            return dayjs(date_string).fromNow();
        },
        addressHtml(address) {
            return address.replace(/\||\r\n?/,'<br>');
        },
    },
    mounted() {
        const loader = new Loader({
            apiKey: process.env.MIX_GOOGLE_MAPS_KEY,
            version: "weekly",
            libraries: []
        });

        this.search_locations = this.locations;
        window.vaccine_vue = this;

        loader
            .load()
            .then(() => {

                this.map.options.mapTypeControl = true;
                this.map.options.mapTypeControlOptions = {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                    position: google.maps.ControlPosition.BOTTOM_LEFT,
                };
                this.map.gmap = new google.maps.Map(document.getElementById('map'), this.map.options)
                this.map.infoWindow = new google.maps.InfoWindow();
                window.map = this.map.gmap;
                window.infoWindow = this.map.infoWindow;

                this.resetMarkers(this.locations);
                this.updateCurrentLocation();
            });
    },
};
</script>
