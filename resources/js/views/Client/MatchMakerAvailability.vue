<!-- resources/js/views/Client/Communication.vue -->
<template>
	<div class="container mx-auto p-4">
	<!-- Conditional Rendering Based on Authentication -->
	<div v-if="isAuthenticated">
		<!-- Schedule a Meeting Tab Content -->
		<div v-if="currentTab === 'schedule'">
		<!-- Matchmaker Selection -->
		<div class="mb-6">
			<h3 class="text-lg font-semibold mb-2">Select a Matchmaker</h3>
			<div v-if="matchmakers.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
				<div
					v-for="matchmaker in matchmakers"
					:key="matchmaker.id"
					class="bg-gray-100 p-4 rounded-lg shadow hover:bg-gray-200 transition duration-200"
				>
					<h4 class="font-bold text-md mb-1">{{ matchmaker.name }}</h4>
					<p class="text-sm text-gray-600 mb-2">{{ matchmaker.email }}</p>
					<button
					@click="selectMatchmaker(matchmaker)"
					:disabled="selectedMatchmaker && selectedMatchmaker.id === matchmaker.id"
					class="w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-blue-300 transition duration-200"
					>
					{{ selectedMatchmaker && selectedMatchmaker.id === matchmaker.id ? 'Selected' : 'Select' }}
					</button>
			</div>
			</div>
			<div v-else class="text-gray-600">No matchmakers available at the moment.</div>
		</div>

		<!-- Display Availability -->
		<div v-if="selectedMatchmaker" class="mb-6">
			<h3 class="text-lg font-semibold mb-2">You selected Matchmaker: <b>{{ selectedMatchmaker.name }}</b></h3>
			<div v-if="selectedMatchmaker.availability.length" class="space-y-4">
				<div class="bg-gray-50 p-4 rounded-lg shadow">
					<label class="block text-gray-700 font-semibold mb-2" for="message">Reason for meeting</label>
					<textarea 
						@change="changeReason($event)" 
						class="w-full h-[200px] resize-none border border-gray-300 rounded p-2 mb-4" 
						placeholder="Enter your message here..." required
					></textarea>
					<label class="block text-gray-700 font-semibold mb-2" for="timeZone">Select the timezone you will be located in when your date occurs.</label>
					<select
						@change="selectTimezone($event)"
						id="timeZone"
						class="w-full border border-gray-300 rounded p-2 mb-4"
						required
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
							class="border !border-r-0 border-gray-300 rounded !rounded-r-none col-span-2"
						>
							<option hidden value="Day" selected>Date</option>
							<option v-for="date in availableDates" :key="formatDate(date)" :value="formatDate(date)">
								{{ formatDate(date, 'EEEE, MMMM d, yyyy') }}
							</option>
						</select>
						<select
							@change="selectTimeFrom($event, i)"
							class="border border-gray-300 !rounded-none"
						>
							<option value="" hidden selected>From</option>
							<option v-for="time in getTimeFrom(i)" :key="'from_' + formatTime(time)" :value="formatTime(time)">
								{{ formatTime(time, 'h:mm aaa') }}
							</option>
						</select>
						<select
							@change="selectTimeTo($event, i)"
							class="border !border-l-0 border-gray-300 rounded !rounded-l-none"
						>
							<option value="" hidden selected>To</option>
							<option v-for="time in getTimeTo(i)" :key="'from_' + formatTime(time)" :value="formatTime(time)">
								{{ formatTime(time, 'h:mm aaa') }}
							</option>
						</select>
					</div>
				</div>
			</div>
			<div v-else class="text-gray-600">No available time slots for this matchmaker.</div>
		</div>

		<!-- Proceed to Payment and Schedule Meeting -->
		<div v-if="reasonMessage && timezone && schedules.length" class="mb-6">
			<button
			@click="proceedToPayment"
			class="w-full py-2 px-4 bg-purple-500 text-white rounded hover:bg-purple-600 transition duration-200"
			:disabled="processing"
			>
			{{ processing ? 'Processing...' : 'Schedule Meeting' }}
			</button>
		</div>
		</div>
	</div>

	<!-- Loading Indicator When Not Authenticated -->
	<div v-else class="text-center py-10">
		<p>Loading...</p>
	</div>
	</div>
</template>

<script>
import axios from 'axios';
import { parseISO, format, isValid } from 'date-fns';
import Messaging from '@/components/Messaging.vue'; // Adjust the path as necessary
import { mapState } from 'vuex';



export default {
	name: 'Communication',
	components: {
	Messaging,
	},
	data() {
	return {
		currentTab: 'schedule', // Default to 'schedule' tab
		matchmakers: [], // List of matchmakers
		selectedMatchmaker: null, // Currently selected matchmaker
		reasonMessage: "",
		timezone: "",
		availableDateTime: [],
		availableDates: [],
		schedules: [],
		dateTimeSlots: [
			{ date: null, from: null, to: null },
			{ date: null, from: null, to: null },
			{ date: null, from: null, to: null }
		],
		processing: false, // To handle loading states
		clients: [], // List of all clients
	};
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
	watch: {
		isAuthenticated(newVal) {
			if (newVal) {
			this.fetchMatchmakers();
			this.getClients();
			}
		},
	},
	created() {
	const urlParams = new URLSearchParams(window.location.search);
	const token = urlParams.get('token');
	if (token) {
		axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
		axios
		.get('/api/user/introspect', {
			headers: { Authorization: `Bearer ${token}` },
		})
		.then((response) => {
			const data = {
			user: response.data.user,
			authorization: {
				token: token,
			},
			};
			this.$store.dispatch('auth/setAuth', data); // Dispatch setAuth action

			// Optionally, remove token from URL to prevent exposure
			window.history.replaceState({}, document.title, '/matchmaker/communication');
		})
		.catch((error) => {
			console.error('Error fetching user data:', error);
			alert('Failed to authenticate. Please log in again.');
			this.$router.push('/login');
		});
	} else {
		this.$store.dispatch('auth/initialize');
	}
	},
	mounted() {
	if (this.isAuthenticated) {
		this.fetchMatchmakers();
		this.getClients();
	}
	console.log('User:', this.user); // Debugging: Verify user object
	console.log('Authorization:', this.authorization); // Debugging: Verify authorization token
	},
	methods: {
	/**
	 * Fetch matchmakers from the API.
	 */
	getTimeZones() {
		return Intl.supportedValuesOf('timeZone');
	},
	async fetchMatchmakers() {
		if (!this.isAuthenticated) return;
		this.processing = true;
		try {
		const response = await axios.get('/api/matchmakers', {
			headers: {
			Authorization: `Bearer ${this.authorization.token}`,
			},
		});
		if (response.data.success) {
			this.matchmakers = response.data.data;
			console.log('Matchmakers:', this.matchmakers);
		} else {
			console.error('Failed to fetch matchmakers:', response.data.message);
			alert('Failed to fetch matchmakers. Please try again later.');
		}
		} catch (error) {
		console.error('Error fetching matchmakers:', error);
		alert('An error occurred while fetching matchmakers.');
		} finally {
		this.processing = false;
		}
	},

	/**
	 * Select a matchmaker and reset any previously selected time slot.
	 * @param {Object} matchmaker - The selected matchmaker object.
	 */
	selectMatchmaker(matchmaker) {
		this.selectedMatchmaker = matchmaker;
		this.updateAvailabilityDateTime(matchmaker.availability);
		this.reasonMessage = "";
		this.timezone = "";
		this.schedules = [];
	},
	changeReason(event) {
		this.reasonMessage = event.target.value;
	},
	selectTimezone(event) {
		this.timezone = event.target.value;
	},
	updateAvailabilityDateTime(availabilities) {
		this.availableDateTime = [];
		this.availableDates = [];
		availabilities.forEach(data => {
			const nowDate = new Date();
			const startTime = data.is_all_day ? '00:00' : data.start_time;
			const endTime = data.is_all_day ? '24:00' : data.end_time;
			let startDate = new Date(data.start_date + ' ' + startTime);
			const endDate = new Date(data.end_date + ' ' + endTime);

			if(nowDate > startDate) startDate = new Date();

			const diffInMs = endDate.getTime() - startDate.getTime();
			const diffInDays = parseInt(diffInMs / (1000 * 60 * 60 * 24)) + 1;
			
			const diffInMsNow = new Date(data.end_date + ' ' + endTime).getTime() - new Date(data.end_date + ' ' + startTime).getTime();
			const diffInHours = parseInt(diffInMsNow / (1000 * 60 * 60));

			for(let i = 0; i < diffInDays; i++) {
				const dateAdded = new Date(startDate);
				let timeAdded = new Date(this.formatDate(dateAdded) + ' ' + startTime);
				this.availableDates.push(dateAdded);
				for(let x = 0; x < (diffInHours * 2) + 1; x++) {
					const dateTime = new Date(timeAdded);
					if(dateTime > nowDate && endDate > dateTime) {
						this.availableDateTime.push(dateTime);
					}
					timeAdded.setMinutes(timeAdded.getMinutes() + 30);
				}
				startDate.setDate(startDate.getDate() + 1);
			}
		})
	},
	selectDate(event, index) {
		this.dateTimeSlots[index].date = event.target.value;
		this.dateTimeSlots[index].from = null;
		this.dateTimeSlots[index].to = null;
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
	selectTimeFrom(event, index) {
		this.dateTimeSlots[index].from = event.target.value;
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
	selectTimeTo(event, index) {
		this.dateTimeSlots[index].to = event.target.value;
		this.schedules = [];
		this.dateTimeSlots.forEach(datetime => {
			if(datetime.date && datetime.from && datetime.to) {
				this.schedules.push(datetime);
			}
		})
	},

	/**
	 * Proceed to payment by creating a Stripe Checkout session.
	 * After successful payment, the meeting will be scheduled.
	 */
	async proceedToPayment() {
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
			// Send a request to create a Stripe Checkout session
			const response = await axios.post(
				'/api/zoom/create-a-meeting',
				{
					matchmaker_id: this.selectedMatchmaker.id,
					client_id: this.user.id, // Assuming the client is scheduling for themselves
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

			if (response.data.success) {
				window.location.href = response.data.payment_link;
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

	/**
	 * Format the date string for display.
	 * @param {String} dateString - The date string.
	 * @returns {String} - Formatted date string.
	 */
	formatDate(date, dateFormat = 'yyyy-MM-dd') {
		return format(new Date(date), dateFormat);
	},
	formatTime(date, dateFormat = 'H:mm:ss') {
		return format(new Date(date), dateFormat);
	},
	formatDateTime(date, dateFormat = 'yyyy-MM-dd H:mm:ss') {
		return format(new Date(date), dateFormat);
	},

	/**
	 * Fetch the list of clients.
	 */
	async getClients() {
		if (!this.isAuthenticated) return;
		try {
		const response = await axios.get('/api/clients', {
			headers: {
			Authorization: `Bearer ${this.authorization.token}`,
			},
		});
		if (response.data.success) {
			this.clients = response.data.data;
			console.log('Clients:', this.clients);
		} else {
			console.error('Failed to fetch clients:', response.data.message);
			alert('Failed to fetch clients. Please try again later.');
		}
		} catch (error) {
		console.error('Error fetching clients:', error);
		// alert('An error occurred while fetching clients.');
		}
	},
	},
};
</script>

<style scoped>
/* Tabs Navigation Styles */
.tabs {
	display: flex;
	gap: 1rem;
}

.tab-button {
	padding: 0.5rem 1rem;
	border: none;
	background-color: #f0f0f0;
	cursor: pointer;
	border-radius: 0.375rem;
	transition: background-color 0.3s;
}

.tab-button.active {
	background-color: #3b82f6;
	color: white;
}

.tab-button:hover {
	background-color: #d1d5db;
}

/* Dropdown Styles */
ul {
	max-height: 200px;
	overflow-y: auto;
}

select {
	appearance: none;
	background-color: #fff;
	border: 1px solid #cbd5e0;
	padding: 0.5rem;
	border-radius: 0.375rem;
	width: 100%;
}

option {
	padding: 0.5rem;
}

/* Selected Clients Styles */
button:disabled {
	cursor: not-allowed;
}

/* Responsive Adjustments */
@media (max-width: 600px) {
	.tabs {
	flex-direction: column;
	}
}

/* Additional Styling for Messaging Component */
.messaging-container {
	margin-top: 2rem;
}

/* Additional Styles for Meetings */
.bg-white {
	background-color: #ffffff;
}

.text-gray-600 {
	color: #4a5568;
}

.text-gray-700 {
	color: #4a5568;
}

.text-blue-500 {
	color: #4299e1;
}

.text-blue-600 {
	color: #2b6cb0;
}

.text-blue-800 {
	color: #2c5282;
}

.text-red-500 {
	color: #f56565;
}

.text-green-500 {
	color: #38a169;
}

.text-gray-500 {
	color: #a0aec0;
}

.text-white {
	color: #ffffff;
}

.bg-blue-100 {
	background-color: #ebf8ff;
}

.bg-blue-500 {
	background-color: #4299e1;
}

.bg-blue-600 {
	background-color: #3182ce;
}

.bg-blue-300 {
	background-color: #63b3ed;
}

.bg-red-500 {
	background-color: #f56565;
}

.bg-red-600 {
	background-color: #c53030;
}

.bg-gray-100 {
	background-color: #f7fafc;
}

.bg-gray-200 {
	background-color: #edf2f7;
}

.bg-green-500 {
	background-color: #48bb78;
}

.bg-green-600 {
	background-color: #38a169;
}

.bg-green-300 {
	background-color: #9ae6b4;
}

.bg-purple-500 {
	background-color: #805ad5;
}

.bg-purple-600 {
	background-color: #6b46c1;
}

.rounded-lg {
	border-radius: 0.5rem;
}

.rounded {
	border-radius: 0.375rem;
}

.border {
	border: 1px solid #e2e8f0;
}

.shadow {
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.hover\:bg-gray-100:hover {
	background-color: #f7fafc;
}

.hover\:bg-blue-600:hover {
	background-color: #3182ce;
}

.hover\:bg-red-600:hover {
	background-color: #c53030;
}

.hover\:bg-blue-500:hover {
	background-color: #4299e1;
}

.hover\:bg-red-500:hover {
	background-color: #f56565;
}

.underline {
	text-decoration: underline;
}

.opacity-50 {
	opacity: 0.5;
}

.transition {
	transition: background-color 0.3s;
}

.cursor-pointer {
	cursor: pointer;
}
</style>
