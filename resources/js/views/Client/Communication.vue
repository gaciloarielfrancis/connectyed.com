<!-- resources/js/views/Client/Communication.vue -->
<template>
  <div class="container mx-auto p-4">
    <!-- Conditional Rendering Based on Authentication -->
    <div v-if="isAuthenticated">
      <!-- Tabs Navigation -->
      <div class="tabs mb-6">
        <button
          @click="currentTab = 'schedule'"
          :class="{ active: currentTab === 'schedule' }"
          class="tab-button"
        >
          Schedule a Meeting
        </button>
        <button
          @click="currentTab = 'upcoming'"
          :class="{ active: currentTab === 'upcoming' }"
          class="tab-button"
        >
          Upcoming Meetings
        </button>
      </div>

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
          <h3 class="text-lg font-semibold mb-2">Available Time Slots for {{ selectedMatchmaker.name }}</h3>

          <div v-if="allTimeSlots.length" class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg shadow">
              <label class="block text-gray-700 font-bold mb-2" for="timeSlot">Select a Time Slot:</label>
              <select
                id="timeSlot"
                v-model="selectedTimeSlot"
                class="w-full border border-gray-300 rounded p-2"
                required
              >
                <option disabled value="">Please select a time slot</option>
                <option
                  v-for="slot in allTimeSlots"
                  :key="slot.id"
                  :value="slot"
                >
                  {{ slot.display }}
                </option>
              </select>
            </div>
          </div>

          <div v-else class="text-gray-600">No available time slots for this matchmaker.</div>
        </div>

        <!-- Proceed to Payment and Schedule Meeting -->
        <div v-if="selectedTimeSlot" class="mb-6">
          <h3 class="text-lg font-semibold mb-2">Proceed to Payment</h3>
          <button
            @click="proceedToPayment"
            class="w-full py-2 px-4 bg-purple-500 text-white rounded hover:bg-purple-600 transition duration-200"
            :disabled="processing"
          >
            {{ processing ? 'Processing...' : 'Pay and Schedule Meeting' }}
          </button>
        </div>
      </div>

      <!-- Upcoming Meetings Tab Content -->
      <div v-if="currentTab === 'upcoming'">
        <h2 class="text-xl font-semibold mb-4">Upcoming Meetings</h2>
        <div v-if="clientMeetings.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="meeting in clientMeetings"
            :key="meeting.id"
            class="bg-white rounded-lg shadow-md p-6"
          >
            <h3 class="text-lg font-bold mb-2">Meeting ID: {{ meeting.google_meet_id }}</h3>
            <p class="text-gray-700">Start Time: {{ formatDate(meeting.start_time) }}</p>
            <p class="text-gray-700">Duration: {{ meeting.duration }} minutes</p>

            <div v-if="meeting.google_meet_link" class="mt-4">
              <a
                :href="meeting.google_meet_link"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-600 hover:text-blue-800 underline"
              >
                Join Google Meet
              </a>
            </div>

            <div class="mt-4">
              <h4 class="font-semibold mb-1">Matchmaker:</h4>
              <p>{{ meeting.matchmaker.name }} ({{ meeting.matchmaker.email }})</p>
            </div>
          </div>
        </div>
        <div v-else class="text-gray-600">No upcoming meetings found.</div>
      </div>

      <!-- Messaging Component Integration -->
      <div class="mt-8">
        <Messaging :currentUser="user" :authorization="authorization" />
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
      selectedTimeSlot: null, // Currently selected time slot (object)
      clientMeetings: [], // Upcoming meetings for the client
      processing: false, // To handle loading states
      allTimeSlots: [], // Flat list of all available time slots
      searchQuery: '',
      showDropdown: false,
      duration: 30, // Default duration
      selectedClients: [], // Selected clients for matchmaker
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
    validTimeSlots() {
      if (this.selectedMatchmaker && this.selectedMatchmaker.availability.length) {
        // Include all slots, including "All Day" (where start_time and end_time are null)
        return this.selectedMatchmaker.availability;
      }
      return [];
    },
    filteredClients() {
      const query = this.searchQuery.toLowerCase();
      return this.clients.filter(
        (client) =>
          client.name &&
          client.name.toLowerCase().includes(query) &&
          !this.selectedClients.find((c) => c.id === client.id)
      );
    },
    minDateTime() {
      // Prevent scheduling meetings in the past
      return new Date().toISOString().slice(0, 16);
    },
  },
  watch: {
    isAuthenticated(newVal) {
      if (newVal) {
        this.fetchMatchmakers();
        this.getUpcomingMeetings();
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
      this.getUpcomingMeetings();
      this.getClients();
    }
    console.log('User:', this.user); // Debugging: Verify user object
    console.log('Authorization:', this.authorization); // Debugging: Verify authorization token
  },
  methods: {
    /**
     * Fetch matchmakers from the API.
     */
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
      this.selectedTimeSlot = null; // Reset time slot selection
      this.prepareAllTimeSlots(); // Prepare the dropdown options
    },

    /**
     * Prepare a flat list of all available time slots, including "All Day" entries.
     */
    prepareAllTimeSlots() {
      this.allTimeSlots = [];

      this.validTimeSlots.forEach((availability) => {
        const startDate = parseISO(availability.start_date);
        const endDate = parseISO(availability.end_date);

        if (!isValid(startDate) || !isValid(endDate) || startDate > endDate) {
          console.warn(`Invalid date range for availability ID ${availability.id}. Skipping.`);
          return;
        }

        // Iterate through each day in the range
        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
          const day = format(currentDate, 'yyyy-MM-dd');
          const formattedDate = format(currentDate, 'EEEE, MMMM d, yyyy');

          if (availability.is_all_day) {
            // "All Day" availability
            this.allTimeSlots.push({
              id: `${availability.id}-${day}-all-day`,
              display: `${formattedDate} - All Day`,
              day: day,
              slot: null, // Indicate all day
              duration: 1440, // 24 hours in minutes
              start_time: null,
              end_time: null,
            });
          } else {
            // Specific time slot
            const startTime = format(parseISO(`${day}T${availability.start_time}`), 'hh:mm a');
            const endTime = format(parseISO(`${day}T${availability.end_time}`), 'hh:mm a');

            this.allTimeSlots.push({
              id: `${availability.id}-${day}-${availability.start_time}-${availability.end_time}`,
              display: `${formattedDate} - ${startTime} to ${endTime}`,
              day: day,
              slot: {
                start: parseISO(`${day}T${availability.start_time}`),
                end: parseISO(`${day}T${availability.end_time}`),
              },
              duration: this.calculateDuration(availability.start_time, availability.end_time),
              start_time: availability.start_time,
              end_time: availability.end_time,
            });
          }

          // Move to the next day
          currentDate.setDate(currentDate.getDate() + 1);
        }
      });

      // Optionally, sort the time slots by date and time
      this.allTimeSlots.sort((a, b) => {
        const dateA = new Date(a.day);
        const dateB = new Date(b.day);
        if (dateA < dateB) return -1;
        if (dateA > dateB) return 1;

        if (a.slot && b.slot) {
          return a.slot.start - b.slot.start;
        } else if (a.slot) {
          return -1;
        } else if (b.slot) {
          return 1;
        } else {
          return 0;
        }
      });
    },

    /**
     * Calculate duration in minutes between start and end time strings (HH:MM).
     * @param {string} startTime - Start time in 'HH:mm' format.
     * @param {string} endTime - End time in 'HH:mm' format.
     * @returns {number} - Duration in minutes.
     */
    calculateDuration(startTime, endTime) {
      const [startHour, startMinute] = startTime.split(':').map(Number);
      const [endHour, endMinute] = endTime.split(':').map(Number);
      const start = new Date();
      start.setHours(startHour, startMinute, 0, 0);
      const end = new Date();
      end.setHours(endHour, endMinute, 0, 0);
      return (end - start) / 60000;
    },

    /**
     * Proceed to payment by creating a Stripe Checkout session.
     * After successful payment, the meeting will be scheduled.
     */
    async proceedToPayment() {
      if (!this.selectedMatchmaker || !this.selectedTimeSlot) {
        alert('Please select a matchmaker and a time slot before proceeding to payment.');
        return;
      }

      const slot = this.selectedTimeSlot;

      let startTimeISO;
      let duration;

      if (!slot.slot) {
        // All Day
        startTimeISO = new Date(`${slot.day}T00:00:00`).toISOString();
        duration = 1440; // 24 hours in minutes
      } else {
        // Specific time slot
        startTimeISO = slot.slot.start.toISOString();
        duration = slot.duration; // Already calculated in prepareAllTimeSlots
      }

      this.processing = true;

      try {
        // Debugging: Log the data being sent
        console.log('Creating checkout session with the following data:');
        console.log('Matchmaker ID:', this.selectedMatchmaker.id);
        console.log('Start Time:', startTimeISO);
        console.log('Duration:', duration);

        // Send a request to create a Stripe Checkout session
        const response = await axios.post(
          // '/api/google/create-meeting',
          '/api/zoom/pay-to-schedule',
          {
            matchmaker_id: this.selectedMatchmaker.id,
            client_ids: [this.user.id], // Assuming the client is scheduling for themselves
            start_time: startTimeISO,
            duration: duration,
          },
          {
            headers: {
              Authorization: `Bearer ${this.authorization.token}`,
            },
          }
        );

        if (response.data.success) {
          const { payment_link } = response.data.data;

          // Notify the user
          alert('🔗 Redirecting to payment...');

          // Redirect to Stripe Checkout
          window.location.href = payment_link;
        } else {
          console.error('Failed to create checkout session:', response.data.message);
          alert(`Error: ${response.data.message || 'Failed to create checkout session.'}`);
        }
      } catch (error) {
        console.error('Error creating checkout session:', error);
        alert('An error occurred while creating the checkout session.');
      } finally {
        this.processing = false;
      }
    },

    /**
     * Format the date string for display.
     * @param {String} dateString - The date string.
     * @returns {String} - Formatted date string.
     */
    formatDate(dateString) {
      return format(parseISO(dateString), 'EEEE, MMMM d, yyyy h:mm a');
    },

    /**
     * Fetch upcoming meetings for the client.
     */
    async getUpcomingMeetings() {
      if (!this.isAuthenticated) return;
      try {
        const response = await axios.get('/api/google/upcoming-meetings', {
          headers: {
            Authorization: `Bearer ${this.authorization.token}`,
          },
        });
        if (response.data.success) {
          this.clientMeetings = response.data.data;
          console.log('Upcoming Meetings:', this.clientMeetings);
        } else {
          console.error('Failed to fetch upcoming meetings:', response.data.message);
          alert('Failed to fetch upcoming meetings. Please try again later.');
        }
      } catch (error) {
        console.error('Error fetching upcoming meetings:', error);
        alert('An error occurred while fetching upcoming meetings.');
      }
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
        alert('An error occurred while fetching clients.');
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
