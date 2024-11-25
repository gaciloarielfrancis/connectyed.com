<!-- resources/js/views/Matchmaker/Layout.vue -->
<template>
    <!-- Header Section Start -->
    <header class="header" id="navbar">
      <div class="d-none d-lg-block bg-connectyed-header-light">
        <div class="container">
          <div class="header__top--area">
            <!-- Optional content -->
          </div>
        </div>
      </div>
      <div class="header__bottom">
        <div class="container">
          <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand" href="#">
              <h2>
                <router-link :to="{ name: 'home' }" class="nav-link">
                  <img src="/assets/images/logo/connectyedlogo.png" alt="Connectyed Logo" class="w-[70px]" />
                </router-link>
              </h2>
            </a>
            <button
              class="navbar-toggler collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarNavAltMarkup"
              aria-controls="navbarNavAltMarkup"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="navbar-toggler--icon"></span>
            </button>
            <div class="navbar-collapse justify-content-end" id="navbarNavAltMarkup">
              <div class="navbar-nav mainmenu">
                <ul>
                  <li>
                    <router-link :to="{ name: 'home' }" class="nav-link hidden">Home</router-link>
                  </li>
                  <li>
                    <router-link :to="{ name: 'matchmaker/request-meeting' }" class="nav-link">Request for Meeting</router-link>
                  </li>
                </ul>
              </div>
              <div class="header__more">
                <button class="btn" v-if="currentRouteName === 'login'">
                  <router-link
                    :to="{ name: 'register' }"
                    class="bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light py-4 px-5"
                  >
                    Register
                  </router-link>
                </button>
                <button class="btn" v-else-if="currentRouteName === 'register' || !user.name">
                  <router-link
                    :to="{ name: 'login' }"
                    class="bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light py-4 px-5"
                  >
                    Login
                  </router-link>
                </button>
                <a
                  @click="logout"
                  href="javascript:void(0)"
                  class="bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-light py-4 px-5"
                  v-if="user && user.name"
                >
                  Logout
                </a>
              </div>
            </div>
          </nav>
        </div>
      </div>
    </header>
    <!-- Header Section End -->

    <main>
      <router-view></router-view>
    </main>

    <!-- Footer Section Start -->
    <footer class="footer footer--style2">
      <div class="footer__bottom">
        <div class="container">
          <div class="footer__content text-center">
            <p class="mb-0">
              All Rights Reserved &copy; <a href="#">Connectyed</a> || Design By: Connectyed
            </p>
          </div>
        </div>
      </div>
    </footer>
    <!-- Footer Section End -->
  </template>

  <script setup>
  import { computed } from 'vue';
  import { useStore } from 'vuex';
  import { useRouter, useRoute } from 'vue-router';

  const store = useStore();
  const router = useRouter();
  const route = useRoute();

  const currentRouteName = computed(() => route.name);
  const user = computed(() => store.state.auth.user);
  const authorization = computed(() => store.state.auth.authorization);

  const logout = async () => {
    if (authorization.value && authorization.value.token) {
      const token = authorization.value.token;
      axios.defaults.headers.common.Authorization = `Bearer ${token}`;
      await axios
        .post('/api/user/logout')
        .then(() => {
          store.dispatch('auth/logout');
          router.push({ name: 'home' });
        })
        .catch((error) => {
          console.error(error);
          store.dispatch('auth/logout');
          router.push({ name: 'home' });
        });
    } else {
      store.dispatch('auth/logout');
      router.push({ name: 'home' });
    }
  };
  </script>
