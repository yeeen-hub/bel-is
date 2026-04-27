<template>
  <LandingLayout v-if="$route.path === '/'">
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <!-- ── Hero ─────────────────────────────────────────────────────────────── -->
    <section id="home" data-section="home"
      class="snap-start min-h-screen relative flex items-start pt-24 sm:pt-28 overflow-hidden">

      <!-- BACKGROUND IMAGE -->
      <img :src="heroBackgroundUrl" class="absolute inset-0 w-full h-full object-cover object-center -z-10 animate-pan"
        alt="Hero background" />

      <!-- DARK OVERLAY -->
      <div class="absolute inset-0 bg-black/50 -z-10"></div>

      <!-- MAIN CONTAINER -->
      <div
        class="w-full px-4 sm:px-8 lg:px-16 3xl:px-24 4xl:px-32 4xl:mt-20 flex flex-col lg:flex-row items-center lg:items-start justify-between gap-8">

        <!-- LEFT CONTENT -->
        <div class="text-center lg:text-left w-full">

          <!-- TAGLINE FIRST -->
          <p class="text-sm sm:text-lg md:text-xl lg:text-xl 3xl:text-3xl 4xl:text-4xl
        text-blue-500 font-kaushan italic leading-tight mb-3 lg:ml-4">
            {{ taglineFirst }}
          </p>

          <!-- TAGLINE REST -->
          <h2 class="text-base sm:text-xl md:text-2xl lg:text-3xl 3xl:text-6xl 4xl:text-7xl
        text-white font-heading leading-tight mb-1 lg:ml-4">
            {{ taglineRest }}
          </h2>

          <!-- BARANGAY -->
          <span class="block font-bold leading-none
        text-4xl sm:text-6xl md:text-7xl lg:text-[10rem] xl:text-[9rem] ml-2 3xl:text-[14rem] 4xl:text-[18rem]
        text-white relative"
            style="text-shadow: 1px 1px 0 #0a2a55, 2px 2px 0 #082244, 3px 3px 0 #061a33, 4px 4px 10px rgba(0,0,0,0.25);">
            {{ hero.barangay || 'Bel-is' }}
          </span>

          <!-- LOCATION -->
          <div class="flex items-center justify-center lg:justify-start gap-2 mt-2 lg:ml-4">

            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 3xl:w-7 3xl:h-7 4xl:w-8 4xl:h-8 flex-shrink-0 text-blue-500"
              xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="currentColor">
              <path
                d="M32,0C18.746,0,8,10.746,8,24c0,5.219,1.711,10.008,4.555,13.93l16,24C29.414,63.332,30.664,64,32,64s2.586-0.668,3.328-1.781l16-24C54.289,34.008,56,29.219,56,24C56,10.746,45.254,0,32,0z M32,32c-4.418,0-8-3.582-8-8s3.582-8,8-8s8,3.582,8,8S36.418,32,32,32z" />
            </svg>

            <h2 v-if="hero.mun_prov"
              class="text-sm sm:text-lg md:text-xl lg:text-3xl 3xl:text-4xl 4xl:text-5xl text-white font-heading leading-none text-center lg:text-left">
              {{ hero.mun_prov }}
            </h2>

          </div>

          <!-- CARDS -->
          <div class="flex flex-wrap gap-4 p-5 justify-center lg:justify-start items-stretch">

            <!-- CARD 1 -->
            <div class="nature-card flex items-center gap-4 w-full sm:w-[200px] lg:w-[220px] xl:w-[220px] 3xl:w-[320px] 4xl:w-[400px]
          px-3 py-3 rounded-[18px]
          border border-blue-400/40
          bg-gradient-to-br from-[#0d47a1]/60 via-[#1565c0]/45 to-[#1976d2]/30
          backdrop-blur-[14px]
          shadow-[0_8px_32px_rgba(13,71,161,0.3)] shadow-inner">

              <div class="shrink-0 flex items-center justify-center text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 3xl:w-8 3xl:h-8 4xl:w-9 4xl:h-14"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                  stroke-linejoin="round">
                  <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
                  <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" />
                </svg>
              </div>

              <div>
                <h3 class="text-white font-bold text-sm 3xl:text-base 4xl:text-3xl">
                  Nature Beauty
                </h3>
                <p class="text-white/80 text-xs 3xl:text-sm 4xl:text-2xl">
                  Pristine beaches, clear waters, and lush landscapes.
                </p>
              </div>

            </div>

            <!-- CARD 2 -->
            <div class="nature-card flex items-center gap-4 w-full sm:w-[200px] lg:w-[220px] xl:w-[220px] 3xl:w-[320px] 4xl:w-[400px]
          px-3 py-3 rounded-[18px]
          border border-blue-400/40
          bg-gradient-to-br from-[#0d47a1]/60 via-[#1565c0]/45 to-[#1976d2]/30
          backdrop-blur-[14px]">

              <div class="text-white">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 3xl:w-8 3xl:h-8 4xl:w-9 4xl:h-14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path
                    d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                  <circle cx="12" cy="13" r="3" />
                </svg>
              </div>

              <div>
                <h3 class="text-white font-bold text-sm 3xl:text-base 4xl:text-3xl">
                  Scenic Spots
                </h3>
                <p class="text-white/80 text-xs 3xl:text-sm 4xl:text-2xl">
                  Breathtaking views perfect for memories that last.
                </p>
              </div>

            </div>

            <!-- CARD 3 -->
            <div class="nature-card flex items-center gap-4 w-full sm:w-[200px] lg:w-[220px] xl:w-[220px] 3xl:w-[320px] 4xl:w-[400px]
          px-3 py-3 rounded-[18px]
          border border-blue-400/40
          bg-gradient-to-br from-[#0d47a1]/60 via-[#1565c0]/45 to-[#1976d2]/30
          backdrop-blur-[14px]">

              <div class="text-white">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 3xl:w-8 3xl:h-8 4xl:w-9 4xl:h-14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                  <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
              </div>

              <div>
                <h3 class="text-white font-bold text-sm 3xl:text-base 4xl:text-3xl">
                  Local Culture
                </h3>
                <p class="text-white/80 text-xs 3xl:text-sm 4xl:text-2xl">
                  Experience warm hospitality and rich local tradition.
                </p>
              </div>

            </div>

          </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="relative w-full max-w-[700px] 3xl:max-w-[900px] 4xl:max-w-[1200px] mx-auto text-center">

          <div class="relative p-4 sm:p-6">

            <div class="relative w-full">

              <!-- IMAGE -->
              <img src="/images/virtualmap.png" class="w-full h-auto object-contain 3xl:scale-110 4xl:scale-125 transition-transform duration-500 ease-out
              drop-shadow-xl
              hover:-translate-y-2 hover:scale-[1.02]
              animate-float" />

              <!-- CLICK AREA -->
              <div @click="$router.push('/VTHome')" class="absolute top-0 left-5 w-full h-full cursor-pointer"
                style="clip-path: polygon(90% 10%, 100% 75%, 0% 75%);">
              </div>

            </div>

          </div>

        </div>

      </div>
    </section>
   

    <!-- ── Attractions ──────────────────────────────────────────────────────── -->
    <section id="attractions" data-section="attractions" class="snap-start bg-white">

      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 sm:pt-20 lg:pt-24 pb-10">

        <!-- Title -->
        <h2 class="reveal text-2xl sm:text-3xl lg:text-4xl text-center font-semibold leading-tight">
          Discover places you're going to love
        </h2>

        <p class="reveal text-center text-gray-500 mt-2 text-sm sm:text-base max-w-2xl mx-auto">
          From cultural wonders to nature escapes, let what you love point you toward Bel-is' most amazing experiences.
        </p>

        <!-- Filters -->
        <div class="reveal flex justify-center mt-6">
          <div
            class="flex flex-wrap justify-center gap-2 sm:gap-4 p-2 rounded-lg bg-white border border-gray-100 shadow-sm">

            <a
              class="px-3 py-1 text-sm sm:text-base border border-gray-300 rounded-md text-gray-600 hover:bg-blue-600 hover:text-white transition cursor-pointer">
              All
            </a>

            <a
              class="px-3 py-1 text-sm sm:text-base border border-gray-300 rounded-md text-gray-600 hover:bg-blue-600 hover:text-white transition cursor-pointer">
              Recommended
            </a>

            <a
              class="px-3 py-1 text-sm sm:text-base border border-gray-300 rounded-md text-gray-600 hover:bg-blue-600 hover:text-white transition cursor-pointer">
              Popular
            </a>

          </div>
        </div>

        <!-- Empty -->
        <div v-if="props.attractions.length === 0" class="text-center py-16 text-gray-400 text-sm sm:text-base">
          No attractions available yet.
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-8 reveal-stagger">

          <div v-for="attraction in visibleAttractions" :key="attraction.id"
            class="relative rounded-xl overflow-hidden shadow-md group cursor-pointer h-56 sm:h-64 lg:h-72"
            @click="openAttractionModal(attraction)">

            <!-- Image -->
            <img :src="attraction.image_url || '/images/h-resort.jpg'"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              :alt="attraction.name" />

            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

            <!-- Text -->
            <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4">
              <h3 class="text-white font-bold text-base sm:text-lg leading-tight">
                {{ attraction.name }}
              </h3>

              <p class="text-white/80 text-xs sm:text-sm mt-1 line-clamp-2">
                {{ attraction.description }}
              </p>

              <span
                class="inline-block mt-2 text-xs text-white/70 border border-white/40 rounded-full px-3 py-0.5 hover:bg-white/20 transition">
                Read more →
              </span>
            </div>

          </div>
        </div>

        <!-- Modal -->
        <div v-if="selectedAttraction" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
          @click.self="selectedAttraction = null">

          <div
            class="relative rounded-2xl overflow-hidden shadow-2xl w-full max-w-md sm:max-w-lg h-[420px] sm:h-[480px]">

            <img :src="selectedAttraction.image_url || '/images/h-resort.jpg'"
              class="absolute inset-0 w-full h-full object-cover" />

            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10"></div>

            <button @click="selectedAttraction = null"
              class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/70 transition text-xl">
              &times;
            </button>

            <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6">
              <h3 class="text-white text-lg sm:text-xl font-bold">
                {{ selectedAttraction.name }}
              </h3>

              <p class="text-white/85 text-xs sm:text-sm mt-2">
                {{ selectedAttraction.description }}
              </p>
            </div>

          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">

          <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-600 rounded-lg hover:bg-blue-600 hover:text-white transition disabled:opacity-40">
            Back
          </button>

          <div class="flex flex-wrap justify-center gap-2">
            <button v-for="p in pageNumbers" :key="p" @click="currentPage = p"
              class="px-3 py-1 text-sm border rounded transition" :class="currentPage === p
                ? 'bg-gray-900 text-white border-gray-900'
                : 'border-gray-300 text-gray-600 hover:bg-blue-600 hover:text-white'">
              {{ p }}
            </button>
          </div>

          <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages"
            class="px-4 py-2 text-sm border border-gray-300 text-gray-600 rounded-lg hover:bg-blue-600 hover:text-white transition disabled:opacity-40">
            Next
          </button>

        </div>

      </div>
    </section>

    <TourismCircuitMap />

    <!-- ── About ────────────────────────────────────────────────────────────── -->
    <!-- overflow-y-auto moved to inner div -->
    <section id="about" data-section="about"
      class="snap-start min-h-screen w-full relative bg-cover bg-center bg-no-repeat reveal"
      style="background-image: url('/images/abstractbg.jpg')">

      <!-- overlay -->
      <div class="absolute inset-0 bg-black/20 backdrop-blur-sm"></div>

      <!-- content -->
      <div
        class="relative z-10 flex flex-col items-center justify-center px-4 sm:px-8 text-center py-16 sm:py-20 lg:py-24">

        <p class="reveal text-xs sm:text-sm uppercase tracking-wider mb-2 text-white/90">About Us</p>

        <h2 class="reveal font-bold text-2xl sm:text-4xl md:text-5xl leading-tight text-white">
          What is Bel-is?
        </h2>

        <!-- GRID -->
        <div class="mt-8 w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 items-center reveal-stagger">

          <!-- IMAGE -->
          <div class="w-full reveal">
            <img src="/images/h-resort.jpg" alt="Bel-is Resort"
              class="w-full h-52 sm:h-64 md:h-80 object-cover rounded-lg shadow-lg" />
          </div>

          <!-- TEXT -->
          <div class="flex flex-col justify-center text-left space-y-6 reveal-stagger">

            <div class="flex gap-3 sm:gap-4 items-start">
              <p class="text-base sm:text-lg font-bold text-gray-300 flex-shrink-0">01</p>
              <div>
                <p class="font-semibold text-lg sm:text-xl text-white reveal">{{ about.feature1_title }}</p>
                <p class="text-xs sm:text-sm mt-2 text-white/80 reveal">
                  {{ about.feature1_desc }}
                </p>
              </div>
            </div>

            <div class="flex gap-3 sm:gap-4 items-start">
              <p class="text-base sm:text-lg font-bold text-gray-300 flex-shrink-0 ">02</p>
              <div>
                <p class="font-semibold text-lg sm:text-xl text-white reveal">{{ about.feature2_title }}</p>
                <p class="text-xs sm:text-sm mt-2 text-white/80 reveal">
                  {{ about.feature2_desc }}
                </p>
              </div>
            </div>

            <div class="flex gap-3 sm:gap-4 items-start">
              <p class="text-base sm:text-lg font-bold text-gray-300 flex-shrink-0">03</p>
              <div>
                <p class="font-semibold text-lg sm:text-xl text-white reveal">{{ about.feature3_title }}</p>
                <p class="text-xs sm:text-sm mt-2 text-white/80 reveal">
                  {{ about.feature3_desc }}
                </p>
              </div>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- ── Pre-Registration ─────────────────────────────────────────────────── -->
    <!-- overflow-y-auto removed — content fits within h-screen -->
    <section id="pre-register" data-section="pre-register"
      class="snap-start h-screen bg-white flex items-center reveal">
      <div class="max-w-6xl mx-auto px-6 py-8 w-full">
        <div class="bg-gray-900 rounded-2xl overflow-hidden">
          <div class="grid grid-cols-1 md:grid-cols-2 items-center reveal-stagger">

            <div class="px-10 py-12 reveal">
              <span
                class="inline-block bg-white/10 text-white text-xs font-semibold px-3 py-1 rounded-full mb-4 tracking-wide uppercase">
                Skip the Queue
              </span>
              <h2 class="text-3xl md:text-4xl font-bold text-white leading-tight mb-4">
                Pre-register before <br class="hidden md:block" /> you arrive
              </h2>
              <p class="text-gray-400 text-sm leading-relaxed mb-8 max-w-sm">
                Fill out your visitor information online and get a reference code.
                Show it at the Bel-is Tourism Hub checkpoint for a faster entry experience.
              </p>

              <div class="space-y-3 mb-8 reveal-stagger">
                <div class="flex items-center gap-3">
                  <span
                    class="w-6 h-6 bg-white text-gray-900 text-xs font-bold rounded-full flex items-center justify-center shrink-0">1</span>
                  <p class="text-gray-300 text-sm">Fill out the short pre-registration form</p>
                </div>
                <div class="flex items-center gap-3">
                  <span
                    class="w-6 h-6 bg-white text-gray-900 text-xs font-bold rounded-full flex items-center justify-center shrink-0">2</span>
                  <p class="text-gray-300 text-sm">Get your 6-digit reference code instantly</p>
                </div>
                <div class="flex items-center gap-3">
                  <span
                    class="w-6 h-6 bg-white text-gray-900 text-xs font-bold rounded-full flex items-center justify-center shrink-0">3</span>
                  <p class="text-gray-300 text-sm">Show it at the checkpoint — pay &amp; go</p>
                </div>
              </div>

              <a href="/pre-register"
                class="reveal inline-flex items-center gap-2 bg-white text-gray-900 font-bold py-3 px-7 rounded-xl hover:bg-gray-100 transition text-sm">
                Pre-Register Now
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
            </div>

            <div class="hidden md:flex items-center justify-center px-10 py-12 reveal-stagger">
              <div class="bg-white rounded-2xl shadow-2xl p-6 w-64">
                <div class="text-center mb-4">
                  <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z" />
                    </svg>
                  </div>
                  <p class="text-xs text-gray-500 font-medium">You're Pre-Registered!</p>
                </div>
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl px-4 py-4 text-center mb-4">
                  <p class="text-xs text-gray-400 mb-1">Reference Code</p>
                  <p class="text-xl font-mono font-bold text-gray-900 tracking-widest">BEL-482951</p>
                </div>
                <div class="flex justify-center">
                  <div class="w-24 h-24 bg-gray-100 rounded-lg grid grid-cols-3 gap-0.5 p-1.5">
                    <div v-for="n in 9" :key="n" class="rounded-sm"
                      :class="[1, 2, 4, 6, 8, 9].includes(n) ? 'bg-gray-800' : 'bg-gray-200'">
                    </div>
                  </div>
                </div>
                <p class="text-center text-xs text-gray-400 mt-3">Screenshot &amp; show at checkpoint</p>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>

    <!-- ── Contact Form ─────────────────────────────────────────────────────── -->
    <!-- overflow-y-auto removed — content fits within h-screen -->
    <section id="contact" data-section="contact" class="snap-start bg-white flex flex-col reveal">

      <div
        class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-12 w-full flex-1 flex flex-col justify-center reveal-stagger">

        <p class="text-sm text-gray-500 mb-3">Get Started</p>

        <!-- HEADER -->
        <div class="flex flex-col md:grid md:grid-cols-2 items-start gap-6 mb-8 reveal">

          <!-- TITLE -->
          <div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-tight reveal">
              Get in touch with us. <br />
              We're here to assist you.
            </h2>
          </div>

          <!-- SOCIALS — only shown if URL is set by admin -->
          <div
            class="flex flex-row md:flex-col justify-start md:justify-end items-center md:items-end gap-4 md:gap-5 reveal">
            <a v-if="contact.facebook_url" :href="contact.facebook_url" target="_blank" rel="noopener"
              class="reveal w-10 h-10 flex items-center justify-center border border-gray-300 rounded-full hover:bg-blue-600 hover:text-white transition">
              <svg fill="currentColor" width="22px" height="22px" viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M21.95 5.005l-3.306-.004c-3.206 0-5.277 2.124-5.277 5.415v2.495H10.05v4.515h3.317l-.004 9.575h4.641l.004-9.575h3.806l-.003-4.514h-3.803v-2.117c0-1.018.241-1.533 1.566-1.533l2.366-.001.01-4.256z" />
              </svg>
            </a>
            <a v-if="contact.instagram_url" :href="contact.instagram_url" target="_blank" rel="noopener"
              class="reveal w-10 h-10 flex items-center justify-center border border-gray-300 rounded-full hover:bg-pink-500 hover:text-white transition">
              <svg width="22px" height="22px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18ZM12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z"
                  fill="currentColor" />
                <path
                  d="M18 5C17.4477 5 17 5.44772 17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5Z"
                  fill="currentColor" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M1.65396 4.27606C1 5.55953 1 7.23969 1 10.6V13.4C1 16.7603 1 18.4405 1.65396 19.7239C2.2292 20.8529 3.14708 21.7708 4.27606 22.346C5.55953 23 7.23969 23 10.6 23H13.4C16.7603 23 18.4405 23 19.7239 22.346C20.8529 21.7708 21.7708 20.8529 22.346 19.7239C23 18.4405 23 16.7603 23 13.4V10.6C23 7.23969 23 5.55953 22.346 4.27606C21.7708 3.14708 20.8529 2.2292 19.7239 1.65396C18.4405 1 16.7603 1 13.4 1H10.6C7.23969 1 5.55953 1 4.27606 1.65396C3.14708 2.2292 2.2292 3.14708 1.65396 4.27606ZM13.4 3H10.6C8.88684 3 7.72225 3.00156 6.82208 3.0751C5.94524 3.14674 5.49684 3.27659 5.18404 3.43597C4.43139 3.81947 3.81947 4.43139 3.43597 5.18404C3.27659 5.49684 3.14674 5.94524 3.0751 6.82208C3.00156 7.72225 3 8.88684 3 10.6V13.4C3 15.1132 3.00156 16.2777 3.0751 17.1779C3.14674 18.0548 3.27659 18.5032 3.43597 18.816C3.81947 19.5686 4.43139 20.1805 5.18404 20.564C5.49684 20.7234 5.94524 20.8533 6.82208 20.9249C7.72225 20.9984 8.88684 21 10.6 21H13.4C15.1132 21 16.2777 20.9984 17.1779 20.9249C18.0548 20.8533 18.5032 20.7234 18.816 20.564C19.5686 20.1805 20.1805 19.5686 20.564 18.816C20.7234 18.5032 20.8533 18.0548 20.9249 17.1779C20.9984 16.2777 21 15.1132 21 13.4V10.6C21 8.88684 20.9984 7.72225 20.9249 6.82208C20.8533 5.94524 20.7234 5.49684 20.564 5.18404C20.1805 4.43139 19.5686 3.81947 18.816 3.43597C18.5032 3.27659 18.0548 3.14674 17.1779 3.0751C16.2777 3.00156 15.1132 3 13.4 3Z"
                  fill="currentColor" />
              </svg>
            </a>
            <a v-if="contact.twitter_url" :href="contact.twitter_url" target="_blank" rel="noopener"
              class="reveal w-10 h-10 flex items-center justify-center border border-gray-300 rounded-full hover:bg-black hover:text-white transition">
              <svg width="18px" height="18px" viewBox="0 0 24 24" fill="currentColor"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.745l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Success banner -->
        <div v-if="messageSent"
          class="mt-3 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm flex items-center justify-between">
          <span>Your message has been sent! We'll get back to you soon.</span>
          <button @click="messageSent = false"
            class="ml-4 text-green-600 font-bold text-lg leading-none">&times;</button>
        </div>

        <!-- FORM GRID -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <div class="reveal">
            <label class="text-sm text-gray-600">Your Name <span class="text-red-500">*</span></label>
            <input v-model="contactForm.name" type="text"
              :class="['w-full border-0 border-b py-2 bg-transparent outline-none focus:ring-0', contactErrors.name ? 'border-red-400' : 'border-gray-400']">
            <p v-if="contactErrors.name" class="text-red-500 text-xs mt-1">{{ contactErrors.name }}</p>
          </div>
          <div class="reveal">
            <label class="text-sm text-gray-600">Email Address <span class="text-red-500">*</span></label>
            <input v-model="contactForm.email" type="email"
              :class="['w-full border-0 border-b py-2 bg-transparent outline-none focus:ring-0', contactErrors.email ? 'border-red-400' : 'border-gray-400']">
            <p v-if="contactErrors.email" class="text-red-500 text-xs mt-1">{{ contactErrors.email }}</p>
          </div>
          <div class="reveal">
            <label class="text-sm text-gray-600">Phone (optional)</label>
            <input v-model="contactForm.phone" type="text"
              class="w-full border-0 border-b border-gray-400 focus:ring-0 outline-none py-2 bg-transparent">
          </div>
        </div>

        <!-- MESSAGE -->
        <div class="mt-5">
          <label class="text-sm text-gray-600 reveal">Message <span class="text-red-500">*</span></label>
          <textarea v-model="contactForm.message" rows="2"
            :class="['w-full border-0 border-b py-2 bg-transparent resize-none outline-none focus:ring-0 reveal', contactErrors.message ? 'border-red-400' : 'border-gray-400']"></textarea>
          <p v-if="contactErrors.message" class="text-red-500 text-xs mt-1">{{ contactErrors.message }}</p>
        </div>

        <!-- BUTTON -->
        <div class="mt-5">
          <button @click.prevent="submitContactForm" :disabled="contactSubmitting"
            class="reveal flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition disabled:opacity-50">
            {{ contactSubmitting ? 'Sending…' : 'Leave us a Message' }}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M5 12h14M13 5l7 7-7 7" />
            </svg>
          </button>
        </div>

      </div>

    </section>

  </LandingLayout>

  <router-view v-else></router-view>
</template>

<style scoped>
@keyframes pan {
  0% {
    transform: scale(1.1) translateX(0%);
  }

  50% {
    transform: scale(1.1) translateX(-3%);
  }

  100% {
    transform: scale(1.1) translateX(0%);
  }
}

.animate-pan {
  animation: pan 20s ease-in-out infinite;
}
</style>

<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue'
import TourismCircuitMap from '@/Components/TourismCircuitMap.vue'
import { useRoute } from 'vue-router'
import { computed, ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

onMounted(() => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible')
        observer.unobserve(entry.target) // animate once only
      }
    })
  }, {
    threshold: 0.15  // trigger when 15% of element is visible
  })

  document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => {
    observer.observe(el)
  })
})

const route = useRoute()

const props = defineProps({
  hero: {
    type: Object,
    default: () => ({
      tagline: 'Discover the beauty of',
      barangay: 'Bel-is',
      mun_prov: 'Buruanga, Aklan',
      sub: 'Explore nature, culture, and hidden destinations',
      background_image_url: null,
    }),
  },
  contact: {
    type: Object,
    default: () => ({
      email: 'help@info.com',
      phone: '+63 123 456 7890',
      email_hours: 'Monday – Friday 6 am to 8 pm',
      phone_hours: 'Monday – Friday 6 am to 8 pm',
      facebook_url: null,
      instagram_url: null,
      twitter_url: null,
    }),
  },
  attractions: {
    type: Array,
    default: () => [],
  },
  about: {
    type: Object,
    default: () => ({
      feature1_title: '',
      feature1_desc: '',
      feature2_title: '',
      feature2_desc: '',
      feature3_title: '',
      feature3_desc: '',
    }),
  },
})

const taglineParts = computed(() =>
  (props.hero.tagline || 'Discover the|beauty of').split('|')
)

const taglineFirst = computed(() => taglineParts.value[0] || '')
const taglineRest = computed(() => taglineParts.value[1] || '')

const heroBackgroundUrl = computed(() =>
  props.hero.background_image_url ?? '/images/bg3.jpeg'
)


// Attractions pagination
const PER_PAGE = 6
const currentPage = ref(1)
const totalPages = computed(() => Math.max(1, Math.ceil(props.attractions.length / PER_PAGE)))
const visibleAttractions = computed(() => {
  const start = (currentPage.value - 1) * PER_PAGE
  return props.attractions.slice(start, start + PER_PAGE)
})
const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

// Read more modal
const selectedAttraction = ref(null)
function openAttractionModal(attraction) {
  selectedAttraction.value = attraction
}

// ── Contact form ──────────────────────────────────────────────────────────────
const contactForm = ref({ name: '', email: '', phone: '', message: '' })
const contactErrors = ref({})
const contactSubmitting = ref(false)
const messageSent = ref(false)

function submitContactForm() {
  contactErrors.value = {}
  contactSubmitting.value = true

  router.post('/contact/send', contactForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      messageSent.value = true
      contactForm.value = { name: '', email: '', phone: '', message: '' }
      contactSubmitting.value = false
      setTimeout(() => { messageSent.value = false }, 6000)
    },
    onError: (errors) => {
      contactErrors.value = errors
      contactSubmitting.value = false
    },
  })
}
</script>