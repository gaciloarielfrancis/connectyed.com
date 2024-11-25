<!-- resources/js/views/Matchmaker/Communication.vue -->
<template>
  <div>
    <!-- Conditional Rendering Based on Authentication -->
    <div v-if="isAuthenticated">
      <!-- Tabs Navigation -->
      <div class="tabs mb-4">
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
        <!-- Client Selection -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-2">Select Clients</h3>
          <div class="relative">
            <input
              v-model="searchQuery"
              @focus="showDropdown = true"
              @blur="hideDropdown"
              @input="showDropdown = true"
              type="text"
              placeholder="Search for clients..."
              class="w-full px-3 py-2 border rounded"
              ref="clientInput"
            />
            <ul
              v-if="showDropdown"
              class="absolute z-10 w-full bg-white border rounded shadow mt-1 max-h-60 overflow-y-auto"
            >
              <li
                v-for="client in filteredClients"
                :key="client.id"
                @click="selectClient(client)"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
              >
                {{ client.name }}
              </li>
              <li v-if="filteredClients.length === 0" class="px-3 py-2 text-gray-500">
                No clients found.
              </li>
            </ul>
          </div>
          <!-- Selected Clients -->
          <div class="mt-2">
            <span
              v-for="client in selectedClients"
              :key="client.id"
              class="inline-flex items-center bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded mr-2"
            >
              {{ client.name }}
              <button @click="removeClient(client.id)" class="ml-1 text-red-500">✖️</button>
            </span>
          </div>
        </div>

        <!-- Meeting Scheduling Form -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-2">Select Date and Time</h3>
          <input
            v-model="startTime"
            type="datetime-local"
            class="w-full px-3 py-2 border rounded"
            :min="minDateTime"
          />
        </div>

        <!-- Duration Selection -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-2">Select Duration</h3>
          <select v-model="duration" class="w-full px-3 py-2 border rounded">
            <option disabled value="">Please select duration</option>
            <option value="15">15 Minutes</option>
            <option value="30">30 Minutes</option>
            <option value="60">60 Minutes</option>
          </select>
        </div>

        <!-- Schedule Meeting Button -->
        <button
          @click="scheduleMeeting"
          :disabled="processing || selectedClients.length === 0 || !startTime || !duration"
          class="w-full py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 transition duration-200"
        >
          {{ processing ? 'Scheduling...' : 'Schedule Meeting' }}
        </button>

        <!-- Messaging Component Integration -->
        <div class="mt-8">
          <Messaging :currentUser="user" :authorization="authorization" />
        </div>
      </div>

      <!-- Upcoming Meetings Tab Content -->
      <div v-if="currentTab === 'upcoming'">
        <h3 class="text-lg font-semibold mb-4">Your Upcoming Meetings</h3>
        <div v-if="processing" class="text-center">Loading upcoming meetings...</div>
        <div v-else>
          <div v-if="matchmakerMeetings.length === 0 && clientMeetings.length === 0" class="text-center text-gray-500">
            No upcoming meetings.
          </div>
          <div v-else>
            <!-- Matchmaker Hosted Meetings -->
            <div v-if="matchmakerMeetings.length > 0" class="mb-6">
              <h4 class="text-md font-semibold mb-2">Hosted Meetings</h4>
              <ul>
                <li
                  v-for="meeting in matchmakerMeetings"
                  :key="meeting.id"
                  class="mb-2 p-4 border rounded"
                >
                  <p><strong>Meeting ID:</strong> {{ meeting.google_meet_id }}</p>
                  <p><strong>Date & Time:</strong> {{ formatDate(meeting.start_time) }}</p>
                  <p><strong>Status:</strong> {{ meeting.status }}</p>
                  <a
                    :href="meeting.google_meet_link"
                    target="_blank"
                    class="text-blue-500 underline"
                  >
                    Join Google Meet
                  </a>
                </li>
              </ul>
            </div>

            <!-- Client Meetings -->
            <div v-if="clientMeetings.length > 0">
              <h4 class="text-md font-semibold mb-2">Client Meetings</h4>
              <ul>
                <li
                  v-for="meeting in clientMeetings"
                  :key="meeting.id"
                  class="mb-2 p-4 border rounded"
                >
                  <p><strong>Meeting ID:</strong> {{ meeting.google_meet_id }}</p>
                  <p><strong>Date & Time:</strong> {{ formatDate(meeting.start_time) }}</p>
                  <p><strong>Status:</strong> {{ meeting.status }}</p>
                  <a
                    :href="meeting.google_meet_link"
                    target="_blank"
                    class="text-blue-500 underline"
                  >
                    Join Google Meet
                  </a>
                </li>
              </ul>
            </div>
          </div>
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
import Messaging from '@/components/Messaging.vue';
import { format, parseISO } from 'date-fns';
import { mapState } from 'vuex';

export default {
  name: 'Communication',
  components: {
    Messaging,
  },
  data() {
    return {
      currentTab: 'schedule', // Default to 'schedule' tab
      selectedClients: [], // Currently selected clients
      clients: [], // List of all clients
      matchmakerMeetings: [], // Hosted meetings
      clientMeetings: [], // Client-specific meetings
      processing: false, // To handle loading states
      searchQuery: '', // Search input
      showDropdown: false, // Control dropdown visibility
      duration: 30, // Default duration
      startTime: '', // Selected start time
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
        this.getClients();
        this.getUpcomingMeetings();
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
      this.getClients();
      this.getUpcomingMeetings();
    }
    console.log('User:', this.user); // Debugging: Verify user object
    console.log('Authorization:', this.authorization); // Debugging: Verify authorization token
  },
  methods: {
    /**
     * Fetch clients from the API.
     * Updated method with additional console.log statements for debugging.
     */
    async getClients() {
      if (!this.isAuthenticated) return;
      this.processing = true;
      try {
        const endpoint = '/api/clients/all?fetch_all=true'; // Added fetch_all parameter
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

    /**
     * Fetch upcoming meetings from the API.
     */
    async getUpcomingMeetings() {
      if (!this.isAuthenticated) return;
      this.processing = true;
      try {
        const response = await axios.get('/api/zoom/upcoming-meetings', {
          headers: { Authorization: `Bearer ${this.authorization.token}` },
        });

        if (response.data.success) {
          this.matchmakerMeetings = response.data.data.matchmakerMeetings;
          this.clientMeetings = response.data.data.clientMeetings;
        } else {
          console.error('Failed to fetch upcoming meetings:', response.data.message);
        }
      } catch (error) {
        console.error('Error fetching upcoming meetings:', error);
      } finally {
        this.processing = false;
      }
    },

    /**
     * Select a client and add to the selectedClients array.
     * @param {Object} client - The selected client object.
     */
    selectClient(client) {
      if (this.selectedClients.length < 2) {
        this.selectedClients.push(client);
        this.searchQuery = '';
        this.showDropdown = false;
        this.$refs.clientInput.blur();
      } else {
        alert('You can select up to 2 clients only.');
      }
    },

    /**
     * Remove a client from the selectedClients array.
     * @param {Number} clientId - The ID of the client to remove.
     */
    removeClient(clientId) {
      this.selectedClients = this.selectedClients.filter(
        (client) => client.id !== clientId
      );
    },

    /**
     * Hide the dropdown after a short delay to allow click events to register.
     */
    hideDropdown() {
      setTimeout(() => {
        this.showDropdown = false;
      }, 100);
    },

    /**
     * Schedule a meeting by sending a request to the backend.
     */
    async scheduleMeeting() {
      if (this.selectedClients.length === 0) {
        alert('Please select at least one client.');
        return;
      }

      if (!this.startTime) {
        alert('Please select a meeting date and time.');
        return;
      }

      if (!this.duration) {
        alert('Please select a meeting duration.');
        return;
      }

      // Validate that the selected time is in the future
      const selectedDateTime = new Date(this.startTime);
      if (selectedDateTime < new Date()) {
        alert('Please select a future date and time for the meeting.');
        return;
      }

      // Validate that duration is one of the allowed values
      if (![15, 30, 60].includes(parseInt(this.duration))) {
        alert('Invalid duration selected.');
        return;
      }

      this.processing = true;

      try {
        const response = await axios.post(
          '/api/google/create-meeting',
          {
            client_ids: this.selectedClients.map((client) => client.id),
            start_time: this.startTime,
            duration: parseInt(this.duration), // Ensure it's an integer
            matchmaker_id: this.user.id,
          },
          {
            headers: {
              Authorization: `Bearer ${this.authorization.token}`,
            },
          }
        );

        if (response.data.success) {
          alert('Meeting scheduled successfully!');
          // Optionally, you can redirect or perform additional actions here.

          // Reset form
          this.selectedClients = [];
          this.startTime = '';
          this.duration = 30; // Reset to default
          this.getUpcomingMeetings();
        } else {
          alert(response.data.message || 'Failed to schedule meeting.');
        }
      } catch (error) {
        console.error(error);
        alert('Failed to schedule meeting. Please try again.');
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
