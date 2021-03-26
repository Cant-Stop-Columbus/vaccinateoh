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

                    <a href="https://info.vaccinateoh.org" class="text-blue font-bold ml-4"  rel="noopener noreferrer">About Us</a>

                     <a href="https://info.vaccinateoh.org/providers/" class="text-blue font-bold ml-4" rel="noopener noreferrer">Providers</a>

                     <a href="https://info.vaccinateoh.org/faq" class="text-blue font-bold ml-4" rel="noopener noreferrer">FAQ</a>
                </div>
            </div>
            <div class="w-full md:flex pb-22 map-search-wrapper">
                <div id="location-sidebar" class="h-full p-2 flex-none md:overflow-y-auto md:order-1">

                    <div class="search-box w-full">
                        <h2 class="text-blue font-bold">Vaccine Appointment Finder</h2>
                        <p>Search vaccine appointment availability by entering in your city, or county, or zipcode.</p>
                        <br>
                        <form @submit.prevent="searchLocations(null)" class="flex">
                            <input class="border border-blue rounded rounded-r-none w-full px-2 py-0 text-gray-700 leading-tight focus:outline-none active:outline-none" id="search" type="text" placeholder="Address/City/Zip Search" v-model="search_q">

                            <button class="bg-blue text-white font-bold rounded-2 py-1 px-3 hover:bg-blue-light rounded rounded-l-none h-10 w-12" type="submit">
                                <span class="sr-only">Search</span>
                                <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.0833 13.3333H14.1617L13.835 13.0183C14.9783 11.6883 15.6667 9.96167 15.6667 8.08333C15.6667 3.895 12.2717 0.5 8.08333 0.5C3.895 0.5 0.5 3.895 0.5 8.08333C0.5 12.2717 3.895 15.6667 8.08333 15.6667C9.96167 15.6667 11.6883 14.9783 13.0183 13.835L13.3333 14.1617V15.0833L19.1667 20.905L20.905 19.1667L15.0833 13.3333ZM8.08333 13.3333C5.17833 13.3333 2.83333 10.9883 2.83333 8.08333C2.83333 5.17833 5.17833 2.83333 8.08333 2.83333C10.9883 2.83333 13.3333 5.17833 13.3333 8.08333C13.3333 10.9883 10.9883 13.3333 8.08333 13.3333Z" fill="white"/>
                                </svg>
                            </button>

                        <div class="cursor-pointer flex flex-col text-sm ml-2 -my-2" @click="toggleFilters">
                            <svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg" class="m-auto w-8">
                                <path d="M29.97 15.12H19.425C19.018 13.08 17.316 11.56 15.318 11.56C13.32 11.56 11.618 13.08 11.211 15.12H0.925C0.407 15.12 0 15.56 0 16.12C0 16.68 0.407 17.12 0.925 17.12H11.211C11.618 19.16 13.32 20.68 15.318 20.68C17.316 20.68 19.018 19.16 19.425 17.12H29.97C30.488 17.12 30.895 16.68 30.895 16.12C30.895 15.6 30.451 15.12 29.97 15.12ZM15.318 18.68C14.023 18.68 12.95 17.52 12.95 16.12C12.95 14.72 14.023 13.56 15.318 13.56C16.613 13.56 17.686 14.72 17.686 16.12C17.686 17.52 16.613 18.68 15.318 18.68Z" fill="#0286FF"/>
                                <path d="M4.25499 9.12C6.25299 9.12 7.95499 7.6 8.36199 5.56H29.933C30.451 5.56 30.858 5.12 30.858 4.56C30.858 4 30.451 3.56 29.933 3.56H8.36199C7.95499 1.52 6.25299 0 4.25499 0C1.92399 0 0.0369873 2.08 0.0369873 4.56C0.0369873 7.04 1.92399 9.12 4.25499 9.12ZM4.25499 2C5.54999 2 6.62299 3.16 6.62299 4.56C6.62299 5.96 5.54999 7.12 4.25499 7.12C2.95999 7.12 1.88699 5.96 1.88699 4.56C1.88699 3.16 2.95999 2 4.25499 2Z" fill="#0286FF"/>
                                <path d="M26.64 23.2C24.642 23.2 22.977 24.72 22.533 26.72H0.925C0.407 26.72 0 27.16 0 27.72C0 28.28 0.407 28.72 0.925 28.72H22.533C22.94 30.76 24.642 32.32 26.64 32.32C28.971 32.32 30.858 30.28 30.858 27.76C30.858 25.24 28.934 23.2 26.64 23.2ZM26.64 30.28C25.345 30.28 24.272 29.12 24.272 27.72C24.272 26.32 25.345 25.16 26.64 25.16C27.935 25.16 29.008 26.32 29.008 27.72C29.008 29.12 27.935 30.28 26.64 30.28Z" fill="#0286FF"/>
                            </svg>
                            <span class="text-blue m-auto" :class="{showing: show_filters}">Filter/Sort</span>
                        </div>

                        </form>

                        <div class="filters-box" :class="{show: show_filters}">
                            <h3 class="text-blue font-bold my-2"></h3>
                            <div class="flex flex-wrap">
                                <div class="p-2 text-sm md:w-1/2">
                                    <h4 class="text-blue">Sort by Availability:</h4>
                                    <radio v-model="search_filters.available" name="search_available" class="text-xs" value="only" label="Available" />
                                    <radio v-model="search_filters.available" name="search_available" class="text-xs" value="no" label="Not Available" />
                                    <radio v-model="search_filters.available" name="search_available" class="text-xs" value="all" label="All" />
                                    <radio v-model="search_filters.available" name="search_available" class="text-xs" value="prefer" label="All with Available First" />
                                </div>
                                <div class="p-2 text-sm md:w-1/2">
                                    <h4 class="text-blue">Sort by Distance:</h4>
                                    <radio v-model="search_filters.distance" name="search_distance" class="text-xs" value="1" label="Within 1 mile" />
                                    <radio v-model="search_filters.distance" name="search_distance" class="text-xs" value="20" label="Within 20 miles" />
                                    <radio v-model="search_filters.distance" name="search_distance" class="text-xs" value="-1" label="Everywhere" />
                                </div>
                                <div class="p-2 text-sm md:w-1/2">
                                    <h4 class="text-blue">Sort by Site Type:</h4>
                                    <checkbox v-model="search_filters.site_type.h" name="search_site_type_h" class="text-xs" value="h" checked label="Healthcare Provider" />
                                    <checkbox v-model="search_filters.site_type.d" name="search_site_type_d" class="text-xs" value="d" checked label="Local Health Department" />
                                    <checkbox v-model="search_filters.site_type.p" name="search_site_type_p" class="text-xs" value="p" checked label="Pharmacies" />
                                </div>
                                <div class="p-2 text-sm md:w-1/2">
                                    <h4 class="text-blue">Sort by Appointment Type:</h4>
                                    <checkbox v-model="search_filters.appt_type.web" name="search_appt_type_w" class="text-xs" value="web" checked label="Schedule by web" />
                                    <checkbox v-model="search_filters.appt_type.phone" name="search_appt_type_p" class="text-xs" value="phone" checked label="Schedule by phone" />
                                    <checkbox v-model="search_filters.appt_type.none" name="search_appt_type_n" class="text-xs" value="none" checked label="Walk-ins" />
                                </div>
                            </div>
                            <button class="float-right bg-blue hover:bg-blue-light text-white font-bold py-1 my-1 px-2 rounded" @click="searchLocations">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                    <br>
                    <ul class="location-list">
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
                </div>
                <div id="map" class="flex-grow md:order-2"></div>

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
import Radio from '../Components/Radio';
import Checkbox from '../Components/Checkbox';

dayjs.extend(relativeTime)

export default {
    components: {
        Checkbox,
        Radio,
    },
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
            search_filters: {
                available: 'all',
                distance: -1,
                /*
                site_type: ['h','d','p'],
                appt_type: ['web','phone','none'],
                */
                site_type: {
                    h: true,
                    d: true,
                    p: true,
                },
                appt_type: {
                    web: true,
                    phone: true,
                    none: true,
                },
            },
            search_page_size: this.mobileCheck() ? 20 : 200,
            search_locations: [],
            search_center: {
                lat:null,
                lng:null
            },
            current_location: null,
            show_filters: false,
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

            let filters = 'available=' + this.search_filters.available
                + '&distance=' + this.search_filters.distance
                + '&site_type=' + this.getFilterValues(this.search_filters.site_type).join(',')
                + '&appt_type=' + this.getFilterValues(this.search_filters.appt_type).join(',')

            toastr.info('Locating vaccine appointments near ' + search_term, 'Searching', {
                closeButton: true,
                timeOut: 0,
                extendedTimeOut: 0,
            });
            axios.get('/api/locations?q=' + search_term + '&page_size=' + this.search_page_size + '&' + filters)
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
        toggleFilters() {
            this.show_filters = !this.show_filters;
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
