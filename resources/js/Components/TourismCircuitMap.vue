<template>
  <section id="map" data-section="map" class="bg-gray-50 py-10">
    <div class="max-w-5xl 3xl:max-w-6xl 4xl:max-w-7xl mx-auto px-4 3xl:px-8 4xl:px-12">

      <!-- Section Header -->
      <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl 3xl:text-4xl 4xl:text-5xl font-semibold text-gray-900">Plan Your Circuit</h2>
        <p class="text-gray-500 text-sm 3xl:text-base 4xl:text-lg mt-1">Choose destinations within Brgy. Belis to build
          your own tour route.</p>
      </div>

      <!-- MAP WRAPPER -->
      <div
      class="relative h-[500px] 3xl:h-[650px] 4xl:h-[800px] rounded-2xl overflow-hidden border border-gray-200 shadow-sm w-full">


        <!-- MAP -->
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m46!1m12!1m3!1d14133.29833504586!2d121.87993414031065!3d11.87366831230851!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m31!3e0!4m5!1s0x33a539f299060e7d%3A0x72b05f34bce9730e!2sBel-is%20Cove%20Beach%20Resort%2C%20VVJM%2B382%2C%20Brgy%2C%20Bel-is%2C%20Buruanga%2C%20Aklan!3m2!1d11.8801399!2d121.88326579999999!4m5!1s0x33a5398ba13d1e11%3A0x53bb7fe427bfedb8!2sBrgy.%20Bel-is%2C%20Cuyo%20Sea!3m2!1d11.8814373!2d121.88260919999999!4m5!1s0x33a5471291aac99b%3A0x6ccf2fb67aa35161!2sTuburan%20Cove%20Beach%20Resort%2C%20Hinugtan%20Beach%20Road%2C%20Buruanga%2C%205609%20Aklan!3m2!1d11.866201799999999!2d121.880917!4m5!1s0x33a547768825d985%3A0xac20318c0dbac03!2sHinugtan%20White%20Beach%20(Juanito&#39;s%20Place)%2C%20Brgy%2C%20Buruanga%2C%205609%20Aklan!3m2!1d11.868233199999999!2d121.8798559!4m5!1s0x33a539f299060e7d%3A0x72b05f34bce9730e!2sBel-is%20Cove%20Beach%20Resort%2C%20VVJM%2B382%2C%20Brgy%2C%20Bel-is%2C%20Buruanga%2C%20Aklan!3m2!1d11.8801399!2d121.88326579999999!5e1!3m2!1sen!2sph!4v1777262587143!5m2!1sen!2sph"
          class="absolute inset-0 w-full h-full border-0" loading="lazy" allowfullscreen
          referrerpolicy="no-referrer-when-downgrade" />

        <!-- FLOATING PANEL -->
        <div
          class="absolute z-20
           bottom-0 left-0 right-0
           sm:top-7 sm:right-3 sm:left-auto sm:bottom-auto
           w-full sm:w-[300px] sm:3xl:w-[380px] 4xl:w-[450px]
           max-h-[85%] sm:h-[calc(100%-1.5rem)]
           bg-white rounded-t-2xl sm:rounded-xl
           shadow-xl border border-gray-100
           flex flex-col overflow-hidden">
           
          <div v-show="!selectedStop" class="flex flex-col h-full">

            <!-- HEADER -->
            <div class="p-4 border-b border-gray-100 flex-shrink-0">
              <div class="text-xl 3xl:text-2xl 4xl:text-3xl font-semibold text-black leading-tight">
                Belis Community Tourism Circuit
              </div>

              <div class="text-xs mt-3 text-gray-400 mt-0.5">
                {{ stops.length }} stop{{ stops.length !== 1 ? 's' : '' }} selected
              </div>

              <!-- SEARCH -->
              <div class="mt-3">
                <div
                  class="flex items-center gap-2 bg-gray-50 border rounded-lg px-3 py-2 3xl:px-4 3xl:py-3 4xl:px-5 4xl:py-4"
                  :class="searchFocused ? 'border-blue-400 ring-1 ring-blue-100' : 'border-gray-200'">

                  <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z" />
                  </svg>
                  <input v-model="searchQuery" type="text" placeholder="Search destinations…" class="flex-1 text-xs 3xl:text-sm 4xl:text-base bg-transparent text-gray-700 placeholder-gray-400 min-w-0
              outline-none focus:outline-none
              ring-0 focus:ring-0
              border-0 focus:border-0" @focus="searchFocused = true" @blur="onSearchBlur" />
                  <button v-if="searchQuery" @mousedown.prevent @click="searchQuery = ''"
                    class="text-gray-300 text-xs 3xl:text-sm 4xl:text-base px-3 py-1.5 3xl:px-4 3xl:py-2 hover:text-gray-500">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg> </button>
                </div>
              </div>

              <!-- ACTIONS -->
              <div class="flex gap-2 mt-3">
                <button @click="openInGoogleMaps" :disabled="stops.length < 1"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white rounded-full px-3 py-1.5 text-xs 3xl:text-sm 4xl:text-base font-medium transition-colors flex items-center justify-center gap-1.5">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4" />
                  </svg> Open in Maps </button>
                <button @click="clearAll" :disabled="stops.length === 0"
                  class="bg-gray-100 hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed border border-gray-200 rounded-full px-3 py-1.5 text-xs 3xl:text-sm 4xl:text-base text-gray-600 transition-colors">
                  Clear </button>
              </div>
            </div>

            <!-- STOPS LIST -->
            <div class="flex-1 overflow-y-auto">

              <!-- EMPTY -->
              <div v-if="stops.length === 0">
                <p class="text-[10px] font-semibold text-gray-400 uppercase px-4 pt-3 pb-2">
                  Available Destinations
                </p>

                <button v-for="dest in filteredDestinations" :key="dest.id" @click="addStop(dest)"
                  class="w-full flex items-center text-left gap-3 px-3 py-2.5 hover:bg-blue-50 border-b border-gray-50">
                  <img :src="dest.photo" class="w-10 h-10 rounded-lg object-cover" />
                  <div class="flex-1">
                    <p class="text-xs 3xl:text-sm 4xl:text-base font-semibold">{{ dest.name }}</p>
                    <p class="text-[10px] 3xl:text-xs 4xl:text-sm text-gray-400">{{ dest.type }}</p>
                  </div>
                </button>

                <!-- Optional: No results -->
                <p v-if="searchQuery && filteredDestinations.length === 0"
                  class="text-xs 3xl:text-sm 4xl:text-base text-gray-400 px-4 py-3">
                  No destinations found for "{{ searchQuery }}"
                </p>
              </div>

              <!-- STOP LIST -->
              <div v-else>

                <!-- SEARCH MODE -->
                <div v-if="searchQuery">
                  <p class="text-[10px] font-semibold text-gray-400 uppercase px-4 pt-3 pb-2">
                    Search Results
                  </p>

                  <button v-for="dest in filteredDestinations" :key="dest.id" @click="addStop(dest)"
                    class="w-full flex items-center text-left gap-3 px-3 py-2.5 hover:bg-blue-50 border-b border-gray-50">
                    <img :src="dest.photo" class="w-10 h-10 rounded-lg object-cover" />

                    <div class="flex-1">
                      <p class="text-xs 3xl:text-sm 4xl:text-base font-semibold">{{ dest.name }}</p>
                      <p class="text-[10px] 3xl:text-xs 4xl:text-sm text-gray-400">{{ dest.type }}</p>
                    </div>
                  </button>

                  <!-- No results -->
                  <p v-if="filteredDestinations.length === 0"
                    class="text-xs 3xl:text-sm 4xl:text-base text-gray-400 px-4 py-3">
                    No destinations found for "{{ searchQuery }}"
                  </p>
                </div>

                <!-- NORMAL MODE (your stops) -->
                <div v-else>
                  <p class="text-[10px] font-semibold text-gray-400 uppercase px-4 pt-3 pb-1">
                    Your Circuit · drag to reorder
                  </p>

                  <div v-for="(stop, i) in stops" :key="stop.id" draggable="true" @dragstart="onDragStart(i)"
                    @dragover.prevent="onDragOver(i)" @drop="onDrop(i)" @dragend="onDragEnd" :class="[
                      'flex items-start gap-2.5 px-3 py-3 hover:bg-gray-50 cursor-pointer transition-opacity',
                      dragging && dragOverIndex === i && dragIndex !== i ? 'opacity-40' : ''
                    ]" @click="openStopDetail(stop, i)">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] text-white"
                      :class="bubbleColor(i)">
                      {{ i + 1 }}
                    </div>

                    <img :src="stop.photo" class="w-10 h-10 rounded-lg object-cover" />

                    <div class="flex-1">
                      <p class="text-xs 3xl:text-sm 4xl:text-base font-semibold">{{ stop.name }}</p>
                      <p class="text-[10px] 3xl:text-xs 4xl:text-sm text-gray-400">{{ stop.type }}</p>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- FOOTER -->
            <div class="p-2.5 border-t bg-gray-50">
              <p class="text-[10px] 3xl:text-xs 4xl:text-sm text-gray-400 text-center">
                ASU-Ibajay trained guides · Brgy. Belis
              </p>
            </div>

          </div>

          <!-- ===================== -->
          <!-- DETAIL VIEW (OVERLAY INSIDE PANEL) -->
          <!-- ===================== -->
          <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="selectedStop" class="absolute inset-0 bg-white flex flex-col z-30">

              <!-- IMAGE -->
              <div class="relative h-52">
                <img :src="selectedStop.photo" class="w-full h-full object-cover" />

                <button @click="selectedStop = null"
                  class="absolute top-3 right-3 w-8 h-8 bg-black/40 text-white rounded-full">
                  ✕
                </button>
              </div>

              <!-- CONTENT -->
              <div class="p-5 flex-1 overflow-y-auto">

                <h3 class="text-lg font-bold">{{ selectedStop.name }}</h3>

                <p class="text-xs text-gray-400 mt-2">
                  {{ selectedStop.address }}
                </p>

                <p class="text-sm text-gray-600 mt-3">
                  {{ selectedStop.description }}
                </p>

                <div class="mt-4 bg-blue-50 p-3 rounded-xl">
                  <p class="text-xs 3xl:text-sm 4xl:text-base font-semibold text-blue-600">
                    Suggested Activity
                  </p>
                  <p class="text-sm 3xl:text-base 4xl:text-lg text-blue-800">
                    {{ selectedStop.activity }}
                  </p>
                </div>

                <div class="flex gap-2 mt-5">
                  <a :href="`https://maps.google.com/?q=${encodeURIComponent(selectedStop.name)}`" target="_blank"
                    class="flex-1 bg-blue-600 text-white text-sm 3xl:text-base 4xl:text-lg rounded-full py-2.5 text-center">
                    View on Maps
                  </a>

                  <button @click="selectedStop = null"
                    class="px-4 py-2.5 bg-gray-100 rounded-full text-sm 3xl:text-base 4xl:text-lg">
                    Close
                  </button>
                </div>

              </div>

            </div>
          </Transition>

        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue'

// ─── HARDCODED DESTINATIONS ───────────────────────────────────────────────────
const DESTINATIONS = [
  {
    id: 1,
    name: 'Bel-is Cove Beach Resort',
    type: 'Beach Resort · Welcome Hub',
    rating: '4.8',
    reviews: '37',
    address: 'Brgy. Bel-is, Buruanga, Aklan',
    description: 'Start point. Community welcome, local breakfast, orientation by trained tour guides. Base for booking and information.',
    activity: 'Enjoy the resort\'s coastal views, have a traditional Aklanon breakfast, and join the community orientation before your circuit begins.',
    tags: ['Welcome Hub', 'Breakfast', 'Orientation', 'Sunset Spot'],
    photo: '/images/beliscove.jpg',
    lat: 11.8801399,
    lng: 121.8832658,
    mapsQuery: 'Bel-is+Cove+Beach+Resort+Buruanga+Aklan',
  },
  {
    id: 2,
    name: 'Brgy. Belis Fishing Village & Coastal Walk',
    type: 'Community · Cultural Site',
    rating: null,
    reviews: null,
    address: 'Coastal area, Brgy. Bel-is, Buruanga, Aklan',
    description: 'Guided walk through the fishing village. Visitors learn about the community\'s livelihood — fishing, boat-making, and traditional practices. Introduction to local cuisine ingredients.',
    activity: 'Walk alongside local fishermen, learn how bangkas are built, and pick fresh catch that will be cooked for your noon meal.',
    tags: ['Cultural Tour', 'Fishing Demo', 'Boat-making', 'Cuisine Prep'],
    photo: '/images/fishingvillage.jpg',
    lat: 11.8814373,
    lng: 121.8826092,
    mapsQuery: 'Brgy+Belis+Buruanga+Aklan',
  },
  {
    id: 3,
    name: 'Tuburan Cove — Snorkeling & Marine Life Tour',
    type: 'Cove · Snorkeling Site',
    rating: '4.7',
    reviews: '123',
    address: 'Hinugtan Beach Road, Buruanga, Aklan',
    description: 'Guided snorkeling stop. Guides explain coral reef conservation and marine biodiversity. Ideal for the nature walk and eco-tourism component.',
    activity: 'Gear up and snorkel through crystal-clear waters. Your ASU-Ibajay trained guide will point out coral species and marine life unique to this cove.',
    tags: ['Snorkeling', 'Marine Life', 'Eco-Tour', 'Coral Reef'],
    photo: '/images/tuburan.jpg',
    lat: 11.8662018,
    lng: 121.880917,
    mapsQuery: 'Tuburan+Cove+Buruanga+Aklan',
  },
  {
    id: 4,
    name: 'Hinugtan White Beach — Site Hopping, Lunch & Leisure',
    type: 'White Sand Beach',
    rating: '4.5',
    reviews: '64',
    address: 'Brgy. Bel-is, Buruanga, Aklan',
    description: 'Main beach attraction of Brgy. Belis — white sand, clear waters. Guides serve local cuisine lunch here. Free swim, kayaking, and banana boat available. Environmental fee supports barangay.',
    activity: 'Swim in turquoise waters, kayak along the shoreline, enjoy a fresh kinilaw lunch prepared by community cooks, and explore nearby coves by boat.',
    tags: ['White Beach', 'Swimming', 'Kayaking', 'Banana Boat', 'Local Cuisine'],
    photo: '/images/hinugtan.jpg',
    lat: 11.8682332,
    lng: 121.8798559,
    mapsQuery: 'Hinugtan+White+Beach+Buruanga+Aklan',
  },
  {
    id: 5,
    name: 'Bel-is Cove — Sunset & Closing Program',
    type: 'Beach Resort · Sunset Spot',
    rating: '4.8',
    reviews: '37',
    address: 'Brgy. Bel-is, Buruanga, Aklan',
    description: 'Return to base. Closing cultural program — local storytelling, souvenir crafts, and sunset viewing. Visitors can purchase local products before departure.',
    activity: 'Settle in for one of the most stunning sunsets in Aklan. Listen to local stories from community elders, browse handmade souvenirs, and say goodbye to Brgy. Belis.',
    tags: ['Sunset Viewing', 'Storytelling', 'Souvenirs', 'Closing Program'],
    photo: '/images/belissunset.jpg',
    lat: 11.8801399,
    lng: 121.8832658,
    mapsQuery: 'Bel-is+Cove+Beach+Resort+Buruanga+Aklan',
  },
]

// Approximate travel times between stop IDs (id_a-id_b)
const TRAVEL_TIMES_MAP = {
  '1-2': '~5 min walk along coast',
  '2-3': '~10 min by boat',
  '3-4': '~5 min walk / boat',
  '4-5': '~10 min by boat',
  '1-3': '~15 min by boat',
  '1-4': '~20 min by boat',
  '2-4': '~15 min by boat',
  '3-5': '~15 min by boat',
  '2-5': '~15 min by boat',
  '4-1': '~20 min by boat',
}

// ─── STATE ────────────────────────────────────────────────────────────────────
const stops = ref([])
const searchQuery = ref('')
const searchFocused = ref(false)
const selectedStop = ref(null)
const selectedStopIndex = ref(-1)
const dragIndex = ref(-1)
const dragOverIndex = ref(-1)
const dragging = ref(false)

// ─── COMPUTED ─────────────────────────────────────────────────────────────────
const filteredDestinations = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return DESTINATIONS.filter(d => !stops.value.find(s => s.id === d.id))
  return DESTINATIONS.filter(d =>
    !stops.value.find(s => s.id === d.id) &&
    (d.name.toLowerCase().includes(q) ||
      d.type.toLowerCase().includes(q) ||
      d.tags.some(t => t.toLowerCase().includes(q)))
  )
})

const travelTimes = computed(() =>
  stops.value.map((stop, i) => {
    if (i === 0) return null
    const prev = stops.value[i - 1]
    const key1 = `${prev.id}-${stop.id}`
    const key2 = `${stop.id}-${prev.id}`
    return TRAVEL_TIMES_MAP[key1] || TRAVEL_TIMES_MAP[key2] || '~short distance'
  })
)

// ─── HELPERS ──────────────────────────────────────────────────────────────────
const BUBBLE_COLORS = [
  'bg-emerald-500', 'bg-amber-500', 'bg-teal-500',
  'bg-sky-500', 'bg-rose-500', 'bg-violet-500',
]
const bubbleColor = (i) => BUBBLE_COLORS[i % BUBBLE_COLORS.length]

// ─── ACTIONS ──────────────────────────────────────────────────────────────────
function addStop(dest) {
  if (stops.value.find(s => s.id === dest.id)) return
  stops.value.push({ ...dest })
  searchQuery.value = ''
  searchFocused.value = false
}

function removeStop(i) {
  stops.value.splice(i, 1)
}

function clearAll() {
  stops.value = []
}

function openStopDetail(stop, i) {
  selectedStop.value = stop
  selectedStopIndex.value = i
}

function openInGoogleMaps() {
  if (stops.value.length === 0) return
  if (stops.value.length === 1) {
    window.open(`https://maps.google.com/?q=${stops.value[0].mapsQuery}`, '_blank')
    return
  }
  const o = stops.value[0]
  const d = stops.value[stops.value.length - 1]
  const wps = stops.value.slice(1, -1).map(s => `${s.lat},${s.lng}`).join('|')
  window.open(
    `https://www.google.com/maps/dir/?api=1`
    + `&origin=${o.lat},${o.lng}`
    + `&destination=${d.lat},${d.lng}`
    + (wps ? `&waypoints=${wps}` : '')
    + `&travelmode=driving`,
    '_blank'
  )
}

function onSearchBlur() {
  setTimeout(() => { searchFocused.value = false }, 150)
}

// ─── DRAG & DROP ──────────────────────────────────────────────────────────────
function onDragStart(i) {
  dragIndex.value = i
  dragging.value = true
}
function onDragOver(i) {
  dragOverIndex.value = i
}
function onDrop(i) {
  if (dragIndex.value !== -1 && dragIndex.value !== i) {
    const items = [...stops.value]
    const [moved] = items.splice(dragIndex.value, 1)
    items.splice(i, 0, moved)
    stops.value = items
  }
}
function onDragEnd() {
  dragIndex.value = -1
  dragOverIndex.value = -1
  dragging.value = false
}
</script>