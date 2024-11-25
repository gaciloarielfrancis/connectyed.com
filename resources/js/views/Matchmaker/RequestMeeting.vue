<template>
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-sm shadow-sm">
        <h2 class="text-2xl font-semibold mb-4">Request for Meeting</h2>
        <label class="block text-gray-700 font-semibold mb-2">Select Client</label>
        <select @change="selectClient($event)" class="w-full border border-gray-300 rounded p-2 mb-4">
            <option value="" hidden selected>Search for Client</option>
            <option v-for="client in clients" :value="client.id">{{ client.name }}</option>
        </select>
        <label class="block text-gray-700 font-semibold mb-2">Reason for meeting</label>
        <textarea 
            @change="changeReason($event)" 
            class="w-full h-[200px] resize-none border border-gray-300 rounded p-2 mb-4" 
            placeholder="Enter your message here..."
        ></textarea>
        <label class="block text-gray-700 font-semibold mb-2">Select the timezone you will be located in when your date occurs.</label>
        <select
            @change="selectTimezone($event)"
            class="w-full border border-gray-300 rounded p-2 mb-4"
        >
            <option hidden value="" selected>Timezone</option>
            <option v-for="zone in getTimeZones()" :key="zone" :value="zone">
                {{ zone }}
            </option>
        </select>
        <label class="block text-gray-700 font-semibold mb-2" for="timeSlot">Then please share your soonest available options.</label>
        <div v-for="(n, i) in 3" :key="i" class="grid grid-cols-4 w-full mb-2">
            <select
                @change="selectDate($event, i)"
                class="border !border-r-0 border-gray-300 p-2 rounded !rounded-r-none col-span-2"
            >
                <option hidden value="Day" selected>Date</option>
                <option v-for="date in availableDates" :key="formatDate(date)" :value="formatDate(date)">
                    {{ formatDate(date, 'EEEE, MMMM d, yyyy') }}
                </option>
            </select>
            <select
                @change="selectTimeFrom($event, i)"
                class="border border-gray-300 p-2 !rounded-none"
            >
                <option value="" hidden selected>From</option>
                <option v-for="time in getTimeFrom(i)" :key="'from_' + formatTime(time)" :value="formatTime(time)">
                    {{ formatTime(time, 'h:mm aaa') }}
                </option>
            </select>
            <select
                @change="selectTimeTo($event, i)"
                class="border !border-l-0 border-gray-300 p-2 rounded !rounded-l-none"
            >
                <option value="" hidden selected>To</option>
                <option v-for="time in getTimeTo(i)" :key="'from_' + formatTime(time)" :value="formatTime(time)">
                    {{ formatTime(time, 'h:mm aaa') }}
                </option>
            </select>
        </div>
        <div v-if="reasonMessage && timezone && schedules.length" class="mb-6">
			<button
			@click="requestScheduleMeeting"
			class="w-full py-2 px-4 bg-purple-500 text-white rounded hover:bg-purple-600 transition duration-200"
			:disabled="processing"
			>
			{{ processing ? 'Processing...' : 'Request Schedule For Meeting' }}
			</button>
		</div>
    </div>
</template>

<script>
    import axios from 'axios';
    import { mapState } from 'vuex';
    import { addDays } from 'date-fns';
    import { getTimeZones, formatDate, formatTime, formatDateTime, parseAvailabilities } from '../../components/Utils';

    export default {
        name: 'RequestMeeting',
        components: {
            
        },
        data() {
            return {
                clients: [],
                selectedClient: null,
                reasonMessage: "",
                timezone: "",
                schedules: [],
                availableDateTime: [],
                availableDates: [],
                dateTimeSlots: [
                    { date: null, from: null, to: null },
                    { date: null, from: null, to: null },
                    { date: null, from: null, to: null }
                ],
                processing: false,
                getTimeZones: getTimeZones,
                formatDate: formatDate,
                formatTime: formatTime,
                formatDateTime: formatDateTime,
                parseAvailabilities: parseAvailabilities,
            }
        },
        computed: {
            ...mapState({
                user: (state) => state.auth.user,
                authorization: (state) => state.auth.authorization,
            }),
            isAuthenticated() {
                return !!this.authorization && !!this.authorization.token;
            },
        },
        mounted() {
            if (this.isAuthenticated) {
                this.getClients();
            }
        },
        methods: {
            async getClients() {
                if (!this.isAuthenticated) return;
                this.processing = true;
                try {
                    const endpoint = '/api/clients/allbasic'; // Added fetch_all parameter
                    const { data } = await axios.get(endpoint, {
                        headers: { Authorization: `Bearer ${this.authorization.token}` },
                    });
                    if (data.success) {
                        this.clients = data.data;
                    } else {
                        alert(data.message || 'Failed to fetch clients. Please try again.');
                    }
                } catch (error) {
                    console.error('Error fetching clients:', error.response || error);
                    if (error.response?.status === 403) {
                        alert('You are not authorized to view clients.');
                    } else {
                        alert('Failed to fetch clients. Please try again.');
                    }
                } finally {
                    this.processing = false;
                }
            },
            selectClient(event) {
                const value = parseInt(event.target.value);
                const client = this.clients.find(c => c.id === value);
                this.selectedClient = client;
                this.availableDateTime = [];
                this.availableDates = [];

                // TO BE UPDATE > DATA MUST POPULATED FROM API
                const d = this.parseAvailabilities(
                    [
                        {
                            start_date: this.formatDate(new Date()),
                            end_date: this.formatDate(addDays(new Date(), 30)),
                            is_all_day: true
                        }
                    ]
                );
                console.log(d)
                this.availableDates = d.availableDates;
                this.availableDateTime = d.availableDateTime;

            },
            changeReason(event) {
                this.reasonMessage = event.target.value;
            },
            selectTimezone(event) {
                this.timezone = event.target.value;
            },
            selectDate(event, index) {
                this.dateTimeSlots[index].date = event.target.value;
                this.dateTimeSlots[index].from = null;
                this.dateTimeSlots[index].to = null;
            },
            selectTimeFrom(event, index) {
                this.dateTimeSlots[index].from = event.target.value;
            },
            selectTimeTo(event, index) {
                this.dateTimeSlots[index].to = event.target.value;
                this.schedules = [];
                this.dateTimeSlots.forEach(datetime => {
                    if(datetime.date && datetime.from && datetime.to) {
                        this.schedules.push(datetime);
                    }
                })
            },
            getTimeFrom(index) {
                const time = [];
                if(!this.dateTimeSlots[index].date) return time;
                this.availableDateTime.forEach(datetime => {
                    if(this.formatDate(datetime) === this.dateTimeSlots[index].date) {
                        time.push(datetime);
                    }
                });
                return time;
            },
            getTimeTo(index) {
                const time = [];
                if(!this.dateTimeSlots[index].from) return time;
                this.availableDateTime.forEach(datetime => {
                    if(this.formatDate(datetime) === this.dateTimeSlots[index].date) {
                        const startDate = new Date(this.dateTimeSlots[index].date + " " + this.dateTimeSlots[index].from);
                        const endDate = new Date(datetime);
                        if(endDate > startDate) time.push(datetime);
                    }
                });
                return time;
	        },
            async requestScheduleMeeting() {
                this.processing = true;

                let startTime = "";
                let endTime = "";
                let duration = 0;

                this.schedules.forEach(d => {
                    const sd = new Date(d.date + ' ' + d.from);
                    const ed = new Date(d.date + ' ' + d.to);
                    if(startTime === "") {
                        startTime = sd;
                    }else{
                        if(sd < startTime) startTime = sd;
                    }
                    if(endTime === "") {
                        endTime = ed;
                    }else{
                        if(ed > endTime) endTime = ed;
                    }
                    const diffMs = ed - sd;
                    duration += Math.floor(diffMs / 1000 / 60);
                });

                try {
                    const response = await axios.post(
                        '/api/zoom/request-a-meeting',
                        {
                            matchmaker_id: this.user.id,
                            client_id: this.selectedClient.id,
                            meeting_response: this.reasonMessage,
                            timezone: this.timezone,
                            schedules: this.schedules,
                            start_time: this.formatDateTime(startTime),
                            end_time: this.formatDateTime(endTime),
                            duration: duration
                        },
                        {
                        headers: {
                            Authorization: `Bearer ${this.authorization.token}`,
                        },
                        }
                    );

                    this.processing = false;

                    if (response.data.success) {
                        alert(response.data.message);
                        window.location.href = "/matchmaker/dashboard";
                    } else {
                        console.error('Failed to create schedule:', response.data.message);
                        alert(`Error: ${response.data.message || 'Failed to create schedule session.'}`);
                    }
                } catch (error) {
                    console.error('Error creating schedule session:', error);
                    alert('An error occurred while creating the schedule session.');
                } finally {
                    this.processing = false;
                }

            },
        }
    }
</script>