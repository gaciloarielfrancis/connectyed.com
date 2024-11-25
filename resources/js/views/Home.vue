<template>
  <!-- ================> Banner section start here <================== -->
  <div class="hero-container">
    <!-- Background Image -->
    <div class="background">
      <img src="assets/images/banner/pexels-orione-conceicao-1531154-2983449.png" alt="banner" />
      <div class="overlay"></div>
    </div>

    <!-- Add Login Button -->
    <div class="login-button-container">
      <a href="login" class="login-button">Login</a>
    </div>

    <!-- Marker Points -->
    <!-- Top Left Marker -->
    <div class="marker top-left">
      <router-link :to="{ name: 'home' }" class="nav-link">
        <img src="assets/images/logo/connectyedlogo.png" alt="Connectyed Logo" class="w-[70px]" />
      </router-link>
    </div>

    <!-- Removed Top Right Marker
    <div class="marker top-right">
      <div class="marker-container">
        <a 
          href="https://www.connectyed.com/client/billing" 
          class="text-white underline hover:text-connectyed-button-hover-dark transition duration-300"
        >
          Pricing Packages
        </a>
      </div>
    </div>
    -->

    <!-- Face Markers -->
    <div class="face-marker man">
      <div class="marker-container">
        <div class="circle large"></div>
        <div class="circle large ping"></div>
      </div>
    </div>

    <div class="face-marker woman">
      <div class="marker-container">
        <div class="circle large"></div>
        <div class="circle large ping"></div>
      </div>
    </div>

    <!-- Content -->
    <div class="content">
      <div class="content-wrapper">
        <h1 class="text-white">Connect To Your Perfect Match.</h1>
        <p class="text-white">
          At Connectyed, we understand the challenges of finding meaningful connections in a busy world. Our mission is to make your search for the perfect match as effortless and rewarding as possible.
        </p>

        <!-- Button and Link Container -->
        <div class="action-buttons">
          <router-link 
            :to="{ name: 'register' }" 
            class="bg-connectyed-button-light text-connectyed-button-dark hover:bg-connectyed-button-hover-light hover:text-connectyed-button-dark py-4 px-5 rounded-full transition duration-300"
          >
            Get Started
          </router-link>
          <a 
            href="https://www.connectyed.com/billing" 
            class="pricing-link text-white underline hover:text-connectyed-button-hover-dark transition duration-300"
          >
            Pricing Packages
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- ================> Banner section end here <================== -->

  <!-- Matchmakers Carousel section start here -->
  <div class="matchmakers padding-bottom padding-top bg_img">
    <div class="container">
      <div class="section__header style-2 text-center">
        <h2>Our Matchmakers</h2>
        <p class="text-black">Choose from our qualified matchmakers to find your perfect match.</p>
      </div>
      <div class="section__wrapper" :class="{ 'center-few': matchmakers.length < 3 }">
        <Swiper
          :modules="[Navigation, Pagination]"
          :spaceBetween="30"
          :slidesPerView="matchmakers.length < 3 ? matchmakers.length : 3"
          :navigation="matchmakers.length > 1"
          :pagination="matchmakers.length > 1 ? { clickable: true } : false"
          :loop="matchmakers.length > 1"
          :allowTouchMove="matchmakers.length > 1"
          :breakpoints="{
            320: { 
              slidesPerView: 1, 
              spaceBetween: 10 
            },
            640: { 
              slidesPerView: Math.min(matchmakers.length, 2), 
              spaceBetween: 20 
            },
            1024: { 
              slidesPerView: Math.min(matchmakers.length, 3), 
              spaceBetween: 30 
            }
          }"
          class="mySwiper"
        >
          <SwiperSlide 
            v-for="matchmaker in matchmakers" 
            :key="matchmaker.id"
            :class="{ 'single-slide': matchmakers.length === 1 }"
          >
            <ProfileCard :matchmaker="matchmaker" />
          </SwiperSlide>
        </Swiper>
      </div>
    </div>
  </div>
  <!-- Matchmakers Carousel section end here -->

  <!-- Story section start here -->
  <!--<div class="story bg_img padding-top padding-bottom">-->
  <!--  <div class="container">-->
  <!--    <div class="section__header style-2 text-center wow fadeInUp" data-wow-duration="1.5s">-->
  <!--      <h2>Dating Tips</h2>-->
  <!--    </div>-->
  <!--    <div class="section__wrapper">-->
  <!--      <div class="row g-4 justify-content-center row-cols-lg-3 row-cols-sm-2 row-cols-1">-->
  <!--        <div class="col wow fadeInUp" data-wow-duration="1.5s" v-for="(post, index) in featuredPosts.data" :key="index">-->
  <!--          <div class="story__item">-->
  <!--            <div class="story__inner">-->
  <!--              <div class="story__thumb">-->
  <!--                <a :href="post.slug"><img :src="post.image" alt="Story Image"></a>-->
  <!--              </div>-->
  <!--              <div class="story__content">-->
  <!--                <a :href="post.slug" class="text-"><h4 class="h-20">{{ post.title }}</h4></a>-->
  <!--              </div>-->
  <!--            </div>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--      </div>-->
  <!--    </div>-->
  <!--  </div>-->
  <!--</div>-->
  <div class="dating-tips-section padding-top padding-bottom">
    <div class="container">
      <div class="section__header text-center">
        <h2>Dating Tips</h2>
        <p class="subtitle">Simple Steps to Boost Your Dating Confidence</p>
      </div>
      <div class="section__wrapper flex items-center justify-center">
        <!-- Left Side with Static Images -->
        <div class="image-gallery">
          <img 
            src="https://media.istockphoto.com/id/993736480/photo/a-couple-of-young-man-and-woman-dancing-hip-hop-at-studio.jpg?s=612x612&w=0&k=20&c=VlyVUnq6XnYwpElWfMVq8hwI_J9xHksfqFLv8WAHYws=" 
            alt="Couple dancing hip-hop" 
            class="gallery-image"
          />
          <img 
            src="https://media.istockphoto.com/id/993736480/photo/a-couple-of-young-man-and-woman-dancing-hip-hop-at-studio.jpg?s=612x612&w=0&k=20&c=VlyVUnq6XnYwpElWfMVq8hwI_J9xHksfqFLv8WAHYws=" 
            alt="Couple dancing hip-hop" 
            class="gallery-image"
          />
        </div>

        <!-- Right Side with Swiper Slider for Dating Tips -->
        <div class="tips-slider">
          <Swiper
            :spaceBetween="30"
            :slidesPerView="1"
            :autoplay="{ delay: 3000, disableOnInteraction: false }"
            :pagination="{ clickable: true }"
            class="datingTipsSwiper"
          >
            <SwiperSlide v-for="(tip, index) in datingTips" :key="index">
              <div class="tip-content">
                <h3 class="tip-title">{{ tip.title }}</h3>
                <p class="tip-description">{{ tip.content }}</p>
              </div>
            </SwiperSlide>
          </Swiper>
        </div>
      </div>
    </div>
  </div>
  <!-- Story section end here -->

  <!-- About section start here -->
  <div class="about padding-top padding-bottom bg_img">
    <div class="container">
      <div class="section__header style-2 text-center wow fadeInUp" data-wow-duration="1.5s">
        <h2>How it Works</h2>
        <p class="text-black">Connectyed specializes in matchmaking for busy professionals. We offer a streamlined, personalized approach to help you connect with someone.</p>
      </div>
      <div class="section__wrapper">
        <div class="row g-4 justify-content-center row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 row-cols-1">
          <div class="col wow fadeInUp" data-wow-duration="1.5s" v-for="(item, index) in howItWorks" :key="index">
            <div class="about__item text-center">
              <div class="about__inner">
                <div class="about__thumb flex justify-center">
                  <img :src="item.image" :alt="item.altText" />
                </div>
                <div class="about__content">
                  <h4>{{ item.title }}</h4>
                  <p class="text-black">{{ item.description }}</p>
                </div>
              </div>
            </div>
          </div>
          <!-- Repeat similar blocks for each "How it Works" step -->
        </div>
      </div>
    </div>
  </div>
  <!-- About section end here -->
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, Autoplay } from 'swiper/modules'; // Updated import path for Swiper modules
import 'swiper/swiper-bundle.css'; // Import Swiper styles

import usePosts from "../composables/posts";
import useMatchmakers from '../composables/matchmakers';
import ProfileCard from './Matchmaker/ProfileCard.vue';

const { matchmakers, getMatchmakers } = useMatchmakers();
const { featuredPosts, getFeaturedPosts } = usePosts();

// Define "howItWorks" data
const howItWorks = ref([
  {
    image: 'assets/images/about/01.jpg',
    altText: 'Choose Your Matchmaker Image',
    title: 'Choose Your Matchmaker',
    description: 'Browse our curated list of qualified matchmakers, each with their own expertise and approach. Select the one who best aligns with your relationship goals and preferences.'
  },
  {
    image: 'assets/images/about/02.jpg',
    altText: 'Introduction Session Image',
    title: 'Introduction Session',
    description: 'Set up an initial introduction session with your chosen matchmaker. This is your opportunity to discuss your relationship goals and get professional advice on how to navigate the dating world.'
  },
  {
    image: 'assets/images/about/03.jpg',
    altText: 'Personalized Matchmaking Image',
    title: 'Personalized Matchmaking',
    description: 'Based on your criteria, your matchmaker will curate 1-on-1 video dates with targeted singles who meet your specifications. Enjoy meaningful connections from the comfort of your own home.'
  },
  {
    image: 'assets/images/about/04.jpg',
    altText: 'Flexible Pricing Image',
    title: 'Flexible Pricing',
    description: 'Prices vary by matchmaker, allowing you to choose a package that fits your needs and budget.'
  }
]);

const datingTips = ref([
  {
    title: 'Be Yourself',
    content: 'Authenticity is key in any relationship. Don’t try to be someone you’re not, as it will only lead to frustration and miscommunication down the line. Embrace your quirks, preferences, and unique personality traits. Remember, the right person will appreciate you for who you truly are, so there’s no need to put on a facade. By being genuine from the start, you set a foundation for a relationship built on trust and honesty.',
  },
  {
    title: 'Listen More',
    content: 'Active listening shows that you genuinely care about your partner and their experiences. It’s not just about hearing words; it’s about understanding emotions and being fully present in the moment. Put away distractions, maintain eye contact, and give thoughtful responses. This will make your partner feel valued and respected, helping to build a deeper emotional connection that’s essential for a lasting relationship.',
  },
  {
    title: 'Stay Positive',
    content: 'A positive attitude can transform your dating experience, making it enjoyable for both you and your partner. Even if things don’t go as planned, try to find humor in the situation and keep the mood light. Positivity is infectious and attractive, drawing people to you and creating a welcoming environment where both of you can relax and be yourselves. Remember, dating is about having fun and discovering new connections, so don’t let minor setbacks affect your outlook.',
  },
  {
    title: 'Take It Slow',
    content: 'Building a meaningful relationship takes time. Rushing things can create misunderstandings and put unnecessary pressure on both parties. Allow yourselves to truly get to know each other’s values, interests, and boundaries. Take time to enjoy each phase of the relationship, from the excitement of early dates to the comfort of deeper emotional intimacy. Moving at a comfortable pace allows both of you to feel secure and grounded in the relationship.',
  },
  {
    title: 'Communicate Openly',
    content: 'Open communication is the foundation of any strong relationship. Don’t hesitate to share your feelings, needs, and expectations with your partner. Whether it’s about setting boundaries, discussing future plans, or simply expressing appreciation, open dialogue prevents misunderstandings and fosters a sense of mutual respect. Remember, effective communication is a two-way street, so be receptive to your partner’s thoughts and feelings as well.',
  },
  {
    title: 'Be Curious and Ask Questions',
    content: 'Show genuine curiosity by asking open-ended questions that encourage your partner to share their thoughts and experiences. This shows that you’re interested in more than just surface-level conversations and helps you discover shared values or goals. Ask about their favorite memories, dreams, and what makes them feel fulfilled. Thoughtful questions can open doors to meaningful discussions and help build a deeper, more connected relationship.',
  },
  {
    title: 'Respect Boundaries',
    content: 'Respecting each other’s boundaries is crucial in any relationship. Make sure to have conversations about what makes each of you comfortable and be attentive to any cues your partner gives. Boundaries are a sign of healthy self-respect, and respecting them demonstrates that you care about your partner’s well-being. This mutual respect forms the backbone of a secure and trusting relationship, where both partners feel safe and valued.',
  },
  {
    title: 'Have Fun Together',
    content: 'While it’s important to have serious discussions and get to know each other, remember that dating should also be enjoyable. Plan fun activities, be playful, and share laughter. Trying new things together can bring excitement and novelty to the relationship, helping you both create happy memories. Whether it’s a spontaneous road trip, a cooking class, or a simple game night, these moments of fun strengthen your bond and create lasting joy.',
  },
  {
    title: 'Show Appreciation',
    content: 'Don’t underestimate the power of a sincere compliment or a small gesture of gratitude. Showing appreciation makes your partner feel valued and loved. A simple “thank you” or “I appreciate you” goes a long way in reinforcing your connection. Make it a habit to acknowledge their efforts, big or small, and let them know how much they mean to you. Regular acts of appreciation foster a positive atmosphere in the relationship.',
  },
  {
    title: 'Be Patient with Differences',
    content: 'Every person has their own set of beliefs, habits, and ways of thinking. Embrace the differences that make your partner unique, and be patient when you encounter perspectives that differ from your own. Learning to accept and understand these differences without judgment strengthens your relationship. Approach disagreements with empathy and an open mind, recognizing that diversity in viewpoints can add richness to your shared experiences.',
  },
]);

onMounted(async () => {
  try {
    await getFeaturedPosts();
    await getMatchmakers();
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});
</script>

<style scoped>
@import 'swiper/swiper-bundle.css';

/* CSS Variables for Consistent Styling */
:root {
  --connectyed-button-light: #f0f0f0;
  --connectyed-button-dark: #333333;
  --connectyed-button-hover-light: #e0e0e0;
  --connectyed-button-hover-dark: #333333; /* Changed from #ffffff for better contrast */
}

.hero-container {
  position: relative;
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

.background {
  position: absolute;
  inset: 0;
}

.background img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.4);
}

.watermark {
  position: absolute;
  top: 32px;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0.2;
}

.watermark p {
  font-size: 24px;
  font-weight: bold;
}

.marker {
  position: absolute;
}

.marker-container {
  position: relative;
}

.circle {
  width: 32px;
  height: 32px;
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-radius: 50%;
}

.circle.large {
  width: 64px;
  height: 64px;
}

.label {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  white-space: nowrap;
}

.label-content {
  background-color: rgba(255, 255, 255, 0.9);
  padding: 8px 16px;
  border-radius: 9999px;
}

.label-content p {
  color: #333;
  font-size: 14px;
}

.label-right {
  left: 48px;
}

.label-left {
  right: 48px;
}

.top-left {
  top: 32px;
  left: 32px;
}

.top-right {
  top: 32px;
  right: 32px;
}

.bottom-right {
  bottom: 32px;
  right: 32px;
}

.face-marker {
  position: absolute;
}

.man {
  top: 33%;
  left: 33%;
}

.woman {
  top: 33%;
  right: 33%;
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.ping {
  position: absolute;
  top: 0;
  left: 0;
  border: 2px solid rgba(255, 255, 255, 0.3);
  animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.content {
  position: relative;
  z-index: 10;
  max-width: 1200px;
  margin: 0 auto;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 0 16px;
}

.content-wrapper {
  max-width: 640px;
}

h1 {
  font-size: 48px;
  font-weight: bold;
  color: white;
  margin-bottom: 16px;
}

p {
  font-size: 18px;
  color: rgba(0, 0, 0, 0.9);
  margin-bottom: 32px;
  line-height: 1.5;
}

button, .router-link {
  /* General button styles can be applied here if needed */
}

/* Button Styles */
.bg-connectyed-button-light {
  background-color: var(--connectyed-button-light);
}

.text-connectyed-button-dark {
  color: var(--connectyed-button-dark);
}

.hover\:bg-connectyed-button-hover-light:hover {
  background-color: var(--connectyed-button-hover-light);
}

.hover\:text-connectyed-button-dark:hover {
  color: var(--connectyed-button-hover-dark); /* Ensures text is readable */
}

/* Specific Button Hover Fixes */
.bg-connectyed-button-light.text-connectyed-button-dark.hover\:bg-connectyed-button-hover-light.hover\:text-connectyed-button-light {
  transition: background-color 0.3s, color 0.3s;
}

button, .bg-connectyed-button-light, .bg-connectyed-button-pagination-light {
  /* Ensure all buttons have smooth transitions */
  transition: background-color 0.3s, color 0.3s;
}

/* Additional Button Hover Styles */
button:hover, .bg-connectyed-button-light:hover, .bg-connectyed-button-pagination-light:hover {
  /* Maintain text readability on hover */
  color: var(--connectyed-button-hover-dark);
}

/* Specific styles for Subscribe button in footer */
.footer__newsletter--form button:hover {
  color: var(--connectyed-button-hover-dark) !important;
}

/* Ensure all header buttons have readable text on hover */
a.bg-connectyed-button-light:hover, .router-link.bg-connectyed-button-light:hover {
  color: var(--connectyed-button-hover-dark) !important;
}

/* New Styles for Pricing Packages Link */
.pricing-link {
  font-size: 18px;  /* Increased from 16px */
  font-weight: 500;
  margin-left: 12px; /* Reduced from 16px to bring it closer to the button */
}

.pricing-link:hover {
  text-decoration: underline;
}

/* Container and Alignment */
.dating-tips-section {
  padding-top: 60px;
  padding-bottom: 60px;
  background-color: #f9f9f9;
}

.section__header h2 {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 8px;
}

.subtitle {
  font-size: 1rem;
  color: #666;
  margin-bottom: 24px;
}

.section__wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 80px;
}

/* Left Side Image Gallery */
.image-gallery {
  position: relative;
  display: flex;
  flex-direction: row;
  /*gap: 20px;*/
}

.gallery-image {
  width: 370px;
  height: 250px;
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.gallery-image:first-child {
  transform: rotate(-5deg);
}

.gallery-image:last-child:hover {
  transform: rotate(5deg);
}

/* Hover effect on images */
.gallery-image:hover {
  transform: scale(1.05);
}

/* Right Side Swiper Slider */
.tips-slider {
  max-width: 400px;
}

.tip-content {
  text-align: left;
}

.tip-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 8px;
}

.tip-description {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
}

/* Swiper Navigation Buttons */
.swiper-button-next,
.swiper-button-prev {
  /*display: none;*/
  color: #333;
  background: #f0f0f0;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease;
}

.swiper-button-next:hover,
.swiper-button-prev:hover {
  background: #ddd;
}

.swiper-pagination-bullet {
  background: #333;
}

/* Add styles for the login button */
.login-button-container {
  position: absolute;
  top: 32px;
  right: 32px;
  z-index: 1000;
}

.login-button {
  background-color: var(--connectyed-button-light);
  color: var(--connectyed-button-dark);
  padding: 10px 20px;
  border-radius: 9999px;
  text-decoration: none;
  transition: background-color 0.3s, color 0.3s;
  font-weight: 500;
}

.login-button:hover {
  background-color: var(--connectyed-button-hover-light);
  color: var(--connectyed-button-hover-dark);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .login-button-container {
    top: 16px;
    right: 16px;
  }

  .login-button {
    padding: 8px 16px;
    font-size: 14px;
  }

  .section__wrapper {
    flex-direction: column;
    gap: 40px;
  }

  .image-gallery {
    flex-direction: column;
    align-items: center;
  }

  .gallery-image {
    width: 100%;
    height: auto;
    max-width: 370px;
  }

  .tips-slider {
    max-width: 100%;
  }
}

/* Updated Styles for Matchmakers Carousel */
.mySwiper {
  min-height: 650px; /* Slightly larger than card height */
  width: 100%;
  padding: 20px 0;
}

.swiper-slide {
  height: auto; /* Let slides adjust to content */
  display: block; /* Remove flex to allow height adjustment */
}

/* Center alignment for few items */
.center-few {
  display: flex;
  justify-content: center;
  align-items: center;
}

.center-few .swiper {
  max-width: 900px;
}

.center-few .swiper-wrapper {
  justify-content: center;
}

/* Single item specific styles */
.single-slide {
  width: 100% !important;
  max-width: 400px !important;
  margin: 0 auto !important;
}

/* Hide navigation and pagination for single item */
.center-few .swiper:has(.single-slide) .swiper-button-next,
.center-few .swiper:has(.single-slide) .swiper-button-prev,
.center-few .swiper:has(.single-slide) .swiper-pagination {
  display: none !important;
}

/* Profile card styles */
.profile-card {
  width: 100%;
  max-width: 400px;
  min-width: 300px;
  min-height: 500px;
  margin: auto;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.profile-image {
  min-height: 250px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .mySwiper {
    min-height: 500px;
  }
  
  .profile-card {
    min-width: 280px;
    min-height: 450px;
  }
  
  .single-slide {
    max-width: 320px !important;
  }
}

/* Additional styles for better mobile display */
@media (max-width: 480px) {
  .profile-card {
    min-width: 260px;
  }
  
  .single-slide {
    max-width: 300px !important;
  }
  
  .mySwiper {
    padding: 10px 0;
  }
}

/* Additional styles (if any) can go here */

</style>
