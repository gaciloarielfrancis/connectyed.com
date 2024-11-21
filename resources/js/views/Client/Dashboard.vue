<template>
    <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8 items-center justify-center">
      <div class="mx-full shadow-connectyed rounded-xl bg-connectyed-card-mm-light flex flex-col mb-5">
        <div class="flex rounded-xl">
          <!-- Sidebar Menu -->
          <aside class="bg-[#e7dccf] text-[#333333] flex flex-col fixed md:relative rounded-l-xl w-72">
            <!-- Profile Section -->
            <div class="flex items-center p-4 bg-[#213366] text-white">
              <img
                v-if="profile && profile.avatar"
                :src="profile.avatar"
                alt="Profile Picture"
                class="w-12 h-12 rounded-full object-cover mr-3"
              />
              <div>
                <h2 class="text-lg font-semibold text-connectyed-button-light">
                  {{ user && user.name ? user.name : 'Guest' }}
                </h2>
                <p class="text-sm text-[#e7dccf]">Client</p>
              </div>
            </div>

            <!-- Navigation Links -->
            <nav class="mt-6 flex-1">
              <ul>
                <li v-for="item in menuItems" :key="item.title" class="mb-1">
                  <router-link
                    :to="'/client' + item.link"
                    class="block px-4 py-3 hover:bg-[#333333] hover:text-white transition-colors duration-200"
                    active-class="bg-connectyed-card-mm-light text-gray-900"
                  >
                    <font-awesome-icon :icon="item.icon" class="w-5 h-5 mr-4" />
                    <span>{{ item.title }}</span>
                  </router-link>
                </li>
              </ul>
            </nav>
          </aside>

          <!-- Main Content Area -->
          <main class="flex-1 p-6">
            <router-view />
          </main>
        </div>
      </div>
    </div>
  </template>

<script>
import { faTachometerAlt, faUser, faDollarSign, faEnvelope } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

export default {
  name: "ClientDashboard",
  components: {
    FontAwesomeIcon,
  },
  data() {
    return {
      authorization: this.$store.state.auth.authorization,
      menuItems: [
        { title: "Dashboard", icon: faTachometerAlt, link: "/dashboard" },
        { title: "My Profile", icon: faUser, link: "/profile" },
        { title: "Billing and Subscription", icon: faDollarSign, link: "/billing" },
        { title: "Communication", icon: faEnvelope, link: "/communication" },
      ],
    };
  },
  computed: {
    user() {
      return this.$store.state.auth.user;
    },
    profile() {
      return this.$store.state.auth.profile;
    },
  },
  mounted() {
    
  },
  methods: {
    
  }
};
</script>
