<template>
    <div id="availability-update" class="w-full h-full">
        <div class="w-full h-full bg-gray-700 bg-opacity-50 p-10">
            <div class="bg-white my-10 bg-white p-10 w-96 mx-auto">
                <p class="text-lg font-bold">{{ update_input.location.name }}</p>
                <p class="text-sm text-gray-900">{{ update_input.location.address }}</p>
                <p class="py-2"><input type="checkbox" name="no_availability" v-model="update_input.no_availability" value="true" /> No appointments currently availabile</p>
                <div v-if="!update_input.no_availability">
                    <p class="py-2">
                        Next availability date: &nbsp;
                            <input v-model="update_input.date_next_available" placeholder="yyyy-mm-dd" id="datepicker" />
                    </p>
                    <p class="py-2">
                        Number of doses: &nbsp;
                            <input v-model="update_input.doses" />
                    </p>
                    <p class="py-2">Vaccine Brand <span class="text-xs text-gray-600">(optional)</span>: &nbsp;
                        <select v-model="update_input.brand">
                            <option value="">-- Select Brand --</option>
                            <option value="p">Pfizer</option>
                            <option value="m">Moderna</option>
                            <option value="j">Johnson & Johnson</option>
                        </select>
                    </p>
                </div>
                <button class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded" @click="submitAvailability">
                    Submit Availability
                </button>
                <p class="py-2 text-sm"><em>If you have additional instructions, please visit our <a href="https://info.vaccinateoh.org/providers/" target="_blank" class="underline text-blue">For Providers</a> page to submit URL, phone, address, or other updates.</em></p>
            </div>
        </div>
    </div>
</template>

<style>
#app {
    height: 100vh;
}
</style>

<script>
import toastr from 'toastr';
import * as dayjs from 'dayjs';
import Checkbox from '../Components/Checkbox';

export default {
    components: {
        Checkbox,
    },
    props: {
        location: Object,
    },
    data() {
        return {
            update_input: {
                location: {},
                date_next_available: null,
                no_availability: false,
                doses: 1,
                clear_existing: true,
                show_modal: false,
            },
        };
    },
    methods: {
        submitAvailability() {
            let vue = this;
            axios.post('/api/locations/' + this.update_input.location.key + '/provider-availability', {
                no_availability: this.update_input.no_availability,
                availability_time: this.update_input.date_next_available,
                doses: this.update_input.doses,
                brand: this.update_input.brand,
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
        formatDate(date_string) {
            return dayjs(date_string).format('M/D');
        },
    },
    mounted() {
        window.vaccine_vue = this;
        this.update_input.location = this.location;
        this.update_input.date_next_available = null;
        this.update_input.show_modal = true;
    },
};
</script>
