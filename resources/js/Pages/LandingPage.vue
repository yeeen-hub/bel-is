<template>
  <LandingLayout v-if="$route.path === '/'">

    <!-- ── Hero ─────────────────────────────────────────────────────────────── -->
    <section
      id="home"
      data-section="home"
      class="snap-start h-screen relative flex items-center"
    >
      <img
        :src="heroBackgroundUrl"
        class="absolute inset-0 w-full h-full object-cover -z-10 animate-pan"
        alt="Hero background"
      />

      <div class="w-full px-16 flex items-center justify-between">
        <div>
          <h2 class="text-6xl text-white font-heading leading-tight">
            {{ hero.tagline || 'Discover the beauty of' }}
          </h2>
          <span class="text-white font-bold" style="font-size: clamp(5rem, 12vw, 9rem); line-height: 1;">
            {{ hero.barangay || 'Bel-is' }}
          </span>
          <p v-if="hero.mun_prov" class="text-white text-lg mt-2 opacity-80">
            {{ hero.mun_prov }}
          </p>
          <p class="border-2 border-white text-white p-2 text-xl rounded-lg inline-flex mt-6">
            {{ hero.sub || 'Explore nature, culture, and hidden destinations' }}
          </p>
        </div>

        <img
          @click="$router.push('/VTHome')"
          src="/images/virtualmap2.png"
          alt="Virtual Map"
          class="w-1/4 max-w-[450px] animate-float flex-shrink-0 cursor-pointer hover:scale-105 transition-transform"
        />
      </div>
    </section>

    <!-- ── Attractions ──────────────────────────────────────────────────────── -->
    <!-- overflow-y-auto moved to inner div so the section itself is not a scroll container -->
    <section
      id="attractions"
      data-section="attractions"
      class="snap-start h-screen bg-white"
    >
      <div class="max-w-6xl mx-auto px-6 pt-24 pb-8 h-full overflow-y-auto">

        <h2 class="text-3xl text-center font-semibold">
          Discover places you're going to love
        </h2>
        <p class="text-center text-gray-500 mt-2">
          From cultural wonders to nature escapes, let what you love point you toward Bel-is' most amazing experiences.
        </p>

        <div class="flex justify-center mt-6">
          <div class="inline-flex gap-4 p-2 rounded-lg bg-white border border-gray-100 shadow-sm">
            <a class="px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-blue-600 hover:text-white cursor-pointer transition">All</a>
            <a class="px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-blue-600 hover:text-white cursor-pointer transition">Recommended</a>
            <a class="px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-blue-600 hover:text-white cursor-pointer transition">Popular</a>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="props.attractions.length === 0" class="text-center py-16 text-gray-400">
          No attractions available yet.
        </div>

        <!-- Dynamic cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
          <div
            v-for="attraction in visibleAttractions"
            :key="attraction.id"
            class="relative rounded-xl overflow-hidden shadow-md group cursor-pointer h-64"
            @click="openAttractionModal(attraction)">

            <!-- Full image -->
            <img
              :src="attraction.image_url || '/images/h-resort.jpg'"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              :alt="attraction.name"
            />

            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

            <!-- Text at bottom -->
            <div class="absolute bottom-0 left-0 right-0 p-4">
              <h3 class="text-white font-bold text-lg leading-tight">{{ attraction.name }}</h3>
              <p class="text-white/80 text-sm mt-1 line-clamp-2">{{ attraction.description }}</p>
              <span class="inline-block mt-2 text-xs text-white/70 border border-white/40 rounded-full px-3 py-0.5 hover:bg-white/20 transition">
                Read more →
              </span>
            </div>
          </div>
        </div>

        <!-- Read More Modal — full image with overlay text -->
        <div v-if="selectedAttraction"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
          @click.self="selectedAttraction = null">
          <div class="relative rounded-2xl overflow-hidden shadow-2xl max-w-lg w-full" style="height: 480px;">

            <!-- Full image -->
            <img
              :src="selectedAttraction.image_url || '/images/h-resort.jpg'"
              class="absolute inset-0 w-full h-full object-cover"
              :alt="selectedAttraction.name"
            />

            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10"></div>

            <!-- Close button -->
            <button @click="selectedAttraction = null"
              class="absolute top-3 right-3 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/40 text-white hover:bg-black/70 transition text-xl font-bold leading-none">
              &times;
            </button>

            <!-- Text over overlay at bottom -->
            <div class="absolute bottom-0 left-0 right-0 p-6">
              <h3 class="text-white text-xl font-bold leading-tight">{{ selectedAttraction.name }}</h3>
              <p class="text-white/85 text-sm mt-3 leading-relaxed">{{ selectedAttraction.description }}</p>
            </div>
          </div>
        </div>

        <!-- Pagination — only shown when there are more than 6 attractions -->
        <div v-if="totalPages > 1" class="flex items-center justify-between mt-6">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-blue-600 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed">
            Back Page
          </button>

          <div class="flex gap-2">
            <button
              v-for="p in pageNumbers"
              :key="p"
              @click="currentPage = p"
              :class="[
                'px-3 py-1 border rounded transition',
                currentPage === p
                  ? 'bg-gray-900 text-white border-gray-900'
                  : 'border-gray-300 text-gray-600 hover:bg-blue-600 hover:text-white'
              ]">
              {{ p }}
            </button>
          </div>

          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 border border-gray-300 text-gray-600 rounded hover:bg-blue-600 hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed">
            Next Page
          </button>
        </div>

      </div>
    </section>

    <!-- ── Map ─────────────────────────────────────────────────────────────── -->
    <!-- overflow-hidden is correct here — keep as-is -->
    <section
      id="map"
      data-section="map"
      class="snap-start h-screen overflow-hidden bg-white"
    >
      <div class="max-w-6xl mx-auto px-6 pt-24 pb-6 h-full flex flex-col">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-1 min-h-0">

          <!-- Location list -->
          <div class="overflow-y-auto pr-2 flex flex-col">
            <h2 class="text-2xl font-bold mb-4">Where do you want to go?</h2>

            <div class="flex items-center bg-gray-100 rounded-lg px-3 py-2 mb-4">
              <svg class="mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 64 64">
                <path fill="#4f4f4f" d="M32,0C18.746,0,8,10.746,8,24c0,5.219,1.711,10.008,4.555,13.93c0.051,0.094,0.059,0.199,0.117,0.289l16,24C29.414,63.332,30.664,64,32,64s2.586-0.668,3.328-1.781l16-24c0.059-0.09,0.066-0.195,0.117-0.289C54.289,34.008,56,29.219,56,24C56,10.746,45.254,0,32,0z M32,32c-4.418,0-8-3.582-8-8s3.582-8,8-8s8,3.582,8,8S36.418,32,32,32z"/>
              </svg>
              <input type="text" placeholder="Pick a location to explore..."
                class="flex-1 outline-none border-none focus:ring-0 bg-gray-100 text-sm" />
              <div class="p-2 bg-blue-700 rounded-full flex-shrink-0">
                <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <g clip-path="url(#clip0_15_152)">
                    <circle cx="10.5" cy="10.5" r="6.5" stroke="white" stroke-linejoin="round"/>
                    <path d="M19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L19.6464 20.3536ZM20.3536 19.6464L15.3536 14.6464L14.6464 15.3536L19.6464 20.3536L20.3536 19.6464Z" fill="white"/>
                  </g>
                  <defs><clipPath id="clip0_15_152"><rect width="24" height="24" fill="white"/></clipPath></defs>
                </svg>
              </div>
            </div>

            <div class="flex gap-3 mb-5">
              <button class="px-3 py-1 border rounded-full text-sm hover:bg-blue-600 hover:text-white transition">Top Rated</button>
              <button class="px-3 py-1 border rounded-full text-sm hover:bg-blue-600 hover:text-white transition">Nearest</button>
              <button class="px-3 py-1 border rounded-full text-sm hover:bg-blue-600 hover:text-white transition">Recommended</button>
            </div>

            <div class="grid grid-cols-1 gap-4">
              <div v-for="n in 2" :key="n" class="flex gap-4 border rounded-lg p-3">
                <img src="/images/h-resort.jpg" class="w-28 h-24 object-cover rounded-md flex-shrink-0" alt="Resort">
                <div class="flex-1">
                  <h3 class="font-semibold text-base">Bel-is Resort</h3>
                  <div class="flex items-center gap-1 mt-1">
                    <svg fill="#0036d6" width="12px" height="12px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                      <path d="M16.114-0.011c-6.559 0-12.114 5.587-12.114 12.204 0 6.93 6.439 14.017 10.77 18.998 0.017 0.020 0.717 0.797 1.579 0.797h0.076c0.863 0 1.558-0.777 1.575-0.797 4.064-4.672 10-12.377 10-18.998 0-6.618-4.333-12.204-11.886-12.204zM16.515 29.849c-0.035 0.035-0.086 0.074-0.131 0.107-0.046-0.032-0.096-0.072-0.133-0.107l-0.523-0.602c-4.106-4.71-9.729-11.161-9.729-17.055 0-5.532 4.632-10.205 10.114-10.205 6.829 0 9.886 5.125 9.886 10.205 0 4.474-3.192 10.416-9.485 17.657zM16.035 6.044c-3.313 0-6 2.686-6 6s2.687 6 6 6 6-2.687 6-6-2.686-6-6-6zM16.035 16.044c-2.206 0-4.046-1.838-4.046-4.044s1.794-4 4-4c2.207 0 4 1.794 4 4 0.001 2.206-1.747 4.044-3.954 4.044z"/>
                    </svg>
                    <p class="text-xs text-gray-500">Buruanga, Aklan</p>
                  </div>
                  <div class="flex items-center gap-1 text-sm mt-1">
                    <p class="font-medium">4.3</p>
                    <svg fill="#ffd500" width="12px" height="12px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" stroke="#ffd500">
                      <path d="M28.61,11.67H20l-2.66-8.2a1.39,1.39,0,0,0-2.64,0L12,11.67H3.39a1.39,1.39,0,0,0-.82,2.51l7,5.07L6.89,27.46a1.39,1.39,0,0,0,1.32,1.82A1.43,1.43,0,0,0,9,29l7-5.07L23,29a1.43,1.43,0,0,0,.81.27,1.39,1.39,0,0,0,1.32-1.82l-2.66-8.21,7-5.07A1.39,1.39,0,0,0,28.61,11.67Z"/>
                    </svg>
                    <span class="text-xs text-gray-500">(59)</span>
                    <span class="w-1 h-1 bg-gray-400 rounded-full inline-block mx-1"></span>
                    <span class="text-xs text-gray-500">2 min</span>
                  </div>
                  <div class="flex items-center mt-1 gap-1 text-xs">
                    <span class="text-green-600 font-medium">Open</span>
                    <span class="text-gray-500">· Closes 21:00</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Map iframe -->
          <div class="h-full">
            <iframe
              src="https://www.google.com/maps?q=Bel-is,Buruanga,Aklan&output=embed"
              class="w-full h-full border-0 rounded-lg"
              loading="lazy">
            </iframe>
          </div>

        </div>
      </div>
    </section>

    <!-- ── About ────────────────────────────────────────────────────────────── -->
    <!-- overflow-y-auto moved to inner div -->
    <section
      id="about"
      data-section="about"
      class="snap-start h-screen w-full relative bg-cover bg-center bg-no-repeat"
      style="background-image: url('/images/abstractbg.jpg')"
    >
      <div class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>
      <div class="relative z-10 flex flex-col items-center justify-center px-8 text-center py-24 h-full overflow-y-auto">

        <p class="text-sm sm:text-base uppercase tracking-wider mb-2">About Us</p>
        <h2 class="font-bold text-3xl sm:text-4xl md:text-5xl leading-tight">
          What is Bel-is?
        </h2>

        <div class="mt-8 w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
          <div class="w-full">
            <img src="/images/h-resort.jpg" alt="Bel-is Resort"
              class="w-full h-64 md:h-80 object-cover rounded-lg shadow-lg">
          </div>
          <div class="flex flex-col justify-center text-left space-y-6">
            <div class="flex gap-4 items-start">
              <p class="text-lg font-bold text-gray-500 flex-shrink-0">01</p>
              <div>
                <p class="font-semibold text-xl">Our History</p>
                <p class="text-sm mt-2 text-gray-800">Experience the beautiful resorts of Bel-is with breathtaking views, serene ambiance, and world-class amenities. Perfect for a relaxing getaway.</p>
              </div>
            </div>
            <div class="flex gap-4 items-start">
              <p class="text-lg font-bold text-gray-500 flex-shrink-0">02</p>
              <div>
                <p class="font-semibold text-xl">Culture &amp; Traditions</p>
                <p class="text-sm mt-2 text-gray-800">Immerse yourself in Bel-is' local traditions, festivals, and culinary delights. Connect with the community and make unforgettable memories.</p>
              </div>
            </div>
            <div class="flex gap-4 items-start">
              <p class="text-lg font-bold text-gray-500 flex-shrink-0">03</p>
              <div>
                <p class="font-semibold text-xl">Nature &amp; Environment</p>
                <p class="text-sm mt-2 text-gray-800">Discover pristine beaches, lush landscapes, and the rich biodiversity that makes Bel-is a must-visit destination in Aklan.</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ── Pre-Registration ─────────────────────────────────────────────────── -->
    <!-- overflow-y-auto removed — content fits within h-screen -->
    <section
      id="pre-register"
      data-section="pre-register"
      class="snap-start h-screen bg-white flex items-center"
    >
      <div class="max-w-6xl mx-auto px-6 py-8 w-full">
        <div class="bg-gray-900 rounded-2xl overflow-hidden">
          <div class="grid grid-cols-1 md:grid-cols-2 items-center">

            <div class="px-10 py-12">
              <span class="inline-block bg-white/10 text-white text-xs font-semibold px-3 py-1 rounded-full mb-4 tracking-wide uppercase">
                Skip the Queue
              </span>
              <h2 class="text-3xl md:text-4xl font-bold text-white leading-tight mb-4">
                Pre-register before <br class="hidden md:block" /> you arrive
              </h2>
              <p class="text-gray-400 text-sm leading-relaxed mb-8 max-w-sm">
                Fill out your visitor information online and get a reference code.
                Show it at the Bel-is Tourism Hub checkpoint for a faster entry experience.
              </p>

              <div class="space-y-3 mb-8">
                <div class="flex items-center gap-3">
                  <span class="w-6 h-6 bg-white text-gray-900 text-xs font-bold rounded-full flex items-center justify-center shrink-0">1</span>
                  <p class="text-gray-300 text-sm">Fill out the short pre-registration form</p>
                </div>
                <div class="flex items-center gap-3">
                  <span class="w-6 h-6 bg-white text-gray-900 text-xs font-bold rounded-full flex items-center justify-center shrink-0">2</span>
                  <p class="text-gray-300 text-sm">Get your 6-digit reference code instantly</p>
                </div>
                <div class="flex items-center gap-3">
                  <span class="w-6 h-6 bg-white text-gray-900 text-xs font-bold rounded-full flex items-center justify-center shrink-0">3</span>
                  <p class="text-gray-300 text-sm">Show it at the checkpoint — pay &amp; go</p>
                </div>
              </div>

              <a href="/pre-register"
                class="inline-flex items-center gap-2 bg-white text-gray-900 font-bold py-3 px-7 rounded-xl hover:bg-gray-100 transition text-sm">
                Pre-Register Now
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
            </div>

            <div class="hidden md:flex items-center justify-center px-10 py-12">
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
                    <div v-for="n in 9" :key="n"
                      class="rounded-sm"
                      :class="[1,2,4,6,8,9].includes(n) ? 'bg-gray-800' : 'bg-gray-200'">
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
    <section
      id="contact"
      data-section="contact"
      class="snap-start h-screen bg-white flex items-center"
    >
      <div class="max-w-6xl mx-auto px-6 py-8 w-full">
        <p class="text-sm text-gray-500">Get Started</p>

        <div class="grid grid-cols-2 items-start mt-1">
          <div>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold leading-tight">
              Get in touch with us. <br />
              We're here to assist you.
            </h2>
          </div>
          <div class="flex flex-col items-end gap-5 pt-1">
            <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-full hover:bg-blue-600 hover:text-white transition">
              <svg fill="#000000" width="22px" height="22px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                <path d="M21.95 5.005l-3.306-.004c-3.206 0-5.277 2.124-5.277 5.415v2.495H10.05v4.515h3.317l-.004 9.575h4.641l.004-9.575h3.806l-.003-4.514h-3.803v-2.117c0-1.018.241-1.533 1.566-1.533l2.366-.001.01-4.256z"/>
              </svg>
            </a>
            <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-full hover:bg-pink-500 hover:text-white transition">
              <svg width="22px" height="22px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18ZM12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" fill="#0F0F0F"/>
                <path d="M18 5C17.4477 5 17 5.44772 17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5Z" fill="#0F0F0F"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.65396 4.27606C1 5.55953 1 7.23969 1 10.6V13.4C1 16.7603 1 18.4405 1.65396 19.7239C2.2292 20.8529 3.14708 21.7708 4.27606 22.346C5.55953 23 7.23969 23 10.6 23H13.4C16.7603 23 18.4405 23 19.7239 22.346C20.8529 21.7708 21.7708 20.8529 22.346 19.7239C23 18.4405 23 16.7603 23 13.4V10.6C23 7.23969 23 5.55953 22.346 4.27606C21.7708 3.14708 20.8529 2.2292 19.7239 1.65396C18.4405 1 16.7603 1 13.4 1H10.6C7.23969 1 5.55953 1 4.27606 1.65396C3.14708 2.2292 2.2292 3.14708 1.65396 4.27606ZM13.4 3H10.6C8.88684 3 7.72225 3.00156 6.82208 3.0751C5.94524 3.14674 5.49684 3.27659 5.18404 3.43597C4.43139 3.81947 3.81947 4.43139 3.43597 5.18404C3.27659 5.49684 3.14674 5.94524 3.0751 6.82208C3.00156 7.72225 3 8.88684 3 10.6V13.4C3 15.1132 3.00156 16.2777 3.0751 17.1779C3.14674 18.0548 3.27659 18.5032 3.43597 18.816C3.81947 19.5686 4.43139 20.1805 5.18404 20.564C5.49684 20.7234 5.94524 20.8533 6.82208 20.9249C7.72225 20.9984 8.88684 21 10.6 21H13.4C15.1132 21 16.2777 20.9984 17.1779 20.9249C18.0548 20.8533 18.5032 20.7234 18.816 20.564C19.5686 20.1805 20.1805 19.5686 20.564 18.816C20.7234 18.5032 20.8533 18.0548 20.9249 17.1779C20.9984 16.2777 21 15.1132 21 13.4V10.6C21 8.88684 20.9984 7.72225 20.9249 6.82208C20.8533 5.94524 20.7234 5.49684 20.564 5.18404C20.1805 4.43139 19.5686 3.81947 18.816 3.43597C18.5032 3.27659 18.0548 3.14674 17.1779 3.0751C16.2777 3.00156 15.1132 3 13.4 3Z" fill="#0F0F0F"/>
              </svg>
            </a>
            <a href="#" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-full hover:bg-black hover:text-white transition">
              <svg width="18px" height="18px" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.745l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
            </a>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-1">
          <div>
            <label class="text-sm text-gray-600">Your Name</label>
            <input type="text" class="w-full border-0 border-b border-gray-400 focus:border-blue-500 focus:outline-none py-2 bg-transparent">
          </div>
          <div>
            <label class="text-sm text-gray-600">Email Address</label>
            <input type="email" class="w-full border-0 border-b border-gray-400 focus:border-blue-500 focus:outline-none py-2 bg-transparent">
          </div>
          <div>
            <label class="text-sm text-gray-600">Phone (optional)</label>
            <input type="text" class="w-full border-0 border-b border-gray-400 focus:border-blue-500 focus:outline-none py-2 bg-transparent">
          </div>
        </div>

        <div class="mt-6">
          <label class="text-sm text-gray-600">Message</label>
          <textarea rows="3" placeholder="Write your message..."
            class="w-full border-0 border-b border-gray-400 focus:border-blue-500 focus:outline-none py-2 bg-transparent resize-none">
          </textarea>
        </div>

        <div class="mt-8">
          <button class="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-700 transition">
            Leave us a Message
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M5 12h14M13 5l7 7-7 7"/>
            </svg>
          </button>
        </div>
      </div>

    <!-- ── Contact Info Band ────────────────────────────────────────────────── -->
    <!-- overflow-y-auto removed — content fits within h-screen -->
    <section
      class="snap-start h-screen w-full relative bg-cover bg-center bg-no-repeat flex items-center"
      style="background-image: url('/images/abstractbg.jpg')"
    >
      <div class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>
      <div class="relative z-10 w-full px-8 md:px-16 py-16">

          <p class="text-sm mb-4">Contact Info</p>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div>
              <h2 class="text-2xl md:text-3xl font-bold leading-tight">
                We are always <br /> happy to assist you
              </h2>
            </div>

            <div class="space-y-3">
              <p class="text-sm uppercase tracking-wide font-medium">Email Address</p>
              <div class="w-10 h-[2px] bg-black"></div>
              <p class="text-base font-medium">{{ contact.email || 'help@info.com' }}</p>
              <div>
                <p class="text-sm text-gray-700">Assistance hours:</p>
                <p class="text-sm text-gray-700">{{ contact.email_hours || 'Monday – Friday 6 am to 8 pm' }}</p>
              </div>
            </div>

            <div class="space-y-3">
              <p class="text-sm uppercase tracking-wide font-medium">Phone Number</p>
              <div class="w-10 h-[2px] bg-black"></div>
              <p class="text-base font-medium">{{ contact.phone || '+63 123 456 7890' }}</p>
              <div>
                <p class="text-sm text-gray-700">Assistance hours:</p>
                <p class="text-sm text-gray-700">{{ contact.phone_hours || 'Monday – Friday 6 am to 8 pm' }}</p>
              </div>
            </div>

          </div>
        </div>
      </div>

    </section>

  </LandingLayout>

  <router-view v-else></router-view>
</template>

<style scoped>
@keyframes pan {
  0%   { transform: scale(1.1) translateX(0%); }
  50%  { transform: scale(1.1) translateX(-5%); }
  100% { transform: scale(1.1) translateX(0%); }
}
.animate-pan {
  animation: pan 20s ease-in-out infinite;
}
</style>

<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue'
import { useRoute } from 'vue-router'
import { computed, ref } from 'vue'

const route = useRoute()

const props = defineProps({
    hero: {
        type: Object,
        default: () => ({
            tagline:              'Discover the beauty of',
            barangay:             'Bel-is',
            mun_prov:             'Buruanga, Aklan',
            sub:                  'Explore nature, culture, and hidden destinations',
            background_image_url: null,
        }),
    },
    contact: {
        type: Object,
        default: () => ({
            email:       'help@info.com',
            phone:       '+63 123 456 7890',
            email_hours: 'Monday – Friday 6 am to 8 pm',
            phone_hours: 'Monday – Friday 6 am to 8 pm',
        }),
    },
    attractions: {
        type: Array,
        default: () => [],
    },
})

const heroBackgroundUrl = computed(() =>
    props.hero.background_image_url ?? '/images/bg1.jpg'
)

// Attractions pagination
const PER_PAGE       = 6
const currentPage    = ref(1)
const totalPages     = computed(() => Math.max(1, Math.ceil(props.attractions.length / PER_PAGE)))
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
</script>